<?php
/**
 * New Request Email.
 *
 * An email sent to the admin when a new return request is created by customer.
 *
 * @version     1.0.0
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '= ' . esc_html( $email_heading ) . " =\n\n";

/* translators: %s: Customer billing full name */
echo sprintf( __( 'Your return request ID #%d is now %s', 'wc-return-warranty' ), $request['id'], $request['status'] ) . "\n\n";

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

foreach ( $request['items'] as $item ) :
    if ( apply_filters( 'wcrw_request_item_visible', true, $item ) ) {
        echo apply_filters( 'wcrw_email_item_name', $item['title'], $item, false );
        echo ' X ' . apply_filters( 'wcrw_email_order_item_quantity', $item['quantity'], $item );
        echo ' = ' . wc_price( $item['price'] ) . "\n";
        // allow other plugins to add additional product information here
        do_action( 'wcrw_email_request_item_meta', $item, $request, $order, $plain_text );
    }
    echo "\n\n";
endforeach;

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
