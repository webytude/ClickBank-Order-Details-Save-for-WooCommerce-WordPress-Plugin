<?php

/**
 * Customer Preferences
 */
class CustomerPreferences
{
	
	function __construct()
	{
		
		add_action('admin_menu', array($this, 'csk_register_admin_customer_preferences_submenu'));

		add_action('wp_ajax_nopriv_save_customer_fruits_box', array($this, 'save_customer_fruits_box_callback'));
        add_action('wp_ajax_save_customer_fruits_box', array($this, 'save_customer_fruits_box_callback'));

        add_action('wp_ajax_nopriv_save_customer_weekly_fruits_box', array($this, 'save_customer_weekly_fruits_box_callback'));
        add_action('wp_ajax_save_customer_weekly_fruits_box', array($this, 'save_customer_weekly_fruits_box_callback'));


        add_action('wp_ajax_nopriv_save_customer_alternate_address', array($this, 'save_customer_alternate_address_callback'));
        add_action('wp_ajax_save_customer_alternate_address', array($this, 'save_customer_alternate_address_callback'));

	}

	//Add Admin menu Item 
	public function csk_register_admin_customer_preferences_submenu()
	{
		add_submenu_page(
	        'woocommerce',
	        'Customer Preferences',
	        'Customer Preferences',
	        'manage_options',
	        'admin_manage_customer_preferences',
	        array($this, 'customer_preferences_callback') 
	    );
	}


	public function customer_preferences_callback() {

		$users = get_users();
		$user_output = '';
		foreach ($users as $key => $user) {

			$selected = '';
			if ( $user->ID == $_GET['user_id'] ) {
				$selected = 'selected="selected"';
			}

			$user_output .=	'<option value="'.$user->ID.'" '.$selected.'>#'.$user->ID.' '.$user->user_login.'</option>';
		}


		?>

		<div class="wrap"><div id="icon-tools" class="icon32"></div>
			<h2>Customer Preferences </h2>
		</div>
		<div class="wrap admin_manage_delivery">
			<form method="get">
				<input type="hidden" name="page" value="admin_manage_customer_preferences">	
				<select name="user_id" id="user_list" required="">
					<option value="">Select User</option>
					<?php echo $user_output; ?>				
				</select>
				<input type="submit" name="submit" value="Customer Preferences">
			</form>
		</div>

		<?php 


		if ( !empty($_GET['user_id']) && isset($_GET['user_id']) ) {

			$fruits_box = get_option( 'fruit_box' );

			$user_id = $_GET['user_id'];

			$my_fruits_box = get_user_meta( $user_id, 'my_fruits_box', true );

			global $wpdb;
		  	$table_name = $wpdb->prefix . "preferences";
			$result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id ");

			foreach ($result as $k) {
				$my_fruits_box = $k->current_preferences;
			}


			if (!empty($my_fruits_box)) {
				$my_fruits_box = unserialize($my_fruits_box);
			}


			$checkbox_output = '';

			foreach ($fruits_box as $fruit) {

				if (in_array($fruit, $my_fruits_box) || empty($my_fruits_box)) {
					$checkbox = 'checked';
				}else{
					$checkbox = '';
				}
				
				$checkbox_output .= '<p class="form-checkbox"><label><input type="checkbox" name="fruits_box[]" value="'.$fruit.'" '.$checkbox.'> <span>'.$fruit.'</span></label></p>';
			}

			?>

			<div class="fruits-box">
				<h3>Customer Fruit & Veg Box</h3>
				<p>Uncheck any Fruit/Vegetables which you would prefer not to have in your box</p>
				<form method="post" id="customerfruitsbox">
					<div class="input-field">
						<?php echo $checkbox_output; ?>
					</div>
					<div class="action" style=" clear: both; ">
						<input type="hidden" name="action" value="save_customer_fruits_box">
						<input type="hidden" name="user_id" value="<?php echo $_GET['user_id']; ?>">
						<input type="submit" name="save" value="Save">
					</div>
				</form>
			</div>


		<?php 

		$week_fruit_output = '';
		$week_fruits_box = get_user_meta( $user_id, 'weekly_fruit_veg_box', true );
		if (!empty($week_fruits_box)) {
			$week_fruits_box = unserialize($week_fruits_box);
		}else{
			$week_fruits_box = array();
		}

		foreach ($fruits_box as $key => $week_fruit) {

			$checkbox = '';
			if (in_array($week_fruit, $week_fruits_box)) {
				$checkbox = 'checked';
			}


			$week_fruit_output .= '<p class="form-checkbox"><label><input type="checkbox" name="week_fruit[]" value="'.$week_fruit.'" '.$checkbox.'> <span>'.$week_fruit.'</span></label></p>';
		}

		?>

		<div class="weekly_fruit_veg_box">
			<!-- <h2>Fruit & Veg that you want every week</h2> -->
			<p>Check Fruit/Vegetables which you would prefer to have every week</p>
			<form method="post" id="customerweeklyfruitsbox">
				<div class="input-field">
					<?php echo $week_fruit_output; ?>
				</div>
				<div class="action" style=" clear: both; ">
					<input type="hidden" name="action" value="save_customer_weekly_fruits_box">
					<input type="hidden" name="user_id" value="<?php echo $_GET['user_id']; ?>">
					<input type="submit" name="save" value="Save">
				</div>
			</form>
		</div>

		<!-- weekly fruit/veg Box -->

		<?php 

		//$user_id = get_current_user_id();

		$alternate_address = get_user_meta($user_id, 'my_alternate_address', true);
		if (!empty($alternate_address)) {
			$alternate_address = unserialize($alternate_address);

			$at_address = $alternate_address['at_address'];
			$at_address2 = $alternate_address['at_address2'];
			$at_city = $alternate_address['at_city'];
			$at_postcode = $alternate_address['at_postcode'];
			$at_phone = $alternate_address['at_phone'];
		}
		?>		

		<div class="alternate-delivery" style="margin-top: 40px">
			<h2>Add Customer Alternate Delivery Address</h2>
			<p>If you want your box delivered elsewhere for a week please leave the address below</p>
			<form method="post" id="customer_alternate_address">	
			<p>
				<label>Street address</label>
				<input type="text" name="at_address" required="" value="<?php echo $at_address; ?>">
				<span style="margin-top: 10px; display: block;"></span>
				<input type="text" name="at_address2" value="<?php echo $at_address2; ?>">
			</p>
			<p>
				<label>Town / City</label>
				<input type="text" name="at_city" required="" value="<?php echo $at_city; ?>">
			</p>
			<p>
				<label>Postcode</label>
				<input type="text" name="at_postcode" required="" value="<?php echo $at_postcode; ?>">
			</p>
			<p>
				<label>Phone</label>
				<input type="text" name="at_phone" required="" value="<?php echo $at_phone; ?>">
			</p>
			<div class="action" style=" clear: both; ">
				<input type="hidden" name="user_id" value="<?php echo $_GET['user_id']; ?>">
				<input type="hidden" name="action" value="save_customer_alternate_address">
				<input type="submit" name="submit" value="Save Address">
			</div>
			</form>
		</div>

		<!-- Add Alternate Delivery Address -->

			<?php 

		}



		?>

		<style type="text/css">
			#customerfruitsbox .form-checkbox, #customerweeklyfruitsbox .form-checkbox {
			    display: inline-block;
			    width: 25%;
			}

