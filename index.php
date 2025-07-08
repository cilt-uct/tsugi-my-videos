<?php
require_once "../config.php";
include 'tool-config_dist.php';
require_once "utils.php";

session_start();

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

    // Check if personal series API call was successful
    if (isset($personalSeriesDetails['status']) && $personalSeriesDetails['status'] === 'success') {
        // Proceed only if personal series exists
        if (!empty($personalSeriesDetails['data']['data'])) {
            $username = $personalSeriesDetails ['data']['data'][0]['contributors'][0];
            $series_id = $personalSeriesDetails ['data']['data'][0]['identifier'];
      
            $OUTPUT->header();
            $OUTPUT->bodyStart();
            $OUTPUT->topNav($menu);

            $parms = array(
                'user_id' => $username,
                'context_id' => $LAUNCH->ltiRawParameter('context_id'),
                'resource_link_id' => $LAUNCH->link->id,
                'custom_tool' => $LAUNCH->ltiRawParameter('custom_tool'),
                'custom_sid' => $series_id,
                'custom_type' => $LAUNCH->ltiRawParameter('custom_type'),
                'link_id' => $LAUNCH->ltiRawParameter('ext_d2l_link_id'),
                'role' => $LAUNCH->ltiRawParameter('ext_d2l_role')
            );

            $parms['ext_submit'] = 'Launch';
            $parms = LTI::signParameters($parms, $tool['opencast_ltiurl'], 'POST', $tool['opencast_ltikey'], $tool['opencast_ltisecret']);
            $content = LTI::postLaunchHTML($parms, $tool['opencast_ltiurl'], $debug, false);
            echo $content;

            $OUTPUT->footerStart();
            $OUTPUT->footerEnd();

        } else {
            $_SESSION['d2l_launch_params'] = $allLTIParams;
            header( 'Location: '.addSession('oc_setup.php') ) ;	
            exit();
        }
    } else {

        $OUTPUT->header();
        $OUTPUT->bodyStart();
        $OUTPUT->topNav($menu);
        $OUTPUT->flashMessages();
        $_SESSION['error'] = 'You do not have access to view this page.';
        // TODO: Send an email to admin to fix for user
    
        $OUTPUT->footerStart();
        $OUTPUT->footerEnd();
    }
}
