<?php
/*
Plugin Name: DukaPress Shopping Cart
Description: DukaPress Shopping Cart
Version: 2.6
Author: Rixeo and Nickel Pro
Author URI: http://dukapress.org/
Plugin URI: http://dukapress.org/
*/

//Lets Define our contants
define('DPSC_PLUGIN_BASENAME',plugin_basename(__FILE__));
define('DPSC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DPSC_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)));
define('DPSC_DUKAPRESS_URL', DPSC_PLUGIN_URL.'/dukapress');
define('DPSC_DUKAPRESS_RESOURCEURL', DPSC_DUKAPRESS_URL.'/resources');
define('DPSC_DUKAPRESS_DIR', DPSC_PLUGIN_DIR.'/dukapress');
define('DPSC_DUKAPRESS_LIB_DIR', DPSC_DUKAPRESS_DIR.'/lib');
define('DPSC_DUKAPRESS_CLASSES_DIR', DPSC_DUKAPRESS_DIR.'/classes');
define('DPSC_DUKAPRESS_GATEWAY_DIR', DPSC_DUKAPRESS_DIR.'/gateways');

define('DPSC_BASENAME', plugin_basename( __FILE__ ));


require_once(DPSC_DUKAPRESS_DIR.'/dukapress-loader.php');
?>
