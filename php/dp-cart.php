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
        if (is_array($product_variations)) {
            foreach ($product_variations as $product_variation) {
                $product_variation_tmp = explode(',:_._:,', $product_variation);
                $product_variation_names[] = $product_variation_tmp[0];
                $product_price = floatval($product_variation_tmp[1]);
                $product_variation_prices += $product_price;
            }
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
        echo dpsc_print_cart_html(FALSE, $product_name);
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
        echo "jQuery('div#dpsc_update_icon_" . $product_id ."').css('display', 'none');";
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
    if (is_array($products)) {
        foreach ($products as $key => $item) {
            unset($products[$key]);
        }
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
        if (is_array($dpsc_products)) {
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
    }
    else {
        $dpsc_products = $_SESSION['dpsc_products'];
        if (is_array($dpsc_products)) {
            foreach ($dpsc_products as $key => $item) {
                if ($item['item_number'] === $product_id && $item['var'] === $product_variation_name) {
                    unset($dpsc_products[$key]);
                }
            }
        }
    }
    sort($dpsc_products);
    $_SESSION['dpsc_products'] = $dpsc_products;
    if ($_REQUEST['ajax'] == 'true') {
        list($dpsc_checkout_html, $dp_shipping_calculate_html) = dpsc_print_checkout_table_html();
        ob_start();
        list($content, $dp_shipping_calculate_html) = dpsc_print_checkout_table_html();
        echo $content;
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
        $output .= '<span id="dpsc-checkout-text">'.__("Please review your order",'dp-lang').'</span>';
        list($dpsc_checkout_html, $dp_shipping_calculate_html) = dpsc_print_checkout_table_html();
        $output .= '<div class="clear"></div><div class="dpsc-table-checkout">' . $dpsc_checkout_html . '</div>';
        if ($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
            if ($dp_shopping_cart_settings['discount_enable'] === 'true') {
                $output .= '<div class="clear"></div>' . dpsc_print_checkout_discount_form();
            }
            $output .= $dp_shipping_calculate_html;
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
        $output .= __("There are no products in your cart.",'dp-lang');
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
            $price_head_output = '<th>'.__("Price","dp-lang").'</th>';
        }
        else {
            $price_head_output = '';
        }
        $content .= '<table class="dpsc-checkout-product-list">';
        $content .= '<tr><th>'.__("Product","dp-lang").'</th><th>'.__("Quantity","dp-lang").'</th>' . $price_head_output . '<th /></tr>';
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

            $content .= '<tr><td>'. __($dpsc_product['name'],"dp-lang").__($dpsc_var,"dp-lang").'</td>
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
                $dpsc_discount_total_at_end = '<tr id="dpsc-checkout-total-discount"><th>'.__("Discount:","dp-lang").'</th><td>-' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="discount_total_price">' . number_format($dpsc_total_discount,2) . '</span><input name="dpsc_discount_code_payment" type="hidden" value="' . $dpsc_discount_value . '"/></td></tr>';
            }
            $dpsc_tax_total_at_end = '';
            if (isset($dp_shopping_cart_settings['tax']) && $dp_shopping_cart_settings['tax'] > 0) {
                $dpsc_total_tax = ($dpsc_total-$dpsc_total_discount)*$dp_shopping_cart_settings['tax']/100;
                $dpsc_tax_total_at_end = '<tr id="dpsc-checkout-total-tax"><th>Tax:</th><td>+' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="tax_total_price">' . number_format($dpsc_total_tax,2) . '</span></td></tr>';
            }

            list($dpsc_total, $dpsc_shipping_weight, $products, $number_of_items_in_cart) = dpsc_pnj_calculate_cart_price();
            $dpsc_shipping_value = dpsc_pnj_calculate_shipping_price($dpsc_shipping_weight, $dpsc_total, $number_of_items_in_cart);
            $dp_shipping_price_html = '<span id="shipping_total_price">0.00</span> ';
            $dp_shipping_calculate_html = '';
            if (is_numeric($dpsc_shipping_value)) {
                $dp_shipping_price = $dpsc_shipping_value;
                $dp_shipping_price_html = '<span id="shipping_total_price">' . number_format($dp_shipping_price,2) . '</span> ';
            }
            else {
                $dp_shipping_price = 0;
                switch ($dpsc_shipping_value) {
                    case 'ship_pro':
                        if ($dpsc_shipping_weight > 0) {
                            $dp_shipping_calculate_html = dp_shipping_pro_options();
                        }
                        break;

                    case 'fedex':
                        if ($dpsc_shipping_weight > 0) {
                            $dp_shipping_calculate_html = dp_fedex_get_country_dropdown();
                        }
                        break;

                    default:
                        break;
                }
            }
            $dpsc_shipping_total_at_end = '';
            $dpsc_shipping_total_at_end = '<tr id="dpsc-checkout-shipping-price"><th>'.__("Shipping:","dp-lang").'</th><td>+' . $dp_shopping_cart_settings['dp_currency_symbol'] . $dp_shipping_price_html . '</td></tr>';
            $dpsc_product_price_at_end = '<tr id="dpsc-checkout-your-price"><th>'.__("Price:","dp-lang").'</th><td>' . $dp_shopping_cart_settings['dp_currency_symbol'] . number_format($dpsc_total,2) . '</td></tr>';
            $dpsc_total_price_at_the_end = '<tr id="dpsc-checkout-total-price"><th>'.__("Total:","dp-lang").'</th><td><strong>' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="total_dpsc_price">' . number_format($dpsc_total+$dp_shipping_price+$dpsc_total_tax-$dpsc_total_discount,2) . '</span></strong></td></tr>';
            $content .= '<input type="hidden" name="dpsc_total_hidden_value" value="' . $dpsc_total . '" />';
            if (!is_numeric($dpsc_shipping_value)) {
                $total_for_shipping = $dpsc_total+$dpsc_total_tax-$dpsc_total_discount;
                $content .= '<input type="hidden" name="dpsc_total_hidden_value_for_shipping" value="' . $total_for_shipping . '" />';
            }
            $content .= $dpsc_product_price_at_end.$dpsc_shipping_total_at_end.$dpsc_tax_total_at_end.$dpsc_discount_total_at_end.$dpsc_total_price_at_the_end;
            $content .= '</table><input type="hidden" name="custom_shipping_value" value="0.00" />';
//            $content .= $dp_shipping_calculate_html;
        }
        if ($_REQUEST['ajax'] === 'true') {
            $content .= '<script type="text/javascript">
                            jQuery("form.product_update").livequery(function(){
                                    jQuery(this).submit(function() {
                                        form_values = "ajax=true&";
                                        form_values += jQuery(this).serialize();
                                        jQuery.post( "index.php", form_values, function(returned_data) {
                                            eval(returned_data);
                                        });
                                        return false;
                                    });
                                });</script>';
    }
    }
    return array($content, $dp_shipping_calculate_html);
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
    $output .= '<label for="dpsc_inquiry_from_name">'.__("Your Name:","dp-lang").' </label><br/><input name="dpsc_inquiry_from_name" type="text" value="" /><br/>';
    $output .= '<label for="dpsc_inquiry_from">'.__("Your Email:","dp-lang").' </label><br/><input name="dpsc_inquiry_from" type="text" value="" /><br/>';
    $output .= '<label for="dpsc_inquiry_subject">'.__("Subject:","dp-lang").' </label><br/><input name="dpsc_inquiry_subject" type="text" value="" /><br/>';
    $output .= '<label for="dpsc_inquiry_custom_msg">'.__("Message:","dp-lang").' </label><br/><textarea name="dpsc_inquiry_custom_msg"></textarea><br/>';
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
                    <span id="dpsc_discount_code_heading">'.__("Enter Discount Code","dp-lang").'</span>
                    <table class="dpsc_discount_checkout_table">
                        <tr><th id="dpsc_your_code">'.__("Discount Code","dp-lang").'</th><td><input type="text" name="dpsc_discount_code" id="dpsc_discount_code" value="" /><br/><span class="dpsc_discount_code_invalid dpsc_error_msg" id="dpsc_check_discount_code">&nbsp;</span></td></tr>
                        <tr><th id="dpsc_check_code">&nbsp;</th><td><input type="submit" id="dpsc_validate_discount_code" name="dpsc_validate_discount_code" value="'.__("Check","dp-lang").'" /></td></tr>
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
        list($dpsc_checkout_html, $dp_shipping_calculate_html) = dpsc_print_checkout_table_html($dpsc_discount_value);
        ob_start();
        echo $dpsc_checkout_html;
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
                                    <td class="description">'.__("PayPal","dp-lang").'</td>
                                </tr>';
                    break;

                case 'authorize':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="authorize" /></td>
                                    <td class="description">'.__("Authorize.net","dp-lang").'</td>
                                </tr>';
                    break;

                case 'worldpay':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="worldpay" /></td>
                                    <td class="description">'.__("WorldPay","dp-lang").'</td>
                                </tr>';
                    break;

                case 'alertpay':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="alertpay" /></td>
                                    <td class="description">'.__("AlertPay","dp-lang").'</td>
                                </tr>';
                    break;

                case 'bank':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="bank" /></td>
                                    <td class="description">'.__("Bank transfer in advance","dp-lang").'</td>
                                </tr>';
                    break;

                case 'cash':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="cash" /></td>
                                    <td class="description">'.__("Cash at store","dp-lang").'</td>
                                </tr>';
                    break;

                case 'delivery':
                    $output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="delivery" /></td>
                                    <td class="description">'.__("Cash on delivery","dp-lang").'</td>
                                </tr>';
                    break;

				case 'mobile':
					$output .= '<tr><td class="radio"><input type="radio" name="dpsc_po" value="mobile" /></td>
								<td class="description">'.__("Pay by Mobile Phone","dp-lang").'</td>
								</tr>';
					break;

                default:
                    break;

            }
        }
        $output .= '</table>';
    }
    else {
        $output .= __('Make payment using ',"dp-lang");
        foreach ($dp_shopping_cart_settings['dp_po'] as $payment_option) {
            switch ($payment_option) {
                case 'paypal':
                    $output .= __('PayPal',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="paypal" />';
                    break;

                case 'authorize':
                    $output .= __('Authorize.net',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="authorize" />';
                    break;

                case 'worldpay':
                    $output .= __('WorldPay',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="worldpay" />';
                    break;

                case 'alertpay':
                    $output .= __('AlertPay',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="alertpay" />';
                    break;

                case 'bank':
                    $output .= __('Bank transfer in advance',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="bank" />';
                    break;

                case 'cash':
                    $output .= __('Cash at store',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="cash" />';
                    break;

				case 'mobile':
                    $output .= __('Pay by Mobile Phone',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="mobile" />';
                    break;

                case 'delivery':
                    $output .= __('Cash on delivery',"dp-lang") . '<input type="hidden" id="dpsc_po_hidden" name="dpsc_po" value="delivery" />';
                    break;

                default:
                    break;

            }
        }
    }
    list($dpsc_total, $dpsc_shipping_weight, $products, $number_of_items_in_cart) = dpsc_pnj_calculate_cart_price();
    $dpsc_shipping_value = dpsc_pnj_calculate_shipping_price($dpsc_shipping_weight, $dpsc_total, $number_of_items_in_cart);
    $disabled_button = '';
    if (!is_numeric($dpsc_shipping_value) && $dpsc_shipping_weight != 0) {
        $disabled_button = 'disabled="disabled"';
    }
    $output .= ' <input type="submit" ' . $disabled_button . ' id="dpsc_make_payment" value="' . __('Make Payment') . '" />';
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
    $phone = $_POST['b_phone'];
    if (isset($_POST['s_fname'])) {
        $sfname = $_POST['s_fname'];
    }
    else {
        $sfname = $bfname;
    }
    if (isset($_POST['s_lname'])) {
        $slname = $_POST['s_lname'];
    }
    else {
        $slname = $blname;
    }
    if (isset($_POST['s_country'])) {
        $scountry = $_POST['s_country'];
    }
    else {
        $scountry = $bcountry;
    }
    if (isset($_POST['s_address'])) {
        $saddress = $_POST['s_address'];
    }
    else {
        $saddress = $baddress;
    }
    if (isset($_POST['s_city'])) {
        $scity = $_POST['s_city'];
    }
    else {
        $scity = $bcity;
    }
    if (isset($_POST['s_state'])) {
        $sstate = $_POST['s_state'];
    }
    else {
        $sstate = $bstate;
    }
    if (isset($_POST['s_zip'])) {
        $szip = $_POST['s_zip'];
    }
    else {
        $szip = $bzip;
    }

    $products = serialize($products);
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $tax = $dp_shopping_cart_settings['tax'];
    if (!$tax) {
        $tax = 0;
    }
    if (!$dpsc_shipping_value || $dpsc_shipping_value === 'ship_pro') {
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
    `billing_address`, `billing_city`, `billing_state`, `billing_zipcode`, `billing_email`, `phone`, `shipping_first_name`, `shipping_last_name`,
    `shipping_country`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zipcode`, `products`, `payment_option`, `discount`,
    `tax`, `shipping`, `total`, `payment_status`) VALUES ('{$invoice}', NOW(), {$order_time}, '{$bfname}', '{$blname}', '{$bcountry}', '{$baddress}',
    '{$bcity}', '{$bstate}', '{$bzip}', '{$bemail}', '{$phone}', '{$sfname}', '{$slname}', '{$scountry}', '{$saddress}', '{$scity}', '{$sstate}', '{$szip}',
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

                You can find details of the items ordered by going here: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=dukapress-shopping-cart-order-log&id=' . $invoice .' <br/><br/>

                Here are the details of the person who placed the order: <br/><br/>

                BILLING ADDRESS<br/>
                Name: ' . $bfname .' ' . $blname . '<br/>
                Address: ' . $baddress . '<br/>
                City: ' . $bcity . '<br/>
                Province/State: ' . $bstate . '<br/>
                Postal Code: ' . $bzip . '<br/>
                Country: ' . $bcountry . '<br/>
                Email: ' . $bemail . '<br/>
                Phone: ' . $phone . '<br/><br/>

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
    dpsc_pnj_send_mail($to, $to, $dp_shopping_cart_settings['shop_name'], $subject, $message);
    if ($dp_shopping_cart_settings['dp_shop_pdf_generation'] === 'checked') {
        make_pdf($invoice, $dpsc_discount_value, $tax, $dpsc_shipping_value, $dpsc_total, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $phone);
    }
    if ($dp_shopping_cart_settings['dp_shop_user_registration'] === 'checked') {
        require_once( ABSPATH . WPINC . '/registration.php');
        global $user_ID;
        if (empty($user_ID)) {
            $user_id = email_exists( $bemail );
        }
        else {
            $user_id = $user_ID;
        }

        if (!$user_id) {
            $user_pass = wp_generate_password();
            $user_id = wp_create_user( $bemail, $user_pass, $bemail );
            update_user_option( $user_id, 'default_password_nag', true, true );
            dp_new_user_notification( $user_id, $user_pass );
        }
        $user_invoice = get_user_meta($user_id, 'dp_user_invoice_number', TRUE);
        if ($user_invoice === '') {
            $user_invoice = array();
        }
        $user_invoice[] = $invoice;
        update_user_meta($user_id, 'dp_user_invoice_number', $user_invoice);
        update_user_meta($user_id, 'first_name', $bfname);
        update_user_meta($user_id, 'last_name', $blname);
        $user_info = array();
        $user_info['address'] = $baddress;
        $user_info['city'] = $bcity;
        $user_info['state'] = $bstate;
        $user_info['zip'] = $bzip;
        $user_info['country'] = $bcountry;
        $user_info['email'] = $bemail;
        $user_info['phone'] = $phone;
        $user_info['sfirst'] = $sfname;
        $user_info['slast'] = $slname;
        $user_info['saddress'] = $saddress;
        $user_info['scity'] = $scity;
        $user_info['sstate'] = $sstate;
        $user_info['szip'] = $szip;
        $user_info['scountry'] = $scountry;
        update_user_meta($user_id, 'dp_user_details', $user_info);
    }
    return array($invoice, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $bemail);
}

function dp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = new WP_User($user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
	$message .= wp_login_url() . "\r\n";

        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');

        $name = $dp_shopping_cart_settings['shop_name'];

        $from = get_option('admin_email');

	dpsc_pnj_send_mail($user_email, $from, $name, sprintf(__('[%s] Your username and password'), $blogname), $message);

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
            $dpsc_var_price = 0.0;
            $all_custom_fields = get_post_custom(intval($dpsc_product['item_number']));
            if (!empty($dpsc_product['var'])) {
                $dpsc_var = ' ('.$dpsc_product['var'].')';
                $get_vars = explode('||',$all_custom_fields['dropdown_option'][0]);
                $all_vars_in_product = array();
                foreach ($get_vars as $get_var) {
                    $pro_vars = explode('|', $get_var);
                    foreach ($pro_vars as $pro_var) {
                        $get_var = explode(';',$pro_var);
                        $var_price = floatval($get_var[1]);
                        $all_vars_in_product[$get_var[0]] = $var_price;
                    }
                }
                $dpsc_var_array = explode(', ', $dpsc_product['var']);
                if (is_array($dpsc_var_array)) {
                    foreach ($dpsc_var_array as $dpsc_check_var) {
                        $dpsc_var_price += $all_vars_in_product[$dpsc_check_var];
                    }
                }
            }
            if (is_numeric($all_custom_fields['new_price'][0])) {
                $product_price = $all_custom_fields['new_price'][0];
            }
            else {
                $product_price = $all_custom_fields['price'][0];
            }
            if (isset($all_custom_fields['item_weight'][0])) {
                $dpsc_product['item_weight'] = $all_custom_fields['item_weight'][0];
            }
//            var_dump($product_price, $dpsc_var_price, $product_price+$dpsc_var_price);die;
            $dpsc_total += floatval(($product_price+$dpsc_var_price)*$dpsc_product['quantity']);
            $dpsc_weight += $dpsc_product['item_weight']*$dpsc_product['quantity'];
            $product['id'] = $dpsc_product['item_number'];
            $product['name'] = $dpsc_product['name'].$dpsc_var;
            $product['price'] = $product_price+$dpsc_var_price;
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
                    $b[1] = $b[1] + 0.0001;
                } else {
                    $b[1] = $b[1] + 0.0001;
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

        case 'fedex':
            $shipping_price = 'fedex';
            break;

        case 'ship_pro':
            $shipping_price = 'ship_pro';
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
    global $dpsc_country_code_name;
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    if (is_user_logged_in () && $dp_shopping_cart_settings['dp_shop_user_registration'] === 'checked') {
        global $current_user;
        $first_name = $current_user->first_name;
        $last_name = $current_user->last_name;
        $email = $current_user->user_email;
        $user_info = get_user_meta($current_user->ID, 'dp_user_details', TRUE);
    }
    $output = '<div id="dpsc_contact_information">';
    $output .= '<div id="dpsc_billing_details">';
    $output .= '<h4>' . __('Billing Address',"dp-lang") . '</h4>';
    $output .= '<label for="b_firstname">' . __('First Name',"dp-lang") . '</label>
                <input id="b_firstname" name="b_f_name" value="' . __($first_name, "dp-lang") . '" type="text" /><span class="dpsc_error_msg" id="firstNameError">' . __('Please enter the First Name',"dp-lang") . '</span><br />';
    $output .= '<label for="b_lastname">' . __('Last Name',"dp-lang") . '</label>
                <input id="b_lastname" name="b_l_name" value="' . __($last_name, "dp-lang") . '" type="text" /><span class="dpsc_error_msg" id="lastNameError">' . __('Please enter the Last Name',"dp-lang") . '</span><br />';
    $output .= '<label for="b_address">' . __('Address',"dp-lang") . '</label>
                <input type="text" id="b_address" name="b_address" value="' . __($user_info['address'], "dp-lang") . '" /><span class="dpsc_error_msg" id="addressError">' . __('Please enter the Address',"dp-lang") . '</span><br />';
    $output .= '<label for="b_city">' . __('City',"dp-lang") . '</label>
                <input type="text" id="b_city" name="b_city" value="' . __($user_info['city'], "dp-lang") . '" /><span class="dpsc_error_msg" id="cityError">' . __('Please enter the City',"dp-lang","dp-lang") . '</span><br />';
    $output .= '<label for="b_state">' . __('Province / State',"dp-lang") . '</label>
                <input type="text" id="b_state" name="b_state" value="' . __($user_info['state'], "dp-lang") . '" /><span class="dpsc_error_msg" id="stateError">' . __('Please enter the State',"dp-lang") . '</span><br />';
    $output .= '<label for="b_zipcode">' . __('Postal Code',"dp-lang") . '</label>
                <input type="text" id="b_zipcode" name="b_zipcode" value="' . __($user_info['zip'], "dp-lang") . '" /><span class="dpsc_error_msg" id="postelError">' . __('Please enter the Postal Code',"dp-lang") . '</span><br />';
    $output .= '<label for="b_country">' . __('Country',"dp-lang") . '</label>
                <select name="b_country" id="b_country">';
    foreach ($dpsc_country_code_name as $country_code => $country_name) {
        $selected = '';
        if ($country_code === $user_info['country']) {
            $selected = 'selected="selected"';
        }
        $output .= '<option ' . $selected . ' value="' . $country_code . '" >' . __($country_name,"dp-lang") . '</option>';
    }
    $output .= '</select><br />';
    $output .= '<label for="b_email">' . __('Email',"dp-lang") . '</label>
                <input type="text" id="b_email" name="b_email" value="' . __($email, "dp-lang") . '" /><span class="dpsc_error_msg" id="emailError">' . __('Please enter the Email',"dp-lang") . '</span><br />';
    $output .= '<label for="b_phone">' . __('Phone Number',"dp-lang") . '</label>
                <input type="text" id="b_phone" name="b_phone" value="' . __($user_info['phone'], "dp-lang") . '" /><span class="dpsc_error_msg" id="phoneError">' . __('Please enter the Phone',"dp-lang") . '</span><br />';
    $output .= '</div>';
    $output .= '<div id="dpsc_shipping_details" style="display: none">';
    $output .= '<h4>' . __('Shipping Address',"dp-lang") . '</h4>';
    $output .= '<label for="s_firstname">' . __('First Name',"dp-lang") . '</label>
                <input id="s_firstname" name="s_f_name" value="' . __($user_info['sfirst'], "dp-lang") . '" type="text" /><span class="dpsc_error_msg" id="shipFNameError">' . __('Please enter the First Name',"dp-lang") . '</span><br />';
    $output .= '<label for="s_lastname">' . __('Last Name',"dp-lang") . '</label>
                <input id="s_lastname" name="s_l_name" value="' . __($user_info['slast'], "dp-lang") . '" type="text" /><span class="dpsc_error_msg" id="shipLNameError">' . __('Please enter the Last Name',"dp-lang") . '</span><br />';
    $output .= '<label for="s_address">' . __('Address',"dp-lang") . '</label>
                <input type="text" id="s_address" name="s_address" value="' . __($user_info['saddress'], "dp-lang") . '" /><span class="dpsc_error_msg"  id="shipAddressError">' . __('Please enter the Address',"dp-lang") . '</span><br />';
    $output .= '<label for="s_city">' . __('City',"dp-lang") . '</label>
                <input type="text" id="s_city" name="s_city" value="' . __($user_info['scity'], "dp-lang") . '" /><span class="dpsc_error_msg" id="shipCityError">' . __('Please enter the City',"dp-lang") . '</span><br />';
    $output .= '<label for="s_state">' . __('Province / State',"dp-lang") . '</label>
                <input type="text" id="s_state" name="s_state" value="' . __($user_info['sstate'], "dp-lang") . '" /><span class="dpsc_error_msg" id="shipStateError">' . __('Please enter the State',"dp-lang") . '</span><br />';
    $output .= '<label for="s_zipcode">' . __('Postal Code',"dp-lang") . '</label>
                <input type="text" id="s_zipcode" name="s_zipcode" value="' . __($user_info['szip'], "dp-lang") . '" /><span class="dpsc_error_msg" id="shipPostalError">' . __('Please enter the Postal Code',"dp-lang") . '</span><br />';
    $output .= '<label for="s_country">' . __('Country',"dp-lang") . '</label>
                <select name="s_country" id="s_country">';
    foreach ($dpsc_country_code_name as $country_code => $country_name) {
        $selected = '';
        if ($country_code === $user_info['scountry']) {
            $selected = 'selected="selected"';
        }
        $output .= '<option ' . $selected . ' value="' . $country_code . '" >' . __($country_name,"dp-lang") . '</option>';
    }
    $output .= '</select><br />';
    $output .= '</div>';
    $output .= '<input type="checkbox" name="dpsc_contact_different_ship_address" id="dpsc_contact_different_ship_address" value="checked">&nbsp;' . __('I have a different Shipping Address.');
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
                                    <th>' . __('Sr No.',"dp-lang") . '</th>
                                    <th>' . __('Product Name',"dp-lang") . '</th>
                                    <th>' . __('Quantity',"dp-lang") . '</th>
                                </tr>';
        $inq_count = 1;
        if (is_array($products)) {
            foreach ($products as $product) {
                $message_content .= '<tr>
                                        <td>' . __($inq_count,"dp-lang") . '</td>
                                        <td>' . __($product['name'],"dp-lang") . '</td>
                                        <td>' . __($product['quantity'],"dp-lang") . '</td>
                                    </tr>';
                $inq_count++;
            }
        }
        $message_content .= '</table>';
        $final_msg = 'From: ' . $from_name . '(' . $from_email . ')<br/>Subject:' . $subject . '<br/>' . $message . '<br/>' . $message_content;
        $to = get_option('admin_email');
        dpsc_pnj_send_mail($to, $to, __('Inquiry Form Submitted',"dp-lang"), $subject, $final_msg);
        $output = '<h3>' . __('Thank you for submitting our Inquiry form.',"dp-lang") . '</h3><p>' . __('We will contact you soon.',"dp-lang") . '</p>';
        return $output;
    }
    if (!$status) {
        $output = '<h2>' . __('Thank you for your order!',"dp-lang") . '</h2>';
        $query = "SELECT * FROM {$table_name} WHERE `invoice`='{$invoice}'";
        $result = $wpdb->get_row($query);
//        var_dump($query,$result);
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
                    $output .= '<h4>' . __('Please keep',"dp-lang") . ' <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ' . __('ready for payment upon delivery.',"dp-lang") . '</h4>';
                    break;


                case 'Cash at store':
                    $output .= '<h4>' . __('Please keep',"dp-lang") . ' <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ' . __('ready for payment when you come to take your order.',"dp-lang") . '</h4>';
                    break;


                case 'Bank Transfer':
                    $output .= '<h4>' . __('Please transfer',"dp-lang") . ' <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ' . __('to our Bank Account using the following information:',"dp-lang") . '</h4>
                                <table>
                                    <tr>
                                        <td>' . __('Name of Recipent:',"dp-lang") . '</td><td>' . __($dp_shopping_cart_settings['bank_account_owner']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __('for:</td><td>Order No.:',"dp-lang") . ' ' . __($invoice) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __('Name of Bank:',"dp-lang") . '</td><td>' . __($dp_shopping_cart_settings['bank_name']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __('Routing Number:',"dp-lang") . '</td><td>' . __($dp_shopping_cart_settings['bank_routing']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __('Account Number:',"dp-lang") . '</td><td>' . __($dp_shopping_cart_settings['bank_account']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __('IBAN:',"dp-lang") . '</td><td>' . __($dp_shopping_cart_settings['bank_IBAN']) . '</td>
                                    </tr>
                                    <tr>
                                        <td>' . __('BIC/SWIFT:',"dp-lang") . '</td><td>' . __($dp_shopping_cart_settings['bank_bic']) . '</td>
                                    </tr>
                                 </table>
                                 <p>' . __('When we have received your payment in our account, we will begin to Process your Order.') . '</p>';
                    break;

                    case 'Mobile Payment':
                        $output .= '<h4>' . __('Please send',"dp-lang") . ' <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ' . __('to any of the following numbers:',"dp-lang") . '</h4>
                                    <table>';

                        if(is_array($dp_shopping_cart_settings['mobile_names'])) {
                            $count_mp = count($dp_shopping_cart_settings['mobile_names']);
                            for($mp_i = 0; $mp_i < $count_mp; $mp_i++) {
                                $output .= '<tr>
                                                <td>' . $dp_shopping_cart_settings['mobile_names'][$mp_i] . ' :</td><td>' . $dp_shopping_cart_settings['mobile_number'][$mp_i] . '</td>
                                            </tr>';
                            }
                        }

                        $output .= '</table>
                                     <p>' . __('Please also send your invoice number to us by SMS using the phone that you used to send the money. When we have received your payment in any of our accounts, we shall begin to Process your Order.',"dp-lang") . '</p>';
                    break;

                    default:
                        $output .= '<h4>' . __('Thank you for making the payment of',"dp-lang") . ' <span id="dpsc_payment_amount">' . $dp_shopping_cart_settings['dp_currency_symbol'] . $amount . '</span> ' . __('using',"dp-lang") . ' ' . $result->payment_option . '.</h4>
                                    <p>' . __('We will process your order soon.',"dp-lang") . '</p>';
                        break;
            }
            if ($dp_shopping_cart_settings['dp_shop_pdf_generation'] === 'checked') {
                $output .= '<p><a href="' . DP_PLUGIN_URL .'/pdf/invoice_' . $invoice . '.pdf">Click here to download your Invoice.</a></p>';
            }
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
        $output = __('Order canceled !!',"dp-lang");
        return $output;
    }
}

add_shortcode('dp_order_log', 'dp_current_user_order_log');
function dp_current_user_order_log($content = NULL) {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    if (is_user_logged_in () && $dp_shopping_cart_settings['dp_shop_user_registration'] === 'checked') {
        global $user_ID, $wpdb;
        $invoices = get_user_meta($user_ID, 'dp_user_invoice_number', TRUE);
        if (is_array($invoices)) {
            $pagenum = isset($_GET['page']) ? $_GET['page'] : 1;
            $per_page = 10;
            $action_count = count($invoices);
            $page_links = paginate_links( array(
                    'base' => add_query_arg( 'page', '%#%' ),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => ceil($action_count / $per_page),
                    'current' => $pagenum
            ));
            $action_offset = ($pagenum-1) * $per_page;
            $output = '';
            $invoices = array_slice(array_reverse($invoices), $action_offset, $per_page);
            $invoices = implode(',', $invoices);
            $table_name = $wpdb->prefix . "dpsc_transactions";
            $order_sql = "SELECT * FROM {$table_name} WHERE `invoice` IN ({$invoices}) ORDER BY `id` DESC";
            $order_results = $wpdb->get_results($order_sql);
            if (is_array($order_results)) {
                $output .= '<div id="order_log">';
                foreach ($order_results as $order) {
                    $output .= '<h3><a href="#">Order Number: '. $order->invoice . '</a></h3>';
                    $output .= '<div>';
                    ///////////////////////
                    $output .= '<p>Date: ' . mysql2date( 'd M Y', $order->date, false ) . '
                                <p>Mode of Payment: ' . $order->payment_option . '</p>
                                <p>Payment Status: ' . $order->payment_status . '</p>
                                <table class="widefat post fixed">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Product Name</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                    $count = 1;
                    $products = $order->products;
                    $products = unserialize($products);
                    foreach ($products as $product) {
                        $output .= '<tr>
                                        <td>' . $count . '</td>
                                        <td>' . $product['name'] . '</td>
                                        <td>' . $product['price'] . '</td>
                                        <td>' . $product['quantity'] . '</td>
                                        <td>' . $product['price']*$product['quantity'] . '</td>
                                    </tr>';
                        $count++;
                    }
                    $output .= '</tbody>
                            </table>';
                    $total = $order->total;
                    $shipping = $order->shipping;
                    $discount = $order->discount;
                    $tax= $order->tax;
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
                    $output .= '<table>
                                    <tr>
                                        <td>Sub-Total: </td><td>' . number_format($total,2) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Shipping: </td><td>+' . number_format($shipping,2) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Discount: </td><td>-' . number_format($total_discount,2) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Tax: </td><td>+' . number_format($total_tax,2) . '</td>
                                    </tr>
                                    <tr>
                                        <td>Total: </td><td>+' . $amount . '</td>
                                    </tr>
                                </table>';
                    if ($dp_shopping_cart_settings['dp_shop_pdf_generation'] === 'checked') {
                        $output .= '<p><a href="' . DP_PLUGIN_URL . '/pdf/invoice_' . $order->invoice . '.pdf">Click here to download your Invoice.</a></p>';
                    }
                    ///////////////////////
                    $output .= '</div>';
                }
                $output .= '</div>';
                if ($page_links) {
                    $output .= $page_links;
                }
            }
            $content .= $output;
        }
        else {
            $content .= 'No order logs found!!!';
        }
    }
    else {
        $content .= 'Please login to view the order logs.';
    }
    return $content;
}

?>