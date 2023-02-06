<?php

	/**
	 * Define the internationalization functionality
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @link       https://webytude.com
	 * @since      1.0.0
	 *
	 * @package    Cb_Order_Save_Wc
	 * @subpackage Cb_Order_Save_Wc/includes
	 */
	/**
	 * Define the internationalization functionality.
	 *
	 * Loads and defines the internationalization files for this plugin
	 * so that it is ready for translation.
	 *
	 * @since      1.0.0
	 * @package    Cb_Order_Save_Wc
	 * @subpackage Cb_Order_Save_Wc/includes
	 * @author     Webytude <mann.webytude@gmail.com>
	 */
class Cb_Order_Save_Wc_i18n {

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'cb-order-save-wc',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
