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

$exclude_filter = $data['exclude_filter'] ?? '';
$exclude_terms = ['webinar', 'zoom', 'meeting', 'room', 'admin', 'localhost'];
if (!empty($exclude_filter)){
    $user_terms = array_filter(array_map('trim', explode(',', $exclude_filter)));
    $exclude_terms = array_merge($exclude_terms, $user_terms);
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

            if (!empty($exclude_terms)){
                $searchIn = [
                    $user['email'] ?? '',
                    $user['first_name'] ?? '',
                    $user['last_name'] ?? '',
                ];
                $shouldExclude = false;
                foreach ($exclude_terms as $term){
                    foreach ($searchIn as $field){
                        if (stripos($field, $term) !== false){
							$shouldExclude = true;
							break 2; //break both loops
						}
                    }
                }
                if ($shouldExclude){
					continue; //skip this user
				}
            }

            $lastLoginTime = $user['last_login_time'] ?? '';
            if ($lastLoginTime) {
                //converts the last login to timestamps
                $lastLoginTimestamp = strtotime($lastLoginTime);

                //if last login was more than 9 month ago, prep the users in more readable format
                if ($lastLoginTimestamp < $nineMonthsAgo) {

                    $userEmail = $user['email'];
                    $settingsUrl = "https://eu01api-www4local.zoom.us/v2/users/" . urlencode($userEmail) . "/settings?custom_query_fields=feature";
                    
                    $chSettings = curl_init($settingsUrl);
                    curl_setopt($chSettings, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($chSettings, CURLOPT_HTTPHEADER, $headers);
                    $settingsResponse = curl_exec($chSettings);
                    $settingsErr = curl_error($chSettings);
                    curl_close($chSettings);

                    if ($settingsErr){
                        error_log('API error for $userEmail: $settingsErr');
                        continue;
                    }

                    $settingsData = json_decode($settingsResponse, true);

                    if (!isset($settingsData['feature'])){
                        continue;
                    }

                    $feature = $settingsData['feature'];

                    $hasLargeMeeting = ($feature['large_meeting'] ?? false) === true;
                    $hasZoomEvent = ($feature['zoom_event'] ?? false) === true;
                    $meetingCapacity = intval($feature['meeting_capacity']) ?? 0;
                    $webinarCapacity = intval($feature['webinar_capacity']) ?? 0;

                    if (
                        $hasLargeMeeting ||
                        $hasZoomEvent ||
                        $meetingCapacity > 500 ||
                        $webinarCapacity > 500
                    ){
                        continue;
                    }

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