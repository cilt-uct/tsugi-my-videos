<?php

require_once('../config.php');
include 'tool-config_dist.php';
require_once "utils.php";

session_start();

use \Tsugi\Util\U;
use \Tsugi\Util\LTI;
use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;

$menu = false;
$debug = false;

$OUTPUT->header();
include 'tool-header.html';

if ($_SESSION['is_valid_user']) {

    $d2l_params = $_SESSION['d2l_launch_params'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate'])) {

        $fullurl = $tool['middleware_opencasturl'].'/personal';

        $createPersonalSeries = postWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password'], ['eid' => $_SESSION['userid']]);

        if (isset($createPersonalSeries['status']) && $createPersonalSeries['status'] === 'success') {
            if (!empty($createPersonalSeries['data'])) {
                $new_series_id = $createPersonalSeries['data']['identifier'];

                // Launch My Videos
                $parms = array(
                    'user_id' => $_SESSION['userid'],
                    'resource_link_id' => $d2l_params['resource_link_id'],     
                    'context_id' => $d2l_params['context_id'],
                    'custom_tool' => $d2l_params['custom_tool'],
                    'custom_sid' => $new_series_id,
                    'custom_type' => $d2l_params['custom_type'],
                    'link_id' => $d2l_params['ext_d2l_link_id'],
                    'role' => $d2l_params['ext_d2l_role'],
                    'ext_submit' => $d2l_params['ext_submit'] ?? 'Submit'
                );

                $parms = LTI::signParameters($parms, $tool['opencast_ltiurl'], 'POST', $tool['opencast_ltikey'], $tool['opencast_ltisecret']);
                $content = LTI::postLaunchHTML($parms, $tool['opencast_ltiurl'], $debug, false);
                echo $content;
                exit;
            } else {
                $errorDetails = 'Series creation returned no data for user ' .$_SESSION['userid'];
                notify_admin($_SESSION['userid'], $errorDetails);

                $_SESSION['error'] = 'Something went wrong. Please contact cilt-helpdesk@uct.ac.za for assistance.';
            }
        } else {
            $errorDetails = 'Failed to create personal series for user ' .$_SESSION['userid'];
            notify_admin($_SESSION['userid'], $errorDetails);

            $_SESSION['error'] = 'Something went wrong. Please contact cilt-helpdesk@uct.ac.za for assistance.';
        }
    }
}

$OUTPUT->bodyStart();
$OUTPUT->topNav($menu);

?>
<section>

    <div class="row">
        <div class="col-md-1 col-sm-4 col-xs-6"></div>
        <div class="col-md-10 col-sm-4 col-xs-6">

            <h3 class="text-center">Welcome to My Videos</h3>
            <p class="text-center">"My Videos" is a personal workspace where you can manage your videos.</p>

            <div class="row">
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="card">
                        <div class="card text-center" style="margin: 5px; padding:5px;">
                            <div style="border-radius: 50%; width: 150px;height: 150px;position: relative;
                                box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                                background-image: url(images/upload_videos.png); background-repeat: no-repeat;
                                background-size: cover;margin : 0px auto;">
                                <span class="text-center bg-primary" style="width: 40px;height: 40px;
                                        bottom: -2px;right: -2px;position: absolute; border-radius: 25px;
                                        color:white;padding: 8px;">
                                    <i class="fa fa-upload"></i>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title">Upload videos<br/>
                                <small>You can upload videos from your desktop.</small></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="card text-center" style="margin: 5px; padding:5px;">
                        <div style="border-radius: 50%; width: 150px;height: 150px;position: relative;
                        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                        background-image: url(images/record_videos.png); background-repeat: no-repeat;
                        background-size: cover;margin : 0px auto;">
                            <span class="text-center bg-primary" style="width: 40px;height: 40px;
                                    bottom: -2px;right: -2px;position: absolute; border-radius: 25px;
                                    color:white;padding: 8px;">
                                <i class="fa fa-video"></i>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">Record videos<br/>
                            <small>You can record videos from your desktop or using Opencast Studio.</small></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="card text-center" style="margin: 5px; padding:5px;">
                    <div style="border-radius: 50%; width: 150px;height: 150px; position: relative;
                        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                        background-image: url(images/manage_videos.png); background-repeat: no-repeat;
                        background-size: cover;margin : 0px auto;">
                        <span class="text-center bg-primary" style="width: 40px;height: 40px;
                                bottom: -2px;right: -2px;position: absolute; border-radius: 25px;
                                color:white;padding: 8px;">
                                <i class="fa fa-folder-open"></i>
                        </span>
                    </div>
                    <div class="card-body">
                            <h5 class="card-title">Manage Videos<br/>
                            <small>You can edit, watch and delete your videos.</small></h5>
                    </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 col-xs-6">
                    <div class="card text-center" style="margin: 5px; padding:5px;">
                        <div style="border-radius: 50%; width: 150px;height: 150px;position: relative;
                            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
                            background-image: url(images/publish_videos.png); background-repeat: no-repeat;
                            background-size: cover;margin : 0px auto;">
                            <span class="text-center bg-primary" style="width: 40px;height: 40px;
                                    bottom: -2px;right: -2px;position: absolute; border-radius: 25px;
                                    color:white;padding: 8px;">
                                <i class="fa fa-location-arrow"></i>
                            </span>
                        </div>
                        <div class="card-body">

                            <h5 class="card-title">Publish videos<br/>
                            <small>You can publish your videos to other sites.</small></h5>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($_SESSION['is_valid_user']) { ?>
                <form class="form-inline text-center" method="post" target="_self" id="metadata">
                    <button id="activate" class="btn btn-success" type="submit" name="activate"><i class="fa fa-check"></i> Activate My Videos</button>
                    <span id="info" class="text-info" style="display:none;"><small>This might take a couple of seconds.</small></span>
                    <div class="col-xs-12" id="message"></div>
                </form>
            <?php } ?>
        </div>
        <div class="col-md-1 col-sm-4 col-xs-6"></div>
    </div>

</section>

<?php
$OUTPUT->flashMessages();
$OUTPUT->footerStart();
$OUTPUT->footerEnd();
?>
