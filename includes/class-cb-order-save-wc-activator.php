<?php

	/**
	 * Fired during plugin activation
	 *
	 * @link       https://webytude.com
	 * @since      1.0.0
	 *
	 * @package    Cb_Order_Save_Wc
	 * @subpackage Cb_Order_Save_Wc/includes
	 */

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    Cb_Order_Save_Wc
	 * @subpackage Cb_Order_Save_Wc/includes
	 * @author     Webytude <mann.webytude@gmail.com>
	 */

class Cb_Order_Save_Wc_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$wpdb->prefix}clickbank (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		-- time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		email varchar(255) NOT NULL,
		receipt varchar(255) NOT NULL,
		-- url varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

	}

}
