<?php
/*
Plugin Name: Heyoya Voice Reviews & Comments
Plugin URI: https://www.heyoya.com/
Description: Heyoya is a revolutionary voice reviews and comments platform that is transforming the way people interact with products and content online! To get started: 1) Click the "Activate" link to the left of this description, and 2) Go to your Heyoya configuration page, and log in / sign up

Version: 1.0
Author: Heyoya <support@heyoya.com>
Author URI: https://www.heyoya.com/
*/

require_once(dirname(__FILE__) . '/admin/admin.php');
require_once(dirname(__FILE__) . '/plugin/plugin.php');

if( is_admin() )
	$admin_options_page = new AdminOptionsPage();
else {	
	$plugin_container = new PluginContainer();	
}


function is_heyoya_installed() {	
	$options = get_option('heyoya_options', null);	
	
	return  $options != null && isset($options["apikey"]) && strlen($options["apikey"]) > 0;	
}

function was_heyoya_purchased() {
	$options = get_option('heyoya_options', null);

	return  $options != null && isset($options["purchased"]) && strlen($options["purchased"]);
}



?>
