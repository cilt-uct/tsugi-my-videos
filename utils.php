<?php
include 'tool-config_dist.php';

function is_valid_user($eid, $role) {
    $eid = strtolower($eid);
    $role = strtolower($role);
    $invalid_roles = ['guest', 'admin', 'super administrator', 'thirdparty'];

    // Rule 1: Disallow users with admin accounts
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
    $maxRetries = 3;
    $retryDelay = 5;
    $retriesUsed = 0;

    while ($retriesUsed < $maxRetries) {
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Basic ' . base64_encode($username . ':' . $password)
        ]);

        // Execute and get the response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for errors
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'success' => false,
                'httpCode' => $httpCode,
                'error' => "cURL error: $error"
            ];
        }

        curl_close($ch);

        if ($httpCode >= 500 && $httpCode < 600) {
            // Server error - retry
            if ($retriesUsed < $maxRetries) {
                $retriesUsed++;
                sleep($retryDelay);
                continue;
            } else {
                return [
                    'success' => false,
                    'httpCode' => $httpCode,
                    'error' => "Server returned HTTP $httpCode after $maxRetries retries.",
                    'retriesUsed' => $retriesUsed
               ];
            }
        }

        $data = json_decode($response, true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
	    return [
                'success' => false,
                'httpCode' => $httpCode,
                'error' => 'JSON decode error: ' . json_last_error_msg(),
                'rawResponse' => $response,
                'retriesUsed' => $retriesUsed
            ];
        }

        if ($httpCode >= 400) {
            return [
                'success' => false,
                'httpCode' => $httpCode,
                'error' => "API returned HTTP $httpCode",
                'data' => $data,
                'retriesUsed' => $retriesUsed
            ];
        }

        return [
            'success' => true,
            'httpCode' => $httpCode,
            'data' => $data,
            'retriesUsed' => $retriesUsed
        ];
    }
}

// send data to middleware using basic auth
function postWithBasicAuth($url, $username, $password, $postData = []) {
    $maxRetries = 3;
    $retryDelay = 5
    $retriesUsed = 0;

    while ($retriesUsed < $maxRetries) {
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'success' => false,
                'httpCode' => $httpCode,
                'error' => "cURL error: $error",
                'retriesUsed' => $retriesUsed
            ];
        }

        curl_close($ch);

        if (in_array($httpCode, [500, 502, 503, 504])) {
            if ($retriesUsed < $maxRetries) {
                $retriesUsed++;
                sleep($retryDelay);
                continue;
            } else {
                return [
                    'success' => false,
                    'httpCode' => $httpCode,
                    'error' => "Server returned HTTP $httpCode after $retriesUsed retries.",
                    'retriesUsed' => $retriesUsed
                ];
            }
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'httpCode' => $httpCode,
                'error' => 'JSON decode error: ' . json_last_error_msg(),
                'rawResponse' => $response,
                'retriesUsed' => $retriesUsed
            ];
        }

        return [
            'success' => true,
            'httpCode' => $httpCode,
            'data' => $data,
            'retriesUsed' => $retriesUsed
        ];
    }
}

function showError($message) {
    global $OUTPUT, $menu;

    $OUTPUT->header();
    $OUTPUT->bodyStart();
    $OUTPUT->topNav($menu);
    $OUTPUT->flashMessages();

    echo "<div class='alert alert-danger' role='alert'>" . htmlspecialchars($message) . "</div>";

    $OUTPUT->footerStart();
    $OUTPUT->footerEnd();
    exit();
}

function notify_admin($tool, $username, $errorDetails) {
    $to = $tool['notification_list'];
    $subject = 'My Videos Error Alert';
    $message = "An error occurred on My Videos tool in Amathuba.\n\n"
             . "User: {$username}\n"
             . "Timestamp: " . date('Y-m-d H:i:s') . "\n"
             . "Details: {$errorDetails}\n\n"
             . "Please investigate the issue.";

    $headers = "From: noreply@tsugi.uct.ac.za\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($to, $subject, $message, $headers);
}

function notify_admin($username, $errorDetails) {
    $to = $tool['notification-list'];
    $subject = 'My Videos Error Alert';
    $message = "An error occurred during personal series fetch or creation.\n\n"
             . "User: {$username}\n"
             . "Timestamp: " . date('Y-m-d H:i:s') . "\n"
             . "Details: {$errorDetails}\n\n"
             . "Please investigate the issue.";

    $headers = "From: noreply@tsugi.uct.ac.za\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    mail($to, $subject, $message, $headers);
}

