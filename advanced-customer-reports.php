<?php
/**
 * Plugin Name: Advanced Customer Reports for WooCommerce
 * Description: Generate advanced reports to view detailed analytics and data for each of your WooCommerce customers.
 * Version: 1.1.4
 * Author: Elliot Sowersby, RelyWP
 * Author URI: https://www.relywp.com
 * License: GPLv3
 * Text Domain: advanced-customer-reports-woo
 * Domain Path: /languages
 *
 * WC requires at least: 3.7
 * WC tested up to: 9.2.3
 *
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/* Freemius */
if ( function_exists( 'acreports_fs' ) ) {
    acreports_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'acreports_fs' ) ) {
        // Create a helper function for easy SDK access.
        function acreports_fs() {
            global $acreports_fs;
            if ( !isset( $acreports_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $acreports_fs = fs_dynamic_init( array(
                    'id'             => '14137',
                    'slug'           => 'advanced-customer-reports-woo',
                    'premium_slug'   => 'advanced-customer-reports-woo-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_aec42eab35b95a152a8eb4ae8a911',
                    'is_premium'     => false,
                    'premium_suffix' => '(PRO)',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 7,
                        'is_require_payment' => true,
                    ),
                    'menu'           => array(
                        'slug'    => 'advanced-customer-reports',
                        'account' => false,
                        'contact' => false,
                        'support' => false,
                        'pricing' => false,
                        'parent'  => array(
                            'slug' => 'woocommerce',
                        ),
                    ),
                    'is_live'        => true,
                ) );
            }
            return $acreports_fs;
        }

        // Init Freemius.
        acreports_fs();
        // Signal that SDK was initiated.
        do_action( 'acreports_fs_loaded' );
    }
    // Add "Report" column to WooCommerce Users page
    add_filter( 'manage_users_columns', 'acreports_add_report_column', 9999 );
    function acreports_add_report_column(  $columns  ) {
        $columns['report'] = 'Customer Report';
        return $columns;
    }

    // Populate "Report" column with link to report
    add_filter(
        'manage_users_custom_column',
        'acreports_populate_report_column',
        9999,
        3
    );
    function acreports_populate_report_column(  $value, $column_name, $user_id  ) {
        if ( $column_name == 'report' ) {
            $user = get_user_by( 'id', $user_id );
            $username = $user->user_login;
            $report_url = admin_url( 'admin.php?page=advanced-customer-reports&user_id=' . $user_id . '&username=' . $username );
            $value = '<a href="' . $report_url . '" target="_blank">View Report</a>';
        }
        return $value;
    }

    // Add to WooCommerce admin menu after "Customers"
    add_action( 'admin_menu', 'acreports_add_customer_report_page' );
    function acreports_add_customer_report_page() {
        add_submenu_page(
            'woocommerce',
            'Customer Reports',
            'Customer Reports',
            'manage_options',
            'advanced-customer-reports',
            'acreports_customer_report_page_callback',
            10
        );
    }

    // Enqueue admin scripts and styles
    add_action( 'admin_enqueue_scripts', 'acreports_enqueue_customer_report_scripts' );
    function acreports_enqueue_customer_report_scripts() {
        // only enqueue on our custom page
        if ( isset( $_GET['page'] ) && $_GET['page'] == 'advanced-customer-reports' ) {
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-autocomplete' );
            // Call local jQuery UI
            wp_enqueue_script( 'jquery-ui-core' );
            // Our scripts and styles
            wp_enqueue_style( 'customer-report-styles', plugin_dir_url( __FILE__ ) . 'css/styles.css' );
            // Enqueue JavaScript
            wp_enqueue_script(
                'acreports-scripts',
                plugin_dir_url( __FILE__ ) . 'js/scripts.js',
                array('jquery'),
                null,
                array(
                    'strategy'  => 'defer',
                    'in_footer' => true,
                )
            );
            wp_localize_script( 'acreports-scripts', 'acreportsScripts', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'acreports_nonce' ),
            ) );
        }
    }

    // Include Functions
    include_once 'inc/functions.php';
    include_once 'inc/functions-customers.php';
    include_once 'inc/functions-order-activity.php';
    include_once 'inc/admin-options.php';
    // HPOS Compatibility
    add_action( 'before_woocommerce_init', function () {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );
}