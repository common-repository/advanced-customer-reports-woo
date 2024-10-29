<?php
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Order_Notes_List_Table extends WP_List_Table {

    private $username;
    private $start_date;
    private $end_date;

    public function __construct($username, $start_date, $end_date) {
        parent::__construct([
            'singular' => 'order_note',
            'plural'   => 'order_notes',
            'ajax'     => false,
        ]);

        $this->username = $username;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    /**
     * Get columns method must return an array where the key is the column slug (ID) and the value is the column title.
     */
    public function get_columns() {
        return [
            'order_id' => __('Order ID', 'advanced-customer-reports-woo'),
            'date'     => __('Date', 'advanced-customer-reports-woo'),
            'time'     => __('Time', 'advanced-customer-reports-woo'),
            'note'     => __('Note', 'advanced-customer-reports-woo')
        ];
    }

    public function prepare_items() {
        global $wpdb;

        $user = get_user_by('login', $this->username);
        $orders = wc_get_orders([
            'customer_id' => $user->ID,
            'limit'       => -1,
            'date_after'  => $this->start_date ? $this->start_date . ' 00:00:00' : '',
            'date_before' => $this->end_date ? $this->end_date . ' 23:59:59' : '',
        ]);

        $order_ids = wp_list_pluck($orders, 'ID');
        $query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID IN (" . implode(',', $order_ids) . ") AND comment_type = 'order_note'";
        $totalitems = $wpdb->query($wpdb->prepare($query));

        $perpage = 20;
        $this->set_pagination_args([
            'total_items' => $totalitems,
            'per_page'    => $perpage
        ]);

        $paged = $this->get_pagenum();
        $offset = ($paged - 1) * $perpage;
        $this->items = $wpdb->get_results($wpdb->prepare($query . " ORDER BY comment_ID DESC LIMIT %d, %d", $offset, $perpage));

        $this->_column_headers = [$this->get_columns(), [], []];
    }

    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'order_id':
                return "<a href='" . admin_url('post.php?post=' . $item->comment_post_ID . '&action=edit') . "'>" . $item->comment_post_ID . "</a>";
            case 'date':
                $date = date_create_from_format('Y-m-d H:i:s', $item->comment_date);
                return date_format($date, 'M j, Y');
            case 'time':
                $date = date_create_from_format('Y-m-d H:i:s', $item->comment_date);
                return date_format($date, 'H:i:s');
            case 'note':
                return $item->comment_content;
            default:
                return print_r($item, true); // For debugging purposes
        }
    }

    public function no_items() {
        esc_html_e('No order notes found.', 'advanced-customer-reports-woo');
    }
}

function setup_advanced_customer_reports_page() {
    add_submenu_page(
        null, // Parent slug - 'null' makes it hidden
        __('Advanced Customer Reports', 'advanced-customer-reports-woo'), // Page title
        __('Advanced Customer Reports', 'advanced-customer-reports-woo'), // Menu title
        'manage_options', // Capability
        'acreports-order-activity', // Menu slug
        'advanced_customer_reports_page_content' // Function to display the page content
    );
}
add_action('admin_menu', 'setup_advanced_customer_reports_page');

function advanced_customer_reports_page_content() {
    $username = isset($_GET['username']) ? sanitize_text_field($_GET['username']) : '';
    $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : '';
    $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : '';

    echo '<div class="wrap"><h1>' . esc_html__('Advanced Customer Reports: Order Activity', 'advanced-customer-reports-woo') . '</h1>';
    echo '<p>' . esc_html__('View a log of order notes for all orders created by a specific user.', 'advanced-customer-reports-woo') . '</p>';
    // Go back to admin.php?page=advanced-customer-reports
    if($username) { 
        echo '<a href="' . esc_url(admin_url('admin.php?page=advanced-customer-reports&username=' . esc_attr($username))) . '">&larr; ' . esc_html__('Go back to customer report', 'advanced-customer-reports-woo') . '</a>';
    } else {
        echo '<a href="' . esc_url(admin_url('admin.php?page=advanced-customer-reports')) . '">&larr; ' . esc_html__('Go back to customer reports', 'advanced-customer-reports-woo') . '</a>';
    }
    // Line
    echo '<br/><br/><hr/>';
    // Search form
    echo '<form method="get" style="margin-bottom: -40px; margin-top: 20px;">';
    echo '<input type="hidden" name="page" value="acreports-order-activity" />';
    echo '<input type="text" name="username" placeholder="' . esc_attr__('Enter username here...', 'advanced-customer-reports-woo') . '" value="' . esc_attr($username) . '" />';
    // Start Date
    echo '<input type="date" name="start_date" id="start_date" value="' . esc_attr($start_date) . '" />';
    // End Date
    echo '<input type="date" name="end_date" id="end_date" value="' . esc_attr($end_date) . '" />';
    // Search button
    echo '<input type="submit" value="' . esc_attr__('Search', 'advanced-customer-reports-woo') . '" class="button" />';
    echo '</form>';

    if (!empty($username)) {
        $list_table = new Order_Notes_List_Table($username, $start_date, $end_date);
        $list_table->prepare_items();
        
        // Debugging: Check if items are correctly passed to the table
        if (empty($list_table->items)) {
            echo '<p>' . esc_html__('No order notes found for the given username.', 'advanced-customer-reports-woo') . '</p>';
        } else {
            $list_table->display();
        }
    }

    echo '</div>';
}
