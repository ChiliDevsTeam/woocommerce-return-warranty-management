<?php
/**
 * WordPress settings API Class
 */

if ( file_exists( WCRW_PATH . '/libs/class.settings-api.php' ) ) {
    require_once WCRW_PATH . '/libs/class.settings-api.php';
}

class WCRW_Admin_Settings {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WCRW_Settings_API();

        add_action( 'admin_init', [ $this, 'admin_init' ] );
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
        add_action( 'wcwr_settings_form_bottom_wcrw_default_warranty', [ $this, 'load_default_warranty_settings' ], 10 );
    }


    /**
     * Admin init cb
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_init() {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    /**
     * Load menu for Return and Warranty
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function admin_menu() {
        $capability           = apply_filters( 'wcrw_menu_capability', 'manage_woocommerce' );
        $return_warranty_page = add_menu_page( 'Return Request', __( 'Return Request', 'wc-return-warranty-management' ), $capability, 'wc-return-warranty-management', [ $this, 'return_warranty_html' ], 'dashicons-image-rotate', 56 );
        $requests             = add_submenu_page( 'wc-return-warranty-management', __( 'Requests', 'wc-return-warranty-management' ), __( 'Requests', 'wc-return-warranty-management' ), $capability, 'wc-return-warranty-management', [ $this, 'return_warranty_html'] );
        $settings             = add_submenu_page( 'wc-return-warranty-management', __( 'Settings', 'wc-return-warranty-management' ), __( 'Settings', 'wc-return-warranty-management' ), $capability, 'wc-return-warranty-management-settings', [ $this, 'settings_page'] );

        add_action( $requests, [ $this, 'load_admin_scripts' ], 10 );
        add_action( $settings, [ $this, 'load_admin_scripts' ], 10 );
        add_action( 'admin_print_scripts-post-new.php', [ $this, 'product_admin_script' ], 11 );
        add_action( 'admin_print_scripts-post.php', [ $this, 'product_admin_script' ], 11 );
    }

    /**
     * Load admin scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_admin_scripts() {
        wp_enqueue_style( 'wcrw-admin-style', WCRW_ASSETS . '/css/admin.css', false, WCRW_VERSION, 'all' );
        wp_enqueue_script( 'jquery-tiptip' );
        wp_enqueue_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array( 'jquery' ), WCRW_VERSION, true );
        wp_enqueue_script( 'wcrw-admin-script', WCRW_ASSETS . '/js/admin-script.js', array( 'jquery', 'jquery-blockui' ), WCRW_VERSION, true );
        wp_localize_script( 'wcrw-admin-script', 'wcrwadmin', [
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'wcrw_admin_nonce' ),
            'ajax_loader' => WCRW_ASSETS . '/images/spinner-2x.gif',
        ] );
    }

    /**
     * Product admin scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function product_admin_script() {
        global $post_type;

        if ( 'product' == $post_type ) {
            $this->load_admin_scripts();
        }
    }

    /**
     * Callback page for return & warrantty main page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function return_warranty_html() {
        if ( isset( $_GET['request_id'] ) && ! empty( $_GET['request_id'] ) ) {
            $request = wcrw_get_warranty_request( [ 'id' => $_GET['request_id'] ] );
            $notes = wcrw_get_request_notes( [ 'request_id' => $_GET['request_id'] ] );
            require_once WCRW_TEMPLATE_PATH . '/admin/single-request.php';
        } else {
            require_once WCRW_TEMPLATE_PATH . '/admin/all-requests.php';
        }
    }

    /**
     * Load all settings sections
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function get_settings_sections() {
        $sections = array(
            array(
                'id'    => 'wcrw_basic',
                'title' => '',
                'name' => __( 'General', 'wc-return-warranty-management' ),
                'icon'  => 'dashicons-admin-generic'
            ),
            array(
                'id'    => 'wcrw_default_warranty',
                'title' => '',
                'name' => __( 'Default Warranty', 'wc-return-warranty-management' ),
                'icon'  => 'dashicons-admin-tools'
            ),
            array(
                'id'    => 'wcrw_frontend',
                'title' => '',
                'name' => __( 'Frontend', 'wc-return-warranty-management' ),
                'icon'  => 'dashicons-admin-appearance'
            )
        );

        return apply_filters( 'wcrw_settings_sections', $sections );
    }

    /**
     * Returns all the settings fields
     *
     * @since 1.0.0
     *
     * @return array settings fields
     */
    public function get_settings_fields() {
        $allowed_status = wc_get_order_statuses();
        unset( $allowed_status['wc-refunded'], $allowed_status['wc-failed'], $allowed_status['wc-cancelled'] );

        $settings_fields = array(
            'wcrw_basic' => array(
                array(
                    'name'    => 'allowed_order_status',
                    'label'   => __( 'Order Status to allow Warranty Request', 'wc-return-warranty-management' ),
                    'desc'    => __( 'What status do you want to allow the warranty request button for your customer', 'wc-return-warranty-management' ),
                    'type'    => 'multicheck',
                    'default' => 'wc-completed',
                    'options' => $allowed_status
                ),
                array(
                    'name'  => 'default_refund_status',
                    'label' => __( 'Returned Status', 'wc-return-warranty-management' ),
                    'desc'  => __( 'Default status for return request when customer first create a return request', 'wc-return-warranty-management' ),
                    'type'    => 'select',
                    'default' => 'new',
                    'options' => wcrw_warranty_request_status()
                ),
            ),
            'wcrw_default_warranty' => array(
                array(
                    'name'    => 'label',
                    'label'   => __( 'Label', 'wc-return-warranty-management' ),
                    'desc'    => __( 'Default warranty label which will be shown in product page', 'wc-return-warranty-management' ),
                    'type'    => 'text',
                    'default' => __( 'Warranty', 'wc-return-warranty-management' )
                ),
                array(
                    'name'    => 'type',
                    'label'   => __( 'Type', 'wc-return-warranty-management' ),
                    'desc'    => __( 'Select your default warranty type which can be override from individual product', 'wc-return-warranty-management' ),
                    'type'    => 'select',
                    'default' => 'no-warranty',
                    'options' => wcrw_warranty_types()
                ),
            ),

            'wcrw_frontend' => array(
                array(
                    'name'    => 'request_btn_label',
                    'label'   => __( 'Request Button Label', 'wc-return-warranty-management' ),
                    'desc'    => __( 'Select button text for request a warranty from customer my order page', 'wc-return-warranty-management' ),
                    'type'    => 'text',
                    'default' => __( 'Request Warranty', 'wc-return-warranty-management' )
                ),
                array(
                    'name'    => 'myaccount_menu_title',
                    'label'   => __( 'Request Menu title', 'wc-return-warranty-management' ),
                    'desc'    => __( 'Set menu title text for showing all warranty request in customer my account page', 'wc-return-warranty-management' ),
                    'type'    => 'text',
                    'default' => __( 'Request Warranty', 'wc-return-warranty-management' )
                ),
                array(
                    'name'    => 'requests_per_page',
                    'label'   => __( 'Per page Request Number', 'wc-return-warranty-management' ),
                    'desc'    => __( 'How many request will be shown in per page in customer my account requests menu', 'wc-return-warranty-management' ),
                    'type'    => 'number',
                    'default' => 10
                ),
            )
        );

        return apply_filters( 'wcrw_settings_fields', $settings_fields );
    }

    /**
     * Render settings page content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e( 'Settings', 'wc-return-warranty-management' ) ?></h1><br>
            <div class="wcrw-settings-wrap">
                <?php
                    $this->settings_api->show_navigation();
                    $this->settings_api->show_forms();
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Get all the pages
     *
     * @since 1.0.0
     *
     * @return array page names with key value pairs
     */
    public function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

    /**
     * Load extra default warranty settings
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_default_warranty_settings( $form ) {
        $default_warranty = get_option( 'wcrw_default_warranty' );

        $addon_warranty                   = ! empty( $default_warranty['add_ons'] ) ? $default_warranty['add_ons'] : [];
        $default_warranty_length          = ! empty( $default_warranty['length'] ) ? $default_warranty['length'] : '';
        $default_warranty_length_value    = ! empty( $default_warranty['length_value'] ) ? $default_warranty['length_value'] : '';
        $default_warranty_length_duration = ! empty( $default_warranty['length_duration'] ) ? $default_warranty['length_duration'] : '';
        ?>
        <table class="form-table show_if_included_warranty">
            <tbody>
                <tr class="length">
                    <th scope="row">
                        <label for="wcrw_default_warranty[length]"><?php _e( 'Length', 'wc-return-warranty-management' ); ?></label>
                    </th>
                    <td>
                        <select name="wcrw_default_warranty[length]" id="wcrw_default_warranty[length]" class="wcrw_default_warranty[length]">
                            <?php foreach ( wcrw_warranty_length() as $length_key => $length_value ): ?>
                                <option value="<?php echo esc_attr( $length_key ); ?>" <?php selected( $default_warranty_length, $length_key ); ?>><?php echo esc_html( $length_value ); ?></option>
                            <?php endforeach ?>
                        </select>
                        <p class="description"><?php _e( 'Choose your warranty length', 'wc-return-warranty-management' ) ?></p>
                    </td>
                </tr>

                <tr class="length_value hide_if_lifetime">
                    <th scope="row">
                        <label for="wcrw_default_warranty[length_value]"><?php _e( 'Length Value', 'wc-return-warranty-management' ); ?></label>
                    </th>
                    <td>
                        <input type="number" class="regular-text" min="0" step="1" name="wcrw_default_warranty[length_value]" value="<?php echo esc_html( $default_warranty_length_value ); ?>">
                        <p class="description"><?php _e( 'Choose your number of day or week or month or year', 'wc-return-warranty-management' ) ?></p>
                    </td>
                </tr>

                <tr class="length_duration hide_if_lifetime">
                    <th scope="row">
                        <label for="wcrw_default_warranty[length_duration]"><?php _e( 'Length Duration', 'wc-return-warranty-management' ); ?></label>
                    </th>
                    <td>
                        <select name="wcrw_default_warranty[length_duration]" id="wcrw_default_warranty[length_duration]" class="wcrw_default_warranty[length_duration]">
                            <?php foreach ( wcrw_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                                <option value="<?php echo esc_attr( $length_duration_key ); ?>" <?php selected( $default_warranty_length_duration, $length_duration_key ); ?>><?php echo esc_html( $length_duration_value ); ?></option>
                            <?php endforeach ?>
                        </select>
                        <p class="description"><?php _e( 'Choose your number of day or week or month or year', 'wc-return-warranty-management' ) ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="form-table show_if_addon_warranty">
            <tbody>
                <tr class="">
                    <th scope="row">
                        <label for="wcrw_default_warranty[add_ons]"><?php _e( 'Price base warranty', 'wc-return-warranty-management' ); ?></label>
                    </th>
                    <td>
                        <table class="wcrw-addon-table">
                            <thead>
                                <tr>
                                    <th class="cost"><?php _e( 'Cost', 'wc-return-warranty-management' ) ?></th>
                                    <th class="duration"><?php _e( 'Duration', 'wc-return-warranty-management' ) ?></th>
                                    <th class="action"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ( ! empty( $addon_warranty['price'] ) ): ?>
                                    <?php foreach ( $addon_warranty['price'] as $key => $price ): ?>
                                        <tr>
                                            <td>
                                                <span class="wcrw-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                                <input type="number" min="0" step="any" value="<?php echo esc_html( $price ); ?>" name="wcrw_default_warranty[add_ons][price][]" class="warranty_price" id="wcrw_default_warranty[add_ons][price][]">
                                            </td>

                                            <td width="45%">
                                                <input type="number" min="0" step="any" name="wcrw_default_warranty[add_ons][length][]" class="warranty_length" id="wcrw_default_warranty[add_ons][length][]" value="<?php echo esc_html( $addon_warranty['length'][$key] ); ?>">
                                                <select name="wcrw_default_warranty[add_ons][duration][]" id="wcrw_default_warranty[add_ons][duration][]" class="warranty_duration">
                                                    <?php foreach ( wcrw_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                                                        <option value="<?php echo esc_attr( $length_duration_key ); ?>" <?php selected(  $addon_warranty['duration'][$key], $length_duration_key ); ?>><?php echo esc_html( $length_duration_value ); ?></option>
                                                    <?php endforeach ?>
                                                </select>
                                            </td>

                                            <td width="20%">
                                                <a href="#" class="add-item"><span class="dashicons dashicons-plus"></span></a>
                                                <a href="#" class="remove-item"><span class="dashicons dashicons-minus"></span></a>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php else: ?>
                                    <tr>
                                        <td>
                                            <span class="wcrw-input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                            <input type="number" min="0" step="any" name="wcrw_default_warranty[add_ons][price][]" class="warranty_price" id="wcrw_default_warranty[add_ons][price][]">
                                        </td>

                                        <td width="45%">
                                            <input type="number" min="0" step="any" name="wcrw_default_warranty[add_ons][length][]" class="warranty_length" id="wcrw_default_warranty[add_ons][length][]">
                                            <select name="wcrw_default_warranty[add_ons][duration][]" id="wcrw_default_warranty[add_ons][duration][]" class="warranty_duration">
                                                <?php foreach ( wcrw_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                                                    <option value="<?php echo esc_attr( $length_duration_key ); ?>"><?php echo esc_html( $length_duration_value ); ?></option>
                                                <?php endforeach ?>
                                            </select>
                                        </td>

                                        <td width="20%">
                                            <a href="#" class="add-item"><span class="dashicons dashicons-plus"></span></a>
                                            <a href="#" class="remove-item"><span class="dashicons dashicons-minus"></span></a>
                                        </td>
                                    </tr>
                                <?php endif ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}