<div class="wcrw-all-request-wrapper">
    <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders">
        <thead>
            <tr>
                <th><?php _e( 'Request ID', 'wc-return-warranty-management' ) ?></th>
                <th><?php _e( 'Order ID', 'wc-return-warranty-management' ) ?></th>
                <th><?php _e( 'Items', 'wc-return-warranty-management' ) ?></th>
                <th><?php _e( 'Status', 'wc-return-warranty-management' ) ?></th>
                <th><?php _e( 'Created Date', 'wc-return-warranty-management' ) ?></th>
            </tr>
        </thead>

        <tbody>
            <?php if ( ! empty( $requests ) ): ?>
                <?php foreach ( $requests as $request ): ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'view-warranty-request' ) . $request['id'] ); ?>"><?php echo '#' . $request['id']; ?></a>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'view-order' ) . $request['order_id'] ); ?>"><?php echo 'Order #' . $request['order_id']; ?></a>
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
                    <td colspan="5"><?php _e( 'No requests found', 'wc-return-warranty-management' ); ?></td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>

    <?php echo $pagination_html; ?>
</div>
