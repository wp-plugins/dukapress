<?php
if(!class_exists('DukaPress_Install')) {
	
	/**
	 * Manage the setup for DukaPress
	 *
	 */
	class DukaPress_Install{
		
		public static function init() {
			//Set up the options
			add_action( 'plugins_loaded', array( __CLASS__, 'install_options' ) );
		}
		
		
		/** 
		 * Install and set up the default options
		 *
		 */
		public static function install_options(){
			$old_settings = get_option( 'dukapress_settings' );
			$old_version = get_option( 'dp_version' );
			
			
			$default_settings = array(
				'pdf_generation' => 0,
				'user_registration' => 0,
				'product_img_height' => 150,
			 	'product_img_width' => 150,
				'list_img_height'  => 150,
				'list_img_width' => 150,
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
				'checkout' => '',
				'thank_you' => '',
				'affiliate_url' => '',
				'terms_url' => '',
				'tax'						 => array(
					'rate'					 => 0,
					'label'					 => __( 'Taxes', 'dp-lang' ),
					'tax_shipping'			 => 1,
					'tax_inclusive'			 => 0,
					'tax_digital'			 => 1,
					'downloadable_address'	 => 0
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
				'shop_inventory_active' => '',
				'shop_inventory_stocks' => '',
				'shop_inventory_soldout' => '',
				'shop_inventory_warning' => '',
				'shop_inventory_email' => '',
				'shop_inventory_stock_warning' => '',
				'dl_link_expiration_time' => '',
				'max_downloads' => 3,
				'per_page' => 20,
				'slugs' => array(
					'products' => __('products','dp-lang'),
					'category' => __('category','dp-lang'),
					'tag' => __('tag','dp-lang')
				),
				'mail' => array(
					'order_placed' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
					),
					'order_cancelled' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
					),
					'user_registered' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
					),
					'payments' => array(
						'admin' => array(
							'to' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
						'user' => array(
							'from' => get_option( "admin_email" ),
							'subject' => '',
							'body' => ''
						),
					),
					'inventory' => array(
						'to' => get_option( "admin_email" ),
						'subject' => '',
						'body' => ''
					),
					'enquiry' => array(
						'to' => get_option( "admin_email" ),
						'subject' => '',
						'body' => ''
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
					'list_img_height'  => 150,
					'list_img_width' => 150,
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
					'checkout' => '',
					'thank_you' => '',
					'affiliate_url' => '',
					'terms_url' => '',
					'tax'						 => array(
						'rate'					 => $dp_shopping_cart_settings['tax'],
						'label'					 => __( 'Taxes', 'dp-lang' ),
						'tax_shipping'			 => 1,
						'tax_inclusive'			 => 0,
						'tax_digital'			 => 1,
						'downloadable_address'	 => 0
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
					'shop_inventory_active' => $dp_shopping_cart_settings['dp_shop_inventory_active'],
					'shop_inventory_stocks' => $dp_shopping_cart_settings['dp_shop_inventory_stocks'],
					'shop_inventory_soldout' => $dp_shopping_cart_settings['dp_shop_inventory_soldout'],
					'shop_inventory_warning' => $dp_shopping_cart_settings['dp_shop_inventory_warning'],
					'dl_link_expiration_time' => get_option('dp_dl_link_expiration_time'),
					'max_downloads' => 3,
					'per_page' => 20,
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