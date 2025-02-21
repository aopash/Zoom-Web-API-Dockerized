<?php
//trying to log errors to file instead of console
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/logs/php-error.log');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method for bearer token generation.");
    echo json_encode(['error' => 'Invalid request method.']);
    exit;
}

//user input params
$clientId = $_POST['clientId'] ?? '';
$clientSecret = $_POST['clientSecret'] ?? '';
$accountId = $_POST['accountId'] ?? '';

//check if user inputs are provided and encodes credentials for header
if ($clientId && $clientSecret && $accountId) {
    $credentials = base64_encode("$clientId:$clientSecret");

    //preps header for requesting token, after sends data to post
    $headers = [
        "Authorization: Basic $credentials",
        "Content-Type: application/x-www-form-urlencoded"
    ];
    $data = http_build_query([
        'grant_type' => 'account_credentials',
        'account_id' => $accountId
    ]);

    //initiates curl for token request and captures responsecodes
    $ch = curl_init("https://zoom.us/oauth/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);





    //if generation successful, output to window, or log errors and print to console/window
    if ($httpCode == 200) {
        echo $response;
    } else {
        error_log("Failed to generate bearer token: $response");
        echo json_encode(['error' => 'Failed to generate token', 'response' => $response]);


    }
} else {
    error_log("Missing client credentials for bearer token generation.");
    echo json_encode(['error' => 'Client credentials are required.']);
}
