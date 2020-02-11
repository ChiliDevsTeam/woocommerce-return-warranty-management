<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

/**
 * Class WCRW_Cancel_Order_Request
 */
class WCRW_Cancel_Order_Request extends WC_Email {
    /**
     * Create an instance of the class.
     *
     * @access public
     *
     * @return void
     */
    function __construct() {
        $this->id          = 'wcrw_cancel_order_request';
        $this->title       = __( 'Order Cancel Request to Admin', 'wc-return-warranty' );
        $this->description = __( 'An email sent to the admin when a customer cancel an order', 'wc-return-warranty' );

        // Template paths.
        $this->template_html  = 'emails/wcrw-cancel-request-admin.php';
        $this->template_plain = 'emails/plain/wcrw-cancel-request-admin.php';
        $this->template_base  = WCRW_PATH . '/templates/';
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        $this->placeholders   = [
            '{site_title}'    => $this->get_blogname(),
            '{site_url}'      => '',
            '{customer_name}' => '',
        ];

        // Action to which we hook onto to send the email.
        add_action( 'wcrw_created_cancel_order_request', [ $this, 'trigger' ], 20, 2 );

        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_name}] A New Cancel Order Request is submitted from ({customer_name})', 'wc-return-warranty' );
    }

    /**
     * Get email heading.
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'New Cancel Order Request by {customer_name}', 'wc-return-warranty' );
    }

    /**
     * Trigger Function that will send this email to the customer.
     *
     * @access public
     * @return void
     */
    function trigger( $request_id, $postdata ) {
        $this->object = wcrw_get_warranty_request( [ 'id' => $request_id ] );

        $this->placeholders['{customer_name}'] = $this->object['customer']['first_name'] . ' ' . $this->object['customer']['last_name'];
        $this->placeholders['{site_name}']     = $this->get_from_name();
        $this->placeholders['{site_url}']      = site_url();

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'request'       => $this->object,
            'email_heading' => $this->get_heading(),
            'order'         => wc_get_order( $this->object['order_id'] ),
            'sent_to_admin' => true,
            'plain_text'    => false,
            'email'         => $this
        ), '', $this->template_base );
    }
    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'request'       => $this->object,
            'order'         => wc_get_order( $this->object['order_id'] ),
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => true,
            'plain_text'    => true,
            'email'         => $this
        ), '', $this->template_base );
    }

    /**
     * Initialise settings form fields.
     */
    public function init_form_fields() {
        /* translators: %s: list of placeholders */
        $placeholder_text  = sprintf( __( 'Available placeholders: %s', 'wc-return-warranty' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' );
        $this->form_fields = array(
            'enabled'    => array(
                'title'   => __( 'Enable/Disable', 'wc-return-warranty' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'wc-return-warranty' ),
                'default' => 'yes',
            ),
            'recipient'  => array(
                'title'       => __( 'Recipient(s)', 'wc-return-warranty' ),
                'type'        => 'text',
                /* translators: %s: WP admin email */
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'wc-return-warranty' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => '',
                'default'     => $this->get_recipient(),
                'desc_tip'    => true,
            ),
            'subject'    => array(
                'title'       => __( 'Subject', 'wc-return-warranty' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ),
            'heading'    => array(
                'title'       => __( 'Email heading', 'wc-return-warranty' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => $placeholder_text,
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'wc-return-warranty' ),
                'description' => __( 'Text to appear below the main email content.', 'wc-return-warranty' ) . ' ' . $placeholder_text,
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'wc-return-warranty' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type' => array(
                'title'       => __( 'Email type', 'wc-return-warranty' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'wc-return-warranty' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ),
        );
    }

}

