<?php
if( !defined('ABSPATH') ) {
    exit;
}

// Create hidden admin menu page
add_action('admin_menu', 'acreports_admin_menu');
function acreports_admin_menu() {
    add_submenu_page(null, 'Advanced Customer Reports Settings', 'Advanced Customer Reports Settings', 'manage_options', 'acreports-settings', 'acreports_settings_content');
}

// Add admin menu
add_action('wp_ajax_acreports_save_settings', 'acreports_save_settings');
function acreports_save_settings() {

    // Verify nonce
    if( !isset($_POST['acreports_settings_nonce_field']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['acreports_settings_nonce_field'])), 'acreports_settings_nonce') ) {
        wp_die('Invalid nonce');
    }

    $show_date_filters = isset($_POST['show_date_filters']) ? intval($_POST['show_date_filters']) : 0;

    // Save settings
    update_option('acreports_settings', array('show_date_filters' => $show_date_filters));

    wp_die();
}

add_action('acreports_settings', 'acreports_settings_content');
function acreports_settings_content() {
?>

<br/>

<h1><?php esc_html_e('Advanced Customer Reports - Settings', 'advanced-customer-reports-woo'); ?></h1>

<form method="post" id="acreports-settings-form">

    <br/><h2><?php esc_html_e('General Settings', 'advanced-customer-reports-woo'); ?></h2>

    <table class="form-table">

        <tr>
            <th scope="row">
                <label for="activity_per_user">Activity Per User</label>
            </th>
            <td>
                <input type="number" name="activity_per_user" id="activity_per_user" value="<?php echo esc_attr(get_option('activity_per_user', 25)); ?>" class="regular-text">
            </td>
        </tr>

    </table>

    <input type="hidden" name="action" value="acreports_save_settings">

    <?php wp_nonce_field('acreports_settings_nonce', 'acreports_settings_nonce_field'); ?>

    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e('Save Changes', 'advanced-customer-reports-woo'); ?>">
    </p>

</form>

<br><br><hr/><br><br>

<p style="font-size: 15px; font-weight: bold;">
    <?php esc_html_e('Developed by', 'advanced-customer-reports-woo'); ?> 
    <a href="https://www.relywp.com" target="_blank">RelyWP</a>.
</p>

<p style="font-size: 15px;">
    <?php esc_html_e('This is a new plugin, still under early stages of active development. If you have any suggestions for new features, please feel free to message us!', 'advanced-customer-reports-woo'); ?>
</p>    

<p style="font-size: 12px; font-weight: bold;"><?php esc_html_e( 'Check out our other plugins:', 'advanced-customer-reports-woo' ); ?>

<a href="https://couponaffiliates.com/?utm_campaign=advanced-customer-reports-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php esc_html_e( 'Coupon Affiliates for WooCommerce', 'advanced-customer-reports-woo' ); ?></a>
|
<a href="https://relywp.com/plugins/tax-exemption-woocommerce/?utm_campaign=advanced-customer-reports-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php esc_html_e( 'Tax Exemption for WooCommerce', 'advanced-customer-reports-woo' ); ?></a>
|
<a href="https://relywp.com/plugins/simple-cloudflare-turnstile/?utm_campaign=advanced-customer-reports-plugin&utm_source=plugin-settings&utm_medium=promo" target="_blank"><?php esc_html_e( 'Simple Cloudflare Turnstile', 'advanced-customer-reports-woo' ); ?></a>

</p>

<?php
}