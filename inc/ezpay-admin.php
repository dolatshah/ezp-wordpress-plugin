<?php

/**
 * register admin menu
 * @see http://codex.wordpress.org/Administration_Menus
 */
add_action('admin_menu', 'ezpay_reg_admin_meun_fn');

function ezpay_reg_admin_meun_fn() {
    global $ezpay_admin_page;
    $ezpay_admin_page = add_menu_page(
            __('ezpay Options', 'ezpay'), // page title 
            __('ezpay', 'ezpay'), // menu title
            'manage_options', // user access capability
            'ezpay_admin_page', // menu slug
            'ezpay_admin_page_fn', //menu content function
            plugins_url('/inc/templates/images/plugin-icon.png', dirname(__FILE__)), // menu icon
            82 // menu position
    );
    add_submenu_page('ezpay_admin_page', __('ezpay About', 'ezpay'), __('About', 'ezpay'), 'manage_options', 'ezpay_help_page', 'ezpay_help_page_fn1');
    add_action('load-' . $ezpay_admin_page, 'ezpay_admin_save_option_page_fn');
}

function ezpay_admin_page_fn() {
    include EZPAY_DIR . 'inc' . DIRECTORY_SEPARATOR . 'ezpay-admin-option.php';
}

function ezpay_help_page_fn1() {

    include EZPAY_DIR . 'inc' . DIRECTORY_SEPARATOR . 'ezpay-help-page.php';
}

function ezpay_admin_save_option_page_fn() {
    global $ezpay_admin_page;
    $screen = get_current_screen();
    if ($screen->id != $ezpay_admin_page)
        return;

    delete_option('ezpay_do_activation');
    remove_action('admin_notices', 'ezpay_admin_message');
    
    if (isset($_POST['save_wper_options'])) {
        global $ezpay_option;
        check_admin_referer('ezpay_save_options');
        $ezpay_option = array(
            'username' => $_POST['username'],
            'password' => $_POST['password'],
        );
        update_option('ezpay_options', json_encode($ezpay_option))
                OR add_option('ezpay_options', json_encode($ezpay_option));
    }
}

/**
 * after install actions
 */
add_action('admin_init', 'ezpay_after_install_actions');

function ezpay_after_install_actions() {
    $active = get_option('ezpay_do_activation');
    if ($active) {
        add_action('admin_notices', 'ezpay_admin_message');
    }
}

function ezpay_admin_message(){
    $Message=  sprintf( __('ezpay successful installed. please check %soptions%s','ezpay') ,'<a href="'.menu_page_url('ezpay_admin_page',FALSE).'">', '</a>' );
    echo '<div class="updated"><p>' . $Message . '</p></div>';
}