			.weekly_fruit_veg_box {
			    margin-top: 30px;
			    padding-top: 30px;
			    border-top: 1px solid #d8d8d8;
			    margin-bottom: 30px;
			    padding-bottom: 30px;
			    border-bottom: 1px solid #d8d8d8;
			}

			.action input[type="submit"] {
			    padding: .5em 1.6em;
			    border: none;
			    border-radius: 3px;
			    color: #fff;
			    background: #111;
			    font-size: 15px;
			    font-weight: 600;
			    transition: all .2s;
			    margin-top: 20px;
			    cursor: pointer;
			}
			.admin_manage_delivery .ajaxloader_footer {
			    width: 30px;
			    display: inline-block;
			    vertical-align: middle;
			    margin-left: 20px;
			}

			/*.admin_manage_delivery .ajaxloader_footer img {
			    max-width: 100%;
			}
			div#admin_manage_delivery {
			    position: relative;
			}
			div#admin_manage_delivery .ajaxloader_footer {
			    position: absolute;
			    top: 0;
			    left: 0;
			    background-color: hsl(0deg 0% 0% / 38%);
			    width: 100%;
			    height: 100%;
			}*/
			.action .ajaxloader_footer img {			
			    width: 30px;			
			}

			.ajaxloader_footer {
			    display: inline-block;
			    vertical-align: middle;
			    margin-top: 20px;
			}


