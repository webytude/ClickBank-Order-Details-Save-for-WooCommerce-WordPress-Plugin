<?php

	/**
	 * The plugin bootstrap file
	 *
	 * This file is read by WordPress to generate the plugin information in the plugin
	 * admin area. This file also includes all of the dependencies used by the plugin,
	 * registers the activation and deactivation functions, and defines a function
	 * that starts the plugin.
	 *
	 * @link              https://webytude.com
	 * @since             1.0.0
	 * @package           Cb_Order_Save_Wc
	 *
	 * @wordpress-plugin
	 * Plugin Name:       Save ClickBank Order Details for WooCommerce
	 * Plugin URI:        https://github.com/webytude/cb-order-save-wc
	 * Description:       This plugin allows you to synchronize ClickBank orders and customers information with WooCommerce orders and customers information.
	 * Version:           1.0.0
	 * Author:            Webytude
	 * Author URI:        https://webytude.com
	 * License:           GPL-2.0+
	 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
	 * Text Domain:       cb-order-save-wc
	 * Domain Path:       /languages
	 */

	// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

	/**
	 * Currently plugin version.
	 * Start at version 1.0.0 and use SemVer - https://semver.org
	 * Rename this for your plugin and update it as you release new versions.
	 */
define( 'CB_ORDER_SAVE_WC_VERSION', '1.0.0' );

/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-cb-order-save-wc-activator.php
	 */
function activate_cb_order_save_wc() {
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cb-order-save-wc-activator.php';
	Cb_Order_Save_Wc_Activator::activate();
}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-cb-order-save-wc-deactivator.php
	 */
function deactivate_cb_order_save_wc() {
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cb-order-save-wc-deactivator.php';
	Cb_Order_Save_Wc_Deactivator::deactivate();
}

	register_activation_hook( __FILE__, 'activate_cb_order_save_wc' );
	register_deactivation_hook( __FILE__, 'deactivate_cb_order_save_wc' );

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path( __FILE__ ) . 'includes/class-cb-order-save-wc.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
function run_cb_order_save_wc() {

	$plugin = new Cb_Order_Save_Wc();
	$plugin->run();
}
run_cb_order_save_wc();
