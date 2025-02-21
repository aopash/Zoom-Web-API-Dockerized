<?php
session_start();

require 'vendor/autoload.php';

use MongoDB\Client;

header('Content-Type: application/json');
//starts mongo connection
$client = new Client('mongodb://mongo:27017');
$collection = $client->zoom_api->users;

//modify for your institution or setup
$collection->updateOne(
    ['username' => 'admin'],
    ['$set' => ['password' => password_hash('specialinstancepassword', PASSWORD_DEFAULT)]],
    ['upsert' => true]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    //logs post data redacted to container
    error_log("Received POST request: username = $username, password = [REDACTED]");
    $user = $collection->findOne(['username' => $username]);

    if ($user) {
        error_log("User found: " . json_encode($user));

        if (password_verify($password, $user['password'])) {
            error_log("Password verified successfully.");
            $_SESSION['logged_in'] = true;
            $_SESSION['username'] = $username;

	    //send response in json format to redirect client
            echo json_encode(['success' => true, 'redirect' => 'index.html']);
            exit;
        } else {
            error_log("Password verification failed.");
            echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
            exit;
        }
    } else {
        error_log("User not found.");
        echo json_encode(['success' => false, 'error' => 'Invalid credentials.']);
        exit;
    }
}
?>