			#customer_alternate_address label {
			    display: block;
			    font-size: 16px;
			}

			#customer_alternate_address input[type="text"] {
			    display: block;
			    width: 50%;
			    border: 1px solid #d2d6dc;
			    border-radius: .3rem;
			    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 5%);
			    font-size: 15px;
			    padding-left: .7em;
			    padding-right: .7em;
			}

		</style>
		<script type="text/javascript">
			jQuery('#customerfruitsbox').submit(function(e){
	            e.preventDefault();
	            var data = jQuery(this).serialize(); 
	            jQuery.ajax({
	              type: "POST",
	              url: '<?php echo admin_url() . 'admin-ajax.php'; ?>',
	              dataType: 'json',
	              data: data,
	              beforeSend: function(){
	              	jQuery('#customerfruitsbox .action').append('<div class="ajaxloader_footer"><img src="/wp-content/uploads/2021/06/loading-buffering.gif"></div>');

	                //jQuery(".ajaxloader_footer").show(); 
	              },complete:function(){
	              	jQuery(".action .ajaxloader_footer").remove(); 
	                //jQuery(".ajaxloader_footer").hide(); 
	              },          
	              success: function (data) {
	                location.reload();        
	              }
	            });
	        });

			jQuery('#customerweeklyfruitsbox').submit(function(e){
	            e.preventDefault();
	            var data = jQuery(this).serialize(); 
	            jQuery.ajax({
	              type: "POST",
	              url: '<?php echo admin_url() . 'admin-ajax.php'; ?>',
	              dataType: 'json',
	              data: data,
	              beforeSend: function(){
	                jQuery('#customerweeklyfruitsbox .action').append('<div class="ajaxloader_footer"><img src="/wp-content/uploads/2021/06/loading-buffering.gif"></div>'); 
	              },complete:function(){
	                jQuery(".action .ajaxloader_footer").remove();  
	              },          
	              success: function (data) {
	                location.reload();        
	              }
	            });
	        });


	        jQuery('#customer_alternate_address').submit(function(e){
	            e.preventDefault();
	            var data = jQuery(this).serialize(); 
	            jQuery.ajax({
	              type: "POST",
	              url: '<?php echo admin_url() . 'admin-ajax.php'; ?>',
	              dataType: 'json',
	              data: data,
	              beforeSend: function(){
	                jQuery('#customer_alternate_address .action').append('<div class="ajaxloader_footer"><img src="/wp-content/uploads/2021/06/loading-buffering.gif"></div>'); 
	              },complete:function(){
	                jQuery(".action .ajaxloader_footer").remove();  
	              },          
	              success: function (data) {
	                location.reload();        
	              }
	            });
	        });


		</script>
		<?php 

	}


	   public function save_customer_fruits_box_callback() {

        global $wpdb;
        $table_name = $wpdb->prefix . "preferences";


        $response = array();

        $fruits_box = $_POST['fruits_box'];

        $user_id = $_POST['user_id'];

		
		

        if (!empty($fruits_box)) {
            //$user_id = get_current_user_id();


            $activity = get_post_meta( $user_id, '_user_activity_log', true );  

        	if (empty($activity)) {
        		$activity = array();
        	}


            $data = serialize($fruits_box);
            update_user_meta( $user_id, 'my_fruits_box', $data );

            $result = $wpdb->get_results("SELECT * FROM $table_name WHERE user_id = $user_id ");

            if (!empty($result)) {
                $preferences = $result[0]->current_preferences;

                $res = $wpdb->update($table_name, array('current_preferences' => $data, 'old_preferences' => $preferences, 'last_update' => date("Y-m-d h:i:s")), array('user_id'=>$user_id));
				
				if ($res) {
                    $response['status'] = "success";
					$response['res'] = $res;
                }

            }else{
				
				

                $res = $wpdb->insert( $table_name, array( 
                    'user_id' => $user_id, 
                    'current_preferences' => $data,                  
                    'created_at' => date("Y-m-d h:i:s"), 
                    'last_update' => date("Y-m-d h:i:s") 
                    )
                );			
				

                if ($res) {
                    $response['status'] = "success";
					$response['res'] = $res;
                }

                $activity[] = array(
		            'activity' => 'fruits box',
		         	'date' => date('Y-m-d'),
		        );

		        update_post_meta($user_id, '_user_activity_log', $activity);


            }
            
        }else{
            $response['status'] = "error";
        }

        echo json_encode($response);
        wp_die();
    }

    public function save_customer_weekly_fruits_box_callback() {
		
		$week_fruit = $_POST['week_fruit'];

		$user_id = $_POST['user_id'];
		
		$response = array();

		if (!empty($week_fruit)) {
			//$user_id = get_current_user_id();
			$activity = get_post_meta( $user_id, '_user_activity_log', true );  

        	if (empty($activity)) {
        		$activity = array();
        	}

        	$data = serialize($week_fruit);
            update_user_meta( $user_id, 'weekly_fruit_veg_box', $data );


            $activity[] = array(
		        'activity' => 'weekly fruit veg box',
		        'date' => date('Y-m-d'),
		    );

		    update_post_meta($user_id, '_user_activity_log', $activity);

		    $response['status'] = "success";

		}else{
            $response['status'] = "error";
        }

		echo json_encode($response);
        wp_die();
	}


	public function save_customer_alternate_address_callback() {
        $response = array();

        $at_address = $_POST['at_address'];
        $at_address2 = $_POST['at_address2'];
        $at_city = $_POST['at_city'];
        $at_postcode = $_POST['at_postcode'];
        $at_phone = $_POST['at_phone'];

        $user_id = $_POST['user_id'];

        if (!empty($at_address) && !empty($at_city)) {

            $address = array('at_address' => $at_address, 'at_address2' => $at_address2, 'at_city' => $at_city, 'at_postcode' => $at_postcode, 'at_phone' => $at_phone);

            //$user_id = get_current_user_id();
            $data = serialize($address);
            update_user_meta( $user_id, 'my_alternate_address', $data );
            $response['status'] = "success";

        }else{
            $response['status'] = "error";
        }

        echo json_encode($response);
        wp_die();
    }

}


new CustomerPreferences;