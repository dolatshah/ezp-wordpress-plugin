<?php

defined('EZPAY_DIR') or define('EZPAY_DIR',  dirname(__FILE__).DIRECTORY_SEPARATOR);
defined('EZPAY_DIR2') or define('EZPAY_DIR2',  dirname(__FILE__));
defined('EZPAY_Main_File_Path') or define('EZPAY_Main_File_Path',  __FILE__ );


defined('plugins_img_url') or define('plugins_img_url',  plugins_url('/inc/templates/images', __FILE__ ));
/* =================================================================== */

/**
 * include structor
 */
include EZPAY_DIR.'ezpay-functions.php';
include EZPAY_DIR.'ezpay-config.php';
include EZPAY_DIR.'ezpay-init.php';
/* =================================================================== */

/**
 * include libs
 */
include EZPAY_DIR.'inc'.DIRECTORY_SEPARATOR.'db.php';
//require_once( dirname( __FILE__ ) .'/db.php' );
/* =================================================================== */

/**
 * initialize...
 */
ezpay_init();
register_activation_hook(__FILE__, 'ezpay_installer');
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ezpay_add_settings_link' );
/* =================================================================== */

/**
 * include admin stuff
 */
include EZPAY_DIR.'inc'.DIRECTORY_SEPARATOR.'ezpay-admin.php';
/* =================================================================== */
?>