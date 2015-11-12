<?php
if(!class_exists('DukaPress_Gateway')) {

	class DukaPress_Gateway{

		//private gateway slug. Lowercase alpha (a-z) and dashes (-) only please!
		var $plugin_name = '';

		//shortname of plugin
		var $plugin_slug = '';

		//always contains the url to send payment notifications to if needed by your gateway. Populated by the parent class
		var $ipn_url;

		var $return_url;

		var $cancel_url;

		//Determine if to use form submit or other method
		var $form_submit = true;

		//Supported Currencies
		var $currencies;

		var $error = false;

		var $error_message = "";

		//Do not overide
		function __construct() {
			$this->on_create();

			//check required vars
			if (empty($this->plugin_name) || empty($this->plugin_slug))
				wp_die( __("You must override all required vars in your payment gateway plugin!", "dp-lang") );
			$this->set_up_ipn_url();

			add_action('admin_enqueue_scripts', array(&$this, 'admin_scripts'));


			add_action( 'dpsc_payment_submit_' . $this->plugin_slug, array(&$this, 'process_payment_form'), 10, 4 ); //Submit gateway
			add_action( 'dpsc_payment_return_'. $this->plugin_slug, array(&$this, 'process_ipn_return') ); //Process IPN
			add_action( 'dpsc_order_log_'. $this->plugin_slug , array(&$this, 'order_form_action'), 10, 1 ); //Order logs for current method
			add_action( 'dpsc_gateway_option_'. $this->plugin_slug , array(&$this, 'set_up_options'), 10, 1 ); //Set up options
			add_action( 'dpsc_payment_update_'. $this->plugin_slug , array(&$this, 'update_payment'), 10, 1 ); //Set up options

			global $dukapress;
			//Register the plugin
			$dukapress->register_gateway(get_class($this), $this->plugin_name, $this->plugin_slug);
		}

		/**
		 * Runs when your class is instantiated. Use to setup your plugin instead of __construct()
		 */
		function on_create(){
		}

		/**
		 * Load custom Admin scripts
		 */
		function admin_scripts($page){

		}

		//Sets Up IPN URL
		//Do not overide
		function set_up_ipn_url(){
			$this->ipn_url = home_url('payment-return/' . $this->plugin_slug);
			$checkout_path = urlencode(dukapress_checkout_step_url('checkout'));
			$cancel_path = $checkout_path;
	    if (count($cancel_path) > 1) {
	        $cancel_path .= '&cancel=1';
	    } else {
	        $cancel_path .= '?cancel=1';
	    }
			$this->return_url = $checkout_path;
			$this->cancel_url = $cancel_path;
		}

		/**
		 * Process Payment
		 */
		function process_payment_form($cart, $user_info, $shipping, $order_id){

		}

		function process_ipn_return(){

		}

		/**
		 * Action to be done on the order form
		 */
		function order_form_action($invoice){
		}

		/**
		 * Set Up Payment gateway options
		 */
		function set_up_options($settings){
		}

		/**
		 * Update payment status and save order
		 *
		 */
		function update_payment($request){

		}
	}
}
?>
