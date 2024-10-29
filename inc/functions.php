<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/*
 * Reports page HTML
 */
function acreports_customer_report_page_callback() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( 'You do not have sufficient permissions to access this page.' );
    }
    // Fetch settings from WordPress option
    $settings = get_option( 'acreports_settings', array() );
    $user_id_from_post = ( isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : "" );
    $user = get_user_by( 'id', esc_html( sanitize_text_field( $user_id_from_post ) ) );
    // If GET username is set autofill the username field and submit the form
    $username = ( isset( $_GET['username'] ) ? esc_html( sanitize_text_field( $_GET['username'] ) ) : "" );
    if ( $username ) {
        echo '<script>
        jQuery(document).ready(function() {
            jQuery("#username_picker").val("' . esc_js( $username ) . '");
            setTimeout(function() { jQuery("#generate_report").click(); }, 10);
         });
        </script>';
    }
    ?>

    <div class="wrap">
        
        <h1>
            <?php 
    echo esc_html( 'Advanced Customer Reports', 'advanced-customer-reports-woo' );
    ?>
            <span style="margin-left: 10px; font-size: 14px; color: #999;">
            <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=acreports-settings' ) );
    ?>" style="font-size: 14px; color: #999; text-decoration: none;">
                <?php 
    echo esc_html( 'Settings', 'advanced-customer-reports-woo' );
    ?>
            </a>
            &nbsp;&nbsp;
            <a href="<?php 
    echo esc_url( admin_url( 'admin.php?page=advanced-customer-reports-account' ) );
    ?>" style="font-size: 14px; color: #999; text-decoration: none;">
                <?php 
    echo esc_html( 'Account', 'advanced-customer-reports-woo' );
    ?>
            </a>
            &nbsp;&nbsp;
            <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>
            <a href="<?php 
        echo esc_url( admin_url( 'admin.php?billing_cycle=annual&page=advanced-customer-reports-pricing' ) );
        ?>" style="font-size: 14px; color: #999; text-decoration: none;">
                <?php 
        echo esc_html( 'Upgrade to PRO', 'advanced-customer-reports-woo' );
        ?>
            </a>
            <?php 
    }
    ?>
            </span>
        </h1> 
        
        <br />

        <!-- Content -->
        <div id="tabs-content">

            <!-- Customer Report Tab Content -->
            <div id="report-tab-content">
                <div id="customer-report">

                    <div id="customer-report-form">

                        <div style="width: 400px;">

                            <!-- Username picker here -->
                            <input type="text" id="username_picker" placeholder="Enter a username here..." value="" required>
                            <a id="clear_username" style="display: inline-block; margin-left: -25px; margin-top: -4px; font-size: 18px; text-decoration: none;" href="javascript:void(0);"><span class="dashicons dashicons-no-alt"></span></a>

                            <br/>
                            
                            <button id="generate_report" style="margin-top: 10px;">Generate Report</button>

                        </div>

                        <div style="float: right;">
                            
                            <!-- Select Field: All TIme, This Month, Last Month -->
                            <select id="date_range" style="margin-top: -4px;">
                                <option value="all_time">All Time</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_year" <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>disabled<?php 
    }
    ?>>This Year <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>(PRO)<?php 
    }
    ?></option>
                                <option value="last_year" <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>disabled<?php 
    }
    ?>>Last Year <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>(PRO)<?php 
    }
    ?></option>
                                <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>
                                    <option value="custom" disabled>Custom (PRO)</option>
                                <?php 
    }
    ?>
                            </select>

                            <span class="pro-feature">

                                <!-- PRO Tooltip -->
                                <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>
                                <div class="tooltip" id="pro_tooltip"><a href="<?php 
        echo esc_url( admin_url( 'admin.php?billing_cycle=annual&page=advanced-customer-reports-pricing' ) );
        ?>" style="color: #fff;">Upgrade to PRO for more filters.</a></div>
                                <?php 
    }
    ?>

                                <!-- Date range picker here -->
                                <span id="acr_date_picker" style="display: inline-block; margin-top: 2px;">
                                    <input type="date" id="start_date" placeholder="Start Date" <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>disabled<?php 
    }
    ?>>
                                    <input type="date" id="end_date" placeholder="End Date" value="<?php 
    echo esc_attr( date_i18n( 'Y-m-d' ) );
    ?>" <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>disabled<?php 
    }
    ?>>
                                </span>

                                <br/><br/>

                                <!-- Status picker here -->
                                <select style="margin-left: 0px; float: right;" id="status" <?php 
    if ( !acreports_fs()->can_use_premium_code() ) {
        ?>disabled<?php 
    }
    ?>>
                                    <?php 
    $statuses = wc_get_order_statuses();
    echo '<option value="all">All Statuses</option>';
    foreach ( $statuses as $status => $status_name ) {
        echo '<option value="' . esc_attr( $status ) . '">' . esc_html( $status_name ) . '</option>';
    }
    ?>
                                </select>

                            </span>

                        </div>

                    </div>

                    <!-- Results table here -->
                    <div id="report_results">
                        <?php 
    do_action( 'acreports_show_customer_list' );
    ?>
                    </div>

                    <div id="loadingreport" style="display:none; font-size: 20px; margin-top: 20px;"><img src="<?php 
    echo esc_url( admin_url( 'images/spinner.gif' ) );
    ?>" style="margin-bottom: -2px; display: inline-block; height: 18px;" alt="Loading"> <span id="loadingreporttext"></span></div>

                </div>
            </div>

            <!-- Settings Tab Content -->
            <div id="settings-tab-content" style="display:none;">
                <?php 
    do_action( 'acreports_settings' );
    ?>
            </div>

        </div>
    </div>

<?php 
}

