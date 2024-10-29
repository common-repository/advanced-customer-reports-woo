<?php
if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ACReports_Customers_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( array(
            'singular' => 'customer',
            'plural'   => 'customers',
            'ajax'     => false,
        ) );
    }

    function get_columns() {
        $columns = array(
            'customer_id' => 'ID',
            'customer_login' => 'Username',
            'customer_name' => 'Name',
            'customer_email' => 'Email',
            'customer_orders' => 'Orders',
            'customer_total_spent' => 'Total Spent',
            'view_report' => 'View Report',
        );

        return $columns;
    }

    public function prepare_items() {
        
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
    
        $per_page = $this->get_items_per_page('customers_per_page', 20);
        $current_page = $this->get_pagenum();
        $search = (isset($_REQUEST['s'])) ? sanitize_text_field($_REQUEST['s']) : '';       

        $usercount = count_users();
        $total_items = $usercount['total_users'];
    
        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page)
        ));
    
        $this->items = $this->get_customers($per_page, $current_page, $search);
    }

    function usort_reorder($a, $b) {
        $orderby = (!empty($_REQUEST['orderby'])) ? sanitize_text_field($_REQUEST['orderby']) : 'customer_id';
        $order = (!empty($_REQUEST['order'])) ? sanitize_text_field($_REQUEST['order']) : 'desc';

        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    public function get_total_customers( $search = '' ) {
        $args = array(
            'search'  => '*' . esc_attr( $search ) . '*',
            'search_columns' => array( 'user_login', 'user_email', 'display_name', 'ID' ),
        );

        $users = get_users( $args );
        return count( $users );
    }

    public function get_customers($per_page = 20, $page_number = 1, $search = '') {
        $args = array(
            'search'  => '*' . esc_attr($search) . '*',
            'search_columns' => array('user_login', 'user_email', 'display_name', 'ID'),
        );

        $all_users = get_users($args);
        $customers = array();

        foreach ($all_users as $user) {
            $customer = array(
                'customer_id' => $user->ID,
                'customer_login' => $user->user_login,
                'customer_name' => $user->display_name,
                'customer_email' => $user->user_email,
                'customer_orders' => wc_get_customer_order_count($user->ID),
                'customer_total_spent' => wc_get_customer_total_spent($user->ID),
            );

            $customers[] = $customer;
        }

        // Apply sorting
        usort($customers, array($this, 'usort_reorder'));

        // Pagination
        $total_customers = count($customers);
        $offset = ($page_number - 1) * $per_page;
        $customers = array_slice($customers, $offset, $per_page);

        return $customers;
    }

    public function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'customer_id':
                return $item[ $column_name ];
            case 'customer_login':
                return $item[ $column_name ];
            case 'customer_name':
                return $item[ $column_name ];
            case 'customer_email':
                return $item[ $column_name ];
            case 'customer_orders':
                return $item[ $column_name ];
            case 'customer_total_spent':
                return wc_price( $item[ $column_name ] );
            case 'view_report':
                $user_login = get_user_by( 'id', $item['customer_id'] )->user_login;
                return '<button class="view-customer-report" data-username="' . esc_attr($user_login) . '">View Report</button>';
            default:
                return print_r( $item, true );
        }
    }

}

function acreports_customers_table() {

    // Check if the current user can manage options or shop manager
    if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    // Enqueue JavaScript
    $output = '<br/><h2>Customers List</h2>';

    // Search form
    $output .= '<form method="get">
        <input type="hidden" name="page" value="' . sanitize_text_field(esc_attr($_REQUEST['page'])) . '" />
        <input type="text" name="s" value="' . ( isset($_REQUEST['s']) ? sanitize_text_field(esc_attr($_REQUEST['s'])) : '' ) . '" placeholder="" />
        <input type="submit" value="Search Customers" id="search-submit" class="button" />
    </form><br/>';

    // Initialize and render the Customers Table
    $customers_table = new ACReports_Customers_Table();
    $customers_table->prepare_items();
    $output .= '<div id="customers-table" style="max-width: 1250px; width: 100%; margin-top: -55px;">';
    ob_start();
    $customers_table->display();
    $output .= ob_get_clean();
    $output .= '</div>';

    echo $output;

}
add_action('acreports_show_customer_list', 'acreports_customers_table');