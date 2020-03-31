<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class WCRW_Admin_Requests_List extends WP_List_Table {

    /**
     * Construct load automatically
     *
     * @since 1.0.0
     */
    public function __construct() {
        parent::__construct( [
            'singular' => __( 'Request', 'wc-return-warranty' ),
            'plural'   => __( 'Requests', 'wc-return-warranty' ),
            'ajax'     => false
        ] );

        $this->table_css();
    }

     /**
     * Table column width css
     *
     * @return void
     */
    function table_css() {
        echo '<style type="text/css">';
        echo '.request-list-table .column-id { width: 20%; }';
        echo '.request-list-table .column-order_id { width: 13%; }';
        echo '.request-list-table .column-type { width: 15%; }';
        echo '.request-list-table .column-items { width: 25%; }';
        echo '.request-list-table .column-status { width: 14%; }';
        echo '.request-list-table .column-created_at { width: 13%; }';
        echo '.request-list-table .column-action { width: 16%; }';
        echo '.request-list-table .column-action a.button{ padding: 3px 5px; color: #7b7b7b ; margin-right: 4px; }';
        echo '</style>';
    }

    /**
     * No request found text
     *
     * @since 1.0.0
     *
     * @return void [print html]
     */
    public function no_items() {
        _e( 'No requests found.', 'wc-return-warranty' );
    }

    /**
     * Set table default class
     *
     * @return [type] [description]
     */
    public function get_table_classes() {
        return array( 'widefat', 'fixed', 'striped', 'request-list-table', $this->_args['plural'] );
    }

    /**
     * Add columns to grid view
     *
     * @since 1.0.0
     *
     *@return array
     */
    public function get_columns(){
        $columns = [
            'cb'         => '<input type="checkbox" />',
            'id'         => __( 'Request ID', 'wc-return-warranty' ),
            'order_id'   => __( 'Order ID', 'wc-return-warranty' ),
            'type'       => __( 'Request Type', 'wc-return-warranty' ),
            'items'      => __( 'Items', 'wc-return-warranty' ),
            'status'     => __( 'Status', 'wc-return-warranty' ),
            'created_at' => __( 'Created Date', 'wc-return-warranty' ),
            'action'     => __( 'Action', 'wc-return-warranty' )
        ];

        return $columns;
    }

    /**
     * Default column
     *
     * @param [type] $item        [description]
     * @param [type] $column_name [description]
     *
     * @return [type] [description]
     */
    function column_default( $item, $column_name ) {
        $actions = [
            'completed' => [
                'icon' => '<span class="dashicons dashicons-yes"></span>',
                'url'  => add_query_arg( [ 'page' => 'wc-return-warranty', 'action' => 'change_status', 'request_id' => $item['id'], 'status' => 'completed', '_wpnonce' => wp_create_nonce( 'change_request_status' ) ], admin_url( 'admin.php' ) )
            ],
            'processing' => [
                'icon' => '<span class="dashicons dashicons-marker"></span>',
                'url'  => add_query_arg( [ 'page' => 'wc-return-warranty', 'action' => 'change_status', 'request_id' => $item['id'], 'status' => 'processing', '_wpnonce' => wp_create_nonce( 'change_request_status' ) ], admin_url( 'admin.php' ) )
            ],
            'rejected' => [
                'icon' => '<span class="dashicons dashicons-no"></span>',
                'url'  => add_query_arg( [ 'page' => 'wc-return-warranty', 'action' => 'change_status', 'request_id' => $item['id'], 'status' => 'rejected', '_wpnonce' => wp_create_nonce( 'change_request_status' ) ], admin_url( 'admin.php' ) )
            ],
        ];

        if ( 'completed' === $item['status'] ) {
            unset( $actions['completed'] );
        }
        if ( 'processing' === $item['status'] ) {
            unset( $actions['processing'] );
        }
        if ( 'rejected' === $item['status'] ) {
            unset( $actions['rejected'] );
        }

        switch( $column_name ) {
            case 'id':
                return 'Request #' . $item['id'];
            case 'order_id':
                $order = wc_get_order( $item['order_id'] );
                if ( 'trash' == $order->get_status() ) {
                    return __( 'Order #', 'wc-return-warranty' ) . $item['order_id'] . '<br>(' . __( 'Trash order', 'wc-return-warranty' ) . ')';
                }

                $edit_order_url = get_edit_post_link( $item['order_id'] );
                return '<a href="' . esc_url( $edit_order_url ). '">' . __( 'Order #', 'wc-return-warranty' ) . $item['order_id'] . '</a>';
            case 'type':
                return wcrw_warranty_request_type( $item['type'] );
            case 'items':
                return wcrw_get_formatted_request_items( $item['items'] );
            case 'status':
                return wcrw_warranty_request_status_html( $item['status'] );
            case 'created_at':
                return date_i18n( get_option( 'date_format' ), strtotime( $item['created_at'] ) );
            case 'action':
                $link = '';
                foreach ( $actions as $status => $action ) {
                    $link .= '<a href="' . $action['url'] . '" class="button tips" data-tip="' . ucfirst( $status ). '">' . $action['icon'] . '</a>';
                }
                return $link;
            default:
                return isset( $item[$column_name] ) ? $item[$column_name] : '';
        }
    }

    /**
     * Method for name column
     *
     * @since 1.0.0
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_id( $item ) {
        $view_url   = add_query_arg( [ 'page' => 'wc-return-warranty', 'request_id' => $item['id'] ], admin_url( 'admin.php' ) );
        $delete_url = add_query_arg( [ 'page' => 'wc-return-warranty', 'action' => 'delete', 'request_id' => $item['id'], '_wpnonce' => wp_create_nonce( 'request_delete' ) ], admin_url( 'admin.php' ) );
        $user_name  = $item['customer']['first_name']. ' ' . $item['customer']['last_name'];
        $title      = sprintf( '<a href="%s"><strong>Request #%d</strong></a> by <a href="%s"><strong>%s</strong><a>', esc_url( $view_url ), $item['id'], get_edit_user_link( $item['customer']['id'] ), $user_name );

        $actions = [
            'view'   => sprintf( '<a href="%s">%s</a>', esc_url( $view_url ), __( 'View', 'wc-return-warranty' ) ),
            'delete' => sprintf( '<a href="%s" onclick="return confirm(\'Are you sure?\')">%s</a>', esc_url( $delete_url ), __( 'Delete', 'wc-return-warranty' ) )
        ];

        if ( $item['type'] == 'refund' ) {
            $edit_order_url    = get_edit_post_link( $item['order_id'] );
            $actions['refund'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_order_url ), __( 'Refund', 'wc-return-warranty' ) );
        }

        if ( $item['type'] == 'cancel' ) {
            $edit_order_url    = get_edit_post_link( $item['order_id'] );
            $actions['refund'] = sprintf( '<a href="%s">%s</a>', esc_url( $edit_order_url ), __( 'Cancel & Refund', 'wc-return-warranty' ) );
        }

        return $title . $this->row_actions( apply_filters( 'wcrw_request_table_row_actions', $actions, $item ) );
    }

    /**
     * Render the checkbox column
     *
     * @param  object  $item
     *
     * @return string
     */
    public function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="req_id[]" value="%s" />', $item['id']
        );
    }

    /**
     * Set the bulk actions
     *
     * @return array
     */
    function get_bulk_actions() {
        $actions = [
            'request_delete'  => __( 'Delete', 'wc-return-warranty' ),
        ];

        return $actions;
    }

    /**
     * Get list status filter
     *
     * @since 1.0.0
     *
     * @return array
     */
    protected function get_views() {
        $status_links = [];
        $current      = ( !empty( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'all' );
        $base_link    = admin_url( 'admin.php?page=wc-return-warranty' );

        foreach ( wcrw_get_request_status_count() as $key => $value ) {
            $class                = ( $key == $current ) ? 'current' : 'status-' . $key;
            $status_links[ $key ] = sprintf( '<a href="%s" class="%s">%s <span class="count">(%s)</span></a>', add_query_arg( array( 'status' => $key ), $base_link ), $class, $value['label'], $value['count'] );
        }

        return $status_links;
    }

    /**
     * Usort for order asc or desc column
     *
     * @since 1.0.0
     *
     * @param array $a
     * @param array $b
     *
     * @return string
     */
    protected function usort_reorder( $a, $b ) {
        $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'id';
        $order   = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'desc';
        $result  = strcmp( $a[$orderby], $b[$orderby] );
        return ( $order === 'asc' ) ? $result : -$result;
    }

    /**
     * Sortable column
     *
     * @since 1.0.0
     *
     * @return array
     */
    protected function get_sortable_columns() {
        $sortable_columns = [
            'id'         => [ 'id', false ],
            'created_at' => [ 'created_at', false ],
        ];

        return $sortable_columns;
    }

    /**
     * Prepare request data for displaying
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function prepare_items() {
        global $wpdb;

        $hidden                = [];
        $current_page          = $this->get_pagenum();
        $columns               = $this->get_columns();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = [ $columns, $hidden, $sortable ];
        $per_page              = 10;
        $offset                = 1 < $current_page ? $per_page * ( $current_page - 1 ) : 0;

        /** Process bulk action */
        $this->process_bulk_action();

        $data = [
            'number'  => $per_page,
            'offset'  => $offset,
            'orderby' => 'created_at',
            'order'   => 'desc',
        ];

        if ( ! empty( $_REQUEST['status'] ) ) {
            $data['status'] = $_REQUEST['status'] == 'all' ? '' : $_REQUEST['status'];
        }

        $this->items = wcrw_get_warranty_request( $data );
        $count       = wcrw_get_warranty_request( [ 'count' => true ] );

        if ( isset( $_REQUEST['orderby'] ) ) {
            usort( $this->items, array( &$this, 'usort_reorder' ) );
        }

        // Set the pagination
        $this->set_pagination_args( array(
            'total_items' => $count['total_count'],
            'per_page'    => $per_page,
            'total_pages' => ceil( $count['total_count'] / $per_page )
        ) );
    }

    /**
     * Process bulk actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function process_bulk_action() {
        if ( 'request_delete' === $this->current_action() ) {
            $postdata = wp_unslash( $_REQUEST );

            if ( ! wp_verify_nonce( $postdata['_wpnonce'], 'bulk-requests' ) ) {
                return;
            }

            if ( ( isset( $postdata['action'] ) && $postdata['action'] == 'request_delete' ) || ( isset( $postdata['action2'] ) && $postdata['action2'] == 'request_delete' ) ) {

                if ( ! empty( $postdata['req_id'] ) ) {
                    foreach ( $postdata['req_id'] as $key => $request_id ) {
                        wcrw_delete_warranty_request( $request_id );
                    }

                    $url = add_query_arg( [ 'page' => 'wc-return-warranty', 'updated' => 1, 'message' => 'deleted' ], admin_url( 'admin.php' ) );
                    wp_redirect( $url );
                    exit();
                }
            }

            $url = add_query_arg( [ 'page' => 'wc-return-warranty' ], admin_url( 'admin.php' ) );
            wp_redirect( $url );
            exit();
        }
    }

}


