<?php
header('Content-Type: application/json');

$requestPayload = file_get_contents('php://input');
$data = json_decode($requestPayload, true);

$bearer_token = $data['bearer_token'] ?? '';
$meeting_id = $data['meeting_id'] ?? '';

if (empty($bearer_token)) {
    echo json_encode(['error' => 'Bearer token is required.']);
    exit;
}

if (empty($meeting_id)) {
    echo json_encode(['error' => 'Meeting ID is required.']);
    exit;
}

$url = "https://eu01api-www4local.zoom.us/v2/meetings/$meeting_id";
$headers = [
    "Authorization: Bearer $bearer_token",
    "Content-Type: application/json"
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['error' => "cURL Error: $err"]);
    exit;
}

$data = json_decode($response, true);
if (isset($data['id'])) {
    echo json_encode($data);
} else {
    echo json_encode(['error' => 'Failed to retrieve meeting data.']);
}
exit;

