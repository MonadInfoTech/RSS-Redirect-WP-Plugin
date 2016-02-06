<?php
/*
Plugin Name: Feedburner Alternative and RSS Redirect Plugin 
Plugin URI: https://wordpress.org/plugins/feedburner-alternative-and-rss-redirect
Description: Switch from Feedburner to the better and FREE service SpecificFeeds with just one click 
Author: SpecificFeeds
Author URI: http://www.specificfeeds.com
Version: 1.5
License: GPLv2
*/

global $wpdb;
/* define the Root for URL and Document */
define('SFM_DOCROOT',    dirname(__FILE__));
define('SFM_PLUGURL',    plugin_dir_url(__FILE__));
define('SFM_WEBROOT',    str_replace(getcwd(), home_url(), dirname(__FILE__)));

/* load all files  */
include SFM_DOCROOT.'/sfm_pluginNotice.php';
function sfm_ModelsAutoLoader($class)
{
  if (!class_exists($class) && is_file(SFM_DOCROOT.'/libs/'.$class.'.class.php'))
  {
     include SFM_DOCROOT.'/libs/' . $class . '.class.php';
  } 
}
spl_autoload_register('sfm_ModelsAutoLoader');
$sfmActionObj = sfmBasicActions :: SFMgetInstance();

/* call the install and uninstall actions */
$sfmInstaller= sfmInstaller :: SFMgetInstance(); 
if(class_exists('sfmInstaller'))
{
    ob_clean();
    register_activation_hook(__FILE__, array($sfmInstaller,'sfmInstaller') );
}

register_uninstall_hook(__FILE__, 'sfmUnistaller');
function sfmUnistaller()
{
  global $wpdb;    
  delete_option('sfm_activate');
  delete_option('sfm_permalink_structure');
  delete_option('SFM_pluginVersion');
  $wpdb->query('DROP TABLE IF EXISTS `'.$wpdb->prefix.'sfm_redirects`');
}

if(!get_option("SFM_pluginVersion") || get_option("SFM_pluginVersion") < 1.5 )
{
	add_action("init", "SFM_pluginUpdates");
}
function SFM_pluginUpdates()
{
	global $wpdb;
	
	if(!get_option("SFM_pluginVersion"))
	{
		$sql = "SHOW TABLES LIKE '".$wpdb->prefix."sfm_redirects'"; 
		$tableExist = $wpdb->get_row($sql);
		if(!empty($tableExist))
		{
			add_option("noticeSetup", "yes");
		}
		
		/* Alter sf_redirect table */
		$sql = "ALTER TABLE `".$wpdb->prefix."sfm_redirects` CHANGE `sf_feedid` `sf_feedid` VARCHAR( 255 ) NOT NULL"; 
		$wpdb->query($sql);
		
		$sql = "ALTER TABLE `".$wpdb->prefix."sfm_redirects` ADD `feedSetup_url` VARCHAR( 255 ) NOT NULL AFTER `rid`";
		$wpdb->query($sql);
		
		/* Alter sf_redirect table */
		$sql = "TRUNCATE TABLE `".$wpdb->prefix."sfm_redirects`"; 
		$wpdb->query($sql);
	}
	
	/*Add version*/
	update_option("SFM_pluginVersion", '1.5');
	add_option('SFM_installDate',date('Y-m-d h:i:s'));
	add_option('SFM_RatingDiv', "no");
}

add_action('admin_notices', 'sfm_admin_notice', 10);
function sfm_admin_notice()
{
	
	if(isset($_GET['page']) && $_GET['page'] == "sfsi-options")
	{
		$style = "overflow: hidden; margin:12px 3px 0px;";
	}
	else
	{
		$style = "overflow: hidden;"; 
	}
	if(get_option("noticeSetup") == "yes")
	{ 
		$url = "?sfm-dismiss-notice=true";
		?>
		<div class="updated" style="<?php echo $style; ?>">
			<div class="alignleft" style="margin: 9px 0;color:red;">
				<b>IMPORTANT:</b> Major bug fixed, please click on "Activate Redirect" again for all the feeds you want to redirect.
			</div>
			<p class="alignright">
				<a href="<?php echo $url; ?>">Dismiss</a>
			</p>
		</div>
	<?php }
}

add_action('admin_init', 'sfm_dismiss_admin_notice');
function sfm_dismiss_admin_notice()
{
	if ( isset($_REQUEST['sfm-dismiss-notice']) && $_REQUEST['sfm-dismiss-notice'] == 'true' )
	{
		update_option( 'noticeSetup', "no" );
		header("Location: ".site_url()."/wp-admin/admin.php?page=sfm-options-page");
	}
}

function sfm_get_bloginfo($url)
{
	$web_url = get_bloginfo($url);
	
	//Block to use feedburner url
	if (preg_match("/(feedburner)/im", $web_url, $match))
	{
		$web_url = site_url()."/feed";
	}
	return $web_url;
}
?>