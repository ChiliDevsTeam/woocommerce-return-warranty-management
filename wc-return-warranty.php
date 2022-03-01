<?php
/*
Plugin Name: WooCommerce Return and Warranty (RMA)
Plugin URI: https://chilidevs.com/downloads/woocommerce-return-warranty-management/
Description: An extension for manage return and warranty system for WooCommerce shop
Version: 1.2.3
Author: chilidevs
Author URI: https://chilidevs.com/
WC requires at least: 3.0
WC tested up to: 6.2.1
Text Domain: wc-return-warranty
Domain Path: /languages/
License: GPL2
*/

/**
 * Copyright (c) YEAR chilidevs (email: info@chilidevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WC_Return_Warranty class
 *
 * @class WC_Return_Warranty The class that holds the entire WC_Return_Warranty plugin
 */
class WC_Return_Warranty {

     /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.2.3';

    /**
     * Minimum PHP version required
     *
     * @var string
     */
    private $min_php = '5.6.0';

    /**
     * Constructor for the WC_Return_Warranty class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @since 1.0.0
     */
    public function __construct() {

        // Define all constant
        $this->define_constant();

        if ( ! $this->is_supported_php() ) {
            return;
        }

        add_action('init', [$this, 'add_myaccount_endpoint'], 1 );
        add_filter( 'query_vars', [ $this, 'add_query_vars' ], 1 );
        add_action( 'admin_notices', [ $this, 'installation_notice' ], 10 );
        add_action( 'woocommerce_loaded', [ $this, 'init_plugin' ] );
    }

