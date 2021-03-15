<?php
global $ezpay_db_version;
$ezpay_db_version = '1.0';

function ezpay_install() {
	global $wpdb;
	global $ezpay_db_version;

	$ezpay_charge = $wpdb->prefix . 'ezpay_charge';

	$charset_collate = 'DEFAULT CHARSET=utf8 COLLATE=utf8_persian_ci';
	if($wpdb->get_var( "show tables like '$ezpay_charge'" ) != $ezpay_charge){
		$sql = "CREATE TABLE IF NOT EXISTS $ezpay_charge (
			id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			client_id int(10) UNSIGNED NOT NULL DEFAULT '0',
			type enum('','topup','pin','internet') COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			platform enum('','client_area','telegram') COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			mobile char(11) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			email varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			product_type varchar(10) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			amount int(10) UNSIGNED NOT NULL DEFAULT '0',
			order_id int(10) UNSIGNED NOT NULL DEFAULT '0',
			check_charge varchar(10) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			payment_type enum('','online','credit') COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			ref_code varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			res_code varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			status enum('unpaid','paid') COLLATE utf8_persian_ci NOT NULL DEFAULT 'unpaid',
			date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			pay_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			pay_result text COLLATE utf8_persian_ci NOT NULL,
			PRIMARY KEY (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	
	$ezpay_bill = $wpdb->prefix . 'ezpay_bill';
	if($wpdb->get_var( "show tables like '$ezpay_bill'" ) != $ezpay_bill){
		$sql = "CREATE TABLE IF NOT EXISTS $ezpay_bill (
			id int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
			client_id int(10) UNSIGNED NOT NULL DEFAULT '0',
			bill_id varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			pay_id varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			mobile char(11) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			bill_type varchar(100) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			pay_type enum('','online','panel') COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			url text COLLATE utf8_persian_ci NOT NULL,
			amount int(10) UNSIGNED NOT NULL DEFAULT '0',
			date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			pay_date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			refcode varchar(200) COLLATE utf8_persian_ci NOT NULL DEFAULT '',
			check_bill_result text COLLATE utf8_persian_ci NOT NULL,
			pay_bill_result text COLLATE utf8_persian_ci NOT NULL,
			status enum('unpaid','paid') COLLATE utf8_persian_ci NOT NULL DEFAULT 'unpaid',
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	add_option( 'ezpay_db_version', $ezpay_db_version );
}
register_activation_hook( EZPAY_Main_File_Path , 'ezpay_install' );

function ezpay_remove_database() {
    global $wpdb;
	$ezpay_charge = $wpdb->prefix . 'ezpay_charge';
    $wpdb->query( "DROP TABLE IF EXISTS $ezpay_charge" );
	
	$ezpay_bill = $wpdb->prefix . 'ezpay_bill';
    $wpdb->query( "DROP TABLE IF EXISTS $ezpay_bill" );
	
    delete_option("ezpay_options");
	delete_option("ezpay_version");
	delete_option("ezpay_db_version");
	delete_option("ezpay_do_activation");
}   
register_uninstall_hook(EZPAY_Main_File_Path , 'ezpay_remove_database');


function plugin_update() {
    global $wpdb, $ezpay_db_version;
    if ( get_option( 'ezpay_db_version' ) != $ezpay_db_version ){
		$ezpay_charge = $wpdb->prefix . 'ezpay_charge';
		$wpdb->query( "ALTER TABLE $ezpay_charge CHANGE date date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';");
		update_option( "ezpay_db_version", $ezpay_db_version );
	}
}
add_action( 'plugins_loaded', 'plugin_update' );


function myplugin_update_db_check() {
    global $ezpay_db_version;
    if ( get_site_option( 'ezpay_db_version' ) != $ezpay_db_version ) {
        jal_install();
    }
}
add_action( 'plugins_loaded', 'myplugin_update_db_check' );
