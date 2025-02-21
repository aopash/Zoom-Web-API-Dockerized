<?php
//logs to console work, logs to docker container work, but cannot write log to logfile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    //first check is token provided, if not prompt error 
    if (empty($_POST['bearer_token'])) {
        echo json_encode(['error' => 'Missing Bearer Token.']);
        error_log('Bearer Token is missing from the request.');
        exit;
    }

    //checks if file uploaded, if not prompts erorr 
    if (empty($_FILES['csv_file']['tmp_name']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        echo json_encode(['error' => 'Missing or invalid CSV file.']);
        error_log('CSV file is missing or invalid in the request.');
        exit;
    }

    $bearerToken = trim($_POST['bearer_token']);
    $file = $_FILES['csv_file']['tmp_name'];

    //resets counter for network response headers
    $processedCount = 0;
    $deletedCount = 0;

    //process csv and get header row
    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle);

        //if provided file is csv utf-8 with bom headers, i remove
        if (substr($headers[0], 0, 3) === "\xEF\xBB\xBF") {
            $headers[0] = substr($headers[0], 3);
        }

        $identifierIndex = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $processedCount++;
            $identifier = trim($row[$identifierIndex]);

            //skip empty rows
            if (empty($identifier)) {
                error_log("Row $processedCount skipped: Empty identifier.");
                continue;
            }

            //check if first "identifier", first part of the row if its an email if not assume its an id
            $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);

            //request api url
            $apiUrl = "https://eu01api-www4local.zoom.us/v2/users/" . urlencode($identifier) . "?action=delete&encrypted_email=false&transfer_meeting=false&transfer_webinar=false&transfer_recording=false&transfer_whiteboard=false";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $bearerToken",
                "Content-Type: application/json"
            ]);

            //curl it
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            //success check 
            if ($httpCode === 204) {
                $deletedCount++;
                error_log("Row $processedCount: Successfully deleted $identifier.");
            } else {
                error_log("Row $processedCount: Failed to delete $identifier. Response: $response; Error: $error; HTTP Code: $httpCode");
            }

            //keep getting ratelimited, 200ms ratelimit
            usleep(200000);
        }

        fclose($handle);

        echo json_encode([
            'processed' => $processedCount,
            'deleted' => $deletedCount
        ]);
    } else {
        //throw some errors to console and to log
        echo json_encode(['error' => 'Unable to open the uploaded CSV file.']);
        error_log('Failed to open CSV file for processing.');
        exit;
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
    error_log('Invalid request method used.');
}







