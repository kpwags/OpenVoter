<?php
/** Define ABSPATH as this files directory */
define( 'ABSOLUTEPATH', dirname(__FILE__) . '/' );


ini_set("include_path", ABSOLUTEPATH);

if ( defined('E_RECOVERABLE_ERROR') )
	error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR);
else
	error_reporting(E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING);

if ( file_exists( ABSOLUTEPATH . 'ov-config.php') ) {

	/** Load the Config File **/
	require_once( ABSOLUTEPATH . 'ov-config.php' );

} else {
	/** No Config File **/
	echo "<p>Error Locating Configuration File</p>";
}

/* Load Class Objects */
require_once( ABSOLUTEPATH . 'ov-include/ovdbconnector.php');

require_once( ABSOLUTEPATH . 'ov-include/ovalerting.php' );
$ovAlerting = new ovAlerting();

require_once( ABSOLUTEPATH . 'ov-include/ovcomment.php' );
$ovComment = new ovComment();

require_once( ABSOLUTEPATH . 'ov-include/ovcontent.php' );
$ovContent = new ovContent();

require_once( ABSOLUTEPATH . 'ov-include/ovcryptography.php' );
$ovCryptography = new ovCryptography();

require_once( ABSOLUTEPATH . 'ov-include/ovlist.php' );
$ovList = new ovList();

require_once( ABSOLUTEPATH . 'ov-include/ovmetatags.php' );
$ovMetaTags = new ovMetaTags();

require_once( ABSOLUTEPATH . 'ov-include/ovrss.php' );
$ovRSS = new ovRSS();

require_once( ABSOLUTEPATH . 'ov-include/ovsettings.php' );
$ovSettings = new ovSettings();

$theme_dir = $ovSettings->ThemeDirectory();
define( 'THEMEDIR', 'ov-content/themes/' . $theme_dir );

if (is_dir(ABSOLUTEPATH . THEMEDIR . "/mobile")) {
	define(MOBILEEXISTS, true);
} else {
	define(MOBILEEXISTS, false);
}

require_once( ABSOLUTEPATH . 'ov-include/ovsubmission.php' );
$ovSubmission = new ovSubmission();

require_once( ABSOLUTEPATH . 'ov-include/ovtheming.php');

require_once( ABSOLUTEPATH . 'ov-include/ovuser.php' );
$ovUser = new ovUser();

require_once( ABSOLUTEPATH . 'ov-include/ovusersecurity.php' );
$ovUserSecurity = new ovUserSecurity();

require_once( ABSOLUTEPATH . 'ov-include/ovusersettings.php' );
$ovUserSettings = new ovUserSettings();

require_once( ABSOLUTEPATH . 'ov-include/ovutilities.php' );
$ovUtilities = new ovUtilities();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadminsecurity.php' );
$ovAdminSecurity = new ovAdminSecurity();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadminbans.php' );
$ovAdminBans = new ovAdminBans();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadmincategories.php' );
$ovAdminCategories = new ovAdminCategories();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadmincontent.php' );
$ovAdminContent = new ovAdminContent();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadminmanagement.php' );
$ovAdminManagement = new ovAdminManagement();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadminreporting.php' );
$ovAdminReporting = new ovAdminReporting();

require_once( ABSOLUTEPATH . 'ov-admin/ov-include/ovadminsettings.php' );
$ovAdminSettings = new ovAdminSettings();

require_once( ABSOLUTEPATH . 'ov-include/ovocomment.php');
require_once( ABSOLUTEPATH . 'ov-include/ovosubmission.php');
require_once( ABSOLUTEPATH . 'ov-include/ovouser.php');

?>