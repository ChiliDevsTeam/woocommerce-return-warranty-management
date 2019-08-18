<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
* Handle all type form submission
*/
class WCRW_Handle_Form {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_init', [ $this, 'handle_update_status' ], 10 );
            add_action( 'admin_init', [ $this, 'handle_row_action' ], 10 );
            add_action( 'admin_init', [ $this, 'handle_status_action' ], 10 );
        } else {
            add_action( 'template_redirect', [ $this, 'handle_request_submission_form' ], 10 );
        }
    }

    /**
     * Handle request submission form
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_request_submission_form() {
        $postdata = wp_unslash( $_POST );

        if ( ! isset( $postdata['wcrw_request_form_nonce' ] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $postdata['wcrw_request_form_nonce'], 'wcrw_request_form_nonce_action' ) ) {
            return;
        }

        if ( ! empty( $postdata['products']['product_id'] ) ) {
            foreach ( $postdata['products']['product_id'] as $key => $product_id ) {
                $postdata['items'][] = [
                    'product_id' => $product_id,
                    'item_id'    => ! empty( $postdata['products']['item_id'] ) ? $postdata['products']['item_id'][$key] : 0,
                    'quantity'   => ! empty( $postdata['products']['quantity'] ) ? $postdata['products']['quantity'][$key] : 0,
                ];
            }
        }

        $request = wcrw_create_warranty_request( $postdata );

        if ( is_wp_error( $request ) ) {
            wc_add_notice( $request->get_error_message(), 'error' );
            return;
        }

        do_action( 'wcrw_after_warranty_request_create', $postdata, $request );

        wc_add_notice( __( 'Request has been submitted successfully', 'wc-return-warranty' ), 'success' );

        wp_redirect( wc_get_account_endpoint_url( 'warranty-requests' ) );
        exit();
    }

    /**
     * Update status for admin
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_update_status() {
        $postdata = wp_unslash( $_POST );

        if ( ! isset( $postdata['wcrw_admin_update_status' ] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $postdata['wcrw_update_status_nonce'], 'wcrw_update_status' ) ) {
            return;
        }

        if ( $postdata['status'] === $postdata['old_status'] ) {
            return;
        }

        $data = [
            'id'     => $postdata['id'],
            'status' => $postdata['status']
        ];

        $request = wcrw_update_warranty_request( $data );

        if ( is_wp_error( $request ) ) {
            wp_redirect( add_query_arg( ['updated' => 0, 'message' => base64_encode( $request->get_error_message() ) ], $postdata['_wp_http_referer'] ) );
            return;
        }

        do_action( 'wcrw_after_warranty_request_udpated', $postdata, $request );

        wp_redirect( add_query_arg( ['updated' => 1 ], $postdata['_wp_http_referer'] ) );
    }

    /**
     * Handle row actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_row_action() {
        if ( ! isset( $_REQUEST['action'] ) ) {
            return;
        }

        if ( $_REQUEST['action'] !== 'delete' ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'request_delete' ) ) {
            return;
        }

        $postdata = wp_unslash( $_REQUEST );

        $request_id = ! empty( $postdata['request_id'] ) ? $postdata['request_id'] : 0;

        if ( ! $request_id ) {
            return;
        }

        $request = wcrw_delete_warranty_request( $request_id );

        if ( is_wp_error( $request ) ) {
            return;
        }

        $url = add_query_arg( [ 'page' => 'wc-return-warranty', 'updated' => 1, 'message' => 'deleted' ], admin_url( 'admin.php' ) );
        wp_redirect( $url );
        exit();
    }

    /**
     * Change status from admin list table
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_status_action() {
        if ( ! isset( $_REQUEST['action'] ) ) {
            return;
        }

        if ( $_REQUEST['action'] !== 'change_status' ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'change_request_status' ) ) {
            return;
        }

        $postdata   = wp_unslash( $_REQUEST );
        $request_id = ! empty( $postdata['request_id'] ) ? $postdata['request_id'] : 0;
        $status     = ! empty( $postdata['status'] ) ? $postdata['status'] : '';

        if ( ! $request_id || ! $status ) {
            return;
        }

        $data = [
            'id'     => $request_id,
            'status' => $status
        ];

        $request = wcrw_update_warranty_request( $data );

        if ( is_wp_error( $request ) ) {
            return;
        }

        $url = add_query_arg( [ 'page' => 'wc-return-warranty', 'updated' => 1, 'message' => 'status_updated' ], admin_url( 'admin.php' ) );
        wp_redirect( $url );
        exit();
    }
}
