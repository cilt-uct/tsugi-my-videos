<?php

require_once "../config.php";
include 'tool-config_dist.php';
require_once "utils.php";

use \Tsugi\Util\U;
use \Tsugi\Util\LTI;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

// Retrieve the launch data if present
$LAUNCH = LTIX::requireData();
$p = $CFG->dbprefix;

$menu = false;
$debug = false;

$allLTIParams = $LAUNCH->ltiRawPostArray();
$site_id = $LAUNCH->ltiRawParameter('context_id','none');
$user_id = $LAUNCH->ltiRawParameter('ext_d2l_username', 'none');
$user_role = $LAUNCH->ltiRawParameter('ext_d2l_role', 'none');

$_SESSION['userid'] = $user_id;
$_SESSION['user_role'] = strtolower($user_role);

if (!(is_valid_user($user_id, $user_role))) {
    $_SESSION['is_valid_user'] = false;
    header( 'Location: '.addSession('oc_setup.php') ) ;
    exit();
} else {
    $_SESSION['is_valid_user'] = true;
    $fullurl = $tool['middleware_opencasturl'] .$user_id . '/personal';
    $personalSeriesDetails = fetchWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password']);
    
    if ($personalSeriesDetails['success'] && $personalSeriesDetails['httpCode'] == 200) {
        if (!empty($personalSeriesDetails['data']['data']['data'])) {
            $series_id = $personalSeriesDetails ['data']['data']['data'][0]['identifier'];
      
            $OUTPUT->header();
            $OUTPUT->bodyStart();
            $OUTPUT->topNav($menu);

            $parms = array(
                'user_id' => $user_id,
                'context_id' => $LAUNCH->ltiRawParameter('context_id'),
                'resource_link_id' => $LAUNCH->link->id,
                'custom_tool' => $LAUNCH->ltiRawParameter('custom_tool'),
                'custom_sid' => $series_id,
                'custom_type' => $LAUNCH->ltiRawParameter('custom_type'),
                'link_id' => $LAUNCH->ltiRawParameter('ext_d2l_link_id'),
                'role' => $LAUNCH->ltiRawParameter('ext_d2l_role'),
                'ext_submit' => 'Launch'
            );

            $parms = LTI::signParameters($parms, $tool['opencast_ltiurl'], 'POST', $tool['opencast_ltikey'], $tool['opencast_ltisecret']);
            $content = LTI::postLaunchHTML($parms, $tool['opencast_ltiurl'], $debug, false);
            echo $content;

            $OUTPUT->footerStart();
            $OUTPUT->footerEnd();

        } else {
            // create a new series if it doesn't exist
            $_SESSION['d2l_launch_params'] = $allLTIParams;
            header( 'Location: '.addSession('oc_setup.php') ) ;	
            exit();
        }
    } else {
	    
        notify_admin($tool, $user_id, "API error response: " . json_encode($personalSeriesDetails));

        if ($personalSeriesDetails['httpCode'] >= 500) {
            $_SESSION['error'] = "My Videos is currently unavailable. Please try again later.";
        } else {
            $_SESSION['error'] = "An unexpected error occurred. Please try again later.";
        }

        $OUTPUT->header();
        $OUTPUT->bodyStart();
        $OUTPUT->topNav($menu);
        $OUTPUT->flashMessages();
        $OUTPUT->footerStart();
        $OUTPUT->footerEnd();

	    exit;
    }
}