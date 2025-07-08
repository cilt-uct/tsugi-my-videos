<?php
include 'tool-config_dist.php';

function is_valid_user($eid, $role) {
    $eid = strtolower($eid);
    $role = strtolower($role);
    $invalid_roles = ['guest', 'admin', 'super administrator', 'thirdparty'];

    // Rule 1: Disallow usernames that start with "admin"
    if (preg_match('/^admin[a-z]{2,}$/', $eid)) {
        return false;
    }

    // Rule 2: Disallow users with certain roles
    if (in_array($role, $invalid_roles)) {
        return false;
    }

    // Rule 3: Disallow test student accounts
    if ($role === 'student') {
         // Block common test student accounts
        $test_patterns = [
            '/test/',                          // matches any 'test' or 'TEST'
            '/one_button_studio/',             // special test account with student role
            '/student\d+/',                    // e.g. student001 or STUDENT001
            '/^[a-z]+_[a-z]+$/',               // matches name_surname
            '/^[a-z]+\.[a-z]+$/',              //  matches name.surname
        ];

        foreach ($test_patterns as $pattern) {
            if (preg_match($pattern, $eid)) {
                return false;
            }
        }

    }

    return true;
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
