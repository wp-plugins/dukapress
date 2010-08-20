<?php
/*
 * This file handles the functions related to Cart, Checkout and Thank You Page.
 */


/**
 * The function is responsible for Adding Product to Cart
 *
 */
if ($_REQUEST['action'] === 'dpsc_add_to_cart') {
    add_action('init', 'dpsc_add_to_cart');
}

function dpsc_add_to_cart() {
    $product_id = trim($_POST['product_id']);
    $product_name = trim(strip_tags($_POST['product']));
    $product_base_price = $_POST['price'];
    $product_updated_price = $_POST['dpsc_price_updated'];
    $product_variation_names = '';
    $product_variation_prices = 0.00;
    $product_quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1 ;
    $product_max_quantity = isset($_POST['max_quantity']) ? intval($_POST['max_quantity']) : FALSE;
    if ($product_max_quantity) {
        if ($product_quantity > $product_max_quantity) {
            $product_quantity = $product_max_quantity;
        }
    }
    $product_weight = isset($_POST['product_weight']) ? intval($_POST['product_weight']) : 0 ;
    if (isset($_POST['var'])) {
        $product_variations = $_POST['var'];
        $product_variation_names = array();
        foreach ($product_variations as $product_variation) {
            $product_variation_tmp = explode(',:_._:,', $product_variation);
            $product_variation_names[] = $product_variation_tmp[0];
            $product_price = floatval($product_variation_tmp[1]);
            $product_variation_prices += $product_price;
        }
        $product_variation_names = implode(', ', $product_variation_names);
    }
    else {
        $product_updated_price = $product_base_price;
    }
    $check_updated_price = floatval($product_base_price+$product_variation_prices);
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    if ($check_updated_price != $product_updated_price && $dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
        exit();
    }
    $dpsc_count = 1;
    $dpsc_products = $_SESSION['dpsc_products'];
    if (is_array($dpsc_products)) {
        foreach ($dpsc_products as $key => $item) {
            if ($item['item_number'] === $product_id && $item['var'] === $product_variation_names) {
                $dpsc_count += $item['quantity'];
                $item['max'] = $product_max_quantity;
                $total_quantity = $product_quantity + $item['quantity'];
                if ($product_max_quantity) {
                    if ($total_quantity > $product_max_quantity) {
                        $product_quantity = $product_max_quantity;
                        $item['quantity'] = $product_quantity;
                    }
                    else {
                        $item['quantity'] += $product_quantity;
                    }
                }
                else {
                    $item['quantity'] += $product_quantity;
                }
                unset($dpsc_products[$key]);
                array_push($dpsc_products, $item);
            }
        }
    }
    else {
        $dpsc_products = array();
    }

    if ($dpsc_count == 1) {
        $dpsc_product = array('name' => $product_name, 'var'=> $product_variation_names, 'price' => $product_updated_price, 'quantity' => $product_quantity, 'item_number' => $product_id, 'item_weight' => $product_weight, 'max' => $product_max_quantity);
        array_push($dpsc_products, $dpsc_product);
    }
    sort($dpsc_products);
    $_SESSION['dpsc_products'] = $dpsc_products;
    if ($_REQUEST['ajax'] == 'true') {
        ob_start();
        echo dpsc_print_cart_html();
        $output = ob_get_contents();
        ob_end_clean();
        $output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
        $output1 = dpsc_print_cart_html(TRUE);
        $output1 = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output1));
        $output2 = dpsc_go_to_checkout_link();
        $output2 = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output2));
        echo "jQuery('div.dpsc-shopping-cart').html('$output');";
        echo "jQuery('div.dpsc-mini-shopping-cart').html('$output1');";
        echo "jQuery('div.dpsc-checkout_url-widget').html('$output2');";
        echo "jQuery('form[id=product_form_".$product_id."]').addClass('product_in_cart');";
        echo "jQuery('span#dpsc_in_cart_".$product_id."').html('In Cart.');";
        exit();
    }
    return;
}


/**
 * This function empties the cart
 *
 */
if ($_REQUEST['dpsc_ajax_action'] === 'empty_cart') {
    add_action('init', 'dpsc_empty_cart');
}
function dpsc_empty_cart() {
    $products = $_SESSION['dpsc_products'];
    foreach ($products as $key => $item) {
        unset($products[$key]);
    }
    $_SESSION['dpsc_products'] = $products;
    if ($_REQUEST['ajax'] == 'true') {
        ob_start();
        echo dpsc_print_cart_html();
        $output = ob_get_contents();
        ob_end_clean();
        $output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
        $output1 = dpsc_print_cart_html(TRUE);
        $output1 = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output1));
        echo "jQuery('div.dpsc-shopping-cart').html('$output');";
        echo "jQuery('div.dpsc-mini-shopping-cart').html('$output1');";
        echo "jQuery('form.product_form').removeClass('product_in_cart');";
        echo "jQuery('span.dpsc_in_cart').html('&nbsp;');";
        exit();
    }
}

/**
 * This function updates the quantity of product in cart
 *
 */
if ($_REQUEST['dpsc_ajax_action'] === 'update_quantity') {
    add_action('init', 'dpsc_update_quantity');
}
function dpsc_update_quantity() {
    $product_id = trim($_POST['qpid']);
    $product_variation_name = trim($_POST['qpvar']);
    $product_quantity = intval($_POST['quantity']);
    if ($_POST['dpsc_ajax_action'] === 'update_quantity' && $product_quantity > 0) {
        $dpsc_products = $_SESSION['dpsc_products'];
        foreach ($dpsc_products as $key => $item) {
            if ($item['item_number'] === $product_id && $item['var'] === $product_variation_name) {
                if (is_numeric($item['max'])) {
                    if ($product_quantity > $item['max']) {
                    $product_quantity = $item['max'];
                }
                }
                $item['quantity'] = $product_quantity;
                unset($dpsc_products[$key]);
                array_push($dpsc_products, $item);
            }
        }
    }
    else {
        $dpsc_products = $_SESSION['dpsc_products'];
        foreach ($dpsc_products as $key => $item) {
            if ($item['item_number'] === $product_id && $item['var'] === $product_variation_name) {
                unset($dpsc_products[$key]);
            }
        }
    }
    sort($dpsc_products);
    $_SESSION['dpsc_products'] = $dpsc_products;
    if ($_REQUEST['ajax'] == 'true') {
        ob_start();
        echo dpsc_print_checkout_table_html();
        $output = ob_get_contents();
        ob_end_clean();
        $output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
        echo "jQuery('div.dpsc-table-checkout').html('$output');";
        ob_start();
        echo dpsc_print_cart_html();
        $output1 = ob_get_contents();
        ob_end_clean();
        $output1 = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output1));
        $output2 = dpsc_print_cart_html(TRUE);
        $output2 = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output2));
        echo "jQuery('div.dpsc-shopping-cart').html('$output1');";
        echo "jQuery('div.dpsc-mini-shopping-cart').html('$output2');";
        exit();
    }
}

/**
 * Checkout Shortcode
 *
 */
add_shortcode('dpsc_checkout', 'dpsc_checkout_shortcode');
function dpsc_checkout_shortcode($atts, $content=NULL) {
    $content .= '<div class="dpsc-checkout">' . dpsc_print_checkout_html() . '</div>';
    return $content;
}

/**
 * Returns the HTML for checkout
 *
 */
function dpsc_print_checkout_html() {
    global $wpdb;
    $output = '';
    $dpsc_products = $_SESSION['dpsc_products'];
    if (is_array($dpsc_products) && count($dpsc_products) > 0) {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        $output .= '<span id="dpsc-checkout-text">Please review your order.</span>';
        $output .= '<div class="clear"></div><div class="dpsc-table-checkout">'.dpsc_print_checkout_table_html().'</div>';
        if ($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
            if ($dp_shopping_cart_settings['discount_enable'] === 'true') {
                $output .= '<div class="clear"></div>' . dpsc_print_checkout_discount_form();
            }
            $output .= dpsc_pnj_show_contact_information();
            if (count($dp_shopping_cart_settings['dp_po']) > 0) {
                $output .= '<div class="clear"></div>' . dpsc_print_checkout_payment_form();
            }
        }
        else {
            $output .= '<div class="clear"></div>' . dpsc_print_checkout_inquiry_form();
        }
    }
    else {
        $output .= 'There are no product in your cart.';
    }
    return $output;
}

