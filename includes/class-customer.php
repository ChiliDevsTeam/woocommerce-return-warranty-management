<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
* Customer related functionality
*/
class WCRW_Customer {

    /**
     * Construct function
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'the_title', [ $this, 'endpoint_title' ] );
        add_filter( 'woocommerce_account_menu_items', [ $this, 'warranty_requests_link' ], 50 );
        add_action( 'woocommerce_account_warranty-requests_endpoint', [ $this, 'warranty_requests_content' ] );
        add_filter( 'woocommerce_my_account_my_orders_actions', [ $this, 'warranty_request_button' ], 10, 2 );
        add_filter( 'woocommerce_order_details_after_order_table', [ $this, 'show_warranty_btn_in_order_details' ], 10 );
        add_action( 'woocommerce_account_new-warranty-request_endpoint', [ $this, 'new_warranty_request_content' ] );
        add_action( 'woocommerce_account_view-warranty-request_endpoint', [ $this, 'content_warranty_requests_view' ] );
    }

    /**
     * Set endpoint title.
     *
     * @since 1.0.0
     *
     * @param string $title
     *
     * @return string
     */
    public function endpoint_title( $title ) {
        global $wp_query;
        if ( isset( $wp_query->query_vars[ 'warranty-requests' ] )
                && ! is_admin()
                && is_main_query()
                && in_the_loop()
                && is_account_page()
            )
        {
            $title = __( 'Request for a Warranty', 'wc-return-warranty' );
            remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
        }

        if ( isset( $wp_query->query_vars[ 'new-warranty-request' ] )
                && ! is_admin()
                && is_main_query()
                && in_the_loop()
                && is_account_page()
            )
        {
            $title = __( 'Create New Request', 'wc-return-warranty' );
            remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
        }

        return $title;
    }

    /**
     * My account menu for Warranty request
     *
     * @param array $menu_links
     *
     * @return array
     */
    public function warranty_requests_link( $menu_links ) {
        $frontend_settings = get_option( 'wcrw_frontend' );
        $menu_title = ! empty( $frontend_settings['myaccount_menu_title'] ) ? $frontend_settings['myaccount_menu_title'] : __( 'Warranty Requests', 'wc-return-warranty' );
        $menu_links = array_slice( $menu_links, 0, 5, true )
        + array( 'warranty-requests' => $menu_title )
        + array_slice( $menu_links, 5, NULL, true );

        return $menu_links;
    }

