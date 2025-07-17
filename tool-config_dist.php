<?php
// Configuration file - copy from tool-config_dist.php to tool-config.php
// and then edit.

if ((basename(__FILE__, '.php') != 'tool-config') && (file_exists('tool-config.php'))) {
    include 'tool-config.php';
    return;
}

# The configuration file - stores the paths to the scripts
$tool = array();
$tool['debug'] = FALSE;
$tool['active'] = TRUE; # if false will show coming soon page
$tool['notification-list'] = 'notification-list';

// middleware settings
$tool['middleware_opencasturl'] = 'middleware_url';
$tool['middleware_username'] = 'username';
$tool['middleware_password'] = 'password';

// opencast settings
$tool['opencast_ltiurl'] = 'opencast_url';
$tool['opencast_ltikey'] = 'ltikey';
$tool['opencast_ltisecret'] = 'ltisecret';

# these sites are used for development - so ignore coming soon page
$tool['dev'] = [];

