<?php
/*
Plugin Name: Feedburner Alternative and RSS Redirect Plugin 
Plugin URI: https://wordpress.org/plugins/feedburner-alternative-and-rss-redirect
Description: Switch from Feedburner to the better and FREE service SpecificFeeds with just one click 
Author: SpecificFeeds
Author URI: http://www.specificfeeds.com
Version: 1.2
License: GPLv2
*/

global $wpdb;
/* define the Root for URL and Document */
define('SFM_DOCROOT',    dirname(__FILE__));
define('SFM_PLUGURL',    plugin_dir_url(__FILE__));
define('SFM_WEBROOT',    str_replace(getcwd(), home_url(), dirname(__FILE__)));

/* load all files  */
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

if(!get_option("SFM_pluginVersion"))
{
	add_action("init", "SFM_pluginUpdates");
}
function SFM_pluginUpdates()
{
	global $wpdb;
	
	if(!get_option("SFM_pluginVersion"))
	{
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
	update_option("SFM_pluginVersion", '1.2');
}
?>