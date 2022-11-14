<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://webytude.com
 * @since      1.0.0
 *
 * @package    Cb_Order_Save_Wc
 * @subpackage Cb_Order_Save_Wc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cb_Order_Save_Wc
 * @subpackage Cb_Order_Save_Wc/admin
 * @author     Webytude <mann.webytude@gmail.com>
 */
class Cb_Order_Save_Wc_Admin {

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
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_action( 'parse_request', array( $this, 'handle_api_requests' ) );

		add_action( 'admin_menu', array( $this, 'create_plugin_settings_pages' ) );
		add_action( 'admin_init', array( $this, 'setup_section' ) );
		add_action( 'admin_init', array( $this, 'setup_field' ) );
		
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cb_Order_Save_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cb_Order_Save_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/cb-order-save-wc-admin.css', array(), $this->version, 'all' );

	}

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
					
					// $body = file_get_contents('php://input');
				    // $cb_order_detail = json_decode($body);

				    $secretKey = "OCUJFSSDWWYQSTQW";
				    // get JSON from raw body...
					$message = json_decode(file_get_contents('php://input'));				    

					// Pull out the encrypted notification and the initialization vector for
					// AES/CBC/PKCS5Padding decryption
					$encrypted = $message->{'notification'};
					$iv = $message->{'iv'};

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
                          
                    //   echo '<pre>'; print_r($order); echo '</pre>';die;
					if(empty($cb_order_detail)){
                        die("-1");
					}

					// $this->update_order( $cb_order_detail );
					// die;
					// $this->create_order( $cb_order_detail );
					// die;
					$receipt = $cb_order_detail->receipt;
					
					$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}clickbank WHERE receipt='$receipt'");
						if(!empty($results)){
							die('Already exit');
						}
						else{
							$this->create_order( $cb_order_detail );
							// Done, clear buffer and exit.
							ob_end_clean();
							die('Order Successfull');
						}
				}
			 }
	}
    
    function create_order( $request ){
		global $wpdb;
		$wpdb->prefix;

		$today_date = date('Y-m-d', time());
		$filename = plugin_dir_path(__FILE__) . $today_date.".txt";
		$myfile = fopen($filename, "a");
		ob_start();

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

		$wpdb->insert($wpdb->prefix.'clickbank', array(
			'email' => $email,
			'receipt' => $receipt	
		));
		
		$start_date = date('Y-m-d H:i:s');

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

		// Now we create the order
		$order = wc_create_order();

        // $new_product_price = $request->lineItems[0]->accountAmount;
		foreach ($request->lineItems as $key => $value) {

			$new_product_price = $value->accountAmount;

			// $product = wc_get_product(13);
			// $product = wc_get_product( wc_get_product_id_by_sku( $request->lineItems[0]->itemNo ));
			$product_id =  wc_get_product_id_by_sku( $request->lineItems[0]->itemNo );
		
			if (!empty($product_id)) {
				
			
				$product = wc_get_product( $product_id );
				$product->set_price( $new_product_price );

				// The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
				$order->add_product( $product, 1); // This is an existing SIMPLE product

			}
			// else{
			// 	// $orderNote = $request->lineItems[0]->itemNo . "product not found in Products";
			// 	$orderNote = $request->lineItems[0]->itemNo . "product not found in Products";
			// }

		}	


		$order->set_address( $address_arr, 'billing' );
		$order->set_address( $address_arr, 'shipping' );
		$order->calculate_totals();
		$order->update_status("completed", 'Imported order', TRUE);

		echo "<pre>"; print_r("completed order -> ".$receipt); echo "</pre>";
		$data = ob_get_clean();
		fwrite($myfile, $data);
		fclose($myfile);
		die;
		
		

	}


	// function update_order( $request ){
	// 	global $wpdb;
	// 	$order_id = 693;
	// 	$order = wc_get_order( $order_id );

	// 	foreach ($request->lineItems as $key => $value) {

	// 		$new_product_price = $value->accountAmount;
	// 		echo '<pre>'; print_r($new_product_price); echo '</pre>';die;

	// 		$product_id =  wc_get_product_id_by_sku( $request->lineItems[0]->itemNo );
	// 		if (!empty($product_id)) {
				
	// 			$product = wc_get_product( $product_id );
	// 			$product->set_price( $new_product_price );

	// 			// The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
	// 			$order->add_product( $product, 1); // This is an existing SIMPLE product

	// 		}

	// 	}
	// 	// $product = wc_get_product(14);
	// 	// $product->set_price(55); 

	// 	// // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
	// 	// $order->add_product( $product, 1); // This is an existing SIMPLE product
	// 	$order->calculate_totals();
	// 	$order->update_status("completed", 'Imported order', TRUE);
    //     die("order updated Success");
	// }
	
// ClickBank Options--------------------------------------------------------------------------------------------------- 

	public function create_plugin_settings_pages() {
		// Add the menu item and page
		$page_title = 'ClickBank Settings Page';
		$menu_title = 'ClickBank Options';
		$capability = 'manage_options';
		$slug = 'ClickBank_options';
		$callback = array( $this, 'plugin_settings_page_contents' );
		$icon = 'dashicons-admin-generic';
		$position = 100;

		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
	}

	public function plugin_settings_page_contents() { ?>
		<div class="wrap">
			<h2>ClickBank Settings Page</h2>
			<form method="post" action="options.php">
				<?php
					settings_fields( 'cb-order' );						
					do_settings_sections( 'cb-order' );
					submit_button();
				?>
			</form>
		</div> <?php
	}

	public function setup_section() {
		add_settings_section( 'our_first_section', '', array( $this, 'section_callbacks' ), 'cb-order' );
	}

	public function section_callbacks( $argument ) {
		switch( $argument['id'] ){
			case 'our_first_section':
				// echo 'This is the first description here!';
				break;
		}
	}


	public function setup_field() {
		
		$fields = array(
			array(
				'uid' => 'cb_secretkey',
				'label' => 'cb_secretkey',
				'section' => 'our_first_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => 'Title',
				//'helper' => 'Does this help?',
				//'supplemental' => 'I am underneath DATE!',
				// 'default' => '01/01/2015'
			),
			
		);
		foreach( $fields as $field ){
			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callbacks' ), 'cb-order', $field['section'], $field );
			register_setting( 'cb-order', $field['uid'] );
		}
	}

	public function field_callbacks( $arguments ) {
		$value = get_option( $arguments['uid'] ); // Get the current value, if there is one
			if( ! $value ) { // If no value exists
				$value = $arguments['default']; // Set to our default
			}

			// Check which type of field we want
			switch( $arguments['type'] ){
				case 'text': // If it is a text field
					printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $value );
					break;
				

					$filename = basename( get_attached_file( $value ) );
					printf( '<input class="upload_image_button button button-primary" type="button" value="Add Icon" /><label class="data_file_name">%3$s</label><input type="hidden" class="data_file" name="%1$s" value="%2$s" />', $arguments['uid'],  $value, $filename );
					break;
			}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Cb_Order_Save_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cb_Order_Save_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-order-save-wc-admin.js', array( 'jquery' ), $this->version, false );

	}

}
