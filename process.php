<?php
require_once('../config.php');
include 'tool-config_dist.php';
require_once "utils.php";


if (isset($_SERVER['HTTP_X_SESSION_ID'])) {
    session_id($_SERVER['HTTP_X_SESSION_ID']);
}

session_start();

use \Tsugi\Util\U;
use \Tsugi\Util\LTI;
use \Tsugi\Core\LTIX;

$debug = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate'])) {
	
    header('Content-Type: application/json'); 

    if ($_SESSION['is_valid_user']) {	
        $fullurl = $tool['middleware_opencasturl'].'personal';
        $createPersonalSeries = postWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password'], ['eid' => $_SESSION['userid']]);

        if ($createPersonalSeries['success'] && $createPersonalSeries['httpCode'] == 200 && !empty($createPersonalSeries['data']['data'])) {
            $new_series_id = $createPersonalSeries['data']['data']['identifier'];

            echo json_encode(['success' => true, 'sid' => $new_series_id, 'session-user' => $_SESSION['userid']]);
            exit;
        } else {
            $errorDetails = 'Failed to create personal series for user: ' .$_SESSION['userid'];
            notify_admin($tool, $_SESSION['userid'], $errorDetails);
            
            echo json_encode(['success' => false, 'message' => 'Series creation failed']);
            exit;
        }
    } else {
        $errorDetails = 'User: ' .$_SESSION['userid'] . ' is not valid.';
        notify_admin($tool, $_SESSION['userid'], $errorDetails);
        
        echo json_encode(['success' => false,'message' => 'Not a valid user']);
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['launchLTI']) && isset($_POST['seriesid'])) {
    if (isset($_SESSION['is_valid_user']) && $_SESSION['is_valid_user']) {
        $d2l_params = $_SESSION['d2l_launch_params'];	
        $seriesId = $_POST['seriesid'];

        $parms = [
            'user_id' => $_SESSION['userid'],
            'resource_link_id' => $d2l_params['resource_link_id'],
            'context_id' => $d2l_params['context_id'],
            'custom_tool' => $d2l_params['custom_tool'],
            'custom_sid' => $seriesId,
            'custom_type' => $d2l_params['custom_type'],
            'link_id' => $d2l_params['ext_d2l_link_id'],
            'role' => $d2l_params['ext_d2l_role'],
            'ext_submit' => $d2l_params['ext_submit'] ?? 'Submit'
        ];

        $parms = LTI::signParameters($parms, $tool['opencast_ltiurl'], 'POST', $tool['opencast_ltikey'], $tool['opencast_ltisecret']);
        $content = LTI::postLaunchHTML($parms, $tool['opencast_ltiurl'], $debug, false);
	
	    echo $content;
        exit;
    } else {
	    $errorDetails = 'Failed to launch My Videos for user: ' .$_SESSION['userid'];
        notify_admin($tool, $_SESSION['userid'], $errorDetails);
	
	    header('Content-Type: application/json');
        echo json_encode(['success' => false,'message' => 'Session not set']);
	    exit;
    }
}
?>