    /**
     * Render content for Requests warranty
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function warranty_requests_content() {
        $frontend_settings = get_option( 'wcrw_frontend' );
        $data              = [];
        $pagination_html   = '';
        $item_per_page     = ! empty( $frontend_settings['requests_per_page'] ) ? $frontend_settings['requests_per_page'] : 20;
        $total_count       = wcrw_get_warranty_request( [ 'count' => true, 'customer_id' => get_current_user_id() ] );
        $page              = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
        $offset            = ( $page * $item_per_page ) - $item_per_page;
        $total_page        = ceil( $total_count['total_count']/$item_per_page );

        if ( ! empty( $_GET['status'] ) ) {
            $data['status'] = $_GET['status'];
        }

        $data['number']      = $item_per_page;
        $data['offset']      = $offset;
        $data['customer_id'] = get_current_user_id();

        if( $total_page > 1 ){
            $pagination_html = '<div class="pagination-wrap">';
            $page_links = paginate_links( array(
                'base'      => add_query_arg( 'cpage', '%#%' ),
                'format'    => '',
                'type'      => 'array',
                'prev_text' => __( '&laquo; Previous', 'wc-return-warranty' ),
                'next_text' => __( 'Next &raquo;', 'wc-return-warranty' ),
                'total'     => $total_page,
                'current'   => $page
            ) );
            $pagination_html .= '<ul class="pagination"><li>';
            $pagination_html .= join( "</li>\n\t<li>", $page_links );
            $pagination_html .= "</li>\n</ul>\n";
            $pagination_html .= '</div>';
        };

        $requests = wcrw_get_warranty_request( $data );

        require_once WCRW_TEMPLATE_PATH . '/all-requests.php';
    }

    /**
     * New warranty request form
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function new_warranty_request_content() {
        require_once WCRW_TEMPLATE_PATH . '/new-requests.php';
    }

    /**
     * View single warranty request view
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function content_warranty_requests_view() {
        require_once WCRW_TEMPLATE_PATH . '/view-requests.php';
    }
    /**
     * Show request warranty button
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function warranty_request_button( $actions, $order ) {
        $general_settings  = get_option( 'wcrw_basic' );
        $frontend_settings = get_option( 'wcrw_frontend' );
        $request_types    = ! empty( $general_settings['default_return_request_type'] ) ? $general_settings['default_return_request_type'] : [ 'replacement', 'refund' ];

        if ( wcrw_order_has_any_item_warranty( $order ) ) {
            $btn_text = ! empty( $frontend_settings['request_btn_label'] ) ? $frontend_settings['request_btn_label'] : __( 'Request Warranty', 'wc-return-warranty' );
            $url      = esc_url_raw( wc_get_account_endpoint_url( 'new-warranty-request' ) . $order->get_id() ) ;
            $actions['warranty_request'] = array( 'url' => $url, 'name' => $btn_text );
        }

        if ( in_array( 'cancel', $request_types ) && in_array( $order->get_status(), [ 'processing', 'on-hold' ] ) ) {
            $url                             = add_query_arg( [ 'order_id' => $order->get_id(), 'action' => 'wcrw_cancel_order', 'nonce' => wp_create_nonce( 'wcrw_cancel_order' ) ], wc_get_account_endpoint_url( 'orders' ) );
            $cancel_btn_text                 = ! empty( $frontend_settings['cancel_btn_text'] ) ? $frontend_settings['cancel_btn_text'] : __( 'Cancel Order', 'wc-return-warranty' );
            $actions['cancel_order_request'] = array( 'url' => esc_url_raw( $url ) , 'name' => $cancel_btn_text );
        }

        return $actions;
    }

    /**
     * Show warranty button in order details page
     *
     * @since 1.1.9
     *
     * @return void
     */
    public function show_warranty_btn_in_order_details( $order ) {
        $general_settings  = get_option( 'wcrw_basic' );
        $frontend_settings = get_option( 'wcrw_frontend' );
        $request_types    = ! empty( $general_settings['default_return_request_type'] ) ? $general_settings['default_return_request_type'] : [ 'replacement', 'refund' ];

        if ( wcrw_order_has_any_item_warranty( $order ) ) {
            $btn_text = ! empty( $frontend_settings['request_btn_label'] ) ? $frontend_settings['request_btn_label'] : __( 'Request Warranty', 'wc-return-warranty' );
            $url      = esc_url_raw( wc_get_account_endpoint_url( 'new-warranty-request' ) . $order->get_id() ) ;
            ?>
            <p class="request-warranty">
                <a href="<?php echo esc_url( $url ); ?>" class="button"><?php echo esc_html( $btn_text ); ?></a>
            </p>
            <?php
        }

        if ( in_array( 'cancel', $request_types ) && in_array( $order->get_status(), [ 'processing', 'on-hold' ] ) ) {
            $url                             = add_query_arg( [ 'order_id' => $order->get_id(), 'action' => 'wcrw_cancel_order', 'nonce' => wp_create_nonce( 'wcrw_cancel_order' ) ], wc_get_account_endpoint_url( 'orders' ) );
            $cancel_btn_text                 = ! empty( $frontend_settings['cancel_btn_text'] ) ? $frontend_settings['cancel_btn_text'] : __( 'Cancel Order', 'wc-return-warranty' );
            ?>
            <p class="request-warranty">
                <a href="<?php echo esc_url( $url ); ?>" class="button"><?php echo esc_html( $cancel_btn_text ); ?></a>
            </p>
            <?php
        }
    }

}