/*
* AJAX callback to generate the report
*/
add_action( 'wp_ajax_acreports_generate_customer_report', 'acreports_generate_customer_report' );
function acreports_generate_customer_report() {
    if ( !isset( $_POST['nonce'] ) || !wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'acreports_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }
    if ( !current_user_can( 'manage_options' ) && !current_user_can( 'manage_woocommerce' ) ) {
        wp_die( 'You do not have sufficient permissions to access this page.' );
    }
    $user = get_user_by( 'login', sanitize_text_field( $_POST['username'] ) );
    $user_id = ( isset( $_POST['user_id'] ) ? intval( esc_html( sanitize_text_field( $_POST['user_id'] ) ) ) : null );
    $username = ( isset( $_POST['username'] ) ? esc_html( sanitize_text_field( $_POST['username'] ) ) : null );
    // If username does not exist
    if ( !$user && $username ) {
        echo '<div class="customer-profile-section">';
        echo '<h2 style="margin: 0 auto;">No customer found with username "' . esc_attr( $username ) . '".</h2>';
        echo '</div>';
        exit;
    }
    if ( $username ) {
        $user = get_user_by( 'login', $username );
        $user_id = ( $user ? $user->ID : null );
        // Fetch WooCommerce Billing Address
        $address = array(
            'company'   => get_user_meta( $user->ID, 'billing_company', true ),
            'address_1' => get_user_meta( $user->ID, 'billing_address_1', true ),
            'address_2' => get_user_meta( $user->ID, 'billing_address_2', true ),
            'city'      => get_user_meta( $user->ID, 'billing_city', true ),
            'postcode'  => get_user_meta( $user->ID, 'billing_postcode', true ),
            'country'   => get_user_meta( $user->ID, 'billing_country', true ),
            'state'     => get_user_meta( $user->ID, 'billing_state', true ),
        );
        $shipping_address = array(
            'company'   => get_user_meta( $user->ID, 'shipping_company', true ),
            'address_1' => get_user_meta( $user->ID, 'shipping_address_1', true ),
            'address_2' => get_user_meta( $user->ID, 'shipping_address_2', true ),
            'city'      => get_user_meta( $user->ID, 'shipping_city', true ),
            'postcode'  => get_user_meta( $user->ID, 'shipping_postcode', true ),
            'country'   => get_user_meta( $user->ID, 'shipping_country', true ),
            'state'     => get_user_meta( $user->ID, 'shipping_state', true ),
        );
        $phone = get_user_meta( $user->ID, 'billing_phone', true );
        $formatted_address = WC()->countries->get_formatted_address( $address );
        $formatted_shipping_address = WC()->countries->get_formatted_address( $shipping_address );
        if ( !$formatted_address ) {
            $formatted_address = $formatted_address;
        }
        ?>

        <div style="max-width: 1000px;">
            <p style="text-align: right; margin-top: 0px; margin-bottom: 25px;">
                <a href="<?php 
        echo esc_url( admin_url( 'admin.php?page=advanced-customer-reports' ) );
        ?>">
                    &larr; <?php 
        echo esc_html( 'Go back to customers list', 'advanced-customer-reports-woo' );
        ?>
                </a>
            </p>
        </div>

        <?php 
        // Constructing the profile box
        echo '<div class="customer-profile-section">';
        // Left Section Wrapper
        echo '<div class="left-wrapper">';
        // Left Side
        echo '<div class="left-section">';
        echo '<div class="profile-image">' . get_avatar(
            $user->ID,
            128,
            'identicon',
            $user->display_name
        ) . '</div>';
        echo '</div>';
        // Left Side2
        echo '<div class="left-section2">';
        echo '<h2 style="margin: 0;">' . esc_html( $user->display_name ) . ' (<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $user->ID ) ) . '" target="_blank">' . esc_html( $user->user_login ) . '</a>)</h2>';
        echo '<br/>';
        echo '<p style="margin: 0;">' . esc_html__( 'Email:', 'advanced-customer-reports-woo' ) . ' <a href="mailto:' . esc_attr( $user->user_email ) . '">' . esc_html( $user->user_email ) . '</a></p>';
        if ( $phone ) {
            echo '<p style="margin: 0;">' . esc_html__( 'Phone:', 'advanced-customer-reports-woo' ) . ' <a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a></p>';
        }
        $date_registered = $user->user_registered;
        $date_registered = date_i18n( 'F jS, Y', strtotime( $date_registered ) );
        echo '<br/><p style="margin: 0;">' . esc_html__( 'Registered on', 'advanced-customer-reports-woo' ) . ': ' . esc_html( $date_registered ) . '</p>';
        echo '</div>';
        echo '</div>';
        // End of left-wrapper
        // Right Side
        echo '<div class="right-section">';
        // Right Side
        echo '<div class="right-section">';
        echo '<div class="contact-details">';
        echo '<strong style="margin: 0;">Billing Address:</strong><br/>';
        echo wp_kses_post( $formatted_address );
        echo '</div>';
        echo '</div>';
        // Right Side
        echo '<div class="right-section">';
        echo '<div class="contact-details">';
        echo '<strong style="margin: 0;">Shipping Address:</strong><br/>';
        echo wp_kses_post( $formatted_shipping_address );
        echo '</div>';
        echo '</div>';
        echo '</div>';
        // End of right-section
        echo '</div>';
        // End of customer-profile-section
    } else {
        echo '<div class="customer-profile-section">';
        echo '<h2 style="margin: 0 auto;">Showing report for all customers.</h2>';
        echo '</div>';
    }
    // Get the report data based on the selected date range
    $date_range = ( isset( $_POST['date_range'] ) ? sanitize_text_field( $_POST['date_range'] ) : null );
    $start_date = ( isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : null );
    $end_date = ( isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : null );
    if ( $date_range == 'this_month' ) {
        $today = new DateTime();
        $first_day = new DateTime($today->format( 'Y-m-01' ));
        $last_day = new DateTime($today->format( 'Y-m-t' ));
        $start_date = $first_day->format( 'Y-m-d' );
        $end_date = $last_day->format( 'Y-m-d' );
    } else {
        if ( $date_range == 'last_month' ) {
            $today = new DateTime();
            $first_day = new DateTime($today->format( 'Y-m-01' ));
            $last_day = new DateTime($today->format( 'Y-m-t' ));
            $first_day->modify( '-1 month' );
            $last_day->modify( '-1 month' );
            $start_date = $first_day->format( 'Y-m-d' );
            $end_date = $last_day->format( 'Y-m-d' );
        } else {
            if ( $date_range == 'all_time' ) {
                $start_date = null;
                $end_date = date_i18n( 'Y-m-d' );
            } else {
                $start_date = null;
                $end_date = date_i18n( 'Y-m-d' );
            }
        }
    }
    $status = 'all';
    if ( $status == 'all' ) {
        $status = array_keys( wc_get_order_statuses() );
        unset($status['trash']);
    }
    if ( $user_id ) {
        $all_orders = wc_get_orders( [
            'customer_id' => $user_id,
            'date_after'  => ( $start_date ? $start_date . ' 00:00:00' : '' ),
            'date_before' => ( $end_date ? $end_date . ' 23:59:59' : '' ),
            'limit'       => -1,
            'status'      => ( $status ? $status : 'all' ),
        ] );
    } else {
        // all customers
        $all_orders = wc_get_orders( [
            'date_after'  => ( $start_date ? $start_date . ' 00:00:00' : '' ),
            'date_before' => ( $end_date ? $end_date . ' 23:59:59' : '' ),
            'limit'       => -1,
            'status'      => ( $status ? $status : 'all' ),
        ] );
    }
    // Initialize data for statistics
    $total_orders = 0;
    $total_spent = 0;
    $largest_order_total = 0;
    $smallest_order_total = '';
    $total_products = 0;
    $product_stats = [];
    $category_stats = [];
    $product_affinity = [];
    $coupon_stats = [];
    $order_notes = [];
    $order_notes_all = [];
    $shipping_addresses = [];
    // First, iterate over ALL orders to compile statistics
    foreach ( $all_orders as $order ) {
        if ( !$order || !$order->get_id() ) {
            continue;
        }
        $total_orders++;
        $total_spent += $order->get_total();
        $total_products += $order->get_item_count();
        $products_in_order = [];
        // Largest Order Checker
        if ( $order->get_total() > $largest_order_total ) {
            $largest_order_total = $order->get_total();
        }
        // Smallest Order Checker
        if ( !$smallest_order_total || $order->get_total() < $smallest_order_total ) {
            if ( $order->get_total() == 0 ) {
                continue;
            }
            $smallest_order_total = $order->get_total();
        }
        if ( !$smallest_order_total ) {
            $smallest_order_total = 0;
        }
        // Get order status and total number of orders
        $order_status = $order->get_status();
        if ( !isset( $status_amounts[$order_status] ) ) {
            $status_amounts[$order_status] = 0;
        }
        $status_amounts[$order_status] += 1;
        // Get payment method and total number of orders
        // if get_payment_method method does not exist
        if ( method_exists( $order, 'get_payment_method' ) ) {
            $payment_method = $order->get_payment_method();
            if ( !isset( $payment_method_amounts[$payment_method] ) ) {
                $payment_method_amounts[$payment_method] = 0;
                $payment_method_spend[$payment_method] = 0;
                $payment_method_count[$payment_method] = 0;
            }
            $payment_method_amounts[$payment_method] += 1;
            $payment_method_spend[$payment_method] += $order->get_total();
            $payment_method_count[$payment_method] += 1;
        }
        // Get array of order notes, save to $order_notes, stop at 10
        if ( count( $order_notes ) < 10 ) {
            $order_id = $order->get_id();
            $note_date = $order->get_date_created();
            // get order notes
            $notes = wc_get_order_notes( [
                'order_id' => $order_id,
                'type'     => 'customer_note',
            ] );
            foreach ( $notes as $note ) {
                if ( count( $order_notes ) < 10 ) {
                    $order_notes[] = (object) array(
                        'comment_post_ID' => $order_id,
                        'comment_date'    => $note_date,
                        'comment_content' => $note->content,
                    );
                }
            }
        }
        // Loop through each product in the order
        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            $total = $item->get_total();
            // Get product stats
            if ( !isset( $product_stats[$product_id] ) ) {
                $product_stats[$product_id] = [
                    'quantity' => 0,
                    'total'    => 0,
                ];
            }
            $product_stats[$product_id]['quantity'] += $quantity;
            $product_stats[$product_id]['total'] += $total;
            // Re-order the products by total quantity
            uasort( $product_stats, function ( $a, $b ) {
                return $b['quantity'] - $a['quantity'];
            } );
            // Get product category stats
            $product_categories = get_the_terms( $product_id, 'product_cat' );
            if ( !$product_categories ) {
                continue;
            }
            foreach ( $product_categories as $category ) {
                if ( !isset( $category_stats[$category->term_id] ) ) {
                    $category_stats[$category->term_id] = [
                        'quantity' => 0,
                        'total'    => 0,
                    ];
                }
                $category_stats[$category->term_id]['quantity'] += $quantity;
                $category_stats[$category->term_id]['total'] += $total;
            }
            // Get List of Coupons Used
            $coupons = $order->get_coupon_codes();
            if ( $coupons ) {
                foreach ( $coupons as $coupon ) {
                    if ( !isset( $coupon_stats[$coupon] ) ) {
                        $coupon_stats[$coupon] = [
                            'quantity'  => 0,
                            'total'     => 0,
                            'last_used' => $order->get_date_created(),
                        ];
                    }
                    $coupon_stats[$coupon]['quantity'] += $quantity;
                    $coupon_stats[$coupon]['total'] += $total;
                    if ( isset( $coupon_stats[$coupon]['discount'] ) ) {
                        $coupon_stats[$coupon]['discount'] += $order->get_discount_total();
                    } else {
                        $coupon_stats[$coupon]['discount'] = $order->get_discount_total();
                    }
                }
            }
        }
        // Get Shipping Addresses - Seperate Lines If Exist By Comma
        $shipping_address = $order->get_shipping_address_1();
        $shipping_address .= ( $order->get_shipping_address_2() ? ', ' . $order->get_shipping_address_2() : '' );
        $shipping_address .= ( $order->get_shipping_city() ? ', ' . $order->get_shipping_city() : '' );
        $shipping_address .= ( $order->get_shipping_state() ? ', ' . $order->get_shipping_state() : '' );
        $shipping_address .= ( $order->get_shipping_postcode() ? ', ' . $order->get_shipping_postcode() : '' );
        $shipping_address .= ( $order->get_shipping_country() ? ', ' . $order->get_shipping_country() : '' );
        if ( !$shipping_address ) {
            // Get Billing Address - Seperate Lines If Exist By Comma
            $shipping_address = $order->get_billing_address_1();
            $shipping_address .= ( $order->get_billing_address_2() ? ', ' . $order->get_billing_address_2() : '' );
            $shipping_address .= ( $order->get_billing_city() ? ', ' . $order->get_billing_city() : '' );
            $shipping_address .= ( $order->get_billing_state() ? ', ' . $order->get_billing_state() : '' );
            $shipping_address .= ( $order->get_billing_postcode() ? ', ' . $order->get_billing_postcode() : '' );
            $shipping_address .= ( $order->get_billing_country() ? ', ' . $order->get_billing_country() : '' );
        }
        $order_date = $order->get_date_created();
        // Save address along with latest order date for that address, and total times address used
        if ( !isset( $shipping_addresses[$shipping_address] ) ) {
            $shipping_addresses[$shipping_address] = [
                'latest_order_date' => $order->get_date_created(),
                'total_orders'      => 0,
                'total_spend'       => 0,
            ];
        }
        $shipping_addresses[$shipping_address]['total_orders'] += 1;
        $shipping_addresses[$shipping_address]['total_spend'] += $order->get_total();
        // Shipping Methods
        $shipping_method = $order->get_shipping_method();
        // Amounts
        if ( !isset( $shipping_method_amounts[$shipping_method] ) ) {
            $shipping_method_amounts[$shipping_method] = 0;
        }
        $shipping_method_amounts[$shipping_method] += 1;
        // Spend
        if ( !isset( $shipping_method_spend[$shipping_method] ) ) {
            $shipping_method_spend[$shipping_method] = 0;
        }
        $shipping_method_spend[$shipping_method] += $order->get_total();
    }
    // General statistics
    echo '<div class="report-section" style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 0;">';
    // Total Orders
    echo '<div class="stat-box">
                    <span class="dashicons dashicons-cart"></span><br>
                    <strong>' . esc_html__( 'Total Orders:', 'advanced-customer-reports-woo' ) . '</strong>
                    <span id="all_orders">' . esc_html( $total_orders ) . '</span>
        </div>';
    // Total Spent
    echo '<div class="stat-box">
                    <span class="dashicons dashicons-chart-bar"></span><br>
                    <strong>' . esc_html__( 'Total Spent:', 'advanced-customer-reports-woo' ) . '</strong>
                    ' . wc_price( esc_html( $total_spent ) ) . '
        </div>';
    // Total Products
    echo '<div class="stat-box" style="margin-right: 0;">
                    <span class="dashicons dashicons-products"></span><br>
                    <strong>' . esc_html__( 'Total Items:', 'advanced-customer-reports-woo' ) . '</strong>
                    ' . esc_html( esc_html( $total_products ) ) . '
        </div>';
    echo '</div>';
    // Other statistics
    echo '<div class="report-section" style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 0;">';
    // Last Order Date
    $last_order_date = ( $all_orders ? $all_orders[0]->get_date_created() : null );
    echo '<div class="stat-box">
                    <span class="dashicons dashicons-calendar-alt"></span><br>
                    <strong>' . esc_html__( 'Last Order:', 'advanced-customer-reports-woo' ) . '</strong>';
    $days_ago = ( $last_order_date ? $last_order_date->diff( new DateTime() )->days : 0 );
    if ( $days_ago <= 0 ) {
        echo ' Today';
    } else {
        if ( $days_ago == 1 ) {
            echo ' Yesterday';
        } else {
            echo ' ' . esc_html( $days_ago ) . ' days ago';
        }
    }
    echo '</div>';
    // Largest Order
    echo '<div class="stat-box">
                    <span class="dashicons dashicons-chart-area"></span><br>
                    <strong>' . esc_html__( 'Largest Order:', 'advanced-customer-reports-woo' ) . '</strong>';
    if ( $all_orders ) {
        echo wc_price( esc_html( $largest_order_total ) );
    } else {
        echo 0;
    }
    echo '</div>';
    // Smallest Order
    echo '<div class="stat-box" style="margin-right: 0;">
                    <span class="dashicons dashicons-chart-bar"></span><br>
                    <strong>' . esc_html__( 'Smallest Order:', 'advanced-customer-reports-woo' ) . '</strong>';
    if ( $all_orders ) {
        echo wc_price( esc_html( $smallest_order_total ) );
    } else {
        echo 0;
    }
    echo '</div>';
    echo '</div>';
    // Other statistics
    echo '<div class="report-section" style="display: flex; flex-wrap: wrap; justify-content: space-between;">';
    // AOV
    echo '<div class="stat-box">
                    <span class="dashicons dashicons-chart-pie"></span><br>
                    <strong>' . esc_html__( 'Average Order Value:', 'advanced-customer-reports-woo' ) . '</strong>
                    ' . (( $total_orders ? wc_price( $total_spent / $total_orders ) : 0 )) . '
        </div>';
    // Average Time Between Orders
    echo '<div class="stat-box">
                    <span class="dashicons dashicons-clock"></span><br>
                    <strong>' . esc_html__( 'Average Time Between Orders:', 'advanced-customer-reports-woo' ) . '</strong>';
    if ( $total_orders ) {
        $first_order_date = $all_orders[count( $all_orders ) - 1]->get_date_created();
        $last_order_date = $all_orders[0]->get_date_created();
        $days_between = $first_order_date->diff( $last_order_date )->days;
        echo esc_html( round( $days_between / $total_orders, 2 ) ) . ' ' . esc_html__( 'days', 'advanced-customer-reports-woo' );
    } else {
        echo 0;
    }
    echo '</div>';
    // Average Items per Order
    echo '<div class="stat-box" style="margin-right: 0;">
                    <span class="dashicons dashicons-chart-line"></span><br>
                    <strong>' . esc_html__( 'Average Items Per Order:', 'advanced-customer-reports-woo' ) . '</strong>
                    ' . (( $total_orders ? esc_html( round( $total_products / $total_orders, 2 ) ) : 0 )) . '
        </div>';
    echo '</div>';
    // Show cart for $user_id
    if ( $total_orders ) {
        ?>

    <div class="tabs">
        <button class="acreports-tab active" data-tab="orders">
            <?php 
        echo esc_html( 'Orders', 'advanced-customer-reports-woo' );
        ?>
        </button>
        <button class="acreports-tab" data-tab="products">
            <?php 
        echo esc_html( 'Products', 'advanced-customer-reports-woo' );
        ?>
        </button>
        <button class="acreports-tab" data-tab="payments">
            <?php 
        echo esc_html( 'Payments', 'advanced-customer-reports-woo' );
        ?>
        </button>
        <button class="acreports-tab" data-tab="shipping">
            <?php 
        echo esc_html( 'Shipping', 'advanced-customer-reports-woo' );
        ?>
        </button>
        <button class="acreports-tab" data-tab="coupons">
            <?php 
        echo esc_html( 'Coupons', 'advanced-customer-reports-woo' );
        ?>
        </button>
        <button class="acreports-tab" data-tab="activity">
            <?php 
        echo esc_html( 'Activity Log', 'advanced-customer-reports-woo' );
        ?>
        </button>
    </div>

    <div class="tab-content" id="orders" style="display: block;">
    
        <div id="orders-content"><p><img src="<?php 
        echo esc_url( admin_url( 'images/spinner.gif' ) );
        ?>" style="margin-bottom: -2px; display: inline-block; height: 18px;" alt="Loading"> Loading Orders...</p><br/></div>

        <?php 
        $status_parts = [];
        foreach ( $status_amounts as $status => $amount ) {
            $status_parts[] = "<span class='order-status " . esc_html( $status ) . "'>" . ucfirst( esc_html( $status ) ) . ": " . esc_html( $amount ) . "</span>";
        }
        echo wp_kses_post( implode( ' ', $status_parts ) );
        ?>

        <br/><br/>

        <?php 
        ?>

        <div class="report-section">
            <h3>
                <?php 
        echo esc_html__( 'Recent Order Activity', 'advanced-customer-reports-woo' );
        ?>
                <button id="download_notes_csv" class="button" style="margin-left: 10px; margin-top: -4px; font-size: 12px;"
                <?php 
        if ( acreports_fs()->is_free_plan() ) {
            ?>disabled<?php 
        }
        ?>>
                    <?php 
        ?>
                        <?php 
        echo esc_html__( 'Export (PRO)', 'advanced-customer-reports-woo' );
        ?>
                    <?php 
        ?>
                </button>
            </h3>
            <table>
                <tr>
                    <th><?php 
        echo esc_html__( 'Order ID', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Date', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Note', 'advanced-customer-reports-woo' );
        ?></th>
                </tr>
                <?php 
        // Loop through $order_notes
        foreach ( $order_notes as $notes ) {
            echo '<tr>';
            echo '<td><a href="' . esc_url( admin_url( 'post.php?post=' . $notes->comment_post_ID . '&action=edit' ) ) . '"> #' . esc_html( $notes->comment_post_ID ) . '</a></td>';
            echo '<td>' . esc_html( date_i18n( 'M j, Y', strtotime( $notes->comment_date ) ) ) . '</td>';
            echo '<td>' . esc_html( $notes->comment_content ) . '</td>';
        }
        ?>
            </table>
            <!-- View More Button -->
            <table>
                <tr>
                    <?php 
        if ( !$start_date ) {
            $activity_page = admin_url( 'admin.php?page=acreports-order-activity&username=' . $username );
        } else {
            $activity_page = admin_url( 'admin.php?page=acreports-order-activity&username=' . $username . '&start_date=' . $start_date . '&end_date=' . $end_date );
        }
        ?>
                    <td style="text-align: right;">
                        <a href="<?php 
        echo esc_url( $activity_page );
        ?>" target="_blank">
                            <?php 
        echo esc_html__( 'View more order activity', 'advanced-customer-reports-woo' );
        ?> &rarr;
                        </a>
                    </td>
                </tr>
            </table>
        </div>

        <br>
    </div>

    <div class="tab-content" id="products">

        <div class="product-tabs">
            <button class="product-tab-link current" data-tab="product-tab-1"><?php 
        echo esc_html__( 'Products Purchased', 'advanced-customer-reports-woo' );
        ?></button>
            <button class="product-tab-link" data-tab="product-tab-2"><?php 
        echo esc_html__( 'Product Categories', 'advanced-customer-reports-woo' );
        ?></button>
            <button class="product-tab-link" data-tab="product-tab-3"><?php 
        echo esc_html__( 'Product Affinity', 'advanced-customer-reports-woo' );
        ?></button>
        </div>

        <div id="product-tab-1" class="product-tab-content current">

            <!-- Product Stats Boxes -->
            <div class="report-section" style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 0;">

                <!-- Most Popular Product -->
                <?php 
        $most_popular_product_id = array_keys( $product_stats )[0];
        $most_popular_product = get_post( $most_popular_product_id );
        $most_popular_product_title = $most_popular_product->post_title;
        $most_popular_product_quantity = $product_stats[$most_popular_product_id]['quantity'];
        $most_popular_product_total = $product_stats[$most_popular_product_id]['total'];
        ?>
                <div class="stat-box" style="margin-right: 0;">
                    <span class="dashicons dashicons-star-filled"></span><br>
                    <p style="font-size: 20px; margin: 0 0 5px 0;"><?php 
        echo esc_html__( 'Most Popular', 'advanced-customer-reports-woo' );
        ?>:
                    <a href="<?php 
        echo esc_url( admin_url( 'post.php?post=' . $most_popular_product_id . '&action=edit' ) );
        ?>" target='_blank'><?php 
        echo esc_html( $most_popular_product_title );
        ?></a></p>
                    <p style="font-size: 15px; margin: 0;"><?php 
        echo esc_html( $most_popular_product_quantity );
        ?> purchases - <?php 
        echo wc_price( esc_html( $most_popular_product_total ) );
        ?></p>
                </div>

            </div>

            <div class="report-section">
                <h3><?php 
        echo esc_html__( 'Products Purchased', 'advanced-customer-reports-woo' );
        ?></h3>
                <table>
                    <tr>
                        <th><?php 
        echo esc_html__( 'Product ID', 'advanced-customer-reports-woo' );
        ?></th>
                        <th><?php 
        echo esc_html__( 'Product Name', 'advanced-customer-reports-woo' );
        ?></th>
                        <th><?php 
        echo esc_html__( 'Total Orders', 'advanced-customer-reports-woo' );
        ?></th>
                        <th><?php 
        echo esc_html__( 'Total Spent', 'advanced-customer-reports-woo' );
        ?></th>
                    </tr>
                    
                    <?php 
        arsort( $product_stats );
        foreach ( $product_stats as $product_id => $stats ) {
            if ( !$stats['quantity'] || !$stats['total'] || !$product_id ) {
                continue;
            }
            ?>
                        <tr>
                            <td><a href="<?php 
            echo esc_url( admin_url( 'post.php?post=' . $product_id . '&action=edit' ) );
            ?>" target='_blank'>#<?php 
            echo esc_html( $product_id );
            ?></a></td>
                            <td><a href="<?php 
            echo esc_url( get_permalink( $product_id ) );
            ?>" target='_blank'><?php 
            echo esc_html( get_the_title( $product_id ) );
            ?></a></td>
                            <td><?php 
            echo esc_html( $stats['quantity'] );
            ?></td>
                            <td><?php 
            echo wc_price( esc_html( $stats['total'] ) );
            ?></td>
                        </tr>
                    <?php 
        }
        ?>
                </table>
            </div>
        </div>

        <div id="product-tab-2" class="product-tab-content">
            <div class="report-section">
                <h3><?php 
        echo esc_html__( 'Product Categories', 'advanced-customer-reports-woo' );
        ?></h3>
                <table>
                    <tr>
                        <th><?php 
        echo esc_html__( 'Category ID', 'advanced-customer-reports-woo' );
        ?></th>
                        <th><?php 
        echo esc_html__( 'Category Name', 'advanced-customer-reports-woo' );
        ?></th>
                        <th><?php 
        echo esc_html__( 'Total Orders', 'advanced-customer-reports-woo' );
        ?></th>
                        <th><?php 
        echo esc_html__( 'Total Spent', 'advanced-customer-reports-woo' );
        ?></th>
                    </tr>
                    
                    <?php 
        arsort( $category_stats );
        foreach ( $category_stats as $category_id => $stats ) {
            if ( !$stats['quantity'] || !$stats['total'] ) {
                continue;
            }
            ?>
                        <tr>
                            <td><a href="<?php 
            echo esc_url( admin_url( 'term.php?taxonomy=product_cat&tag_ID=' . esc_attr( $category_id ) . '&post_type=product' ) );
            ?>" target='_blank'>#<?php 
            echo esc_html( $category_id );
            ?></a></td>
                            <td><a href="<?php 
            echo esc_url( get_term_link( $category_id ) );
            ?>" target='_blank'><?php 
            echo esc_html( get_term( $category_id )->name );
            ?></a></td>
                            <td><?php 
            echo esc_html( $stats['quantity'] );
            ?></td>
                            <td><?php 
            echo wc_price( esc_html( $stats['total'] ) );
            ?></td>
                        </tr>
                    <?php 
        }
        ?>
                </table>
            </div>
        </div>

        <div id="product-tab-3" class="product-tab-content">
            <div class="report-section">
                <h3><?php 
        echo esc_html__( 'Product Affinity', 'advanced-customer-reports-woo' );
        ?></h3>
                <?php 
        ?>
                    <p>
                        <?php 
        echo sprintf( wp_kses_post( __( '<a href="%s">Upgrade to PRO</a> to see which products are frequently bought together.', 'advanced-customer-reports-woo' ) ), '#' );
        ?>
                    </p>
                    <br/><br/><br/><br/>
                <?php 
        ?>
            </div>
        </div>

        <br/><br/>

    </div>

    <div class="tab-content" id="coupons">

        <div class="report-section">
            <h3><?php 
        echo esc_html__( 'Coupons Used', 'advanced-customer-reports-woo' );
        ?></h3>
            <?php 
        if ( !empty( $coupon_stats ) ) {
            ?>
            <table>
                <tr>
                    <th><?php 
            echo esc_html__( 'Coupon Code', 'advanced-customer-reports-woo' );
            ?></th>
                    <th><?php 
            echo esc_html__( 'Total Orders', 'advanced-customer-reports-woo' );
            ?></th>
                    <th><?php 
            echo esc_html__( 'Total Spent', 'advanced-customer-reports-woo' );
            ?></th>
                    <th><?php 
            echo esc_html__( 'Total Discounts', 'advanced-customer-reports-woo' );
            ?></th>
                    <th><?php 
            echo esc_html__( 'Last Used', 'advanced-customer-reports-woo' );
            ?></th>
                </tr>
                
                <?php 
            arsort( $coupon_stats );
            foreach ( $coupon_stats as $coupon => $stats ) {
                if ( !$stats['quantity'] || !$stats['total'] ) {
                    continue;
                }
                ?>
                    <tr>
                        <td><?php 
                echo esc_html( $coupon );
                ?></td>
                        <td><?php 
                echo esc_html( $stats['quantity'] );
                ?></td>
                        <td><?php 
                echo wc_price( esc_html( $stats['total'] ) );
                ?></td>
                        <td><?php 
                echo wc_price( esc_html( $stats['discount'] ) );
                ?></td>
                        <td><?php 
                echo esc_html( $stats['last_used']->format( 'M j, Y' ) );
                ?></td>
                    </tr>
                <?php 
            }
            ?>
            </table>
            <?php 
        } else {
            ?>
                <p><?php 
            echo esc_html__( 'No coupons used.', 'advanced-customer-reports-woo' );
            ?></p>
            <?php 
        }
        ?>
        </div>

    </div>

    <div class="tab-content" id="payments">

        <!-- Payment Methods -->
        <div class="report-section">
            <h3><?php 
        echo esc_html__( 'Payment Methods Used', 'advanced-customer-reports-woo' );
        ?></h3>
            <table>
                <tr>
                    <th><?php 
        echo esc_html__( 'Method', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Total Orders', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Total Spent', 'advanced-customer-reports-woo' );
        ?></th>
                </tr>
                
                <?php 
        foreach ( $payment_method_amounts as $payment_method => $amount ) {
            if ( !$amount ) {
                continue;
            }
            // If method exists
            if ( isset( WC()->payment_gateways->payment_gateways()[$payment_method] ) ) {
                $method_full_name = WC()->payment_gateways->payment_gateways()[$payment_method]->title;
            } else {
                $method_full_name = $payment_method;
            }
            if ( !$method_full_name ) {
                $method_full_name = $payment_method;
            }
            if ( !$method_full_name ) {
                $method_full_name = 'N/A';
            }
            $spend = $payment_method_spend[$payment_method];
            $count = $payment_method_count[$payment_method];
            ?>
                    <tr>
                        <td><?php 
            echo esc_html( $method_full_name );
            ?></td>
                        <td><?php 
            echo esc_html( $count );
            ?></td>
                        <td><?php 
            echo wc_price( esc_html( $spend ) );
            ?></td>
                    </tr>
                <?php 
        }
        ?>
            </table>
        </div>

    </div>

    <div class="tab-content" id="shipping">
            
        <!-- Shipping Addresses -->
        <div class="report-section">

            <h3><?php 
        echo esc_html__( 'Shipping Addresses', 'advanced-customer-reports-woo' );
        ?></h3>
            <table>
                <tr>
                    <th><?php 
        echo esc_html__( 'Shipping Address', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Total Orders', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Total Spent', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Last Order', 'advanced-customer-reports-woo' );
        ?></th>
                </tr>
                
                <?php 
        foreach ( $shipping_addresses as $address => $date ) {
            if ( !$date['total_orders'] ) {
                continue;
            }
            if ( !$address ) {
                $address = 'N/A';
            }
            ?>
                    <tr>
                        <td><?php 
            echo esc_html( $address );
            ?></td>
                        <td><?php 
            echo esc_html( $date['total_orders'] );
            ?></td>
                        <td><?php 
            echo wc_price( esc_html( $date['total_spend'] ) );
            ?></td>
                        <td><?php 
            echo esc_html( $date['latest_order_date']->format( 'M j, Y' ) );
            ?></td>
                    </tr>
                <?php 
        }
        ?>
            </table>

            <br/>

            <h3><?php 
        echo esc_html__( 'Shipping Methods Used', 'advanced-customer-reports-woo' );
        ?></h3>
            <table>
                <tr>
                    <th><?php 
        echo esc_html__( 'Method', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Total Orders', 'advanced-customer-reports-woo' );
        ?></th>
                    <th><?php 
        echo esc_html__( 'Total Spent', 'advanced-customer-reports-woo' );
        ?></th>
                </tr>
                
                <?php 
        foreach ( $shipping_method_amounts as $shipping_method => $amount ) {
            if ( !$amount ) {
                continue;
            }
            $spend = $shipping_method_spend[$shipping_method];
            if ( !$shipping_method ) {
                $shipping_method = 'N/A';
            }
            ?>
                    <tr>
                        <td><?php 
            echo esc_html( $shipping_method );
            ?></td>
                        <td><?php 
            echo esc_html( $amount );
            ?></td>
                        <td><?php 
            echo wc_price( esc_html( $spend ) );
            ?></td>
                    </tr>
                <?php 
        }
        ?>
            </table>

        </div>

    </div>

    <div class="tab-content" id="activity">
    
    <?php 
        if ( acreports_fs()->can_use_premium_code() ) {
            ?>
        <?php 
            do_action( 'acreports_show_acreports_activity', $user );
            ?>
    <?php 
        } else {
            ?>
        <p>
            <?php 
            echo sprintf( wp_kses_post( __( '<a href="%s">Upgrade to PRO</a> to enable the customer activity log.', 'advanced-customer-reports-woo' ) ), '#' );
            ?>
        </p>
        <br/><br/><br/><br/>
    <?php 
        }
        ?>

    </div>

    <?php 
    }
    ?>

    <?php 
    wp_die();
}

/*
* Load Orders via AJAX
*/
function acreports_load_orders_ajax() {
    // Get the data passed from AJAX
    $start_date = ( isset( $_POST['start_date'] ) ? esc_html( sanitize_text_field( $_POST['start_date'] ) ) : '' );
    $end_date = ( isset( $_POST['end_date'] ) ? esc_html( sanitize_text_field( $_POST['end_date'] ) ) : '' );
    $status = ( isset( $_POST['status'] ) ? esc_html( sanitize_text_field( $_POST['status'] ) ) : 'all' );
    if ( $status == 'all' ) {
        $status = array_keys( wc_get_order_statuses() );
        unset($status['trash']);
    }
    $page = ( isset( $_POST['page'] ) ? esc_html( sanitize_text_field( $_POST['page'] ) ) : 1 );
    $all_orders = ( isset( $_POST['all_orders'] ) ? esc_html( sanitize_text_field( $_POST['all_orders'] ) ) : [] );
    $username = ( isset( $_POST['username'] ) ? esc_html( sanitize_text_field( $_POST['username'] ) ) : '' );
    $user = get_user_by( 'login', $username );
    $user_id = ( $user ? $user->ID : 0 );
    // Orders Pagination
    $orders_per_page = 10;
    $offset = ($page - 1) * $orders_per_page;
    // Query to get paginated orders
    $orders = wc_get_orders( [
        'customer_id' => esc_html( sanitize_text_field( $user_id ) ),
        'date_after'  => ( $start_date ? $start_date . ' 00:00:00' : '' ),
        'date_before' => ( $end_date ? $end_date . ' 23:59:59' : '' ),
        'limit'       => $orders_per_page,
        'offset'      => $offset,
        'status'      => $status,
    ] );
    ob_start();
    ?>

    <?php 
    ?>

    <div class="report-section">
        
        <h3>
            <?php 
    echo esc_html__( 'Customers Orders', 'advanced-customer-reports-woo' );
    ?>
            <button id="download_csv" class="button" style="margin-left: 10px; margin-top: -4px; font-size: 12px;"
            <?php 
    if ( acreports_fs()->is_free_plan() ) {
        ?>disabled<?php 
    }
    ?>>
                <?php 
    ?>
                    <?php 
    echo esc_html__( 'Export (PRO)', 'advanced-customer-reports-woo' );
    ?>
                <?php 
    ?>
            </button>
        </h3>

        <table>
            <tr>
                <th><?php 
    echo esc_html__( 'Order ID', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Date', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Time', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Status', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Subtotal', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Total', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Discount', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Shipping', 'advanced-customer-reports-woo' );
    ?></th>
                <th><?php 
    echo esc_html__( 'Tax', 'advanced-customer-reports-woo' );
    ?></th>
            </tr>

            <?php 
    foreach ( $orders as $order ) {
        $order_id = $order->get_id();
        $order_date = $order->get_date_created();
        $order_total = $order->get_total();
        ?>
                <tr>
                    <td><a href="<?php 
        echo esc_url( admin_url( 'post.php?post=' . esc_html( $order_id ) . '&action=edit' ) );
        ?>">#<?php 
        echo esc_html( $order_id );
        ?></a></td>
                    <td><?php 
        echo esc_html( $order_date->format( 'M j, Y' ) );
        ?></td>
                    <td><?php 
        echo esc_html( $order_date->format( 'H:i:s' ) );
        ?></td>
                    <td><?php 
        echo ucfirst( esc_html( $order->get_status() ) );
        ?></td>
                    <td><?php 
        echo wc_price( esc_html( $order->get_subtotal() ) );
        ?></td>
                    <td><?php 
        echo wc_price( esc_html( $order_total ) );
        ?></td>
                    <td><?php 
        echo wc_price( esc_html( $order->get_discount_total() ) );
        ?></td>
                    <td><?php 
        echo wc_price( esc_html( $order->get_shipping_total() ) );
        ?></td>
                    <td><?php 
        echo wc_price( esc_html( $order->get_total_tax() ) );
        ?></td>
                </tr>
            <?php 
    }
    ?>
        </table>

        <?php 
    $total_orders_count = $all_orders;
    $total_pages = ceil( $total_orders_count / $orders_per_page );
    ?>

        <table>
            <tr>
                <td>
                <div class="pagination" style="margin-top: 0px; float: right;">
                    <?php 
    if ( $page > 1 ) {
        ?>
                        <a href="javascript:void(0);" class="pagination-link" data-page="<?php 
        echo esc_attr( $page - 1 );
        ?>">&laquo; Previous</a>
                    <?php 
    }
    ?>
                    <span>Page <?php 
    echo esc_html( $page );
    ?> of <?php 
    echo esc_html( $total_pages );
    ?></span>
                    <?php 
    if ( $page < $total_pages ) {
        ?>
                        <a href="javascript:void(0);" class="pagination-link" data-page="<?php 
        echo esc_attr( $page + 1 );
        ?>">Next &raquo;</a>
                    <?php 
    }
    ?>
                </div>
                </td>
            </tr>
        </table>

    </div>

    <?php 
    $output = ob_get_clean();
    echo $output;
    wp_die();
}

add_action( 'wp_ajax_acreports_load_orders_ajax', 'acreports_load_orders_ajax' );
// If user is logged in
add_action( 'wp_ajax_nopriv_acreports_load_orders_ajax', 'acreports_load_orders_ajax' );
// If user is not logged in
/*
* AJAX callback to search for usernames
*/
add_action( 'wp_ajax_nopriv_acreports_search_usernames', 'acreports_search_usernames' );
add_action( 'wp_ajax_acreports_search_usernames', 'acreports_search_usernames' );
function acreports_search_usernames() {
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die();
    }
    global $wpdb;
    // login or email like
    $term = ( isset( $_POST['term'] ) ? esc_html( sanitize_text_field( $_POST['term'] ) ) : '' );
    $results = $wpdb->get_results( $wpdb->prepare( "\r\n    SELECT ID, user_login\r\n    FROM {$wpdb->users}\r\n    WHERE user_login LIKE %s", '%' . $wpdb->esc_like( $term ) . '%' ) );
    $response = [];
    foreach ( $results as $result ) {
        $response[] = [
            'label' => $result->user_login,
            'id'    => $result->ID,
        ];
    }
    // Escape and return
    echo wp_json_encode( $response );
    wp_die();
}
