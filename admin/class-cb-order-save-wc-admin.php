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

		add_action( 'admin_menu', array( $this, 'create_plugin_settings_pages' ) );
		add_action( 'admin_init', array( $this, 'setup_section' ) );

		add_filter( 'woocommerce_product_data_tabs', array( $this, 'cb_woo_clickBank_product_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'cb_woocommerce_product_data_panels' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'cb_save_woocommerce_product_data_panels' ) );

	}

	public function create_plugin_settings_pages() {
		// Add the menu item and page
		$page_title = __('ClickBank Settings Page','cb-order-save-wc');
		$menu_title = __('ClickBank Options','cb-order-save-wc');
		$capability = 'manage_options';
		$slug = 'clickbank_options';
		$callback = array( $this, 'plugin_settings_page_contents' );
		$icon = 'dashicons-admin-generic';
		$position = 100;

		add_menu_page( $page_title, $menu_title, $capability, $slug, $callback, $icon, $position );
	}

	public function plugin_settings_page_contents() { ?>
		<div class="wrap">
			<h2><?php echo _e('ClickBank Settings Page', 'cb-order-save-wc' )?></h2>
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

		$fields = array(
			array(
				'uid' => 'cb_secretkey',
				'label' => __('Secret Key','cb-order-save-wc'),
				'section' => 'our_first_section',
				'type' => 'text',
				'options' => false,
				'placeholder' => '',
				'supplemental' => __('Get Secret Key from, ClickBank.com under "My Site" -> "Advanced Tools" -> "Secret Key"','cb-order-save-wc'),
			),
			array(
				'uid' => 'cb_ipn_url',
				'label' => __('IPN URL','cb-order-save-wc'),
				'section' => 'our_first_section',
				'type' => 'label',				
				'default' => site_url().'/?cb-api=ClickBank_Order',
				'supplemental' => __('Paste this URL on ClickBank.com under "My Site" -> "Advanced Tools" -> "Instant Notification URL"','cb-order-save-wc'),
			),
			
		);
		foreach( $fields as $field ){
			add_settings_field( $field['uid'], $field['label'], array( $this, 'field_callbacks' ), 'cb-order', $field['section'], $field );
			register_setting( 'cb-order', $field['uid'] );
		}
	}

	public function section_callbacks( $argument ) {
		switch( $argument['id'] ){
			case 'our_first_section':
			break;
		}
	}

	public function field_callbacks( $arguments ) {
		$value = get_option( $arguments['uid'] ); // Get the current value, if there is one
		
			// Check which type of field we want
			switch( $arguments['type'] ){

				case 'text': // If it is a text field
					printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%5$s" /><br><p>%4$s</p>', $arguments['uid'], $arguments['type'], $arguments['placeholder'], $arguments['supplemental'], $value );
					break;
				case 'label': 
					printf( '<label><strong>%1$s</strong></label><br><p>%2$s</p>', $arguments['default'], $arguments['supplemental'] );
					break;
			}
	}
	
	public function cb_woo_clickBank_product_tab( $tabs ) {
		$tabs['clickbank '] = [
			'label' => __('ClickBank ', 'cb-order-save-wc'),
			'target' => 'additional_product_data',
			'class' => ['show_if_simple'],
			'priority' => 70
		];
		return $tabs;
	}
	public function cb_woocommerce_product_data_panels(){
		?><div id="additional_product_data" class="panel woocommerce_options_panel hidden"><?php
 
		woocommerce_wp_text_input([
			'id' => '_dummy_text_input',
			'label' => __('ClickBank Product Item Number:', 'cb-order-save-wc'),
			'wrapper_class' => 'show_if_simple',
			'desc_tip'    => true,
			'description' => __('Get Product Item Number from, ClickBank.com under "My Products" -> First column as "Item Number" ','cb-order-save-wc')
		]);
		?></div><?php
	}

	public function cb_save_woocommerce_product_data_panels($post_id){
		$product = wc_get_product($post_id);	
		$product->update_meta_data('_dummy_text_input', sanitize_text_field($_POST['_dummy_text_input'])); 
		$product->save();
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
	 * classs.
	 * between the defined hooks and the functions defined in this
	 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/cb-order-save-wc-admin.js', array( 'jquery' ), $this->version, false );

	}

}
