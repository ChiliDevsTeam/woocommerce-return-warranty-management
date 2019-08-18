<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e( 'All Requests', 'wc-return-warranty' ); ?></h1>

    <?php
    if ( isset( $_GET['updated'] ) && $_GET['updated'] ) {
        if ( ! empty( $_GET['message'] ) && $_GET['message'] == 'deleted' ) {
            ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong><?php _e( 'Request deleted successfully', 'wc-return-warranty' ) ?></strong></p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>
            <?php
        }
        if ( ! empty( $_GET['message'] ) && $_GET['message'] == 'status_updated' ) {
            ?>
                <div id="message" class="updated notice is-dismissible">
                    <p><strong><?php _e( 'Request status updated', 'wc-return-warranty' ) ?></strong></p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>
            <?php
        }
    }
    ?>
    <div id="wcrw-requests-wrap">

        <div class="wcrw-requests-wrap-inner">

            <form method="get">
                <input type="hidden" name="page" value="wc-return-warranty-management">
                <?php
                    $requests = new WCRW_Admin_Requests_List();
                    $requests->prepare_items();
                    $requests->views();
                    $requests->display();
                ?>
            </form>

        </div><!-- .list-table-inner -->
    </div><!-- .list-table-wrap -->
</div>
