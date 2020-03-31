<div class="wcrw-all-request-wrapper <?php echo ( ! empty( $attributes['is_shortcode'] ) && $attributes['is_shortcode'] ) ? 'wcrw-shortcode-all-requests' : ''; ?>">

    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders">
        <thead>
            <tr>
                <th><?php _e( 'Request ID', 'wc-return-warranty' ) ?></th>
                <th><?php _e( 'Order ID', 'wc-return-warranty' ) ?></th>
                <th><?php _e( 'Type', 'wc-return-warranty' ) ?></th>
                <th><?php _e( 'Items', 'wc-return-warranty' ) ?></th>
                <th><?php _e( 'Status', 'wc-return-warranty' ) ?></th>
                <th><?php _e( 'Created Date', 'wc-return-warranty' ) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $requests ) ): ?>
                <?php foreach ( $requests as $request ): ?>
                    <tr>
                        <td>
                            <?php if ( ! empty( $attributes['is_shortcode'] ) && $attributes['is_shortcode'] ): ?>
                                <?php echo sprintf( '<a href="%s">%s #%d</a>', esc_url( add_query_arg( ['request_id' => $request['id'] ], get_permalink() ) ), __( 'Request', 'wc-return-warranty' ), $request['id'] ); ?>
                            <?php else: ?>
                                <?php echo sprintf( '<a href="%s">%s #%d</a>', esc_url( wc_get_account_endpoint_url( 'view-warranty-request' ) . $request['id'] ), __( 'Request', 'wc-return-warranty' ), $request['id'] ); ?>
                            <?php endif ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'view-order' ) . $request['order_id'] ); ?>"><?php echo __( 'Order #', 'wc-return-warranty' ) . $request['order_id']; ?></a>
                        </td>
                        <td>
                            <?php echo wcrw_warranty_request_type( $request['type'] ); ?>
                        </td>
                        <td>
                            <?php echo wcrw_get_formatted_request_items( $request['items'] ); ?>
                        </td>
                        <td>
                            <?php echo wcrw_warranty_request_status_html( $request['status'] ); ?>
                        </td>
                        <td>
                            <?php echo date_i18n( get_option( 'date_format' ), strtotime( $request['created_at'] ) ); ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="5"><?php _e( 'No requests found', 'wc-return-warranty' ); ?></td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>

    <?php echo $pagination_html; ?>
</div>
