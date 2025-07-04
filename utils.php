<?php
include 'tool-config_dist.php';

function is_admin_user($cn) {
    return preg_match('/^admin[a-z]{2,}$/', $cn) === 1;
}

// fetch data from middleware using basic auth
function fetchWithBasicAuth($url, $username, $password) {
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($username . ':' . $password)
    ]);

    // Execute and get the response
    $response = curl_exec($ch);

    // Check for errors
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error);
    }

    curl_close($ch);

    // Decode JSON response into an associative array
    $data = json_decode($response, true);

    // Check for JSON decoding errors
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg(). "\n\nResponse:\n" . $response);
    }

    return $data;
}

// send data to middleware using basic auth
function postWithBasicAuth($url, $username, $password, $postData = []) {
    $ch = curl_init();

    $headers = [
        'Authorization: Basic ' . base64_encode($username . ':' . $password),
        'Content-Type: application/json'
    ];

    // Convert post data to JSON
    $jsonData = json_encode($postData);

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => $headers
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL Error: ' . $error);
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Decode Error: ' . json_last_error_msg());
    }

    return $data;
}
