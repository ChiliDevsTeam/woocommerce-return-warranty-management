<?php

/**
* Admin product related functions
*/
class WCRW_Admin_Product {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'woocommerce_product_data_tabs', [ $this, 'add_warranty_tab' ], 10, 1 );
        add_action( 'woocommerce_product_data_panels', [ $this, 'warranty_tab_content' ], 10 );
        add_action( 'woocommerce_process_product_meta_simple', [ $this, 'save_warranty_options' ], 15 );
    }

    /**
     * Show Warranty tab in product pannel
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_warranty_tab( $default_tabs ) {
        $default_tabs['wcrw_warranty_tab'] = array(
            'label'    =>  __( 'Warranty', 'wc-return-warranty' ),
            'target'   =>  'wcrw_warranty_tab',
            'priority' => 71,
            'class'    => array( 'show_if_simple', 'show_if_grouped' )
        );

        return $default_tabs;
    }

    /**
     * Display warranty tab content
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function warranty_tab_content() {
        global $thepostid;
        $override_warranty = get_post_meta( $thepostid, '_wcrw_override_default_warranty', true );
        $settings          = wcrw_get_warranty_settings( $thepostid );
        ?>
        <div id="wcrw_warranty_tab" class="panel woocommerce_options_panel">
            <div class="options_group show_if_simple">
                <?php
                    woocommerce_wp_checkbox(
                        [
                            'id'            => '_wcrw_override_default_warranty',
                            'value'         => $override_warranty,
                            'cbvalue'       => 'yes',
                            'wrapper_class' => 'show_if_simple',
                            'label'         => __( 'Override default', 'wc-return-warranty' ),
                            'description'   => __( 'If you want to override default warranty settings', 'wc-return-warranty' ),
                        ]
                    );

                    woocommerce_wp_text_input(
                        [
                            'id'          => 'wcrw_product_warranty[label]',
                            'label'       => __( 'Label', 'wc-return-warranty' ),
                            'placeholder' => __( 'Warranty', 'wc-return-warranty' ),
                            'value'       => $settings['label'],
                            'description' => __( 'Enter your warranty label for override defaults', 'wc-return-warranty' ),
                        ]
                    );

                    woocommerce_wp_select(
                        [
                            'id'          => 'wcrw_product_warranty[type]',
                            'label'       => __( 'Type', 'wc-return-warranty' ),
                            'placeholder' => __( 'Warranty', 'wc-return-warranty' ),
                            'value'       => $settings['type'],
                            'description' => __( 'Set your warranty types for override defaults', 'wc-return-warranty' ),
                            'options'     => wcrw_warranty_types()
                        ]
                    );
                ?>
            </div>

            <div class="options_group show_if_included_warranty">
                <?php
                    woocommerce_wp_select(
                        [
                            'id'          => 'wcrw_product_warranty[length]',
                            'label'       => __( 'Length', 'wc-return-warranty' ),
                            'description' => __( 'Set your warranty lenght lifetime or limited', 'wc-return-warranty' ),
                            'value'       => $settings['length'],
                            'options'     => wcrw_warranty_length()
                        ]
                    );

                    woocommerce_wp_text_input(
                        [
                            'id'            => 'wcrw_product_warranty[length_value]',
                            'type'          => 'number',
                            'label'         => __( 'Lenght value', 'wc-return-warranty' ),
                            'placeholder'   => __( '10', 'wc-return-warranty' ),
                            'description'   => __( 'Set your warranty length', 'wc-return-warranty' ),
                            'wrapper_class' => 'hide_if_lifetime',
                            'value'         => $settings['length_value'],
                            'custom_attributes' => [
                                'min'  => 0,
                                'step' => '1',
                            ]
                        ]
                    );

                    woocommerce_wp_select(
                        [
                            'id'            => 'wcrw_product_warranty[length_duration]',
                            'label'         => __( 'Length duration', 'wc-return-warranty' ),
                            'description'   => __( 'Set your warranty length duration', 'wc-return-warranty' ),
                            'wrapper_class' => 'hide_if_lifetime',
                            'value'         => $settings['length_duration'],
                            'options'       => wcrw_warranty_length_duration()
                        ]
                    );

                    woocommerce_wp_checkbox(
                        [
                            'id'            => 'wcrw_product_warranty[hide_warranty]',
                            'value'         => $settings['hide_warranty'],
                            'cbvalue'       => 'yes',
                            'wrapper_class' => 'show_if_simple',
                            'label'         => __( 'Hide warranty Text', 'wc-return-warranty' ),
                            'description'   => __( 'If checked, then warranty text will be hidden on product, cart and checkout page. Admin and customer can see warranty only order page', 'wc-return-warranty' ),
                        ]
                    );
                ?>
            </div>

            <div class="options_group show_if_addon_warranty">
                <table class="form-table wcrw-addon-table wcrw-product-addon-table">
                    <thead>
                        <tr>
                            <th class="cost"><?php esc_html_e( 'Cost', 'wc-return-warranty' ); ?></th>
                            <th class="duration"><?php esc_html_e( 'Duration', 'wc-return-warranty' ); ?></th>
                            <th class="action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty( $settings['addon_settings'] ) ): ?>
                            <?php foreach ( $settings['addon_settings'] as $key => $addon ): ?>
                                <tr>
                                    <td>
                                        <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                        <input type="number" min="0" step="any" name="wcrw_product_warranty[add_ons][price][]" class="warranty_price" id="wcrw_product_warranty[add_ons][price][]" value="<?php echo esc_attr( $addon['price'] ); ?>">
                                    </td>

                                    <td width="45%">
                                        <input type="number" min="0" step="any" name="wcrw_product_warranty[add_ons][length][]" class="warranty_length" id="wcrw_product_warranty[add_ons][length][]" value="<?php echo esc_attr( $addon['length'] ); ?>" style="margin-right: 5px;">
                                        <select name="wcrw_product_warranty[add_ons][duration][]" id="wcrw_product_warranty[add_ons][duration][]" class="warranty_duration">
                                            <?php foreach ( wcrw_warranty_length_duration() as $length_duration_key => $length_duration_value ): ?>
                                                <option value="<?php echo esc_attr( $length_duration_key ); ?>" <?php selected( $addon['duration'], $length_duration_key ); ?>><?php echo esc_html( $length_duration_value ); ?></option>
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
                                    <span class="input-group-addon"><?php echo get_woocommerce_currency_symbol(); ?></span>
                                    <input type="number" min="0" step="any" name="wcrw_product_warranty[add_ons][price][]" class="warranty_price" id="wcrw_product_warranty[add_ons][price][]">
                                </td>

                                <td width="45%">
                                    <input type="number" min="0" step="any" name="wcrw_product_warranty[add_ons][length][]" class="warranty_length" id="wcrw_product_warranty[add_ons][length][]" style="margin-right: 5px;">
                                    <select name="wcrw_product_warranty[add_ons][duration][]" id="wcrw_product_warranty[add_ons][duration][]" class="warranty_duration">
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
            </div>
        </div>
        <?php
    }

    /**
     * Save warranty options
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save_warranty_options( $post_id ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( isset( $_POST['_wcrw_override_default_warranty'] ) ) {
            update_post_meta( $post_id, '_wcrw_override_default_warranty', 'yes' );
        } else {
            update_post_meta( $post_id, '_wcrw_override_default_warranty', 'no' );
        }

        $settings = wcrw_transform_warranty_settings( $_POST['wcrw_product_warranty'] );

        update_post_meta( $post_id, '_wcrw_product_warranty', $settings );
    }
}