/**
 * Returns the HTML for table at checkout
 *
 */
function dpsc_print_checkout_table_html($dpsc_discount_value = 0) {
    global $wpdb;
    $dpsc_products = $_SESSION['dpsc_products'];
    if (is_array($dpsc_products) && count($dpsc_products) > 0) {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        if ($dp_shopping_cart_settings['dp_shop_paypal_use_sandbox'] == "checked") {
            $dpsc_form_action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        else {
            $dpsc_form_action = 'https://www.paypal.com/cgi-bin/webscr';
        }
        $dpsc_total = 0.00;
        $dpsc_tax_rate = !empty($dp_shopping_cart_settings['tax']) ? $dp_shopping_cart_settings['tax'] : 0;
        $dpsc_total_discount = 0.00;
        $dpsc_total_shipping = 0.00;
        $dpsc_total_tax = 0.00;
        if ($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
            $price_head_output = '<th>Price</th>';
        }
        else {
            $price_head_output = '';
        }
        $content .= '<table class="dpsc-checkout-product-list">';
        $content .= '<tr><th>Product</th><th>Quantity</th>' . $price_head_output . '<th /></tr>';
        $dpsc_count_product = 1;
        foreach ($dpsc_products as $dpsc_product) {
            $dpsc_total += floatval($dpsc_product['price']*$dpsc_product['quantity']);
            $dpsc_var = '';
            if (!empty($dpsc_product['var'])) {
                $dpsc_var = ' ('.$dpsc_product['var'].')';
            }
            $dpsc_at_checkout_to_be_displayed_price = number_format(floatval($dpsc_product['price']*$dpsc_product['quantity']),2);

            if ($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
                $price_row_output = '<td class="price">'.$dpsc_at_checkout_to_be_displayed_price.'</td>';
            }
            else {
                $price_row_output = '';
            }

            $content .= '<tr><td>'.$dpsc_product['name'].$dpsc_var.'</td>
                <td class="quantity"><form action="" method="post" class="product_update">
                <input type="hidden" name="qpid" value="'.$dpsc_product['item_number'].'"/>
                <input type="hidden" name="qpvar" value="'.$dpsc_product['var'].'"/>
                <input type="hidden" name="dpsc_ajax_action" value="update_quantity"/>
                <input type="text" name="quantity" size="1" value="'.$dpsc_product['quantity'].'"/>
                <input type="submit" value="Update" name="qupdate"></form></td>
                ' . $price_row_output . '
                <td><form action="" method="post" class="product_update">
                <input type="hidden" name="qpid" value="'.$dpsc_product['item_number'].'"/>
                <input type="hidden" name="quantity" value="0"/>
                <input type="hidden" name="dpsc_ajax_action" value="update_quantity"/>
                <input type="hidden" name="qpvar" value="'.$dpsc_product['var'].'"/>
                <input type="submit" value="Remove" name="qupdate"></form></td></tr>';
            $dpsc_count_product++;
        }
        $content .= '</table>';
        if ($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
            $content .= '<table id="dpsc-final-price-display">';
            $dpsc_discount_total_at_end = '';
            $dpsc_total_discount = 0.00;
            if ($dp_shopping_cart_settings['discount_enable'] === 'true') {
                $dpsc_total_discount = $dpsc_total*$dpsc_discount_value/100;
                $dpsc_discount_total_at_end = '<tr id="dpsc-checkout-total-discount"><th>Discount:</th><td>-' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="discount_total_price">' . number_format($dpsc_total_discount,2) . '</span><input name="dpsc_discount_code_payment" type="hidden" value="' . $dpsc_discount_value . '"/></td></tr>';
            }
            $dpsc_tax_total_at_end = '';
            if (isset($dp_shopping_cart_settings['tax']) && $dp_shopping_cart_settings['tax'] > 0) {
                $dpsc_total_tax = ($dpsc_total-$dpsc_total_discount)*$dp_shopping_cart_settings['tax']/100;
                $dpsc_tax_total_at_end = '<tr id="dpsc-checkout-total-tax"><th>Tax:</th><td>+' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="tax_total_price">' . number_format($dpsc_total_tax,2) . '</span></td></tr>';
            }

            list($dpsc_total, $dpsc_shipping_weight, $products, $number_of_items_in_cart) = dpsc_pnj_calculate_cart_price();
            $dpsc_shipping_value = dpsc_pnj_calculate_shipping_price($dpsc_shipping_weight, $dpsc_total, $number_of_items_in_cart);
            $dpsc_shipping_total_at_end = '';
            $dpsc_shipping_total_at_end = '<tr id="dpsc-checkout-shipping-price"><th>Shipping:</th><td>+' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="shipping_total_price">' . number_format($dpsc_shipping_value,2).'</span> '.'</td></tr>';
            $dpsc_product_price_at_end = '<tr id="dpsc-checkout-your-price"><th>Price:</th><td>' . $dp_shopping_cart_settings['dp_currency_symbol'] . number_format($dpsc_total,2) . '</td></tr>';
            $dpsc_total_price_at_the_end = '<tr id="dpsc-checkout-total-price"><th>Total:</th><td><strong>' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="total_dpsc_price">' . number_format($dpsc_total+$dpsc_shipping_value+$dpsc_total_tax-$dpsc_total_discount,2) . '</span></strong></td></tr>';
            $content .= '<input type="hidden" name="dpsc_total_hidden_value" value="' . $dpsc_total . '" />';
            $content .= $dpsc_product_price_at_end.$dpsc_shipping_total_at_end.$dpsc_tax_total_at_end.$dpsc_discount_total_at_end.$dpsc_total_price_at_the_end;
            $content .= '</table>';
        }
        if ($_REQUEST['ajax'] === 'true') {
            $content .= '<script type="text/javascript">jQuery("span.dpsc_delete_discount_code").click(function(){
                            jQuery("form.product_update").livequery(function(){
                                    jQuery(this).submit(function() {
                                        form_values = "ajax=true&";
                                        form_values += jQuery(this).serialize();
                                        jQuery.post( "index.php", form_values, function(returned_data) {
                                            eval(returned_data);
                                        });
                                        return false;
                                    });
                                });
                            });</script>';
    }
    }
    return $content;
}

/**
 * Returns the HTML for inquiry form in checkout
 *
 */
function dpsc_print_checkout_inquiry_form() {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $return_path = $dp_shopping_cart_settings['thank_you'];
    $check_return_path = explode('?', $return_path);
    if (count($check_return_path) > 1) {
        $return_path .= '&action=inquiry';
    }
    else {
        $return_path .= '?action=inquiry';
    }
    $output = '<div id="dpsc_inquiry_form">';
    $output .= '<form name="dpsc_inquiry_form" action="' . $return_path . '" method="POST">';
    $output .= '<label for="dpsc_inquiry_from_name">Your Name: </label><br/><input name="dpsc_inquiry_from_name" type="text" value="" /><br/>';
    $output .= '<label for="dpsc_inquiry_from">Your Email: </label><br/><input name="dpsc_inquiry_from" type="text" value="" /><br/>';
    $output .= '<label for="dpsc_inquiry_subject">Subject: </label><br/><input name="dpsc_inquiry_subject" type="text" value="" /><br/>';
    $output .= '<label for="dpsc_inquiry_custom_msg">Message: </label><br/><textarea name="dpsc_inquiry_custom_msg"></textarea><br/>';
    $output .= '<input type="submit" name="dpsc_inquire_submit" value="Ask For Quote"/>';
    $output .= '</form>';
    $output .= '</div>';
    return $output;
}

/**
 * Returns HTML for discount form.
 *
 */
