<?php
class Dpsc_PayPal extends DukaPress_Gateway{

	var $sandbox_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

	var $post_url = 'https://www.paypal.com/cgi-bin/webscr';

	var $currencies = "";

	var $locales = "";

	//url for an image for your checkout method. Displayed on checkout form if set
  var $method_img_url = '';

  //url for an submit button image for your checkout method. Displayed on checkout form if set
  var $method_button_img_url = '';

	//API Version
	var $version;

	function on_create(){
		global $dukapress;
		$this->plugin_name = __('PayPal Express','dp-lang');
		$this->plugin_slug = 'paypal';
		$this->currencies = $dukapress->paypal_supported_currency;
		$this->locales = $dukapress->paypal_locale;

		//dynamic button img, see: https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECButtonIntegration
	 	$this->method_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=' . get_locale();
	 	$this->method_button_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=' . get_locale();

		$this->version = "69.0"; //api version
	}

	function process_ipn_return() {

	}

	function set_up_options($settings){
		?>
		<h3 class='hndle'><span><?php _e('PayPal Express Checkout Settings','dp-lang'); ?></span></h3>
		<table class="form-table">
				<tr>
				    <th scope="row" colspan="2">
							<span class="description"><?php _e('Express Checkout is PayPal\'s premier checkout solution, which streamlines the checkout process for buyers and keeps them on your site after making a purchase. Unlike PayPal Pro, there are no additional fees to use Express Checkout, though you may need to do a free upgrade to a business account. <a target="_blank" href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/">More Info &raquo;</a>', 'dp-lang') ?></span>
						</th>
				</tr>
				<tr>
					<th scope="row"><?php _e('PayPal Mode','dp-lang'); ?></th>
					<td>
						<select name="dpsc[gateways][paypal][mode]">
							<option value="sandbox" <?php selected($settings['gateways']['paypal']['mode'], 'sandbox'); ?>><?php _e('Sandbox','dp-lang'); ?></option>
							<option value="live" <?php selected($settings['gateways']['paypal']['mode'], 'live'); ?>><?php _e('Live','dp-lang'); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('PayPal Currency','dp-lang') ?></th>
					<td>
						<select name="dpsc[gateways][paypal][currency]">
							<?php
							$sel_currency = $settings['gateways']['paypal']['currency'];
							foreach ($this->currencies as $k => $v) {
								echo '<option value="' . $k . '"' . ($k == $sel_currency ? ' selected' : '') . '>' . wp_specialchars($v, true) . '</option>' . "\n";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('PayPal Site','dp-lang') ?></th>
					<td>
						<select name="dpsc[gateways][paypal][locale]">
							<?php
							$sel_locale = $settings['gateways']['paypal']['locale'];
							foreach ($this->locales as $k => $v) {
								echo '<option value="' . $k . '"' . ($k == $sel_locale ? ' selected' : '') . '>' . wp_specialchars($v, true) . '</option>' . "\n";
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e('PayPal Merchant E-mail','dp-lang'); ?></th>
					<td>
						<input value="<?php echo $settings['gateways']['paypal']['id']; ?>"  name="dpsc[gateways][paypal][id]" type="text" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<?php _e('PayPal API Credentials','dp-lang'); ?><br/>
						<span class="description"><?php _e('You must login to PayPal and create an API signature to get your credentials. <a target="_blank" href="https://developer.paypal.com/webapps/developer/docs/classic/api/apiCredentials/">Instructions &raquo;</a>', 'dp-lang') ?></span>
					</th>
					<td>
						<p>
							<label><?php _e('API Username','dp-lang') ?><br />
							  <input value="<?php echo $settings['gateways']['paypal']['api_user']; ?>"  name="dpsc[gateways][paypal][api_user]" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('API Password','dp-lang') ?><br />
							  <input value="<?php echo $settings['gateways']['paypal']['api_pass']; ?>"  name="dpsc[gateways][paypal][api_pass]" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('API Signature','dp-lang') ?><br />
							  <input value="<?php echo $settings['gateways']['paypal']['api_sig']; ?>"  name="dpsc[gateways][paypal][api_sig]" type="text" />
							</label>
						</p>
					</td>
				</tr>
		</table>
		<?php
	}

	function process_payment_form($cart, $user_info, $shipping, $order_id){
		global $dukapress;
		if (is_array($cart) && count($cart) > 0) {
			$return_path = dukapress_page_url(false,'thankyou');
			$check_return_path = explode('?', $return_path);
      if (count($check_return_path) > 1) {
          $return_path .= '&id=' . $invoice;
      } else {
          $return_path .= '?id=' . $invoice;
      }
			$this->return_url = $return_path;
			$this->cancel_url = $return_path.'&c=c';
			$currency = $dukapress->get_setting('gateways->paypal->currency');
			$conversion_rate = dukapress_shop_currency_conversion_rate($currency);
			$action_url = $this->post_url;
			if ( $dukapress->get_setting('gateways->paypal->mode') == 'sandbox' )	{
				$action_url = $this->sandbox_url;
			}
			$output = '<form name="dpsc_paypal_form" id="dpsc_payment_form" class="dpsc_payment_form" action="' . $action_url . '" method="post">';
      $output .= '<input type="hidden" name="return" value="' . $this->return_url . '"/>
                   <input type="hidden" name="cmd" value="_ext-enter" />
                   <input type="hidden" name="notify_url" value="' . $this->ipn_url . '"/>
                   <input type="hidden" name="redirect_cmd" value="_cart" />
                   <input type="hidden" name="business" value="' . $dukapress->get_setting('gateways->paypal->id') . '"/>
                   <input type="hidden" name="cancel_return" value="' . $this->cancel_url . '"/>
                   <input type="hidden" name="rm" value="2" />
                   <input type="hidden" name="upload" value="1" />
                   <input type="hidden" name="currency_code" value="' . $currency . '"/>
                   <input type="hidden" name="no_note" value="1" />
                   <input type="hidden" name="invoice" value="' . $order_id . '">';
			$output .= '<input type="hidden" name="first_name" value="' . __($user_info['fname'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="last_name" value="' . __($user_info['lname'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="address1" value="' . __($user_info['address'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="city" value="' . __($user_info['city'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="state" value="' . __($user_info['state'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="zip" value="' . __($user_info['zip'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="country" value="' . __($user_info['country'], "dp-lang") . '"/>';
 			$output .= '<input type="hidden" name="email" value="' . __($user_info['email'], "dp-lang") . '"/>';

			$dpsc_count_product = 1;
      $tax_rate = $dukapress->get_setting('tax->rate');
      $dpsc_shipping_total = 0.00;
      foreach ($cart as $dpsc_product) {
				$dpsc_var = '';
        $var_paypal_field = '';
        if (!empty($dpsc_product['var'])) {
            $dpsc_var = ' (' . $dpsc_product['var'] . ')';
            $var_paypal_field = '<input type="hidden" name="on0_' . $dpsc_count_product . '" value="Variation Selected" />
                                 <input type="hidden" name="os0_' . $dpsc_count_product . '" value="' . $dpsc_var . '"  />';
        }
        $output .= '<input type="hidden" name="item_name_' . $dpsc_count_product . '" value="' . $dpsc_product['name'] . $dpsc_var . '"/>
                         <input type="hidden" name="amount_' . $dpsc_count_product . '" value="' . number_format($conversion_rate * $dpsc_product['price'], 2) . '"/>
                         <input type="hidden" name="quantity_' . $dpsc_count_product . '" value="' . $dpsc_product['quantity'] . '"/>
                         <input type="hidden" name="item_number_' . $dpsc_count_product . '" value="' . $dpsc_product['item_number'] . '"/>
                         <input type="hidden" name="tax_rate_' . $dpsc_count_product . '" value="' . $tax_rate . '"/>'
                . $var_paypal_field;
        if ($dukapress->get_setting('discount') === 'true' && $dpsc_discount_value) {
            $output .= '<input type="hidden" name="discount_rate_' . $dpsc_count_product . '" value="' . $dpsc_discount_value . '">';
        }
        $dpsc_count_product++;
			}
			if (is_array($shipping)) {
          $dpsc_shipping_total = $conversion_rate * $shipping['total'];
      }
			$output .= '<input type="hidden" name="handling_cart" value="' . number_format($dpsc_shipping_total, 2) . '"/></form>';
		}
		return $output;
	}
}
?>
