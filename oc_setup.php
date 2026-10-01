<?php

require_once('../config.php');
include 'tool-config_dist.php';
require_once "utils.php";
include 'tool-header.html';

session_start();
$session_id = session_id();

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

        $fullurl = $tool['middleware_opencasturl'].'personal';

        $createPersonalSeries = postWithBasicAuth($fullurl, $tool['middleware_username'], $tool['middleware_password'], ['eid' => $_SESSION['userid']]);

        if ($createPersonalSeries['success'] && $createPersonalSeries['httpCode'] == 200) {
            if (!empty($createPersonalSeries['data']['data'])) {
                $new_series_id = $createPersonalSeries['data']['data']['identifier'];

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
<section class="section" id="section">

    <div class="row">
	 <div class="col-sm-1"></div>

        <div class="col-sm-12 col-lg-10">
	    <div class="text-center">
                <h1>Welcome to My Videos!</h1>
                <h4><b><!--<span class="myvideos">"My Videos"</span> is--> A personal workspace where you can manage your videos.</b></h4>
	    </div>

	    <div class="row text-center">
	        <div class="col-xs-12 col-sm-6 col-md-3">
		    <div class="panel">
                        <div class="panel-body">
                            <img src="images/upload-videos.svg" title="upload videos" alt="upload videos"/><br/><br/>
                            <span><b>Upload Videos</b></span><br/>
                            You can Upload videos from your desktop
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3">
		     <div class="panel">
                        <div class="panel-body">
                            <img src="images/record-videos.svg" title="record videos" alt="record videos"/><br/><br/>
                            <span><b>Record Videos</b></span><br/>
                            You can record videos from Opencast Studio
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-md-3">
		     <div class="panel">
                        <div class="panel-body">
                            <img src="images/manage-videos.svg" title="manage videos" alt="manage videos"/><br/><br/>
                            <span><b>Manage Videos</b></span><br/>
                            You can edit, watch and delete your videos
                        </div>
                    </div>
                </div>

		<div class="col-xs-12 col-sm-6 col-md-3">
                    <div class="panel">
                        <div class="panel-body">
                            <img src="images/publish-videos.svg" title="publish videos" alt="publish videos"/><br/><br/>
                            <span><b>Publish Videos</b></span><br/>
                            You can publish your videos to other sites
                        </div>
                    </div>
                </div>
	    </div>


	    <div class="panel">
                <div class="panel-heading terms-heading">
                   <h3>Terms of Use</h3>
                </div>
                <div class="panel-body terms-body">
		    <div class="">
			<?php if ($_SESSION['is_valid_user']) { ?>
			<h4 class="terms text-center"><b>Please read the following terms carefully.</b></h4>
			<?php } ?>
                        <p><span>All videos uploaded, recorded, or managed through this tool must be <b>directly related to teaching, learning, or
                            academic research</b>.This tool <b>may not be used</b> to upload or manage personal content intended for personal non-academic
                            platforms</b>. Misuse of this tool for personal gain or content unrelated to your role as a staff member or student may result
                            in restricted access or further action. You are responsible for ensuring your content complies with these terms and institutional
	    		    policies on appropriate use of resources.</span></p>

			<?php if ($_SESSION['is_valid_user']) { ?>
                        <p><span>By clicking <b>"I acknowledge and agree to the Terms of Use for My Videos "</b> below, you agree that you have read and
			    understood these terms. If you do not agree to these terms of use, you do not have permission to use this tool.</span></p>
			<?php } ?>
		    </div>
		    <?php if ($_SESSION['is_valid_user']) { ?>
		    <div class="checkbox text-center">
                        <label>
                            <input type="checkbox" id="cbxAgree"><b> I acknowledge and agree to the Terms of Use for My Videos</b>.
                        </label>
		    </div>
		     <?php } ?>
		</div>

            <?php if ($_SESSION['is_valid_user']) { ?>
                <form class="form-inline text-center" method="post" target="_self" id="frmActivate">
                    <button id="activate" class="btn btn-success" type="submit" name="activate"><i class="fa fa-check"></i> Activate My Videos</button>
                    <span id="info" class="text-info" style="display:none;"><small>This might take a couple of seconds.</small></span>
                    <div class="col-xs-12" id="message"></div>
                </form>
     		</div>
            <?php } ?>
	    </div>

	    <div class="modal d2l-dialog-flex" id="activationModal" tabindex="-1" aria-labelledby="activationModalLabel" >
  		<div class="modal-dialog">
    		    <div class="modal-content d2l-dialog-body">
      			<div class="modal-body d2l-dialog-padding rounded text-center" id="modalFeedback">
          		    <div id="modalIcon" class="d2l-textblock">
            		        <i class="fa fa-cog fa-spin fa-2x text-primary"></i>
          		    </div>
			    <p id="modalMessage">Activating "My Videos"...</p>
			    <button type="button" class="btn btn-primary rounded" id="modalBtn" style="display:none;">Get Started</button>
			    <input type="hidden" name="launchLTI" value="1">
			    <input type="hidden" name="seriesid" id="seriesid" value="">

      	    		</div>

    		     </div>
  		</div>
	    </div>
	</div>
	</div>

	<div class="col-sm-1"></div>
    </div>

</section>

<?php
$OUTPUT->flashMessages();
$OUTPUT->footerStart();
?>
<script>
$(document).ready(function () {

    const sessionId = "<?php echo session_id(); ?>";

    $('#cbxAgree').on('change', function (event) {
      event.preventDefault();
      $('#activate').prop('disabled', !this.checked);
    });


    $('#frmActivate').on('submit', function (e) {
      e.preventDefault();

      $('#modalIcon').html('<i class="fa fa-cog fa-spin fa-3x text-primary"></i>');
      $('#modalMessage').text('Activating "My Videos...');
      $('#modalBtn').hide();
      $('#activationModal').modal('show');

      $.ajax({
        type: 'POST',
        url: 'process.php',
	dataType: 'json',
	headers: { 'X-Session-ID': sessionId },
	data: { activate: true, ajax: true },
	success: function (response) {
	    if (response.success) {
                $('#modalIcon').html('<i class="fa fa-check-circle fa-3x text-success"></i>');
		$('#modalMessage').text('Activation successful! Click "Get Started" to continue.');
		$('#getStarted').show();
		$('#modalBtn').removeClass('btn-primary').addClass('btn-success get-started');
		$('#modalBtn').show();
		$('#seriesid').val(response.sid);
            } else {
                $('#modalIcon').html('<i class="fa fa-times-circle fa-3x text-danger"></i>');
                $('#modalMessage').text('Activation failed. Please try again later.');
		$('#modalBtn').addClass('cancel');
		$('#modalBtn').text('Ok');
		$('#modalBtn').show();
           }
        },
	error: function (xhr, status, error) {
            $('#modalIcon').html('<i class="fa fa-times-circle fa-2x text-danger"></i>');
            $('#modalMessage').text('Something went wrong. Please try again.');
	    $('#modalBtn').addClass('cancel');
            $('#modalBtn').text('Ok');
	    $('#modalBtn').show();
	}
      });

    });

    $('#modalBtn').on('click', function (e) {
	e.preventDefault();
	const $btn = $(this);

        if ($btn.hasClass('cancel')) {
            location.reload();
        } else if ($btn.hasClass('get-started')) {
	    var seriesId = $('#seriesid').val();
	    $('#activationModal').modal('hide');

    	    $.ajax({
      	        type: 'POST',
	        url: 'process.php',
	        headers: { 'X-Session-ID': sessionId },
      	        data: { launchLTI: true, seriesid: seriesId },
      	        dataType: 'html',
      	        success: function (response) {
                    $('#section').html(response);
      	        },
      	        error: function () {
          	    $('#modalIcon').html('<i class="fa fa-times-circle fa-2x text-danger"></i>');
		    $('#modalMessage').text('Unable to launch My Videos. Please try again.');
		    $('#modalBtn').addClass('cancel');
                    $('#modalBtn').text('Ok');
          	    $('#modalBtn').show();
		}
	    });
	}
    });
  });
</script>
<?php
$OUTPUT->footerEnd();
?>