function dpsc_print_checkout_discount_form() {
    $output = '<div class="dpsc_discount_checkout_form">
                    <span id="dpsc_discount_code_heading">Enter Discount Code</span>
                    <table class="dpsc_discount_checkout_table">
                        <tr><th id="dpsc_your_code">Discount Code</th><td><input type="text" name="dpsc_discount_code" id="dpsc_discount_code" value="" /><br/><span class="dpsc_discount_code_invalid" id="dpsc_check_discount_code">&nbsp;</span></td></tr>
                        <tr><th id="dpsc_check_code">&nbsp;</th><td><input type="submit" id="dpsc_validate_discount_code" name="dpsc_validate_discount_code" value="Check" /></td></tr>
                    </table>
                </div>';
    return $output;
}

/**
 * This function validates the discount code
 *
 */
if ($_REQUEST['dpsc_ajax_action'] === 'validate_discount_code') {
    add_action('init', 'dpsc_validate_discount_code');
}

function dpsc_validate_discount_code() {
    $discount_code = trim($_POST['dpsc_check_code']);
    $dpsc_discount_codes = get_option('dpsc_discount_codes');
    if (is_array($dpsc_discount_codes)) {
        $dpsc_validate_code = FALSE;
        $dpsc_discount_value = 0.00;
        foreach ($dpsc_discount_codes as $check_code) {
            if ($check_code['code'] === $discount_code) {
                $one_time = FALSE;
                if ($check_code['one_time'] === 'true' ) {
                    if ($check_code['count'] != 0) {
                        $one_time = TRUE;
                    }
                }
                if (!$one_time) {
                    $dpsc_validate_code = TRUE;
                    $dpsc_discount_value = floatval($check_code['amount']);
                    $_SESSION['dpsc_discount'] = $discount_code;
                }
            }
        }
    }
    if ($_REQUEST['ajax'] == 'true') {
        ob_start();
        echo dpsc_print_checkout_table_html($dpsc_discount_value);
        $valid_output = ob_get_contents();
        ob_end_clean();
        $valid_output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($valid_output));
        echo "jQuery('div.dpsc-table-checkout').html('$valid_output');";
        if ($dpsc_validate_code) {
            echo "jQuery('input#dpsc_discount_code').val('" . $discount_code . "');";
            echo "jQuery('span#dpsc_check_discount_code').css('display', 'block').html('Valid Discount Code');";
            exit();
        }
        else {
            echo "jQuery('span#dpsc_check_discount_code').css('display', 'block').addClass('dpsc_discount_code_invalid').html('Invalid or Expired or Already Used');";
            exit();
        }
    }
}

/**
 * This function returns the HTML for Payment form
 *
 */
function dpsc_print_checkout_payment_form() {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $output = '<div class="dpsc_payment">';
    if (count($dp_shopping_cart_settings['dp_po']) > 1) {
        $output .= '<span id="dpsc_po_error" style="display: none"></span>';
        $output .= '<table class="dpsc_payment_table">';
        foreach ($dp_shopping_cart_settings['dp_po'] as $payment_option) {
            switch ($payment_option) {
                case 'paypal':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="paypal" /></td>
                                    <td class="description">PayPal</td>
                                </tr>';
                    break;

                case 'authorize':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="authorize" /></td>
                                    <td class="description">Authorize.net</td>
                                </tr>';
                    break;

                case 'worldpay':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="worldpay" /></td>
                                    <td class="description">WorldPay</td>
                                </tr>';
                    break;

                case 'alertpay':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="alertpay" /></td>
                                    <td class="description">AlertPay</td>
                                </tr>';
                    break;

                case 'bank':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="bank" /></td>
                                    <td class="description">Bank transfer in advance</td>
                                </tr>';
                    break;

                case 'cash':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="cash" /></td>
                                    <td class="description">Cash at store</td>
                                </tr>';
                    break;

                case 'delivery':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="delivery" /></td>
                                    <td class="description">Cash on delivery</td>
                                </tr>';
                    break;

				case 'mobile':
					$output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="mobile" /></td>
								<td class="description">Pay by Mobile Phone</td>
								</tr>';
					break;

                default:
                    break;

            }
        }
        $output .= '</table>';
    }
    else {
        $output .= 'Make payment using ';
        foreach ($dp_shopping_cart_settings['dp_po'] as $payment_option) {
            switch ($payment_option) {
                case 'paypal':
                    $output .= 'PayPal<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="paypal" />';
                    break;

                case 'authorize':
                    $output .= 'Authorize.net<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="authorize" />';
                    break;

                case 'worldpay':
                    $output .= 'WorldPay<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="worldpay" />';
                    break;

                case 'alertpay':
                    $output .= 'AlertPay<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="alertpay" />';
                    break;

                case 'bank':
                    $output .= 'Bank transfer in advance<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="bank" />';
                    break;

                case 'cash':
                    $output .= 'Cash at store<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="cash" />';
                    break;

				case 'mobile':
                    $output .= 'Pay by Mobile Phone<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="mobile" />';
                    break;

                case 'delivery':
                    $output .= 'Cash on delivery<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="delivery" />';
                    break;

                default:
                    break;

            }
        }
    }
    $output .= ' <input type="submit" id="dpsc_make_payment" value="Make Payment" />';
    $output .= '</div>';
    $output .= '<div id="dpsc_hidden_payment_form" style="display: none"></div>';
    return $output;
}

/**
 * This function saves the order in database and creates invoice PDF.
 *
 */
