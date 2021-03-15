<?php
/**
 * admin options page
 */
global $ezpay_option;
?>

<div class="wrap">
    <h2><?php _e('wp persian option', 'ezpay'); ?></h2>
    <div class="ezpay_option_logo">
        <a href="http://wp-persian.com" target="_BLANK" title="وردپرس فارسی">
            <img src="<?php echo plugins_url('/inc/templates/images/ezpay-80x80.png', dirname(__FILE__)); ?>" />
        </a>
    </div>

    <form method="post">
        <?php wp_nonce_field('ezpay_save_options'); ?> 
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="username">نام کاربری</label></th>
                    <td>
						<input type="text" name="username" dir="ltr" size="35" value="<?php echo ( esc_attr($ezpay_option['username'] ) ); ?>" />
                    </td>
                </tr> 
				<tr>
                    <th scope="row"><label for="password">پسورد</label></th>
                    <td>
						<input type="text" name="password" dir="ltr" size="35" value="<?php echo esc_attr( $ezpay_option['password'] ); ?>" />
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" value="ذخیره تغییرات" class="button button-primary" id="save_wper_options" name="save_wper_options">
        </p>
    </form>
</div>
<?php 



function ezpay_help_page_fn() {

    include EZPAY_DIR . 'inc' . DIRECTORY_SEPARATOR . 'ezpay-help-page.php';
}

?>