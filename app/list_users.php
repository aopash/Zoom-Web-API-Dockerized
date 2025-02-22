<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="zoom_users_lastlogin_9month.csv"');

//retrieve bearertoken from the body
$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);

$bearer_token = $data['bearer_token'] ?? '';
if (empty($bearer_token)) {
    echo json_encode(['error' => 'Bearer token is required.']);
    exit;
}

//prep url for the request
//$url = "https://api.zoom.us/v2/users?page_size=300";
//for correct cluster
$url = "https://eu01api-www4local.zoom.us/v2/users?page_size=300";
$headers = [
    "Authorization: Bearer $bearer_token",
    "Content-Type: application/json"
];

$outputFile = fopen('php://output', 'w');

//output the file with utf-8 bom headers, but probably will remove
fputs($outputFile, "\xEF\xBB\xBF");

//write the csv headers 
$csvHeaders = [
    'id', 'Email', 'First Name', 'Last Name', 'Phone Number', 'Department', 'Manager',
    'User Groups', 'User Type', 'Large Meeting', 'Zoom Webinars', 'Job Title', 'Location', 'Last Login Time'
];
fputcsv($outputFile, $csvHeaders);

//fetch users, and sort, 9 months our lastlogin
$pageNumber = 1;
$nextPageToken = null;
$nineMonthsAgo = strtotime('-9 months');

do {
    $urlWithToken = $nextPageToken ? "$url&next_page_token=$nextPageToken" : $url;
    $ch = curl_init($urlWithToken);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        header('Content-Type: application/json');
        echo json_encode(['error' => "cURL Error: $err"]);
        fclose($outputFile);
        exit;
    }

    $data = json_decode($response, true);
    if (isset($data['users'])) {
        foreach ($data['users'] as $user) {
            if (isset($user['email']) && substr($user['email'], 0, 3) === 'xg0') {
                continue; //skip servicekonto
            }

            $lastLoginTime = $user['last_login_time'] ?? '';
            if ($lastLoginTime) {
                //converts the last login to timestamps
                $lastLoginTimestamp = strtotime($lastLoginTime);

                //if last login was more than 9 month ago, prep the users in more readable format
                if ($lastLoginTimestamp < $nineMonthsAgo) {
                    $userData = [
                        $user['id'],
                        $user['email'],
                        $user['first_name'],
                        $user['last_name'],
                        $user['phone_number'] ?? '',
                        $user['dept'] ?? '',
                        $user['manager'] ?? '',
                        implode(', ', $user['user_groups'] ?? []),
                        $user['type'] ?? '',
                        $user['large_meeting'] ?? '',
                        $user['zoom_webinars'] ?? '',
                        $user['job_title'] ?? '',
                        $user['location'] ?? '',
                        $lastLoginTime,
                    ];

                    //ensures uttf-8 encoding, ty stackoverflow
                    $userData = array_map(function($value) {
                        return mb_convert_encoding($value, 'UTF-8', 'auto');
                    }, $userData);

                    fputcsv($outputFile, $userData);
                }
            }
        }

        $nextPageToken = $data['next_page_token'] ?? null;
        $pageNumber++;
    } 
    else 
        { 
        //errors out 
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to retrieve users.']);
        fclose($outputFile);
        exit;
    }
} while ($nextPageToken);

fclose($outputFile);
exit;

