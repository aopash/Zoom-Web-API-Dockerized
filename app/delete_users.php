<?php

set_time_limit(0); //setting limit to 0 since it fails after 25min
ini_set('max_execution_time', 0);
ini_set('output_buffering', 'Off');
ob_implicit_flush(true);
ob_end_flush();

if (ob_get_level()) ob_end_clean();
ob_implicit_flush(true); //no output buffer

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid request method.']) . "\n";
    exit;
}

$bearerToken = trim($_POST['bearer_token'] ?? '');
$csvFile = $_FILES['csv_file']['tmp_name'] ?? '';

//pass stream headers
header('Content-Type: text/plain');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');
header('Connection: close');

flush();

if (empty($bearerToken)) {
    echo json_encode(['error' => 'Missing Bearer Token.']) . "\n";
    flush();
    exit;
}

if (empty($csvFile) || !is_uploaded_file($csvFile)) {
    echo json_encode(['error' => 'Missing or invalid CSV file.']) . "\n";
    flush();
    exit;
}


$rows = []; //parse csv file etc
if (($handle = fopen($csvFile, 'r')) !== false) {
    $headers = fgetcsv($handle);
    if (substr($headers[0], 0, 3) === "\xEF\xBB\xBF") {
        $headers[0] = substr($headers[0], 3);
    }
    while (($row = fgetcsv($handle)) !== false) {
        $rows[] = $row;
    }
    fclose($handle);
} else {
    echo json_encode(['error' => 'Unable to read CSV file.']) . "\n";
    flush();
    exit;
}

$total = count($rows);
$deletedCount = 0;

echo json_encode(['status' => 'start', 'total' => $total]) . "\n";
flush();

foreach ($rows as $index => $row) {
    if (connection_aborted()) break;

    $identifier = trim($row[0]);
    if (empty($identifier)) {
        echo json_encode([
            'status' => 'progress',
            'identifier' => '[empty]',
            'success' => false,
            'current' => $index + 1,
            'total' => $total,
            'deleted' => $deletedCount
        ]) . "\n";
        flush();
        usleep(200000);
        continue;
    }

    $apiUrl = "https://eu01api-www4local.zoom.us/v2/users/" . urlencode($identifier) . "?action=delete&transfer_meeting=false&transfer_webinar=false&transfer_recording=false&transfer_whiteboard=false";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $bearerToken",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $success = ($httpCode === 204);
    if ($success) {
        $deletedCount++;
        error_log("SUCCESS: Deleted $identifier");
    } else {
        error_log("FAILED: $identifier - HTTP $httpCode - $response");
    }

//pass progress will fix and make it into a percentage at some point right now it maths deletedcount/total
    echo json_encode([
        'status' => 'progress',
        'identifier' => $identifier,
        'success' => $success,
        'httpCode' => $httpCode,
        'response' => $response,
        'current' => $index + 1,
        'total' => $total,
        'deleted' => $deletedCount
    ]) . "\n";

    flush();
    usleep(200000);
}

if (!connection_aborted()) {
    echo json_encode([
        'status' => 'complete',
        'deleted' => $deletedCount,
        'total' => $total
    ]) . "\n";
    flush();
}

