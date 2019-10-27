<?php
/**
* Ajax handler class
*
* @since 1.0.0
*/
class WCRW_Admin_Ajax {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_ajax_add_request_note', [ $this, 'add_request_note' ], 10, 1 );
        add_action( 'wp_ajax_delete_request_note', [ $this, 'delete_request_note' ], 10, 1 );
        add_action( 'wp_ajax_wcrw_save_builder_form_data', [ $this, 'save_form_builder_data' ], 10, 1 );
        add_action( 'wp_ajax_wcrw_get_builder_form_data', [ $this, 'get_form_builder_data' ], 10, 1 );
        add_action( 'wp_ajax_wcrw-promotional-offer-notice', array( $this, 'dismiss_promotional_offer' ) );
    }

    /**
     * Add request notes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_request_note() {
        $postdata = wp_unslash( $_POST );

        if ( ! wp_verify_nonce( $postdata['nonce'], 'wcrw_admin_nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification faild', 'wc-return-warranty' ) );
        }

        wp_parse_str( $postdata['formData'], $data );

        if ( empty( $data['request_id'] ) ) {
            wp_send_json_error( __( 'Request not found', 'wc-return-warranty' ) );
        }

        $response = wcrw_add_request_note( $data );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( $response );
    }

    /**
     * Delete request note
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function delete_request_note() {
        $postdata = wp_unslash( $_POST );

        if ( ! wp_verify_nonce( $postdata['nonce'], 'wcrw_admin_nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification faild', 'wc-return-warranty' ) );
        }

        if ( empty( $postdata['id'] ) ) {
            wp_send_json_error( __( 'Request id not found', 'wc-return-warranty' ) );
        }

        $response = wcrw_delete_request_note( (int)$postdata['id'] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( __( 'Note deleted successfully', 'wc-return-warranty' ) );
    }

    /**
     * Save form builder data
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function save_form_builder_data() {
        if ( ! is_admin() ) {
            wp_send_json_error( __( 'You are not allowed to do this', 'wc-return-warranty' ) );
        }

        $postdata = wp_unslash( $_POST );

        if ( ! wp_verify_nonce( $postdata['nonce'], 'wcrw_admin_nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification faild', 'wc-return-warranty' ) );
        }

        $data = json_decode( $postdata['formData'], true );

        update_option( 'wcrw_request_form_data', $data );

        wp_send_json_success( __( 'Request form udpated successfully', 'wc-return-warranty' ) );
    }

    /**
     * Get form builder feild data
     *
     * @since 1.1.0
     *
     * @return void
     */
    public function get_form_builder_data() {
        if ( ! is_admin() ) {
            wp_send_json_error( __( 'You are not allowed to do this', 'wc-return-warranty' ) );
        }

        $postdata = wp_unslash( $_POST );

        if ( ! wp_verify_nonce( $postdata['nonce'], 'wcrw_admin_nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification faild', 'wc-return-warranty' ) );
        }

        $default_data = [
            [
                'label' => __( 'Reason for request', 'wc-return-warranty' ),
                'name'  => 'request_reasons',
                'type' => 'textarea',
                'settings' => [
                    'description'  => '',
                    'class'        => '',
                    'id'           => '',
                    'wrapperClass' => '',
                    'size'         => '',
                    'required'     => false,
                    'row'          => '4',
                    'placeholder'  => __( 'Write your valid reasons', 'wc-return-warranty' ),
                ]
            ]
        ];

        $data = get_option( 'wcrw_request_form_data', $default_data );

        wp_send_json_success( $data );
    }

    /**
     * Dismiss promotion notice
     *
     * @since  2.5.6
     *
     * @return void
     */
    public function dismiss_promotional_offer() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'You have no permission to do that', 'wc-return-warranty' ) );
        }

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        if ( ! wp_verify_nonce( $nonce, 'wcrw_promo' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'wc-return-warranty' ) );
        }

        if ( ! empty( $_POST['wcrw_promo_dismissed'] ) ) {
            $offer_key = 'wcrw_promotional_offer';
            update_option( $offer_key, 'hide' );
        }
    }

}