    /**
     * Initializes the WC_Return_Warranty() class
     *
     * Checks for an existing WC_Return_Warranty() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WC_Return_Warranty();
        }

        return $instance;
    }

    /**
     * Installation notice
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function installation_notice() {
        if ( ! function_exists( 'WC' ) ) {
            ?>
            <div id="message" class="error notice is-dismissible">
                <p><?php echo sprintf( wp_kses_post( '<b>WooCommerce Return and Warranty</b> requires <a href="%s">WooCommerce</a> to be installed & activated! Go back your <a href="%s">Plugin page</a>', 'wc-return-warranty' ), 'https://wordpress.org/plugins/woocommerce/', esc_url( admin_url( 'plugins.php' ) ) ) ?></p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
            </div>
            <?php
        }
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public static function activate() {
        global $wpdb;
        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $tables = [
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wcrw_warranty_requests` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `order_id` int(11) NOT NULL,
              `customer_id` int(11) NOT NULL,
              `type` varchar(25) NOT NULL DEFAULT '',
              `status` varchar(25) NOT NULL DEFAULT '',
              `reasons` varchar(200) DEFAULT NULL,
              `meta` longtext,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wcrw_request_product_map` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `request_id` int(11) DEFAULT NULL,
              `item_id` int(11) DEFAULT NULL,
              `product_id` int(11) DEFAULT NULL,
              `quantity` int(11) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;",
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}wcrw_request_notes` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `request_id` bigint(20) DEFAULT NULL,
              `note` longtext,
              `created_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;"
        ];

        foreach ( $tables as $key => $table ) {
            dbDelta( $table );
        }

        add_rewrite_endpoint('warranty-requests', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('new-warranty-request', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('view-warranty-request', EP_ROOT | EP_PAGES);

        flush_rewrite_rules();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public static function deactivate() {

    }

    /**
    * Defined constant
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function define_constant() {
        define( 'WCRW_VERSION', $this->version );
        define( 'WCRW_FILE', __FILE__ );
        define( 'WCRW_PATH', dirname( WCRW_FILE ) );
        define( 'WCRW_ASSETS', plugins_url( '/assets', WCRW_FILE ) );
        define( 'WCRW_TEMPLATE_PATH', WCRW_PATH . '/templates' );
    }

    /**
     * Check if the PHP version is supported
     *
     * @return bool
     */
    public function is_supported_php() {
        if ( version_compare( PHP_VERSION, $this->min_php, '<=' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Add endpoin in my function
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_myaccount_endpoint($value = '') {
        add_rewrite_endpoint('warranty-requests', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('new-warranty-request', EP_ROOT | EP_PAGES);
        add_rewrite_endpoint('view-warranty-request', EP_ROOT | EP_PAGES);
    }

    /**
     * Register the query vars
     *
     * @param array
     *
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'warranty-requests';
        $vars[] = 'new-warranty-request';
        $vars[] = 'view-warranty-request';

        return $vars;
    }

    /**
     * Init plugin files after loaded WooCommerce
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();

        $this->init_hooks();

        do_action( 'wcrw_loaded', $this );
    }


    /**
    * Includes all files
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function includes() {
        require_once WCRW_PATH . '/includes/functions.php';

        if ( is_admin() ) {
            require_once WCRW_PATH . '/includes/admin/class-admin-settings.php';
            require_once WCRW_PATH . '/includes/admin/class-admin-products.php';
            require_once WCRW_PATH . '/includes/admin/class-admin-order.php';
            require_once WCRW_PATH . '/includes/admin/class-requests-list-table.php';
            require_once WCRW_PATH . '/includes/admin/class-ajax.php';
        }

        require_once WCRW_PATH . '/includes/class-frontend.php';
        require_once WCRW_PATH . '/includes/class-customer.php';
        require_once WCRW_PATH . '/includes/class-warranty-item.php';
        require_once WCRW_PATH . '/includes/class-form-handler.php';
    }

    /**
    * Init all actions
    *
    * @since 1.0.0
    *
    * @return void
    **/
    private function init_hooks() {
        add_action( 'init', [ $this, 'localization_setup' ] );
        add_action( 'init', [ $this, 'init_classes' ] );
        add_filter( 'woocommerce_email_classes', [ $this, 'register_email' ], 40 );
        add_filter( 'woocommerce_email_actions' , array( $this, 'register_email_actions' ) );
    }

    /**
    * Inistantiate all classes
    *
    * @since 1.0.0
    *
    * @return void
    **/
    public function init_classes() {
        if ( is_admin() ) {
            new WCRW_Admin_Settings();
            new WCRW_Admin_Product();
            new WCRW_Admin_Order();
            new WCRW_Admin_Ajax();
        }
        new WCRW_Frontend();
        new WCRW_Customer();
        new WCRW_Handle_Form();
    }

    /**
     * Initialize plugin for localization
     *
     * @since 1.0.0
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'wc-return-warranty', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Register all emails
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_email( $emails ) {
        require_once WCRW_PATH . '/includes/emails/class-create-request-to-admin.php';
        require_once WCRW_PATH . '/includes/emails/class-create-request-to-customer.php';
        require_once WCRW_PATH . '/includes/emails/class-cancel-request.php';
        require_once WCRW_PATH . '/includes/emails/class-update-status.php';

        $emails['WCRW_Create_Request_Admin']    = new WCRW_Create_Request_Admin();
        $emails['WCRW_Create_Request_Customer'] = new WCRW_Create_Request_Customer();
        $emails['WCRW_Cancel_Order_Request']    = new WCRW_Cancel_Order_Request();
        $emails['WCRW_Update_Request']          = new WCRW_Update_Request();

        return $emails;
    }

    /**
     * Load email actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_email_actions( $actions ) {
        $email_actions = apply_filters( 'wcrw_email_actions', array(
            'wcrw_created_warranty_request',
            'wcrw_created_cancel_order_request',
            'wcrw_update_request_status'
        ) );

        foreach ( $email_actions as $action ) {
            $actions[] = $action;
        }

        return $actions;
    }
}


$wcrw = WC_Return_Warranty::init();

register_activation_hook( __FILE__, array( 'WC_Return_Warranty', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WC_Return_Warranty', 'deactivate' ) );
