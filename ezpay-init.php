<?php

/**
 * plugin installer
 */
function ezpay_installer() {

    $default_options = include EZPAY_DIR . 'ezpay-config.php';
    add_option('ezpay_options', json_encode($default_options));
    
    $current_version = ezpay_get_plugin_version();
    add_option('ezpay_version',$current_version ) OR update_option('ezpay_version', $current_version );
    add_option('ezpay_do_activation', true) OR update_option('ezpay_do_activation', true );
}
/* =================================================================== */

add_action('upgrader_process_complete','ezpay_updater');

/**
 * plugin update
 */
function ezpay_updater() {
    $current_ver = ezpay_get_plugin_version();
    if($current_ver != get_option('ezpay_version')){
        ezpay_installer();
    }
}
/* =================================================================== */

/**
 * init function
 * @global type $ezpay_option
 */
function ezpay_init() {
    global $ezpay_option;
    $db_options 	= get_option('ezpay_options');
    $ezpay_option 	= json_decode($db_options, TRUE);
}
/* =================================================================== */

/**
 * Setup language text domain
 */
load_plugin_textdomain('ezpay', false, basename(dirname(__FILE__)).'/languages');
/* =================================================================== */

/**
 * Setup plugin page option link
 */
function ezpay_add_settings_link( $links ) {
    $settings_link = '<a href="'.menu_page_url('ezpay_admin_page',FALSE).'">'.__('setting','ezpay').'</a>';
    Array_unshift( $links, $settings_link );
    return $links;
}
/* =================================================================== */

/**
 * Enqueue styles & scripts
 * @see http://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
 */
// site -------------------------
//add_action('wp_enqueue_scripts', 'ezpay_reg_css_and_js');
//function ezpay_reg_css_and_js() {
//    wp_register_style('ezpay_reg_style', plugins_url('assets/css/style.css', __FILE__));
//    wp_enqueue_style('ezpay_reg_style');
//    wp_enqueue_script('ezpay_reg_js', plugins_url('assets/js/js.js', __FILE__), array('jquery'));
//}


//admin -------------------------

add_action('admin_enqueue_scripts', 'ezpay_reg_admin_css_and_js');

function ezpay_reg_admin_css_and_js() {
    wp_register_style('ezpay_reg_admin_style', plugins_url('inc/templates/css/admin.css', __FILE__));
    wp_enqueue_style('ezpay_reg_admin_style');
}


/* =================================================================== */

//include css & js to wordpress header
add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts() {
    wp_register_style( 'bootstrap', plugins_url('/inc/templates/css/bootstrap.my.css',__FILE__ ) );
	wp_enqueue_style('bootstrap'); 
	
	wp_register_style( 'my_style',  plugins_url('/inc/templates/css/style.css',__FILE__ ) );
	wp_enqueue_style( 'my_style' );
	
	wp_register_style( 'font_awesome',  plugins_url('/inc/templates/font-awesome-4.7.0/css/font-awesome.min.css',__FILE__ ) );
	wp_enqueue_style( 'font_awesome' );
    
	wp_enqueue_script( 'namespaceformyscript', plugins_url('/inc/templates/js/jquery-1.12.3.min.js',__FILE__ ) );
	wp_enqueue_script( 'ezpay_bootstrap', plugins_url('/inc/templates/js/bootstrap.min.js',__FILE__ ) );
}

//ajax js file
add_action( 'wp_enqueue_scripts', 'ezpay_ajax_scripts' );
function ezpay_ajax_scripts(){
	wp_register_script( 'ajaxHandle', plugins_url('/inc/templates/js/check_bill_ajax.js',__FILE__ ), array(), false, true );
	wp_enqueue_script( 'ajaxHandle' );
	wp_localize_script('ajaxHandle', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( "wp_ajax_check_bill", "check_ajax_function" );
add_action( "wp_ajax_nopriv_check_bill", "check_ajax_function" );
function check_ajax_function(){ 
	require_once EZPAY_DIR.'inc'.DIRECTORY_SEPARATOR.'ajax_check_bill.php';
	wp_die(); 
}
//ajax


function ezpay_plugin( $content ){
	global $wpdb, $ezpay_option;
	
	
	if( is_page() || is_single() ){
	
		if( ( strpos($content, '[topup]')!==false || strpos($content, '[pin]')!==false || strpos($content, '[bill]')!==false || strpos($content, '[internet]')!==false ) ){
			require_once EZPAY_DIR.'inc'.DIRECTORY_SEPARATOR.'load.php';

			if( $ezpay_option['username']=='' || $ezpay_option['password']=='' ){
				$content .= 'لطفا ابتدا از تنظیمات پلاگین ایزیپی نام کاربری و پسورد خود را وارد نمائید.';
			}else{
				if( strpos($content, '[topup]')!==false ){
					require_once( dirname( __FILE__ ) . '/inc/topup.php' );
				}
				else if( strpos($content, '[pin]')!==false ){
					require_once( dirname( __FILE__ ) . '/inc/pin.php' );
				}
				else if( strpos($content, '[bill]')!==false ){
					require_once( dirname( __FILE__ ) . '/inc/bill.php' );
				}
				else if( strpos($content, '[internet]')!==false ){
					require_once( dirname( __FILE__ ) . '/inc/internet.php' );
				}
			}
			
			$content = str_replace( array("[topup]","[pin]","[bill]","[internet]") ,array("","","",""), $content, $matches);
		}
	}
   return $content;
}
add_filter( 'the_content', 'ezpay_plugin' );
