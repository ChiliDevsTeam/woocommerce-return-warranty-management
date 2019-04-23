<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'Request Details', 'wc-return-warranty-management' ); ?></h1>
    <?php
    if ( isset( $_GET['updated'] ) && $_GET['updated'] ) {
        ?>
            <div id="message" class="updated notice is-dismissible">
                <p><strong><?php _e( 'Request updated', 'wc-return-warranty-management' ) ?></strong></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wc-return-warranty-management' ) ?></span></button>
            </div>
        <?php
    }
    if ( isset( $_GET['message'] ) && $_GET['message'] ) {
        ?>
            <div id="message" class="error notice is-dismissible">
                <p><strong><?php echo base64_decode( $_GET['message'] ); ?></strong></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'wc-return-warranty-management' ) ?></span></button>
            </div>
        <?php
    }
    ?>
    <div id="poststuff" class="wcrw-single-request-wrap">
        <div id="post-body" class="metabox-holder columns-2 wcrw-requests-wrap-inner">
            <div id="postbox-container-1" class="postbox-container">
                <div id="wcrw-request-actions" class="postbox">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Toggle panel: Order actions', 'wc-return-warranty-management' ) ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle ui-sortable-handle"><span><?php _e( 'Request actions', 'wc-return-warranty-management' ) ?></span></h2>
                    <div class="inside">
                        <form method="post" style="overflow: hidden;">
                            <p>
                                <select name="status" id="request_status">
                                    <?php foreach ( wcrw_warranty_request_status() as $key => $value ): ?>
                                        <option value="<?php echo $key; ?>" <?php selected( $request['status'], $key ); ?>><?php echo $value; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                            <p style="text-align: left; float: left">
                                <?php
                                    $delete_url = add_query_arg( [ 'page' => 'wc-return-warranty-management', 'action' => 'delete', 'request_id' => $request['id'], '_wpnonce' => wp_create_nonce( 'request_delete' ) ], admin_url( 'admin.php' ) );
                                    echo sprintf( '<a href="%s" class="submitdelete" onclick="return confirm(\'Are you sure?\')">%s</a>', esc_url( $delete_url ), __( 'Delete Permanently', 'wc-return-warranty-management' ) );
                                ?>
                            </p>
                            <p style="text-align: right; float: right">
                                <input type="hidden" name="old_status" value="<?php echo $request['status']; ?>">
                                <input type="hidden" name="id" value="<?php echo $request['id']; ?>">
                                <input type="hidden" name="order_id" value="<?php echo $request['order_id']; ?>">
                                <?php wp_nonce_field( 'wcrw_update_status', 'wcrw_update_status_nonce' ); ?>
                                <input type="submit" class="button button-primary" name="wcrw_admin_update_status" value="<?php _e( 'Update', 'wc-return-warranty-management' ); ?>">
                            </p>
                        </form>
                    </div>
                </div>
                <div id="wcrw-request-admin-notes" class="postbox">
                    <button type="button" class="handlediv" aria-expanded="true">
                        <span class="screen-reader-text"><?php _e( 'Toggle panel: Order actions', 'wc-return-warranty-management' ) ?></span>
                        <span class="toggle-indicator" aria-hidden="true"></span>
                    </button>
                    <h2 class="hndle"><span><?php _e( 'Admin Notes', 'wc-return-warranty-management' ) ?></span></h2>
                    <div class="inside">
                        <div class="request-note-list">
                            <?php if ( ! empty( $notes ) ): ?>
                                <ul class="request-note">
                                    <?php foreach ( $notes as $key => $note ): ?>
                                        <li>
                                            <div class="note-content">
                                                <p><?php echo $note['note']; ?></p>
                                            </div>
                                            <p class="meta">
                                                <span><?php echo sprintf( 'added on %s', date_i18n( get_option( 'date_format' ), strtotime( $note['created_at'] ) ) ) ?></span><a href="#" class="delete delete-note" data-request_id="<?php echo $note['id']; ?>"><?php _e( 'Delete', 'wc-return-warranty-management' ); ?></a>
                                            </p>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                            <?php else: ?>
                                <p class="no-note-found"><?php _e( 'No note found', 'wc-return-warranty-management' ); ?></p>
                            <?php endif ?>
                        </div>
                        <form class="request-note-form" id="request-note-form" method="post">
                            <label for="request-note-field"><?php _e( 'Notes', 'wc-return-warranty-management' ); ?></label>
                            <textarea name="note" id="request-note-field" rows="4"></textarea>
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <input type="submit" class="button button-primary" id="add-request-note" name="add_request_note" value="<?php _e( 'Add Note', 'wc-return-warranty-management' ) ?>">
                        </form>
                    </div>
                </div>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <div id="wcrw-request-detilas" class="postbox">
                    <div class="inside">
                        <div class="heading">
                            <?php echo sprintf( 'Request ID #%d', $request['id'] ); ?>
                        </div>
                        <div class="heading-meta">
                            <?php echo sprintf( 'Created at %s, Customer IP: %s', date_i18n( get_option( 'date_format' ), strtotime( $request['created_at'] ) ), $request['customer']['ip_address'] ); ?>
                        </div>
                        <div class="content">
                            <ul class="list-item">
                                <li>
                                    <span class="label"><?php _e( 'Status', 'wc-return-warranty-management' ) ?></span>
                                    <span class="value"><?php echo wcrw_warranty_request_status( $request['status'] ) ?></span>
                                </li>
                                <li>
                                    <span class="label"><?php _e( 'Type', 'wc-return-warranty-management' ) ?></span>
                                    <span class="value"><?php echo wcrw_warranty_request_type( $request['type'] ); ?></span>
                                </li>
                                <li>
                                    <span class="label"><?php _e( 'Reasons', 'wc-return-warranty-management' ) ?></span>
                                    <span class="value"><?php echo $request['reasons']; ?></span>
                                </li>

                                <?php if ( ! empty( $request['meta'] ) ): ?>
                                    <?php foreach ( $request['meta'] as $name => $value ): ?>
                                        <li>
                                            <span class="label"><?php echo $name; ?>:</span>
                                            <span class="value"><?php echo $value; ?></span>
                                        </li>
                                    <?php endforeach ?>
                                <?php endif ?>
                            </ul>
                            <div class="left-content">
                                <h3><?php _e( 'Customer Basic Details', 'wc-return-warranty-management' ); ?></h3>
                                <div class="customer-details">
                                    <?php if ( !empty( $request['customer']['id'] ) ): ?>
                                        <a href="<?php echo get_edit_user_link( $request['customer']['id'] ); ?>"><?php echo $request['customer']['first_name']. ' ' . $request['customer']['last_name']; ?></a>
                                        <p><?php _e( 'Email', 'wc-return-warranty-management' ) ?> : <?php echo $request['customer']['email']; ?></p>
                                    <?php else: ?>
                                        <a href="#"><?php _e( 'Guest Customer', 'wc-return-warranty-management' ); ?></a>
                                        <p><?php echo $request['customer']['billing']['first_name']. ' ' . $request['customer']['billing']['last_name']; ?></p>
                                        <p><?php echo $request['customer']['billing']['email']; ?></p>
                                    <?php endif ?>
                                </div>
                            </div>
                            <div class="right-content">
                                <h3><?php _e( 'Customer Billing Details', 'wc-return-warranty-management' ); ?></h3>
                                <div class="customer-details">
                                    <?php echo $request['customer']['billing']['address']; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="postbox" id="wcrw-request-items">
                    <div class="inside">
                        <div class="woocommerce_order_items_wrapper">
                            <table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
                                <thead>
                                    <tr>
                                        <th colspan="2"><?php _e( 'Item', 'wc-return-warranty-management' ) ?></th>
                                        <th><?php _e( 'Unit Cost', 'wc-return-warranty-management' ) ?></th>
                                        <th><?php _e( 'Qty', 'wc-return-warranty-management' ) ?></th>
                                        <th><?php _e( 'Request Qty', 'wc-return-warranty-management' ) ?></th>
                                        <th><?php _e( 'Expiry Date', 'wc-return-warranty-management' ) ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ( $request['items'] as $item ): ?>
                                        <tr>
                                            <td class="thumb">
                                                <div class="wc-order-item-thumbnail">
                                                    <img width="150" height="150" src="<?php echo $item['thumbnail']; ?>" class="attachment-thumbnail size-thumbnail" alt="<?php echo $item['title']; ?>" title="<?php echo $item['title']; ?>">
                                                </div>
                                            </td>
                                            <td class="name">
                                                <a href="<?php echo get_edit_post_link( $item['id'] ); ?>"><?php echo $item['title']; ?></a>
                                            </td>
                                            <td class="item_cost">
                                                <?php echo wc_price( $item['price'] ); ?>
                                            </td>
                                            <td class="quantity">
                                                <?php echo wc_get_order_item_meta( $item['item_id'], '_qty', true ); ?>
                                            </td>
                                            <td class="quantity">
                                                <?php echo $item['quantity']; ?>
                                            </td>
                                            <td class="expiry-date">
                                                <?php
                                                    $warranty_item = new WCRW_Warranty_Item( $item['item_id'] );
                                                    echo $warranty_item->get_expiry_date_string();
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>