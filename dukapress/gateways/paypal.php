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

	//paypal vars
  var $email, $API_Username, $API_Password, $API_Signature, $SandboxFlag, $returnURL, $cancelURL, $API_Endpoint, $paypalURL, $version, $currencyCode, $locale;

	function on_create(){
		global $dukapress;
		$this->plugin_name = __('PayPal Express','dp-lang');
		$this->plugin_slug = 'paypal';
		$this->currencies = $dukapress->paypal_supported_currency;
		$this->locales = $dukapress->paypal_locale;

		//dynamic button img, see: https://cms.paypal.com/us/cgi-bin/?&cmd=_render-content&content_ID=developer/e_howto_api_ECButtonIntegration
	 	$this->method_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=' . get_locale();
	 	$this->method_button_img_url = 'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&locale=' . get_locale();

		$this->email = $dukapress->get_setting('gateways->paypal->id');
		$this->API_Username = $dukapress->get_setting('gateways->paypal->api_user');
    $this->API_Password = $dukapress->get_setting('gateways->paypal->api_pass');
    $this->API_Signature = $dukapress->get_setting('gateways->paypal->api_sig');
    $this->currencyCode = $dukapress->get_setting('gateways->paypal->currency');
    $this->locale = $dukapress->get_setting('gateways->paypal->locale');
		$this->version = "69.0"; //api version

		//set api urls
  	if ( $this->get_setting('gateways->paypal->mode') == 'sandbox' )	{
  		$this->API_Endpoint = "https://api-3t.sandbox.paypal.com/nvp";
  		$this->paypalURL = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=";
  	} else {
  		$this->API_Endpoint = "https://api-3t.paypal.com/nvp";
  		$this->paypalURL = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
    }
	}

	function admin_scripts($page){
		global $dukapress;
		wp_enqueue_style('dpsc-colorpicker', DPSC_DUKAPRESS_RESOURCEURL . '/colorpicker/css/colorpicker.css', array(), $dukapress->version);
		wp_enqueue_script('jquery');
		wp_enqueue_script('dpsc-colorpicker', DPSC_DUKAPRESS_RESOURCEURL . '/colorpicker/js/colorpicker.js', array('jquery'), $dukapress->version);
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
				<tr>
					<th scope="row">
						<?php _e('Optional Settings','dp-lang'); ?><br/>
						<span class="description"><?php _e('Optional settings for your PayPal payment page', 'dp-lang') ?></span>
					</th>
					<td>
						<p>
							<label><?php _e('PayPal Header Image','dp-lang') ?> <span class="description"><?php _e('URL for an image you want to appear at the top left of the payment page. The image has a maximum size of 750 pixels wide by 90 pixels high. PayPal recommends that you provide an image that is stored on a secure (https) server. If you do not specify an image, the business name is displayed.', 'dp-lang') ?></span><br />
							  <input value="<?php echo $settings['gateways']['paypal']['header_img']; ?>"  name="dpsc[gateways][paypal][header_img]" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('PayPal Header Border Color','dp-lang') ?> <span class="description"><?php _e('Sets the border color around the header of the payment page. The border is a 2-pixel perimeter around the header space, which is 750 pixels wide by 90 pixels high. By default, the color is black.', 'dp-lang') ?></span><br />
							  <input id="dpsc_hbc" value="<?php echo $settings['gateways']['paypal']['header_border']; ?>"  name="dpsc[gateways][paypal][header_border]" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('PayPal Header Background Color','dp-lang') ?> <span class="description"><?php _e('Sets the background color for the header of the payment page. By default, the color is white.', 'dp-lang') ?></span><br />
							  <input id="dpsc_hbbc" value="<?php echo $settings['gateways']['paypal']['header_back']; ?>"  name="dpsc[gateways][paypal][header_back]" type="text" />
							</label>
						</p>
						<p>
							<label><?php _e('PayPal Page Background Color','dp-lang') ?> <span class="description"><?php _e('Sets the background color for the payment page. By default, the color is white.', 'dp-lang') ?></span><br />
							  <input id="dpsc_pbc" value="<?php echo $settings['gateways']['paypal']['page_back']; ?>"  name="dpsc[gateways][paypal][page_back]" type="text" />
							</label>
						</p>
					</td>
				</tr>
		</table>
		<script type="text/javascript">
    	  jQuery(document).ready(function ($) {
      		$('#dpsc_hbc').ColorPicker({
          	onSubmit: function(hsb, hex, rgb, el) {
          		$(el).val(hex);
          		$(el).ColorPickerHide();
          	},
          	onBeforeShow: function () {
          		$(this).ColorPickerSetColor(this.value);
          	},
            onChange: function (hsb, hex, rgb) {
          		$('#dpsc_hbc').val(hex);
          	}
          })
          .bind('keyup', function(){
          	$(this).ColorPickerSetColor(this.value);
          });
          $('#dpsc_hbbc').ColorPicker({
          	onSubmit: function(hsb, hex, rgb, el) {
          		$(el).val(hex);
          		$(el).ColorPickerHide();
          	},
          	onBeforeShow: function () {
          		$(this).ColorPickerSetColor(this.value);
          	},
            onChange: function (hsb, hex, rgb) {
          		$('#dpsc_hbbc').val(hex);
          	}
          })
          .bind('keyup', function(){
          	$(this).ColorPickerSetColor(this.value);
          });
          $('#dpsc_pbc').ColorPicker({
          	onSubmit: function(hsb, hex, rgb, el) {
          		$(el).val(hex);
          		$(el).ColorPickerHide();
          	},
          	onBeforeShow: function () {
          		$(this).ColorPickerSetColor(this.value);
          	},
            onChange: function (hsb, hex, rgb) {
          		$('#dpsc_pbc').val(hex);
          	}
          })
          .bind('keyup', function(){
          	$(this).ColorPickerSetColor(this.value);
          });
    		});
    	</script>
		<?php
	}

	function process_payment_form($cart, $user_info, $shipping, $order_id){

		if (is_array($cart) && count($cart) > 0) {
			$result = $this->SetExpressCheckout($cart, $shipping_info, $order_id);
			if($result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning")	{
	      $token = urldecode($result["TOKEN"]);
	      $this->redirect($token);
	    }else{
				$error_response = "";
				for ($i = 0; $i <= 5; $i++) { //print the first 5 errors
	        if (isset($result["L_ERRORCODE$i"])) {
	          $error_response .= "<li>{$result["L_ERRORCODE$i"]} - {$result["L_SHORTMESSAGE$i"]} - {$result["L_LONGMESSAGE$i"]}</li>";
	        }
	      }
				$error_response = '<br /><ul>' . $error_response . '</ul>';
				$this->$error_message = __('There was a problem connecting to PayPal to setup your purchase. Please try again.', 'dp-lang').$error_response;
				$this->error = true;
			}
		}else{
			$this->$error_message = __('There are no items in your cart', 'dp-lang');
			$this->error = true;
		}
	}

	function update_payment($request){
		if (isset($_SESSION['token']) && isset($_SESSION['PayerID']) && isset($_SESSION['final_amt'])) {
			$result = $this->DoExpressCheckoutPayment($_SESSION['token'], $_SESSION['PayerID']);
			if($result["ACK"] == "Success" || $result["ACK"] == "SuccessWithWarning")	{
				$payment_info['gateway_name'] = $this->plugin_name;
				for ($i=0; $i<10; $i++) {
					if (!isset($result['PAYMENTINFO_'.$i.'_PAYMENTTYPE'])) {
				    continue;
				  }
					$payment_info['method'] = ($result["PAYMENTINFO_{$i}_PAYMENTTYPE"] == 'echeck') ? __('eCheck', 'dp-lang') : __('PayPal balance, Credit Card, or Instant Transfer', 'dp-lang');
					$payment_info['transaction_id'] = $result["PAYMENTINFO_{$i}_TRANSACTIONID"];
					$timestamp = time();
					switch ($result["PAYMENTINFO_{$i}_PAYMENTSTATUS"]) {

					}
				}
			}
		}
	}

	function DoExpressCheckoutPayment($token, $payer_id){
		$nvpstr  = '&TOKEN=' . urlencode($token);
	  $nvpstr .= '&PAYERID=' . urlencode($payer_id);
		$nvpstr .= '&BUTTONSOURCE=incsub_SP';
		$nvpstr .= $_SESSION['nvpstr'];

	  /* Make the call to PayPal to finalize payment
	    */
	  return $this->api_call("DoExpressCheckoutPayment", $nvpstr);
	}

	function SetExpressCheckout($cart, $shipping_info, $order_id){
		global $dukapress;
		$nvpstr = "";
    $nvpstr .= "&ReturnUrl=" . $this->return_url;
    $nvpstr .= "&CANCELURL=" . $this->cancel_url;
    $nvpstr .= "&ADDROVERRIDE=1";
    $nvpstr .= "&NOSHIPPING=2";
    $nvpstr .= "&LANDINGPAGE=Billing";
    $nvpstr .= "&SOLUTIONTYPE=Sole";
    $nvpstr .= "&LOCALECODE=" . $this->locale;
    $nvpstr .= "&EMAIL=" . urlencode($shipping_info['email']);

		//formatting
    $nvpstr .= "&HDRIMG=" . urlencode($dukapress->get_setting('gateways->paypal->header_img', ''));
    $nvpstr .= "&HDRBORDERCOLOR=" . urlencode($dukapress->get_setting('gateways->paypal-express->header_border', ''));
    $nvpstr .= "&HDRBACKCOLOR=" . urlencode($dukapress->get_setting('gateways->paypal-express->header_back', ''));
    $nvpstr .= "&PAYFLOWCOLOR=" . urlencode($dukapress->get_setting('gateways->paypal-express->page_back', ''));
		$j = 0;
		$i = 0;
		$shipping_price = 0;
		$request = '';
		$payment_action = 'Sale';
		$merchant_email = $this->email;
		$totals = array();
		$request .= "&PAYMENTREQUEST_{$j}_SELLERPAYPALACCOUNTID=" . $merchant_email;
    $request .= "&PAYMENTREQUEST_{$j}_PAYMENTACTION=" . $payment_action;
    $request .= "&PAYMENTREQUEST_{$j}_CURRENCYCODE=" . $this->currencyCode;
    $request .= "&PAYMENTREQUEST_{$j}_NOTIFYURL=" . $this->ipn_url;
		if(is_array($shipping_info)){
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTONAME=" . dukapress_trim_name($shipping_info['name'], 32);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOSTREET=" . dukapress_trim_name($shipping_info['address1'], 100);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOSTREET2=" . dukapress_trim_name($shipping_info['address2'], 100);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOCITY=" . dukapress_trim_name($shipping_info['city'], 40);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOSTATE=" . dukapress_trim_name($shipping_info['state'], 40);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOCOUNTRYCODE=" . dukapress_trim_name($shipping_info['country'], 2);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOZIP=" . dukapress_trim_name($shipping_info['zip'], 20);
			$request .= "&PAYMENTREQUEST_{$j}_SHIPTOPHONENUM=" . dukapress_trim_name($shipping_info['phone'], 20);
			$shipping_price = $shipping_info['price'];
		}
		foreach ($cart as $product) {
			$price = $product['price'];
			$totals[] = ($price * $product['quantity']);
		  $request .= "&L_PAYMENTREQUEST_{$j}_NAME$i=" . dukapress_trim_name($data['name']);
		  $request .= "&L_PAYMENTREQUEST_{$j}_AMT$i=" . urlencode($price);
		  $request .= "&L_PAYMENTREQUEST_{$j}_NUMBER$i=" . urlencode($product['SKU']);
		  $request .= "&L_PAYMENTREQUEST_{$j}_QTY$i=" . urlencode($product['quantity']);
		  $request .= "&L_PAYMENTREQUEST_{$j}_ITEMURL$i=" . urlencode($product['url']);
		  $request .= "&L_PAYMENTREQUEST_{$j}_ITEMCATEGORY$i=Physical";
		  $i++;
		}
		$tax_rate = $dukapress->get_setting('tax->rate');
		$total = array_sum($totals);
		$request .= "&PAYMENTREQUEST_{$j}_ITEMAMT=" . $total; //items subtotal
		if($shipping_price > 0){
			$total += $shipping_price;
			$request .= "&PAYMENTREQUEST_{$j}_SHIPPINGAMT=" . $shipping_price; //shipping total
		}

		if ($tax_rate > 0) {
			$total_tax = ($tax_rate * $total)/100;
			$total += $total_tax;
			$request .= "&PAYMENTREQUEST_{$j}_TAXAMT=" . $total_tax; //taxes total
		}
		//order details
    $request .= "&PAYMENTREQUEST_{$j}_DESC=" . $this->trim_name(sprintf(__('%s Store Purchase - Order ID: %s', 'dp-lang'), $dukapress->get_setting('shop_name'), $order_id));
		//cart name
    $request .= "&PAYMENTREQUEST_{$j}_AMT=" . $total; //cart total
    $request .= "&PAYMENTREQUEST_{$j}_INVNUM=" . $order_id;
		$request .= "&PAYMENTREQUEST_{$j}_ALLOWEDPAYMENTMETHOD=InstantPaymentOnly";
		$nvpstr .= $request;
		$_SESSION['nvpstr'] = $request;

		$resArray = $this->api_call("SetExpressCheckout", $nvpstr);
		$ack = strtoupper($resArray["ACK"]);
		if($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING")	{
      $token = urldecode($resArray["TOKEN"]);
      $_SESSION['TOKEN'] = $token;
    }
    return $resArray;
	}

	function api_call($methodName, $nvpStr){
		global $dukapress;
		$query_string = "METHOD=" . urlencode($methodName) . "&VERSION=" . urlencode($this->version) . "&PWD=" . urlencode($this->API_Password) . "&USER=" . urlencode($this->API_Username) . "&SIGNATURE=" . urlencode($this->API_Signature) . $nvpStr;
		$args['user-agent'] = "Dukapress/{$dukapress->version}: http://dukapress.org | PayPal Express Plugin/{$dukapress->version}";
	  $args['body'] = $query_string;
	  $args['sslverify'] = false;
	  $args['timeout'] = 60;
	  $args['httpversion'] = '1.1';	//api call will fail without this!

		$response = wp_remote_post($this->API_Endpoint, $args);
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			$this->error = true;
			$this->error_message = __('There was a problem connecting to PayPal. Please try again.', 'dp-lang');
			return false;
		}else {
	    //convert NVPResponse to an Associative Array
	    $nvpResArray = $this->deformatNVP($response['body']);
	    return $nvpResArray;
	  }
	}

	function redirect($token) {
	  // Redirect to paypal.com here
	  $payPalURL = $this->paypalURL . $token;
	  wp_redirect($payPalURL);
	  exit;
	}

	function deformatNVP($nvpstr) {
		parse_str($nvpstr, $nvpArray);
		return $nvpArray;
	}
}
?>
