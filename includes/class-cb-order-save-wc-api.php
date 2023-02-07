<?php

	/**
	 * The file that defines the core plugin class
	 *
	 * A class definition that includes attributes and functions used across both the
	 * public-facing side of the site and the admin area.
	 *
	 * @link       https://webytude.com
	 * @since      1.0.0
	 *
	 * @package    Cb_Order_Save_Wc
	 * @subpackage Cb_Order_Save_Wc/includes
	 */

	/**
	 * The core plugin class.
	 *
	 * This is used to define internationalization, admin-specific hooks, and
	 * public-facing site hooks.
	 *
	 * Also maintains the unique identifier of this plugin as well as the current
	 * version of the plugin.
	 *
	 * @since      1.0.0
	 * @package    Cb_Order_Save_Wc
	 * @subpackage Cb_Order_Save_Wc/includes
	 * @author     Webytude <mann.webytude@gmail.com>
	 */

class Cb_Order_Save_Wc_API {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name  The name of this plugin.
	 * @param      string    $version  The version of this plugin.
	 */

	 /**
	 * The __construct function for add actions
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'parse_request', array( $this, 'handle_api_requests' ) );
	}

	 /**
	 * The  handle_api_requests function for  get api requesrs
	 * @since    1.0.0
	 */
	function handle_api_requests(){
	    global $wp,$wpdb;
		$wpdb->prefix;

		if ( ! empty( $_GET['cb-api'] ) ) { // WPCS: input var okay, CSRF ok.
			$wp->query_vars['cb-api'] = sanitize_key( wp_unslash( $_GET['cb-api'] ) ); // WPCS: input var okay, CSRF ok.
		}

		// cb-api endpoint requests.
		if ( ! empty( $wp->query_vars['cb-api'] ) ) {
			if( $wp->query_vars['cb-api'] == "clickbank_order" ){
				// Buffer, we won't want any output here.
				ob_start();
				$secretKey = get_option( 'cb_secretkey' );

				// get JSON from raw body...
				$message = json_decode(file_get_contents('php://input'));
				
				// Pull out the encrypted notification and the initialization vector for
				// AES/CBC/PKCS5Padding decryption
				$encrypted = $message->{'notification'};
				$iv = $message->{'iv'};
				#error_log("IV: $iv");
				 
				// decrypt the body...
				$decrypted = trim(
				 openssl_decrypt(base64_decode($encrypted),
				 'AES-256-CBC',
				 substr(sha1($secretKey), 0, 32),
				 OPENSSL_RAW_DATA,
				 base64_decode($iv)), "\0..\32");

				 
				////UTF8 Encoding, remove escape back slashes, and convert the decrypted string to a JSON object...
				$sanitizedData = utf8_encode(stripslashes($decrypted));
				$cb_order_detail = json_decode($decrypted);

				if(empty($cb_order_detail)){
                    die("-1");
				}

				$receipt = $cb_order_detail->receipt;
				
				$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}clickbank WHERE receipt='$receipt'");
					if(!empty($results)){
						die('Already exit');
					}
					else{
						$this->cb_create_order( $cb_order_detail );
						// Done, clear buffer and exit.
						ob_end_clean();
						die('Order Successfull');
					}
			}
		}
	}

	 /**
	 * The  cb_create_order function for create order request
	 * @since    1.0.0
	 */

	function cb_create_order( $request ){
		global $wpdb;
		
		#$today_date = date('Y-m-d', time());
		#$filename = plugin_dir_path(__FILE__) . $today_date.".txt";
		#$myfile = fopen($filename, "a");
		#ob_start();

        WC()->cart->empty_cart();

		$email = $request->customer->shipping->email;
        $receipt = $request->receipt;
		$firstname = $request->customer->shipping->firstName;
		$lastname = $request->customer->shipping->lastName;
		$fullname = $request->customer->shipping->fullName;
		$email = $request->customer->shipping->email;

		$address_obj = $request->customer->shipping->address;
		$address_1 = $address_obj->address1;
		$address_2 = $address_obj->address2;
		$city = $address_obj->city;
		$postalCode = $address_obj->postalCode;
		$country = $address_obj->country;

		#echo "<pre>"; print_r($request); echo "</pre>";
		#$data = ob_get_clean();
		#fwrite($myfile, $data);
		// fclose($myfile);
		#ob_start();

		$wpdb->insert($wpdb->prefix.'clickbank', array(
			'email' => $email,
			'receipt' => $receipt	
		));
		
		$address_arr = array(
			'first_name' => $firstname,
			'last_name'  => $lastname,
			'full_name'  => $fullname,
			'email'     => $email,
			'address_1'   => $address_1,
			'address_2'   => $address_2,
			'city'   => $city,
			// 'postalCode'   => $postalCode
			'country'   => $country
		); 	  

		$coupon_code = "";
		foreach ($request->lineItems as $key => $value) {
			$new_product_price = $value->accountAmount;
			$itemNo = $value->itemNo;
			$product_id = $this->cb_get_product_id( $itemNo );
			if (!empty($product_id)) {
				WC()->cart->add_to_cart( $product_id, 1); 
			}
		}	

		if( !empty( $coupon_code ) ){
			$coupon = new \WC_Coupon( $coupon_code );   
			$discounts = new \WC_Discounts( WC()->cart );
			$valid_response = $discounts->is_coupon_valid( $coupon );
			if ( ! is_wp_error( $valid_response ) ) {
				WC()->cart->apply_coupon( $coupon_code );
			}
		}

		$checkout = WC()->checkout();
		$order_id = $checkout->create_order(array());
		$order = wc_get_order($order_id);
		
		$order->set_address( $address_arr, 'billing' );
		$order->set_address( $address_arr, 'shipping' );
		$order->calculate_totals();
		$order->update_status("processing", 'Imported order', TRUE);
		
	}

	/**
	 * The cb_get_product_id function for get the produvt ids
	 *
	 * @since    1.0.0
	*/
	function cb_get_product_id( $itemNo ){
		global $wpdb;

		$params = array(
			'post_type' => 'product',
			'meta_query' => array(
				array(
					'key' => '_dummy_text_input', //meta key name here
					'value' => $itemNo, 
					'compare' => '=',
				)
			) 
		);
		$wc_query = new WP_Query($params);
				
		if( $wc_query->have_posts() ) {		
		  while( $wc_query->have_posts() ) {		
			$wc_query->the_post();		
				return get_the_ID($wc_query->ID);			
		  } // end while
		} // end if
		else{
			return false;
		}
	}

}
