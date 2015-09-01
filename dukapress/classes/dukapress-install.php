<?php
if(!class_exists('DukaPress_Install')) {
	
	/**
	 * Manage the setup for DukaPress
	 *
	 */
	class DukaPress_Install{
		
		public static function init() {
			self::install_options();
			$checkout_settings = get_option( 'dukapress_checkout_settings' );
			if(empty($checkout_settings)){
				$default_options = array(
					array(
						'name' => 'Full Names',
						'type' => 'text',
						'uname' => 'dpsc_fullname',
						'initial' => '',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						),
					array(
						'name' => 'First Name',
						'type' => 'text',
						'uname' => 'dpsc_firstname',
						'initial' => '',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						),
					array(
						'name' => 'Last Name',
						'type' => 'text',
						'uname' => 'dpsc_lastname',
						'initial' => '',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						),
					array(
						'name' => 'Email',
						'type' => 'text',
						'uname' => 'dpsc_email',
						'initial' => '',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						),
					array(
						'name' => 'Phone',
						'type' => 'text',
						'uname' => 'dpsc_phone',
						'initial' => '',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						),
					array(
						'name' => 'Country',
						'type' => 'select',
						'uname' => 'dpsc_country',
						'initial' => '--'.__('Select Country','dp-lang').'--',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						),
					array(
						'name' => 'State',
						'type' => 'text',
						'uname' => 'dpsc_state',
						'initial' => '',
						'mandatory' => 'checked',
						'visible' => 'checked',
						'delete' => false
						)
				);
				update_option( 'dukapress_checkout_settings', $default_options );
			}
		}
		
		
		/** 
		 * Install and set up the default options
		 *
		 */
		public static function install_options(){
			$old_settings = get_option( 'dukapress_settings' );
			
			$default_settings = array(
				'pdf_generation' => 0,
				'user_registration' => 0,
				'product_img_height' => 150,
			 	'product_img_width' => 150,
				'grid_img_height'  => 150,
				'grid_img_width' => 150,
				'thumb_img_height'  => 50,
				'thumb_img_width' => 50,
				'image_effect' => 'mz_effect',
				'shop_city' => '',
				'shop_zip' => '',
				'shop_state' => '',
				'shop_address' => '',
				'shop_name' => '',
				'gateways' => array(
					'worldpay' => array(
						'currency' => '',
						'mode' => 'sandbox'
					),
					'alertpay' => array(
						'currency' => '',
					),
					'paypal' => array(
						'currency' => 'USD',
						'mode'	=> 'sandbox'
					)
				),
				'dp_shop_mode' => '',
				'disable_cart' => false,
				'tax'						 => array(
					'rate'					 => 0,
					'label'					 => __( 'Taxes', 'dp-lang' )
				),
				'shop_country' => '',
				'shop_currency' => 'USD',
				'shop_currency_position' => 1,
				'currency_code_enable' => '',
				'currency_symbol' => '$',
				'page_urls' => array(
					'thankyou_url' => '',
					'thankyou_id' => '',
					'affiliate_url' => '',
					'affiliate_id' => '',
					'terms_url' => '',
					'terms_id' => ''
				),
				'inventory_threshhold' => '',
				'inventory_remove' => '',
				'max_downloads' => 3,
				'per_page' => 20,
				'ga_ecommerce' => 'none',
				'slugs' => array(
					'products' => __('products','dp-lang'),
					'category' => __('category','dp-lang'),
					'tag' => __('tag','dp-lang')
				),
				'mail' => array(
					'order_placed' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' => __( 'New Order %inv% ', 'dp-lang' ),
							'body' => __('New order from %fname% %lname% 
New Order ID %inv% has been created. Here are the details:

Order Information:
%order-details%

You can view the order here: %order-log-transaction%', 'dp-lang' )
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' =>  __( 'New Order %inv% ', 'dp-lang' ),
							'body' => __('Thank you for your order %fname% %lname% 
Your order has been received, and any items to be shipped will be processed as soon as possible. Please refer to your Order ID %inv% whenever contacting us.
Here is a confirmation of your order details:

Order Information:
%order-details%

You can view the latest status of your order here: %order-log-transaction%

Thanks again!', 'dp-lang' )
						),
					),
					'order_cancelled' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' =>  __( 'Order %inv% Cancelled', 'dp-lang' ),
							'body' =>  __('Hello 
Order %inv% has been cancelled', 'dp-lang' )
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' =>  __( 'Order %inv% Cancelled', 'dp-lang' ),
							'body' =>  __('Hello 
Order %inv% has been cancelled', 'dp-lang' )
						),
					),
					'user_registered' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' =>  __( 'New User Registered on %shop%', 'dp-lang' ),
							'body' => __('Hello 
A new user has registered with username %uname% and email %email%', 'dp-lang' )
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' =>  __( 'New Account Information on %shop%', 'dp-lang' ),
							'body' => __('Hello 
A new user has registered with username %uname% and email %email%', 'dp-lang' )
						),
					),
					'payments' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' => __( 'Order %inv% %status% ', 'dp-lang' ),
							'body' => __('Hello
Order ID %inv% is %status%', 'dp-lang' )
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' => __( 'Order %inv% %status% ', 'dp-lang' ),
							'body' => __('Hello %fname% %lname% 
Your Order ID %inv% is %status% .

%digi%

Thanks again!', 'dp-lang' )
						),
					),
					'inventory' => array(
						'to' => get_option( "admin_email" ),
						'subject' => __( 'Inventory Message for Product %pname% ', 'dp-lang' ),
						'body' => __('Hello
Product %pname% currently has stock of %stock%
%footer%', 'dp-lang' )
					),
					'enquiry' => array(
						'to' => get_option( "admin_email" ),
						'subject' => __( 'New Enquiry - %enq_subject% ', 'dp-lang' ),
						'body' =>  __('Hello
New Enquiry :

Name : %from%
Email : %from_email%
Subject : %enq_subject%
Details : %details%
Message : %custom_message%', 'dp-lang' )
					),
				),
				'social' => array(
					'pinterest' => array(
						'show_pinit_button'	 => 'off',
						'show_pin_count'	 => 'none'
					),
				),
				'related_products' => array(
					'show'			 => 1,
					'relate_by'		 => 'both',
					'simple_list'	 => 0,
					'show_limit'	 => 3
				),
				'shipping' => array(
					'method' => 'free',
					'flat_rate' => '',
					'flat_limit_rate' => '',
					'weight_flat_rate' => '',
					'weight_class_rate' => '',
					'per_item_rate' => ''
				) 
			);
			
			$default_settings = apply_filters( 'dp_default_settings', $default_settings );
			$settings = wp_parse_args( (array) $old_settings, $default_settings );
			update_option( 'dukapress_settings', $settings );
			
			if ( empty( $old_settings ) ) {
				self::update_2point6();
			}
		}
		
		/** 
		 * Update from Old Dukapress Version 2.6.x
		 *
		 */
		public static function update_2point6(){
			$dp_shopping_cart_settings = get_option('dp_shopping_cart_settings'); //Get old settings
			if (!empty($dp_shopping_cart_settings)) {
				$order_placed_mail = get_option('dp_order_mail_options', true);
				$order_placed_mail_user = get_option('dp_order_mail_user_options', true);
				$order_cancelled_mail = get_option('dp_order_cancelled_mail_options', true);
				$order_cancelled_mail_user = get_option('dp_order_cancelled_mail_user_options', true);
				$user_registered = get_option('dp_reg_admin_mail', true);
				$user_registered_user = get_option('dp_usr_reg_mail_options', true);
				$inventory_mail = get_option('dp_usr_inventory_mail', true);
				$enquiry_mail = get_option('dp_usr_enquiry_mail', true);
				$payment_mail = get_option('dp_admin_payment_mail', true);
				$payment_mail_user = get_option('dp_usr_payment_mail', true);
				
				$new_settings = array(
					'pdf_generation' => 0,
					'user_registration' => 0,
					'product_img_height' => 150,
				 	'product_img_width' => 150,
					'grid_img_height'  => 150,
					'grid_img_width' => 150,
					'thumb_img_height'  => $dp_shopping_cart_settings['t_h'],
					'thumb_img_width' => $dp_shopping_cart_settings['t_w'],
					'image_effect' => $dp_shopping_cart_settings['image_effect'],
					'shop_city' => $dp_shopping_cart_settings['shop_city'],
					'shop_zip' => $dp_shopping_cart_settings['shop_zip'],
					'shop_state' => $dp_shopping_cart_settings['shop_state'],
					'shop_address' => $dp_shopping_cart_settings['shop_address'],
					'shop_name' => $dp_shopping_cart_settings['shop_name'],
					'gateways' => array(
						'worldpay' => array(
							'currency' => '',
							'mode' => 'sandbox'
						),
						'alertpay' => array(
							'currency' => '',
						),
						'paypal' => array(
							'currency' => 'USD',
							'mode'	=> 'sandbox'
						)
					),
					'shop_mode' => $dp_shopping_cart_settings['dp_shop_mode'],
					'tax'						 => array(
						'rate'					 => $dp_shopping_cart_settings['tax'],
						'label'					 => __( 'Taxes', 'dp-lang' )
					),
					'shop_country' => $dp_shopping_cart_settings['dp_shop_country'],
					'shop_currency' => $dp_shopping_cart_settings['dp_shop_currency'],
					'shop_currency_position' => 1,
					'currency_code_enable' => '',
					'currency_symbol' => $dp_shopping_cart_settings['dp_currency_symbol'],
					'page_urls' => array(
						'thankyou_url' => $dp_shopping_cart_settings['thank_you'],
						'thankyou_id' => '',
						'affiliate_url' => $dp_shopping_cart_settings['affiliate_url'],
						'affiliate_id' => '',
						'terms_url' => $dp_shopping_cart_settings['terms_url'],
						'terms_id' => ''
					),
					'inventory_threshhold' => '',
					'inventory_remove' => '',
					'max_downloads' => 3,
					'per_page' => 20,
					'ga_ecommerce' => 'none',
					'slugs' => array(
						'products' => __('products','dp-lang'),
						'category' => __('category','dp-lang'),
						'tag' => __('tag','dp-lang')
					),
					'mail' => array(
						'order_placed' => array(
							'admin' => array(
								'to' => get_option( "admin_email" ),
								'subject' => $order_placed_mail['dp_order_send_mail_title'],
								'body' => $order_placed_mail['dp_order_send_mail_body']
							),
							'user' => array(
								'from' => get_option( "admin_email" ),
								'subject' => $order_placed_mail_user['dp_order_send_mail_user_title'],
								'body' => $order_placed_mail_user['dp_order_send_mail_user_body']
							),
						),
						'order_cancelled' => array(
							'admin' => array(
								'to' => get_option( "admin_email" ),
								'subject' => $order_cancelled_mail['dp_order_cancelled_send_mail_title'],
								'body' => $order_cancelled_mail['dp_order_cancelled_send_mail_body']
							),
							'user' => array(
								'from' => get_option( "admin_email" ),
								'subject' => $order_cancelled_mail_user['dp_order_cancelled_send_mail_user_title'],
								'body' => $order_cancelled_mail_user['dp_order_cancelled_send_mail_user_body']
							),
						),
						'user_registered' => array(
							'admin' => array(
								'to' => get_option( "admin_email" ),
								'subject' => $user_registered['dp_reg_admin_mail_title'],
								'body' => $user_registered['dp_reg_admin_mail_body']
							),
							'user' => array(
								'from' => get_option( "admin_email" ),
								'subject' => $user_registered_user['dp_usr_reg_mail_title'],
								'body' => $user_registered_user['dp_usr_reg_mail_body']
							),
						),
						'payments' => array(
							'admin' => array(
								'to' => get_option( "admin_email" ),
								'subject' => $payment_mail['dp_usr_admin_payment_mail_title'],
								'body' => $payment_mail['dp_admin_payment_mail_body']
							),
							'user' => array(
								'from' => get_option( "admin_email" ),
								'subject' => $payment_mail_user['dp_usr_payment_mail_title'],
								'body' => $payment_mail_user['dp_usr_payment_mail_body']
							),
						),
						'inventory' => array(
							'to' => get_option( "admin_email" ),
							'subject' => $inventory_mail['dp_usr_inventory_mail_title'],
							'body' => $inventory_mail['dp_usr_inventory_mail_body']
						),
						'enquiry' => array(
							'to' => get_option( "admin_email" ),
							'subject' => $enquiry_mail['dp_usr_enquiry_mail_title'],
							'body' => $enquiry_mail['dp_usr_enquiry_mail_body']
						),
					),
					'social' => array(
						'pinterest' => array(
							'show_pinit_button'	 => 'off',
							'show_pin_count'	 => 'none'
						),
					),
					'related_products'			 => array(
						'show'			 => 1,
						'relate_by'		 => 'both',
						'simple_list'	 => 0,
						'show_limit'	 => 3
					),
					'shipping' => array(
						'method' => $dp_shopping_cart_settings['dp_shipping_calc_method'],
						'flat_rate' => $dp_shopping_cart_settings['dp_shipping_flat_rate'],
						'flat_limit_rate' => $dp_shopping_cart_settings['dp_shipping_flat_limit_rate'],
						'weight_flat_rate' => $dp_shopping_cart_settings['dp_shipping_weight_flat_rate'],
						'weight_class_rate' => $dp_shopping_cart_settings['dp_shipping_weight_class_rate'],
						'per_item_rate' => $dp_shopping_cart_settings['dp_shipping_per_item_rate']
					)
				);
				
				update_option( 'dukapress_settings', $new_settings );
				
				//TODO Update Payment gateways also
				
				//We then delete the old options
				delete_option('dp_dl_link_expiration_time');
				delete_option('dp_order_mail_options');
				delete_option('dp_order_mail_user_options');
				delete_option('dp_order_cancelled_mail_options');
				delete_option('dp_order_cancelled_mail_user_options');
				delete_option('dp_reg_admin_mail');
				delete_option('dp_usr_reg_mail_options');
				delete_option('dp_usr_inventory_mail');
				delete_option('dp_usr_enquiry_mail');
				delete_option('dp_admin_payment_mail');
				delete_option('dp_usr_payment_mail');
				delete_option('dp_shopping_cart_settings');
				
			}
		}
		
	}
}	
?>