function dpsc_on_payment_save($dpsc_total = FALSE, $dpsc_shipping_value = FALSE, $products = FALSE, $dpsc_discount_value = FALSE, $dpsc_payment_option = FALSE) {
    global $wpdb;
    $bfname = $_POST['b_fname'];
    $blname = $_POST['b_lname'];
    $bcountry = $_POST['b_country'];
    $baddress = $_POST['b_address'];
    $bcity = $_POST['b_city'];
    $bstate = $_POST['b_state'];
    $bzip = $_POST['b_zip'];
    $bemail = $_POST['b_email'];
    if ($_POST['ship_present'] === 'true') {
        $sfname = $_POST['s_fname'];
        $slname = $_POST['s_lname'];
        $scountry = $_POST['s_country'];
        $saddress = $_POST['s_address'];
        $scity = $_POST['s_city'];
        $sstate = $_POST['s_state'];
        $szip = $_POST['s_zip'];
    }
    else {
        $sfname = $bfname;
        $slname = $blname;
        $scountry = $bcountry;
        $saddress = $baddress;
        $scity = $bcity;
        $sstate = $bstate;
        $szip = $bzip;
    }
    $products = serialize($products);
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $tax = $dp_shopping_cart_settings['tax'];
    if (!$tax) {
        $tax = 0;
    }
    if (!$dpsc_shipping_value) {
        $dpsc_shipping_value = 0.00;
    }
    if (!$dpsc_discount_value) {
        $dpsc_discount_value = 0.00;
    }
    $invoice = date(YmdHis);
    $order_time = microtime(true);
    switch ($dpsc_payment_option) {
        case 'paypal':
            $payment_option = 'PayPal';
            break;

        case 'authorize':
            $payment_option = 'Authorize.net';
            break;

        case 'worldpay':
            $payment_option = 'WorldPay';
            break;

        case 'alertpay':
            $payment_option = 'AlertPay';
            break;

        case 'bank':
            $payment_option = 'Bank Transfer';
            break;

        case 'cash':
            $payment_option = 'Cash at store';
            break;

		case 'mobile':
            $payment_option = 'Mobile Payment';
            break;

        case 'delivery':
            $payment_option = 'Cash on delivery';
            break;

        default:
            break;
    }
    $table_name = $wpdb->prefix . "dpsc_transactions";
    $query = "INSERT INTO {$table_name} (`invoice`, `date`, `order_time`, `billing_first_name`, `billing_last_name`, `billing_country`,
    `billing_address`, `billing_city`, `billing_state`, `billing_zipcode`, `billing_email`, `shipping_first_name`, `shipping_last_name`,
    `shipping_country`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zipcode`, `products`, `payment_option`, `discount`,
    `tax`, `shipping`, `total`, `payment_status`) VALUES ('{$invoice}', NOW(), {$order_time}, '{$bfname}', '{$blname}', '{$bcountry}', '{$baddress}',
    '{$bcity}', '{$bstate}', '{$bzip}', '{$bemail}', '{$sfname}', '{$slname}', '{$scountry}', '{$saddress}', '{$scity}', '{$sstate}', '{$szip}',
    '{$products}', '{$payment_option}', {$dpsc_discount_value}, {$tax}, {$dpsc_shipping_value}, {$dpsc_total}, 'Pending')";
    $wpdb->query($query);
    if (isset($_SESSION['dpsc_discount'])) {
        $dpsc_discount_codes = get_option('dpsc_discount_codes');
        $discount_code = $_SESSION['dpsc_discount'];
        if (is_array($dpsc_discount_codes)) {
            $updated_discount_codes = array();
            foreach ($dpsc_discount_codes as $check_code) {
                if ($check_code['code'] === $discount_code) {
                    $check_code['count']++;
                }
                $updated_discount_codes[] = $check_code;
            }
            update_option('dpsc_discount_codes', $updated_discount_codes);
        }
    }
    $order_id = $wpdb->insert_id;
    $subject = 'New Order #' . $invoice;
    $message = 'Hello,<br/><br/>

                Someone has just placed an order at your shop located at ' . get_bloginfo('url') . '.<br/><br/>

                You can find details of the items ordered by going here: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=dukapress-shopping-cart-order-log&id=' . $order_id .' <br/><br/>

                Here are the details of the person who placed the order: <br/><br/>

                BILLING ADDRESS<br/>
                Name: ' . $bfname .' ' . $blname . '<br/>
                Address: ' . $baddress . '<br/>
                City: ' . $bcity . '<br/>
                Province/State: ' . $bstate . '<br/>
                Postal Code: ' . $bzip . '<br/>
                Country: ' . $bcountry . '<br/>
                Email: ' . $bemail . '<br/><br/>

                SHIPPING ADDRESS<br/>
                Name: ' . $sfname . ' ' . $slname . '<br/>
                Address: ' . $saddress . '<br/>
                City: ' . $scity . '<br/>
                Province/State: ' . $sstate . '<br/>
                Postal Code: ' . $szip . '<br/>
                Country: ' . $scountry . '<br/><br/>

                --
                Warm regards,<br/><br/>' . $dp_shopping_cart_settings['shop_name'];
    $to = get_option('admin_email');
    dpsc_pnj_send_mail($to, $to, 'DukaPress Order Notification', $subject, $message);
    make_pdf($invoice, $dpsc_discount_value, $tax, $dpsc_shipping_value, $dpsc_total, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry);
    return array($invoice, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $bemail);
}

/**
 * This function returns total price, total weight, product information and count
 *
 */
function dpsc_pnj_calculate_cart_price($on_payment = FALSE) {
    $dpsc_products = $_SESSION['dpsc_products'];
    if (is_array($dpsc_products) && count($dpsc_products) > 0) {
        $dpsc_total = 0.00;
        $dpsc_weight = 0;
        $products = array();
        $count = 0;
        foreach ($dpsc_products as $dpsc_product) {
            $dpsc_var = '';
            if (!empty($dpsc_product['var'])) {
                $dpsc_var = ' ('.$dpsc_product['var'].')';
            }
            $dpsc_total += floatval($dpsc_product['price']*$dpsc_product['quantity']);
            $dpsc_weight += $dpsc_product['item_weight']*$dpsc_product['quantity'];
            $product['id'] = $dpsc_product['item_number'];
            $product['name'] = $dpsc_product['name'].$dpsc_var;
            $product['price'] = $dpsc_product['price'];
            $product['quantity'] = $dpsc_product['quantity'];
            $product['weight'] = $dpsc_product['item_weight'];
            $products[] = $product;
            $in_stock = get_post_meta(intval($dpsc_product['item_number']),'currently_in_stock', true);
            if ($on_payment) {
            if ($in_stock && intval($in_stock) > 0) {
                $in_stock = $in_stock - $dpsc_product['quantity'];
                update_post_meta(intval($dpsc_product['item_number']), 'currently_in_stock', $in_stock);
                if ((intval(get_post_meta(intval($dpsc_product['item_number']),'currently_in_stock', true)) < 10) && $dp_shopping_cart_settings['dp_shop_inventory_warning'] === 'yes') {
                    $to = $dp_shopping_cart_settings['dp_shop_inventory_email'];
                    $from = get_option('admin_email');
                    $message = 'Hey,<br/>
                                Product No.: ' . $dpsc_product['item_number'] . '<br/>
                                Product Name: ' . $dpsc_product['name'] . ' is running low in inventory.<br/>
                                Currently in stock: ' . $in_stock . '<br/>
                                Kindly replenish your inventory.<br/>
                                <br/>
                                -DukaPress Automatic Warning Mail Service';
                    dpsc_pnj_send_mail($to, $from, 'Low Inventory Warning', 'Low Inventory Warning', $message);
                }
            }
            }
            if (get_post_meta(intval($dpsc_product['item_number']),'digital_file', true) === '') {
                $count += $dpsc_product['quantity'];
            }
        }
        return array($dpsc_total, $dpsc_weight, $products, $count);
    }
    return array(FALSE, FALSE, FALSE, FALSE);
}

/**
 * This function calculates the shipping price.
 *
 */
function dpsc_pnj_calculate_shipping_price($shipping_weight = FALSE, $sub_total_price = FALSE, $number_of_items_in_cart = FALSE) {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $shipping_method = $dp_shopping_cart_settings['dp_shipping_calc_method'];
    switch ($shipping_method) {
        case 'free':
            $shipping_price = 0.00;
            break;

        case 'flat':
            $shipping_price = $dp_shopping_cart_settings['dp_shipping_flat_rate'];
            break;

        case 'flat_limit':
            $flat_limit = $dp_shopping_cart_settings['dp_shipping_flat_limit_rate'];
            $flat_limit = explode('|', $flat_limit);
            $flat_limit_rate = $flat_limit[0];
            $flat_limit_cutoff = $flat_limit[1];
            if ($sub_total_price > $flat_limit_cutoff) {
                $shipping_price = 0.00;
            }
            else {
                $shipping_price = $flat_limit_rate;
            }
            break;

        case 'weight_flat':
            $per_kg_price = $dp_shopping_cart_settings['dp_shipping_weight_flat_rate'];
            $weight_in_kg = $shipping_weight / 1000;
            $shipping_price = $weight_in_kg*$per_kg_price;
            break;


        case 'weight_class':
            $weight_class = $dp_shopping_cart_settings['dp_shipping_weight_class_rate'];
            $wClasses = array();
            $param = $weight_class;
            $kg = $shipping_weight / 1000;
            $p = explode("#", $param);

            foreach ($p as $v) {
                $a = explode("|", $v);
                $wClasses["$a[1]"] = $a[0];
            }

            foreach ($wClasses as $k => $v) {

                $b = explode("-", $v);

                if ($b[1] == 'ul') {
                    $b[1] = $kg + 100.00;
                }

                $b[0] = (float) $b[0];
                $b[1] = (float) $b[1];

                if ($b[1] > 1.00) {
                    $b[1] = $b[1] + 1.00;
                } else {
                    $b[1] = $b[1] + 0.10;
                }


                if ($kg > $b[0] && $kg < $b[1]) {
                    $sFee = $k;
                }
            }
            $shipping_price = $sFee;
            break;

        case 'per_item':
            $per_item_rate = $dp_shopping_cart_settings['dp_shipping_per_item_rate'];
            $shipping_price = $per_item_rate*$number_of_items_in_cart;
            break;

        default:
            $shipping_price = 0.00;
            break;
    }
    return $shipping_price;
}

/**
 * This function generates the HTML for contact form
 *
 */
function dpsc_pnj_show_contact_information() {
    $output = '<div id="dpsc_contact_information">';
    $output .= '<div id="dpsc_billing_details">';
    $output .= '<h4>Billing Address</h4>';
    $output .= '<label for="b_firstname">First Name</label>
                <input id="b_firstname" name="b_f_name" value="" type="text" /><br />';
    $output .= '<label for="b_lastname">Last Name</label>
                <input id="b_lastname" name="b_l_name" value="" type="text" /><br />';
    $output .= '<label for="b_address">Address</label>
                <input type="text" id="b_address" name="b_address" value="" /><br />';
    $output .= '<label for="b_city">City</label>
                <input type="text" id="b_city" name="b_city" value="" /><br />';
    $output .= '<label for="b_state">Province / State</label>
                <input type="text" id="b_state" name="b_state" value="" /><br />';
    $output .= '<label for="b_zipcode">Postal Code</label>
                <input type="text" id="b_zipcode" name="b_zipcode" value="" /><br />';
    $output .= '<label for="b_country">Country</label>
                <select name="b_country" id="b_country">
                    <option value="AF">Afghanistan</option>
                    <option value="AX">√Öland Islands</option>
                    <option value="AL">Albania</option>
                    <option value="DZ">Algeria</option>
                    <option value="AS">American Samoa</option>
                    <option value="AD">Andorra</option>
                    <option value="AO">Angola</option>
                    <option value="AI">Anguilla</option>
                    <option value="AQ">Antarctica</option>
                    <option value="AG">Antigua and Barbuda</option>
                    <option value="AR">Argentina</option>
                    <option value="AM">Armenia</option>
                    <option value="AW">Aruba</option>
                    <option value="AU">Australia</option>
                    <option value="AT">Austria</option>
                    <option value="AZ">Azerbaijan</option>
                    <option value="BS">Bahamas</option>
                    <option value="BH">Bahrain</option>
                    <option value="BD">Bangladesh</option>
                    <option value="BB">Barbados</option>
                    <option value="BY">Belarus</option>
                    <option value="BE">Belgium</option>
                    <option value="BZ">Belize</option>
                    <option value="BJ">Benin</option>
                    <option value="BM">Bermuda</option>
                    <option value="BT">Bhutan</option>
                    <option value="BO">Bolivia</option>
                    <option value="BA">Bosnia and Herzegovina</option>
                    <option value="BW">Botswana</option>
                    <option value="BV">Bouvet Island</option>
                    <option value="BR">Brazil</option>
                    <option value="IO">British Indian Ocean Territory</option>
                    <option value="BN">Brunei Darussalam</option>
                    <option value="BG">Bulgaria</option>
                    <option value="BF">Burkina Faso</option>
                    <option value="BI">Burundi</option>
                    <option value="KH">Cambodia</option>
                    <option value="CM">Cameroon</option>
                    <option value="CA">Canada</option>
                    <option value="CV">Cape Verde</option>
                    <option value="KY">Cayman Islands</option>
                    <option value="CF">Central African Republic</option>
                    <option value="TD">Chad</option>
                    <option value="CL">Chile</option>
                    <option value="CN">China</option>
                    <option value="CX">Christmas Island</option>
                    <option value="CC">Cocos (Keeling) Islands</option>
                    <option value="CO">Colombia</option>
                    <option value="KM">Comoros</option>
                    <option value="CG">Congo</option>
                    <option value="CD">Congo, The Democratic Republic of The</option>
                    <option value="CK">Cook Islands</option>
                    <option value="CR">Costa Rica</option>
                    <option value="CI">Cote D\'ivoire</option>
                    <option value="HR">Croatia</option>
                    <option value="CU">Cuba</option>
                    <option value="CY">Cyprus</option>
                    <option value="CZ">Czech Republic</option>
                    <option value="DK">Denmark</option>
                    <option value="DJ">Djibouti</option>
                    <option value="DM">Dominica</option>
                    <option value="DO">Dominican Republic</option>
                    <option value="EC">Ecuador</option>
                    <option value="EG">Egypt</option>
                    <option value="SV">El Salvador</option>
                    <option value="GQ">Equatorial Guinea</option>
                    <option value="ER">Eritrea</option>
                    <option value="EE">Estonia</option>
                    <option value="ET">Ethiopia</option>
                    <option value="FK">Falkland Islands (Malvinas)</option>
                    <option value="FO">Faroe Islands</option>
                    <option value="FJ">Fiji</option>
                    <option value="FI">Finland</option>
                    <option value="FR">France</option>
                    <option value="GF">French Guiana</option>
                    <option value="PF">French Polynesia</option>
                    <option value="TF">French Southern Territories</option>
                    <option value="GA">Gabon</option>
                    <option value="GM">Gambia</option>
                    <option value="GE">Georgia</option>
                    <option value="DE">Germany</option>
                    <option value="GH">Ghana</option>
                    <option value="GI">Gibraltar</option>
                    <option value="GR">Greece</option>
                    <option value="GL">Greenland</option>
                    <option value="GD">Grenada</option>
                    <option value="GP">Guadeloupe</option>
                    <option value="GU">Guam</option>
                    <option value="GT">Guatemala</option>
                    <option value="GG">Guernsey</option>
                    <option value="GN">Guinea</option>
                    <option value="GW">Guinea-bissau</option>
                    <option value="GY">Guyana</option>
                    <option value="HT">Haiti</option>
                    <option value="HM">Heard Island and Mcdonald Islands</option>
                    <option value="VA">Holy See (Vatican City State)</option>
                    <option value="HN">Honduras</option>
                    <option value="HK">Hong Kong</option>
                    <option value="HU">Hungary</option>
                    <option value="IS">Iceland</option>
                    <option value="IN">India</option>
                    <option value="ID">Indonesia</option>
                    <option value="IR">Iran, Islamic Republic of</option>
                    <option value="IQ">Iraq</option>
                    <option value="IE">Ireland</option>
                    <option value="IM">Isle of Man</option>
                    <option value="IL">Israel</option>
                    <option value="IT">Italy</option>
                    <option value="JM">Jamaica</option>
                    <option value="JP">Japan</option>
                    <option value="JE">Jersey</option>
                    <option value="JO">Jordan</option>
                    <option value="KZ">Kazakhstan</option>
                    <option value="KE">Kenya</option>
                    <option value="KI">Kiribati</option>
                    <option value="KP">Korea, Democratic People\'s Republic of</option>
                    <option value="KR">Korea, Republic of</option>
                    <option value="KW">Kuwait</option>
                    <option value="KG">Kyrgyzstan</option>
                    <option value="LA">Lao People\'s Democratic Republic</option>
                    <option value="LV">Latvia</option>
                    <option value="LB">Lebanon</option>
                    <option value="LS">Lesotho</option>
                    <option value="LR">Liberia</option>
                    <option value="LY">Libyan Arab Jamahiriya</option>
                    <option value="LI">Liechtenstein</option>
                    <option value="LT">Lithuania</option>
                    <option value="LU">Luxembourg</option>
                    <option value="MO">Macao</option>
                    <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                    <option value="MG">Madagascar</option>
                    <option value="MW">Malawi</option>
                    <option value="MY">Malaysia</option>
                    <option value="MV">Maldives</option>
                    <option value="ML">Mali</option>
                    <option value="MT">Malta</option>
                    <option value="MH">Marshall Islands</option>
                    <option value="MQ">Martinique</option>
                    <option value="MR">Mauritania</option>
                    <option value="MU">Mauritius</option>
                    <option value="YT">Mayotte</option>
                    <option value="MX">Mexico</option>
                    <option value="FM">Micronesia, Federated States of</option>
                    <option value="MD">Moldova, Republic of</option>
                    <option value="MC">Monaco</option>
                    <option value="MN">Mongolia</option>
                    <option value="ME">Montenegro</option>
                    <option value="MS">Montserrat</option>
                    <option value="MA">Morocco</option>
                    <option value="MZ">Mozambique</option>
                    <option value="MM">Myanmar</option>
                    <option value="NA">Namibia</option>
                    <option value="NR">Nauru</option>
                    <option value="NP">Nepal</option>
                    <option value="NL">Netherlands</option>
                    <option value="AN">Netherlands Antilles</option>
                    <option value="NC">New Caledonia</option>
                    <option value="NZ">New Zealand</option>
                    <option value="NI">Nicaragua</option>
                    <option value="NE">Niger</option>
                    <option value="NG">Nigeria</option>
                    <option value="NU">Niue</option>
                    <option value="NF">Norfolk Island</option>
                    <option value="MP">Northern Mariana Islands</option>
                    <option value="NO">Norway</option>
                    <option value="OM">Oman</option>
                    <option value="PK">Pakistan</option>
                    <option value="PW">Palau</option>
                    <option value="PS">Palestinian Territory, Occupied</option>
                    <option value="PA">Panama</option>
                    <option value="PG">Papua New Guinea</option>
                    <option value="PY">Paraguay</option>
                    <option value="PE">Peru</option>
                    <option value="PH">Philippines</option>
                    <option value="PN">Pitcairn</option>
                    <option value="PL">Poland</option>
                    <option value="PT">Portugal</option>
                    <option value="PR">Puerto Rico</option>
                    <option value="QA">Qatar</option>
                    <option value="RE">Reunion</option>
                    <option value="RO">Romania</option>
                    <option value="RU">Russian Federation</option>
                    <option value="RW">Rwanda</option>
                    <option value="SH">Saint Helena</option>
                    <option value="KN">Saint Kitts and Nevis</option>
                    <option value="LC">Saint Lucia</option>
                    <option value="PM">Saint Pierre and Miquelon</option>
                    <option value="VC">Saint Vincent and The Grenadines</option>
                    <option value="WS">Samoa</option>
                    <option value="SM">San Marino</option>
                    <option value="ST">Sao Tome and Principe</option>
                    <option value="SA">Saudi Arabia</option>
                    <option value="SN">Senegal</option>
                    <option value="RS">Serbia</option>
                    <option value="SC">Seychelles</option>
                    <option value="SL">Sierra Leone</option>
                    <option value="SG">Singapore</option>
                    <option value="SK">Slovakia</option>
                    <option value="SI">Slovenia</option>
                    <option value="SB">Solomon Islands</option>
                    <option value="SO">Somalia</option>
                    <option value="ZA">South Africa</option>
                    <option value="GS">South Georgia and The South Sandwich Islands</option>
                    <option value="ES">Spain</option>
                    <option value="LK">Sri Lanka</option>
                    <option value="SD">Sudan</option>
                    <option value="SR">Suriname</option>
                    <option value="SJ">Svalbard and Jan Mayen</option>
                    <option value="SZ">Swaziland</option>
                    <option value="SE">Sweden</option>
                    <option value="CH">Switzerland</option>
                    <option value="SY">Syrian Arab Republic</option>
                    <option value="TW">Taiwan, Province of China</option>
                    <option value="TJ">Tajikistan</option>
                    <option value="TZ">Tanzania, United Republic of</option>
                    <option value="TH">Thailand</option>
                    <option value="TL">Timor-leste</option>
                    <option value="TG">Togo</option>
                    <option value="TK">Tokelau</option>
                    <option value="TO">Tonga</option>
                    <option value="TT">Trinidad and Tobago</option>
                    <option value="TN">Tunisia</option>
                    <option value="TR">Turkey</option>
                    <option value="TM">Turkmenistan</option>
                    <option value="TC">Turks and Caicos Islands</option>
                    <option value="TV">Tuvalu</option>
                    <option value="UG">Uganda</option>
                    <option value="UA">Ukraine</option>
                    <option value="AE">United Arab Emirates</option>
                    <option value="GB">United Kingdom</option>
                    <option value="US">United States</option>
                    <option value="UM">United States Minor Outlying Islands</option>
                    <option value="UY">Uruguay</option>
                    <option value="UZ">Uzbekistan</option>
                    <option value="VU">Vanuatu</option>
                    <option value="VE">Venezuela</option>
                    <option value="VN">Viet Nam</option>
                    <option value="VG">Virgin Islands, British</option>
                    <option value="VI">Virgin Islands, U.S.</option>
                    <option value="WF">Wallis and Futuna</option>
                    <option value="EH">Western Sahara</option>
                    <option value="YE">Yemen</option>
                    <option value="ZM">Zambia</option>
                    <option value="ZW">Zimbabwe</option>
                </select><br />';
    $output .= '<label for="b_email">Email</label>
                <input type="text" id="b_email" name="b_email" value="" /><br />';
    $output .= '</div>';
    $output .= '<div id="dpsc_shipping_details" style="display: none">';
    $output .= '<h4>Shipping Address</h4>';
    $output .= '<label for="s_firstname">First Name</label>
                <input id="s_firstname" name="s_f_name" value="" type="text" /><br />';
    $output .= '<label for="s_lastname">Last Name</label>
                <input id="s_lastname" name="s_l_name" value="" type="text" /><br />';
    $output .= '<label for="s_address">Address</label>
                <input type="text" id="s_address" name="s_address" value="" /><br />';
    $output .= '<label for="s_city">City</label>
                <input type="text" id="s_city" name="s_city" value="" /><br />';
    $output .= '<label for="s_state">Province / State</label>
                <input type="text" id="s_state" name="s_state" value="" /><br />';
    $output .= '<label for="s_zipcode">Postal Code</label>
                <input type="text" id="s_zipcode" name="s_zipcode" value="" /><br />';
    $output .= '<label for="s_country">Country</label>
                <select name="s_country" id="s_country">
                    <option value="AF">Afghanistan</option>
                    <option value="AX">√Öland Islands</option>
                    <option value="AL">Albania</option>
                    <option value="DZ">Algeria</option>
                    <option value="AS">American Samoa</option>
                    <option value="AD">Andorra</option>
                    <option value="AO">Angola</option>
                    <option value="AI">Anguilla</option>
                    <option value="AQ">Antarctica</option>
                    <option value="AG">Antigua and Barbuda</option>
                    <option value="AR">Argentina</option>
                    <option value="AM">Armenia</option>
                    <option value="AW">Aruba</option>
                    <option value="AU">Australia</option>
                    <option value="AT">Austria</option>
                    <option value="AZ">Azerbaijan</option>
                    <option value="BS">Bahamas</option>
                    <option value="BH">Bahrain</option>
                    <option value="BD">Bangladesh</option>
                    <option value="BB">Barbados</option>
                    <option value="BY">Belarus</option>
                    <option value="BE">Belgium</option>
                    <option value="BZ">Belize</option>
                    <option value="BJ">Benin</option>
                    <option value="BM">Bermuda</option>
                    <option value="BT">Bhutan</option>
                    <option value="BO">Bolivia</option>
                    <option value="BA">Bosnia and Herzegovina</option>
                    <option value="BW">Botswana</option>
                    <option value="BV">Bouvet Island</option>
                    <option value="BR">Brazil</option>
                    <option value="IO">British Indian Ocean Territory</option>
                    <option value="BN">Brunei Darussalam</option>
                    <option value="BG">Bulgaria</option>
                    <option value="BF">Burkina Faso</option>
                    <option value="BI">Burundi</option>
                    <option value="KH">Cambodia</option>
                    <option value="CM">Cameroon</option>
                    <option value="CA">Canada</option>
                    <option value="CV">Cape Verde</option>
                    <option value="KY">Cayman Islands</option>
                    <option value="CF">Central African Republic</option>
                    <option value="TD">Chad</option>
                    <option value="CL">Chile</option>
                    <option value="CN">China</option>
                    <option value="CX">Christmas Island</option>
                    <option value="CC">Cocos (Keeling) Islands</option>
                    <option value="CO">Colombia</option>
                    <option value="KM">Comoros</option>
                    <option value="CG">Congo</option>
                    <option value="CD">Congo, The Democratic Republic of The</option>
                    <option value="CK">Cook Islands</option>
                    <option value="CR">Costa Rica</option>
                    <option value="CI">Cote D\'ivoire</option>
                    <option value="HR">Croatia</option>
                    <option value="CU">Cuba</option>
                    <option value="CY">Cyprus</option>
                    <option value="CZ">Czech Republic</option>
                    <option value="DK">Denmark</option>
                    <option value="DJ">Djibouti</option>
                    <option value="DM">Dominica</option>
                    <option value="DO">Dominican Republic</option>
                    <option value="EC">Ecuador</option>
                    <option value="EG">Egypt</option>
                    <option value="SV">El Salvador</option>
                    <option value="GQ">Equatorial Guinea</option>
                    <option value="ER">Eritrea</option>
                    <option value="EE">Estonia</option>
                    <option value="ET">Ethiopia</option>
                    <option value="FK">Falkland Islands (Malvinas)</option>
                    <option value="FO">Faroe Islands</option>
                    <option value="FJ">Fiji</option>
                    <option value="FI">Finland</option>
                    <option value="FR">France</option>
                    <option value="GF">French Guiana</option>
                    <option value="PF">French Polynesia</option>
                    <option value="TF">French Southern Territories</option>
                    <option value="GA">Gabon</option>
                    <option value="GM">Gambia</option>
                    <option value="GE">Georgia</option>
                    <option value="DE">Germany</option>
                    <option value="GH">Ghana</option>
                    <option value="GI">Gibraltar</option>
                    <option value="GR">Greece</option>
                    <option value="GL">Greenland</option>
                    <option value="GD">Grenada</option>
                    <option value="GP">Guadeloupe</option>
                    <option value="GU">Guam</option>
                    <option value="GT">Guatemala</option>
                    <option value="GG">Guernsey</option>
                    <option value="GN">Guinea</option>
                    <option value="GW">Guinea-bissau</option>
                    <option value="GY">Guyana</option>
                    <option value="HT">Haiti</option>
                    <option value="HM">Heard Island and Mcdonald Islands</option>
                    <option value="VA">Holy See (Vatican City State)</option>
                    <option value="HN">Honduras</option>
                    <option value="HK">Hong Kong</option>
                    <option value="HU">Hungary</option>
                    <option value="IS">Iceland</option>
                    <option value="IN">India</option>
                    <option value="ID">Indonesia</option>
                    <option value="IR">Iran, Islamic Republic of</option>
                    <option value="IQ">Iraq</option>
                    <option value="IE">Ireland</option>
                    <option value="IM">Isle of Man</option>
                    <option value="IL">Israel</option>
                    <option value="IT">Italy</option>
                    <option value="JM">Jamaica</option>
                    <option value="JP">Japan</option>
                    <option value="JE">Jersey</option>
                    <option value="JO">Jordan</option>
                    <option value="KZ">Kazakhstan</option>
                    <option value="KE">Kenya</option>
                    <option value="KI">Kiribati</option>
                    <option value="KP">Korea, Democratic People\'s Republic of</option>
                    <option value="KR">Korea, Republic of</option>
                    <option value="KW">Kuwait</option>
                    <option value="KG">Kyrgyzstan</option>
                    <option value="LA">Lao People\'s Democratic Republic</option>
                    <option value="LV">Latvia</option>
                    <option value="LB">Lebanon</option>
                    <option value="LS">Lesotho</option>
                    <option value="LR">Liberia</option>
                    <option value="LY">Libyan Arab Jamahiriya</option>
                    <option value="LI">Liechtenstein</option>
                    <option value="LT">Lithuania</option>
                    <option value="LU">Luxembourg</option>
                    <option value="MO">Macao</option>
                    <option value="MK">Macedonia, The Former Yugoslav Republic of</option>
                    <option value="MG">Madagascar</option>
                    <option value="MW">Malawi</option>
                    <option value="MY">Malaysia</option>
                    <option value="MV">Maldives</option>
                    <option value="ML">Mali</option>
                    <option value="MT">Malta</option>
                    <option value="MH">Marshall Islands</option>
                    <option value="MQ">Martinique</option>
                    <option value="MR">Mauritania</option>
                    <option value="MU">Mauritius</option>
                    <option value="YT">Mayotte</option>
                    <option value="MX">Mexico</option>
                    <option value="FM">Micronesia, Federated States of</option>
                    <option value="MD">Moldova, Republic of</option>
                    <option value="MC">Monaco</option>
                    <option value="MN">Mongolia</option>
                    <option value="ME">Montenegro</option>
                    <option value="MS">Montserrat</option>
                    <option value="MA">Morocco</option>
                    <option value="MZ">Mozambique</option>
                    <option value="MM">Myanmar</option>
                    <option value="NA">Namibia</option>
                    <option value="NR">Nauru</option>
                    <option value="NP">Nepal</option>
                    <option value="NL">Netherlands</option>
                    <option value="AN">Netherlands Antilles</option>
                    <option value="NC">New Caledonia</option>
                    <option value="NZ">New Zealand</option>
                    <option value="NI">Nicaragua</option>
                    <option value="NE">Niger</option>
                    <option value="NG">Nigeria</option>
                    <option value="NU">Niue</option>
                    <option value="NF">Norfolk Island</option>
                    <option value="MP">Northern Mariana Islands</option>
                    <option value="NO">Norway</option>
                    <option value="OM">Oman</option>
                    <option value="PK">Pakistan</option>
                    <option value="PW">Palau</option>
                    <option value="PS">Palestinian Territory, Occupied</option>
                    <option value="PA">Panama</option>
                    <option value="PG">Papua New Guinea</option>
                    <option value="PY">Paraguay</option>
                    <option value="PE">Peru</option>
                    <option value="PH">Philippines</option>
                    <option value="PN">Pitcairn</option>
                    <option value="PL">Poland</option>
                    <option value="PT">Portugal</option>
                    <option value="PR">Puerto Rico</option>
                    <option value="QA">Qatar</option>
                    <option value="RE">Reunion</option>
                    <option value="RO">Romania</option>
                    <option value="RU">Russian Federation</option>
                    <option value="RW">Rwanda</option>
                    <option value="SH">Saint Helena</option>
                    <option value="KN">Saint Kitts and Nevis</option>
                    <option value="LC">Saint Lucia</option>
                    <option value="PM">Saint Pierre and Miquelon</option>
                    <option value="VC">Saint Vincent and The Grenadines</option>
                    <option value="WS">Samoa</option>
                    <option value="SM">San Marino</option>
                    <option value="ST">Sao Tome and Principe</option>
                    <option value="SA">Saudi Arabia</option>
                    <option value="SN">Senegal</option>
                    <option value="RS">Serbia</option>
                    <option value="SC">Seychelles</option>
                    <option value="SL">Sierra Leone</option>
                    <option value="SG">Singapore</option>
                    <option value="SK">Slovakia</option>
                    <option value="SI">Slovenia</option>
                    <option value="SB">Solomon Islands</option>
                    <option value="SO">Somalia</option>
                    <option value="ZA">South Africa</option>
                    <option value="GS">South Georgia and The South Sandwich Islands</option>
                    <option value="ES">Spain</option>
                    <option value="LK">Sri Lanka</option>
                    <option value="SD">Sudan</option>
                    <option value="SR">Suriname</option>
                    <option value="SJ">Svalbard and Jan Mayen</option>
                    <option value="SZ">Swaziland</option>
                    <option value="SE">Sweden</option>
                    <option value="CH">Switzerland</option>
                    <option value="SY">Syrian Arab Republic</option>
                    <option value="TW">Taiwan, Province of China</option>
                    <option value="TJ">Tajikistan</option>
                    <option value="TZ">Tanzania, United Republic of</option>
                    <option value="TH">Thailand</option>
                    <option value="TL">Timor-leste</option>
                    <option value="TG">Togo</option>
                    <option value="TK">Tokelau</option>
                    <option value="TO">Tonga</option>
                    <option value="TT">Trinidad and Tobago</option>
                    <option value="TN">Tunisia</option>
                    <option value="TR">Turkey</option>
                    <option value="TM">Turkmenistan</option>
                    <option value="TC">Turks and Caicos Islands</option>
                    <option value="TV">Tuvalu</option>
                    <option value="UG">Uganda</option>
                    <option value="UA">Ukraine</option>
                    <option value="AE">United Arab Emirates</option>
                    <option value="GB">United Kingdom</option>
                    <option value="US">United States</option>
                    <option value="UM">United States Minor Outlying Islands</option>
                    <option value="UY">Uruguay</option>
                    <option value="UZ">Uzbekistan</option>
                    <option value="VU">Vanuatu</option>
                    <option value="VE">Venezuela</option>
                    <option value="VN">Viet Nam</option>
                    <option value="VG">Virgin Islands, British</option>
                    <option value="VI">Virgin Islands, U.S.</option>
                    <option value="WF">Wallis and Futuna</option>
                    <option value="EH">Western Sahara</option>
                    <option value="YE">Yemen</option>
                    <option value="ZM">Zambia</option>
                    <option value="ZW">Zimbabwe</option>
                </select><br />';
    $output .= '</div>';
    $output .= '<input type="checkbox" name="dpsc_contact_different_ship_address" id="dpsc_contact_different_ship_address" value="checked">&nbsp;I have a different Shipping Address.';
    $output .= '</div>';
    return $output;
}

/**
 * This function checks whether a product is digital or not
 *
 */
function dpsc_pnj_is_digital_present($products = FALSE) {
    if ($products) {
        $products = unserialize($products);
        if (is_array($products) && count($products) > 0) {
            $is_digital = FALSE;
            $digital_id = array();
            foreach ($products as $product) {
                if (get_post_meta(intval($product['id']),'digital_file', true) != '') {
                    $is_digital = TRUE;
                    $digital_id[] = $product['id'];
                }
            }
            if ($is_digital) {
                return $digital_id;
            }
            else {
                return $is_digital;
            }
        }
    }
    return FALSE;
}

/**
 * Thank you page shortcode
 *
 */
add_shortcode('dpsc_thank_you_page', 'dpsc_thank_you_shortcode');
function dpsc_thank_you_shortcode($content = NULL) {
    $content .= '<div id="dpsc_thank_you_page">' . dpsc_pnj_thank_you_page() . '</div>';
    return $content;
}

function dpsc_pnj_thank_you_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . "dpsc_transactions";
    $output .= '';
    $invoice = $_GET['id'];
    $status = isset($_GET['status']) ? $_GET['status'] : FALSE;
    if ( $_GET['action'] === 'inquiry' ) {
        $from_name = stripcslashes(trim($_POST['dpsc_inquiry_from_name']));
        $from_email = stripcslashes(trim($_POST['dpsc_inquiry_from']));
        $subject = stripcslashes(trim($_POST['dpsc_inquiry_subject']));
        $message = stripcslashes(trim($_POST['dpsc_inquiry_custom_msg']));
        list ($dpsc_total, $dpsc_weight, $products, $count) = dpsc_pnj_calculate_cart_price();
        $message_content = '<table>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                </tr>';
        $inq_count = 1;
        foreach ($products as $product) {
            $message_content .= '<tr>
                                    <td>' . $inq_count . '</td>
                                    <td>' . $product['name'] . '</td>
                                    <td>' . $product['quantity'] . '</td>
                                </tr>';
            $inq_count++;
        }
        $message_content .= '</table>';
        $final_msg = 'From: ' . $from_name . '(' . $from_email . ')<br/>Subject:' . $subject . '<br/>' . $message . '<br/>' . $message_content;
        $to = get_option('admin_email');
        dpsc_pnj_send_mail($to, $to, 'Inquiry Form Submitted', $subject, $final_msg);
        $output = '<h3>Thank you for submitting Inquiry form.</h3><p>We will contact you soon.</p>';
        return $output;
    }
    if (!$status) {
        $output = '<h2>Thank you for your order!</h2>';
        $query = "SELECT * FROM {$table_name} WHERE `invoice`='{$invoice}'";
        $result = $wpdb->get_row($query);
        if ($result) {
            $total = $result->total;
            $shipping = $result->shipping;
            $discount = $result->discount;
            $tax= $result->tax;
            $to_email = $result->billing_email;
            $from_email = get_option('admin_email');
            $bfname = $result->billing_first_name;
            $blname = $result->billing_last_name;
            if ($discount > 0) {
                $total_discount = $total*$discount/100;
            }
            else {
                $total_discount = 0;
            }
            if ($tax > 0) {
                $total_tax = ($total-$total_discount)*$tax/100;
            }
            else {
                $total_tax = 0;
            }
            $amount = number_format($total+$shipping+$total_tax-$total_discount,2);
            $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');

            switch ($result->payment_option) {
                case 'Cash on delivery':
                    $output .= '<h4>Please keep <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ready for payment upon delivery.</h4>';
                    break;


                case 'Cash at store':
                    $output .= '<h4>Please keep <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ready for payment when you come to take your order.</h4>';
                    break;


                case 'Bank Transfer':
                    $output .= '<h4>Please transfer <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> to our Bank Account using following information:</h4>
                                <table>
                                    <tr>
                                        <td>Name of Recipent:</td><td>' . $dp_shopping_cart_settings['bank_account_owner'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>for:</td><td>Order No.: ' . $invoice . '</td>
                                    </tr>
                                    <tr>
                                        <td>Name of Bank:</td><td>' . $dp_shopping_cart_settings['bank_name'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>Routing Number:</td><td>' . $dp_shopping_cart_settings['bank_routing'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>Account Number:</td><td>' . $dp_shopping_cart_settings['bank_account'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>IBAN:</td><td>' . $dp_shopping_cart_settings['bank_IBAN'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>BIC/SWIFT:</td><td>' . $dp_shopping_cart_settings['bank_bic'] . '</td>
                                    </tr>
                                 </table>
                                 <p>When we have received your payment in our account, we will begin to Process your Order.</p>';
                    break;

				case 'Mobile Payment':
                    $output .= '<h4>Please send <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> to any of the following numbers:</h4>
                                <table>

                                    <tr>
                                        <td>MPESA:</td><td>' . $dp_shopping_cart_settings['safaricom_number'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>YU Cash:</td><td>' . $dp_shopping_cart_settings['yu_number'] . '</td>
                                    </tr>
                                    <tr>
                                        <td>ZAP:</td><td>' . $dp_shopping_cart_settings['zain_number'] . '</td>
                                    </tr>

                                 </table>
                                 <p>Please also send your invoice number to us by SMS using the phone that you used to send the money. When we have received your payment in any of our accounts, we shall begin to Process your Order.</p>';
                    break;

                default:
                    $output .= '<h4>Thank you for making the payment of <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> using ' . $result->payment_option . '.</h4>
                                <p>We will process your order soon.</p>';
                    break;
            }
            $output .= '<p><a href="' . DP_PLUGIN_URL .'/pdf/invoice_' . $invoice . '.pdf">Click here to download your Invoice.</a></p>';
            $message = 'Hi ' . $bfname . ' ' . $blname . ',<br/>
                        We have received your Order No.: ' . $invoice . '.<br/>
                        We will start processing your Order the moment we get payment.
                        <br/><br/>
                        Thanks,<br/>
                        ' . $dp_shopping_cart_settings['shop_name'];
            $subject = 'Receipt of Order No.: ' . $invoice;
            dpsc_pnj_send_mail($to_email, $from_email, $dp_shopping_cart_settings['shop_name'], $subject, $message, $invoice);
            return $output;
        }
    }
    else {
        $update_query = "UPDATE {$table_name} SET `payment_status`='Canceled'
                        WHERE `invoice`='{$invoice}'";
        $wpdb->query($update_query);
        $output = 'Order canceled !!';
        return $output;
    }
}


?>