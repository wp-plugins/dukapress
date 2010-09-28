<?php
/*
Plugin Name: DukaPress Shopping Cart
Description: DukaPress Shopping Cart
Version: 1.3.2.1
Author: Parshwa Nemi Jain and Nickel Pro
Author URI: http://dukapress.org/
Plugin URI: http://dukapress.org/
*/

$dp_version = 1.22;

require_once('php/dp-products.php');
require_once('php/dp-cart.php');
require_once('php/dp-widgets.php');
require_once('php/dp-payment.php');
require_once('lib/currency_convertor.php');

session_start();
define('DP_PLUGIN_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
define('DP_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)));
define('DP_DOWNLOAD_FILES_DIR', WP_CONTENT_DIR. '/uploads/dpsc_download_files/' );
define('DP_DOWNLOAD_FILES_DIR_TEMP', WP_CONTENT_DIR. '/uploads/dpsc_temp_download_files/' );

/**
 * This function shows Transaction Widget on Dashboard
 */
add_action('wp_dashboard_setup', 'dp_show_paid_transaction', 1);
function dp_show_paid_transaction() {
    wp_add_dashboard_widget( 'dp_dashboard_widget_test', __( 'DukaPress Transactions' ), 'dp_dashboard_transactions' );
}

function dp_dashboard_transactions() {
    global $wpdb;
    $table_name = $wpdb->prefix . "dpsc_transactions";
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $query = "SELECT `total`, `shipping`, `tax`, `discount` FROM {$table_name} WHERE `payment_status`='Paid'";
    $results = $wpdb->get_results($query);
    $all_total = 0.00;
    $count = 0;
    foreach ($results as $result) {
        $total = $result->total;
        $shipping = $result->shipping;
        $discount = $result->discount;
        $tax= $result->tax;
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
        $amount = $total+$shipping+$total_tax-$total_discount;
        $all_total += $amount;
        $count++;
    }
    echo 'Total ' . $count . ' orders sold with total amount of ' .$dp_shopping_cart_settings['dp_currency_symbol'] . number_format($all_total,2);
}

/**
 * This function creates Admin Menu.
 *
 */
add_action('admin_menu', 'dp_pnj_create_admin_menu');
function dp_pnj_create_admin_menu() {
    add_object_page('DukaPress', 'DukaPress', 'manage_options', 'dukapress-shopping-cart-order-log', '', DP_PLUGIN_URL . '/images/dp_icon.png');
    add_submenu_page('dukapress-shopping-cart-order-log', 'DukaPress Order Log', 'Order Log', 'manage_options', 'dukapress-shopping-cart-order-log', 'dukapress_shopping_cart_order_log');
    add_submenu_page('dukapress-shopping-cart-order-log', 'DukaPress Settings', 'Settings', 'manage_options', 'dukapress-shopping-cart-settings', 'dukapress_shopping_cart_setting');
}


/**
 * This part handles the CSS and JS
 *
 */
if (is_admin()) {
    wp_enqueue_style('dpsc_admin_css', DP_PLUGIN_URL.'/css/dp-admin.css');
    if ($_REQUEST['page'] === 'dukapress-shopping-cart-settings') {
        wp_enqueue_script('dp_jquery_ui_js', DP_PLUGIN_URL . '/js/jquery-ui-1.8.4.custom.min.js', array('jquery'));
    }
    wp_enqueue_style('dp_acc_style', DP_PLUGIN_URL . '/css/style.css');
    wp_enqueue_script('dpsc_admin_js', DP_PLUGIN_URL . '/js/dukapress-admin.js', array('jquery'));
}
else {
    wp_enqueue_style('dpsc_basic_css', DP_PLUGIN_URL.'/css/dpsc-basic.css');
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $image_effect = $dp_shopping_cart_settings['image_effect'];
    switch ($image_effect) {
        case 'mz_effect':
            wp_enqueue_script('dpsc_magiczoom', DP_PLUGIN_URL . '/js/magiczoom.js', array('jquery'));
            break;

        case 'mzp_effect':
            wp_enqueue_script('dpsc_magiczoomplus', DP_PLUGIN_URL . '/js/magiczoomplus.js', array('jquery'));
            break;
            break;

        case 'lightbox':
            wp_enqueue_style('jquery.fancybox', DP_PLUGIN_URL .'/js/jquery.fancybox/jquery.fancybox.css', false, '1.0', 'screen');
            wp_enqueue_script('dpsc_lightbox', DP_PLUGIN_URL . '/js/jquery.fancybox/jquery.fancybox-1.2.1.pack.js', array('jquery'));
            wp_enqueue_script('dpsc_lightbox_call', DP_PLUGIN_URL . '/js/lightbox.js', array('jquery', 'dpsc_lightbox'));
            break;

        case 'no_effect':
            break;

        case 'jqzoom_effect':
            wp_enqueue_style('dpsc_jqzoom', DP_PLUGIN_URL .'/css/jqzoom.css', false, '1.0', 'screen');
            wp_enqueue_script('dpsc_jqzoom', DP_PLUGIN_URL . '/js/jqzoom.pack.1.0.1.js', array('jquery'));
            break;

        default:
            break;
    }
    $tim_url = DP_PLUGIN_URL . '/lib/timthumb.php?src=';
    $tim_end = '&w=310&h=383&zc=1';
    $dpsc_site_url = get_bloginfo('url');
    wp_enqueue_script('dpsc_js_file', DP_PLUGIN_URL . '/js/dukapress.js', array('jquery'));
    wp_localize_script( 'dpsc_js_file', 'dpsc_js', array( 'tim_url' => $tim_url, 'tim_end' => $tim_end, 'dpsc_url' => $dpsc_site_url, 'width' => $dp_shopping_cart_settings['m_w'], 'height' => $dp_shopping_cart_settings['m_h']) );
    wp_enqueue_script('dpsc_livequery',DP_PLUGIN_URL.'/js/jquery.livequery.js',array('jquery'));
}

/**
 * This function displays Order Log
 *
 */
function dukapress_shopping_cart_order_log() {
    global $wpdb;
    echo '<h2>DukaPress Shop Order Log</h2>';
    $table_name = $wpdb->prefix . "dpsc_transactions";
    if (!isset($_GET['id'])) {
        $query = "SELECT * FROM {$table_name} ORDER BY `id` DESC";
        $results = $wpdb->get_results($query);
        if (is_array($results) && count($results) > 0) {
            ?>
            <table class="widefat post fixed">
                <thead>
                    <tr>
                        <th></th>
                        <th>Invoice Number</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Mode of Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $count = 1;
                foreach ($results as $result) {
                    ?>
                    <tr>
                        <td><?php echo $count;?></td>
                        <td><a href="?page=dukapress-shopping-cart-order-log&id=<?php echo $result->id; ?>"><?php echo $result->invoice;?></a></td>
                        <td><?php echo $result->billing_first_name . ' ' . $result->billing_last_name;?></td>
                        <td><?php echo $result->date;?></td>
                        <td><?php
                        $total = $result->total;
                        $shipping = $result->shipping;
                        $discount = $result->discount;
                        $tax= $result->tax;
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
                                echo $amount;
                        ?></td>
                        <td><?php echo $result->payment_option;?></td>
                        <td id="dpsc_order_status_<?php echo $result->id; ?>"><input type="submit" value="<?php echo $result->payment_status;?>" onclick="dpsc_pnj_change_status('<?php echo $result->payment_status; ?>', <?php echo $result->id; ?>)" /></td>
                    </tr>
                    <?php
                    $count++;
                }
                ?>
                </tbody>
            </table>
    <script type="text/javascript">
    function dpsc_pnj_change_status(current_status, order_id) {
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: 'action=dpsc_change_order_status&id=' + order_id + '&current_status=' + current_status,
            success:function(msg){
                jQuery('td#dpsc_order_status_'+order_id).html(msg);
            }
        });
    }
    </script>
            <?php
        }
        else {
            echo 'No records found!';
        }
    }
    else {
        $order_id = intval($_GET['id']);
        $query = "SELECT * FROM {$table_name} WHERE `id`={$order_id}";
        $result = $wpdb->get_row($query);
        if ($result) {
            if (isset($_GET['status']) && $_GET['status'] === 'send') {
                $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
                $message = '';
                if ($result->payment_status === 'Paid') {
                    $digital_message = '';
                    $is_digital = dpsc_pnj_is_digital_present($result->products);
                    if ($is_digital) {
                        $file_names = dpsc_pnj_get_download_links($is_digital);
                        if ($file_names) {
                            if (is_array($file_names) && count($file_names) > 0) {
                                $digital_message .= '<br/>Your download links:<br/><ul>';
                                foreach ($file_names as $file_name) {
                                    $file_name = explode('@_@||@_@', $file_name);
                                    $temp_name = $file_name[0];
                                    $real_name = $file_name[1];
                                    $digital_message .= '<li><a href="' . DP_PLUGIN_URL . '/download.php?id=' . $temp_name . '">' . $real_name . '</a></li>';
                                }
                                $digital_message .= '</ul><br/>';
                            }
                        }
                    }
                    $message = 'Hi ' . $result->billing_first_name .',<br/>
                                We have received the payment for Invoice No.: '. $result->invoice . '.<br/>
                                We will start processing your order soon.<br/>' . $digital_message . '
                                Thanks,<br/>
                                '. $dp_shopping_cart_settings['shop_name'];

                    $subject = 'Payment Received For Invoice No: ' . $result->invoice;
                }
                elseif ($result->payment_status === 'Canceled') {
                    $subject = 'Payment Canceled For Invoice No.:' . $result->invoice;
                    $message = 'Hi ' . $result->billing_first_name .',<br/>
                                The payment for Invoice No.: '. $result->invoice . ' was canceled. Kindly make the payment, so that we can proceed with the order.<br/>
                                <br/>
                                Thanks,<br/>
                                '. $dp_shopping_cart_settings['shop_name'];
                }
                else {
                    $subject = 'Payment Pending For Invoice No.:' . $result->invoice;
                    $message = 'Hi ' . $result->billing_first_name .',<br/>
                                The payment for Invoice No.: '. $result->invoice . ' is still pending. Kindly make the payment, so that we can proceed with the order.<br/>
                                <br/>
                                Thanks,<br/>
                                '. $dp_shopping_cart_settings['shop_name'];
                }
                $to = $result->billing_email;
                $from = get_option('admin_email');
                dpsc_pnj_send_mail($to, $from, $dp_shopping_cart_settings['shop_name'], $subject, $message);
                dpsc_pnj_send_mail($from, $to, $dp_shopping_cart_settings['shop_name'], $subject, $subject);
            }
            ?>
<h3>Transaction Details for Invoice No. <?php echo $result->invoice;?></h3>
<p>Mode of Payment: <?php echo $result->payment_option;?></p>
<p>Payment Status: <?php echo $result->payment_status;?></p>
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
                <tbody>
<?php
$count = 1;
$products = $result->products;
$products = unserialize($products);
foreach ($products as $product) {
    ?>
    <tr>
        <td><?php echo $count;?></td>
        <td><?php echo $product['name'];?></td>
        <td><?php echo $product['price'];?></td>
        <td><?php echo $product['quantity'];?></td>
        <td><?php echo $product['price']*$product['quantity'];?></td>
    </tr>
    <?php
    $count++;
}
?>
                </tbody>
            </table>
<?php
$total = $result->total;
$shipping = $result->shipping;
$discount = $result->discount;
$tax= $result->tax;
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
?>
<table>
    <tr>
        <td>Sub-Total: </td><td><?php echo number_format($total,2);?></td>
    </tr>
    <tr>
        <td>Shipping: </td><td>+<?php echo number_format($shipping,2);?></td>
    </tr>
    <tr>
        <td>Discount: </td><td>-<?php echo number_format($total_discount,2);?></td>
    </tr>
    <tr>
        <td>Tax: </td><td>+<?php echo number_format($total_tax,2);?></td>
    </tr>
    <tr>
        <td>Total: </td><td>+<?php echo $amount;?></td>
    </tr>
</table>
<p><a href="<?php echo DP_PLUGIN_URL .'/pdf/invoice_' . $result->invoice . '.pdf';?>">Click here to download your Invoice.</a></p>
<p><a href="?page=dukapress-shopping-cart-order-log&id=<?php echo $result->id; ?>&status=send">Send Payment Notification.</a></p>
            <?php
        }
    }
}

/**
 * This function changes the order status
 *
 */
add_action('wp_ajax_dpsc_change_order_status', 'dpsc_change_order_status');
function dpsc_change_order_status() {
    $order_id = intval($_POST['id']);
    $current_status = $_POST['current_status'];
    if ($order_id > 0) {
        if ($current_status === "Pending") {
            $updated_status = "Paid";
        }
        elseif ($current_status === "Paid"){
            $updated_status = "Canceled";
        }
        else {
            $updated_status = "Pending";
        }
        global $wpdb;
        $table_name = $wpdb->prefix . "dpsc_transactions";
        $query = "UPDATE {$table_name} SET `payment_status`='{$updated_status}' WHERE `id`={$order_id}";
        $wpdb->query($query);
        $updated_status1 = "'" . $updated_status . "'";
        $button_html = '<input type="submit" value="' . $updated_status . '" onclick="dpsc_pnj_change_status(' . $updated_status1 . ', ' . $order_id . ')" />';
        die($button_html);
    }
}

/**
 * This function handles all the settings of  DukaPress
 *
 */
function dukapress_shopping_cart_setting() {
    echo '<h2>DukaPress Shop Settings</h2>';
    if (isset($_POST['dp_submit'])) {
        $dp_mobile_name = $_POST['mobile_payment_name'];
        $dp_mobile_number = $_POST['mobile_payment_number'];
        $dp_shop_mode = $_POST['dp_shop_mode'];
        $dp_shop_country = $_POST['dp_shop_country'];
        $dp_shop_currency = $_POST['dp_shop_currency'];
        $dp_currency_code_enable = $_POST['dp_currency_code_enable'];
        $dp_currency_symbol = $_POST['dp_currency_symbol'];
        $dp_checkout_url = $_POST['dp_checkout_url'];
        $dp_thank_you_url = $_POST['dp_thank_you_url'];
        $dp_tax = $_POST['dp_tax'];
        $dp_shop_paypal_id = $_POST['dp_shop_paypal_id'];
        $dp_shop_paypal_pdt = $_POST['dp_shop_paypal_pdt'];
        $dp_shop_paypal_use_sandbox = $_POST['dp_shop_paypal_use_sandbox'];
        $dp_shop_dl_duration = $_POST['dp_shop_dl_duration'];
        $dp_shop_inventory_active = $_POST['dp_shop_inventory_active'];
        $dp_shop_inventory_stocks = $_POST['dp_shop_inventory_stocks'];
        $dp_shop_inventory_soldout = $_POST['dp_shop_inventory_soldout'];
        $dp_shop_inventory_warning = $_POST['dp_shop_inventory_warning'];
        $dp_po = $_POST['dp_po'];
        $dp_shipping_flat_rate = $_POST['dp_shipping_flat_rate'];
        $dp_shipping_flat_limit_rate = $_POST['dp_shipping_flat_limit_rate'];
        $dp_shipping_weight_flat_rate = $_POST['dp_shipping_weight_flat_rate'];
        $dp_shipping_weight_class_rate = $_POST['dp_shipping_weight_class_rate'];
        $dp_shipping_per_item_rate = $_POST['dp_shipping_per_item_rate'];
        $dp_shipping_calc_method = $_POST['dp_shipping_calc_method'];
        $authorize_api = $_POST['authorize_api'];
        $authorize_transaction_key = $_POST['authorize_transaction_key'];
        $authorize_url = $_POST['authorize_url'];
        $authorize_test_request = $_POST['authorize_test_request'];
        $alertpay_id = $_POST['alertpay_id'];
        $worldpay_id = $_POST['worldpay_id'];
        $worldpay_testmode = $_POST['worldpay_testmode'];
        $discount_enable = $_POST['discount_enable'];
        $bank_name = $_POST['bank_name'];
        $bank_routing = $_POST['bank_routing'];
        $bank_account = $_POST['bank_account'];
        $bank_account_owner = $_POST['bank_account_owner'];
        $bank_IBAN = $_POST['bank_IBAN'];
        $bank_bic = $_POST['bank_bic'];
        $safaricom_number = $_POST['safaricom_number'];
        $yu_number = $_POST['yu_number'];
        $zain_number = $_POST['zain_number'];
        $shop_name = $_POST['shop_name'];
        $shop_address = $_POST['shop_address'];
        $shop_state = $_POST['shop_state'];
        $shop_zip = $_POST['shop_zip'];
        $shop_city = $_POST['shop_city'];
        $image_effect = $_POST['image_effect'];
        $pp_c_code = $_POST['dp_paypal_currency'];
        $ap_c_code = $_POST['dp_alertpay_currency'];
        $wp_c_code = $_POST['dp_worldpay_currency'];
        $dp_main_image_width = !empty($_POST['dp_main_image_width']) ? $_POST['dp_main_image_width'] : '310';
        $dp_main_image_height = !empty($_POST['dp_main_image_height']) ? $_POST['dp_main_image_height'] : '383';
        $dp_thumb_image_width = !empty($_POST['dp_thumb_image_width']) ? $_POST['dp_thumb_image_width'] : '50';
        $dp_thumb_image_height = !empty($_POST['dp_thumb_image_height']) ? $_POST['dp_thumb_image_height'] : '63';
        $dp_thumb_grid_width = !empty($_POST['dp_thumb_grid_width']) ? $_POST['dp_thumb_grid_width'] : '160';
        $dp_thumb_grid_height = !empty($_POST['dp_thumb_grid_height']) ? $_POST['dp_thumb_grid_height'] : '120';
        do_action('dp_on_settings_saved');
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        if (!is_array($dp_shopping_cart_settings)) {
            $dp_shopping_cart_settings = array();
        }
        $dp_shopping_cart_settings['mobile_names'] = $dp_mobile_name;
        $dp_shopping_cart_settings['mobile_number'] = $dp_mobile_number;
        $dp_shopping_cart_settings['g_h'] = $dp_thumb_grid_height;
        $dp_shopping_cart_settings['g_w'] = $dp_thumb_grid_width;
        $dp_shopping_cart_settings['t_h'] = $dp_thumb_image_height;
        $dp_shopping_cart_settings['t_w'] = $dp_thumb_image_width;
        $dp_shopping_cart_settings['m_h'] = $dp_main_image_height;
        $dp_shopping_cart_settings['m_w'] = $dp_main_image_width;
        $dp_shopping_cart_settings['worldpay_currency'] = $wp_c_code;
        $dp_shopping_cart_settings['alertpay_currency'] = $ap_c_code;
        $dp_shopping_cart_settings['paypal_currency'] = $pp_c_code;
        $dp_shopping_cart_settings['image_effect'] = $image_effect;
        $dp_shopping_cart_settings['shop_city'] = $shop_city;
        $dp_shopping_cart_settings['shop_zip'] = $shop_zip;
        $dp_shopping_cart_settings['shop_state'] = $shop_state;
        $dp_shopping_cart_settings['shop_address'] = $shop_address;
        $dp_shopping_cart_settings['shop_name'] = $shop_name;
        $dp_shopping_cart_settings['bank_bic'] = $bank_bic;
        $dp_shopping_cart_settings['bank_IBAN'] = $bank_IBAN;
        $dp_shopping_cart_settings['bank_account_owner'] = $bank_account_owner;
        $dp_shopping_cart_settings['bank_account'] = $bank_account;
        $dp_shopping_cart_settings['bank_name'] = $bank_name;
        $dp_shopping_cart_settings['bank_routing'] = $bank_routing;
        $dp_shopping_cart_settings['safaricom_number'] = $safaricom_number;
        $dp_shopping_cart_settings['yu_number'] = $yu_number;
        $dp_shopping_cart_settings['zain_number'] = $zain_number;
        $dp_shopping_cart_settings['discount_enable'] = $discount_enable;
        $dp_shopping_cart_settings['alertpay_id'] = $alertpay_id;
        $dp_shopping_cart_settings['worldpay_id'] = $worldpay_id;
        $dp_shopping_cart_settings['worldpay_testmode'] = $worldpay_testmode;
        $dp_shopping_cart_settings['authorize_api'] = $authorize_api;
        $dp_shopping_cart_settings['authorize_transaction_key'] = $authorize_transaction_key;
        $dp_shopping_cart_settings['authorize_url'] = $authorize_url;
        $dp_shopping_cart_settings['authorize_test_request'] = $authorize_test_request;
        $dp_shopping_cart_settings['dp_shipping_per_item_rate'] = $dp_shipping_per_item_rate;
        $dp_shopping_cart_settings['dp_shipping_weight_class_rate'] = $dp_shipping_weight_class_rate;
        $dp_shopping_cart_settings['dp_shipping_weight_flat_rate'] = $dp_shipping_weight_flat_rate;
        $dp_shopping_cart_settings['dp_shipping_flat_limit_rate'] = $dp_shipping_flat_limit_rate;
        $dp_shopping_cart_settings['dp_shipping_flat_rate'] = $dp_shipping_flat_rate;
        $dp_shopping_cart_settings['dp_shipping_calc_method'] = $dp_shipping_calc_method;
        $dp_shopping_cart_settings['dp_po'] = $dp_po;
        $dp_shopping_cart_settings['dp_shop_mode'] = $dp_shop_mode;
        $dp_shopping_cart_settings['checkout'] = $dp_checkout_url;
        $dp_shopping_cart_settings['thank_you'] = $dp_thank_you_url;
        $dp_shopping_cart_settings['tax'] = $dp_tax;
        $dp_shopping_cart_settings['dp_shop_country'] = $dp_shop_country;
        $dp_shopping_cart_settings['dp_shop_currency'] = $dp_shop_currency;
        $dp_shopping_cart_settings['dp_currency_code_enable'] = $dp_currency_code_enable;
        $dp_shopping_cart_settings['dp_currency_symbol'] = $dp_currency_symbol;
        $dp_shopping_cart_settings['dp_shop_paypal_id'] = $dp_shop_paypal_id;
        $dp_shopping_cart_settings['dp_shop_paypal_pdt'] = $dp_shop_paypal_pdt;
        $dp_shopping_cart_settings['dp_shop_paypal_use_sandbox'] = $dp_shop_paypal_use_sandbox;
        $dp_shopping_cart_settings['dp_shop_inventory_active'] = $dp_shop_inventory_active;
        $dp_shopping_cart_settings['dp_shop_inventory_stocks'] = $dp_shop_inventory_stocks;
        $dp_shopping_cart_settings['dp_shop_inventory_soldout'] = $dp_shop_inventory_soldout;
        $dp_shopping_cart_settings['dp_shop_inventory_warning'] = $dp_shop_inventory_warning;
        update_option('dp_shopping_cart_settings', $dp_shopping_cart_settings);
        update_option('dp_dl_link_expiration_time', $dp_shop_dl_duration);
        echo '<h4>Settings Saved</h4>';
    }
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    if (!is_array($dp_shopping_cart_settings['dp_po'])) {
        $dp_shopping_cart_settings['dp_po'] = array();
    }
    $dp_digital_time = get_option('dp_dl_link_expiration_time');
    if (!is_numeric($dp_digital_time)) {
        $dp_digital_time = 48;
    }
    $paypal_supported_currency = array('AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD');
    $alertpay_supported_currency = array('AUD', 'BGN', 'CAD', 'CHF', 'CZK', 'DKK', 'EKK', 'EUR', 'GBP', 'HKD', 'HUF', 'INR', 'LTL', 'MYR', 'MKD', 'NOK', 'NZD', 'PLN', 'RON', 'SEK', 'SGD', 'USD', 'ZAR');
    $worldpay_supported_currency = array('ARS', 'AUD', 'BRL', 'CAD', 'CHF', 'CLP', 'CNY', 'COP', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'IDR', 'ISK', 'JPY', 'KES', 'KRW', 'MXP', 'MYR', 'NOK', 'NZD', 'PLN', 'PTE', 'SEK', 'SGD', 'SKK', 'THB', 'TWD', 'USD', 'VND', 'ZAR');
    $authorize_supported_currency = array('USD');
    ?>
<form action="" method="post" enctype="multipart/form-data">
<div id="dp_settings" class="dukapress-settings">

        <h3><a href="#">Basic Shop Settings</a></h3>
        <div>
            <div id="basic" class="tabdiv">
                <table class="form-table">
                    <tr>
                        <th scope="row">Name of Shop</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['shop_name'])) {echo $dp_shopping_cart_settings['shop_name'];}?>" name="shop_name">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Address</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['shop_address'])) {echo $dp_shopping_cart_settings['shop_address'];}?>" name="shop_address">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">State / Province</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['shop_state'])) {echo $dp_shopping_cart_settings['shop_state'];}?>" name="shop_state">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Postal Code</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['shop_zip'])) {echo $dp_shopping_cart_settings['shop_zip'];}?>" name="shop_zip">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">City / Town</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['shop_city'])) {echo $dp_shopping_cart_settings['shop_city'];}?>" name="shop_city">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Shop Mode</th>
                        <td>
                            <select name="dp_shop_mode">
                                <option value="regular" <?php if($dp_shopping_cart_settings['dp_shop_mode'] === 'regular') {echo 'selected';}?>>Regular Shop Mode</option>
                                <option value="inquiry" <?php if($dp_shopping_cart_settings['dp_shop_mode'] === 'inquiry') {echo 'selected';}?>>Inquiry Email Mode</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Country of your shop</th>
                        <td>
                            <select name="dp_shop_country" style="width: 240px;">
                                <?php
                                    if (isset($dp_shopping_cart_settings['dp_shop_country'])) {
                                        echo '<option value="'.$dp_shopping_cart_settings['dp_shop_country'].'">'.$dp_shopping_cart_settings['dp_shop_country'].'</option>';
                                    }
                                ?>
                                <option value="AF">AFGHANISTAN</option><option value="AL">ALBANIA</option><option value="DZ">ALGERIA</option><option value="AS">AMERICAN SAMOA</option><option value="AD">ANDORRA</option><option value="AO">ANGOLA</option><option value="AI">ANGUILLA</option><option value="AQ">ANTARCTICA</option><option value="AG">ANTIGUA AND BARBUDA</option><option value="AR">ARGENTINA</option><option value="AM">ARMENIA</option><option value="AW">ARUBA</option><option value="AU">AUSTRALIA</option><option value="AT">AUSTRIA</option><option value="AZ">AZERBAIJAN</option><option value="BS">BAHAMAS</option><option value="BH">BAHRAIN</option><option value="BD">BANGLADESH</option><option value="BB">BARBADOS</option><option value="BY">BELARUS</option><option value="BE">BELGIUM</option><option value="BZ">BELIZE</option><option value="BJ">BENIN</option><option value="BM">BERMUDA</option><option value="BT">BHUTAN</option><option value="BO">BOLIVIA</option><option value="BA">BOSNIA AND HERZEGOVINA</option><option value="BW">BOTSWANA</option><option value="BV">BOUVET ISLAND</option><option value="BR">BRAZIL</option><option value="IO">BRITISH INDIAN OCEAN TERRITORY</option><option value="BN">BRUNEI DARUSSALAM</option><option value="BG">BULGARIA</option><option value="BF">BURKINA FASO</option><option value="BI">BURUNDI</option><option value="KH">CAMBODIA</option><option value="CM">CAMEROON</option><option value="CA">CANADA</option><option value="CV">CAPE VERDE</option><option value="KY">CAYMAN ISLANDS</option><option value="CF">CENTRAL AFRICAN REPUBLIC</option><option value="TD">CHAD</option><option value="CL">CHILE</option><option value="CN">CHINA</option><option value="CX">CHRISTMAS ISLAND</option><option value="CC">COCOS (KEELING) ISLANDS</option><option value="CO">COLOMBIA</option><option value="KM">COMOROS</option><option value="CG">CONGO</option><option value="CD">CONGO, THE DEMOCRATIC REPUBLIC OF THE</option><option value="CK">COOK ISLANDS</option><option value="CR">COSTA RICA</option><option value="CI">COTE D?IVOIRE</option><option value="HR">CROATIA</option><option value="CU">CUBA</option><option value="CY">CYPRUS</option><option value="CZ">CZECH REPUBLIC</option><option value="DK">DENMARK</option><option value="DJ">DJIBOUTI</option><option value="DM">DOMINICA</option><option value="DO">DOMINICAN REPUBLIC</option><option value="EC">ECUADOR</option><option value="EG">EGYPT</option><option value="SV">EL SALVADOR</option><option value="GQ">EQUATORIAL GUINEA</option><option value="ER">ERITREA</option><option value="EE">ESTONIA</option><option value="ET">ETHIOPIA</option><option value="FK">FALKLAND ISLANDS (MALVINAS)</option><option value="FO">FAROE ISLANDS</option><option value="FJ">FIJI</option><option value="FI">FINLAND</option><option value="FR">FRANCE</option><option value="GF">FRENCH GUIANA</option><option value="PF">FRENCH POLYNESIA</option><option value="TF">FRENCH SOUTHERN TERRITORIES</option><option value="GA">GABON</option><option value="GM">GAMBIA</option><option value="GE">GEORGIA</option><option value="DE">GERMANY</option><option value="GH">GHANA</option><option value="GI">GIBRALTAR</option><option value="GR">GREECE</option><option value="GL">GREENLAND</option><option value="GD">GRENADA</option><option value="GP">GUADELOUPE</option><option value="GU">GUAM</option><option value="GT">GUATEMALA</option><option value="GG">GUERNSEY</option><option value="GN">GUINEA</option><option value="GW">GUINEA-BISSAU</option><option value="GY">GUYANA</option><option value="HT">HAITI</option><option value="HM">HEARD ISLAND AND MCDONALD ISLANDS</option><option value="VA">HOLY SEE (VATICAN CITY STATE)</option><option value="HN">HONDURAS</option><option value="HK">HONG KONG</option><option value="HU">HUNGARY</option><option value="IS">ICELAND</option><option value="IN">INDIA</option><option value="ID">INDONESIA</option><option value="IR">IRAN, ISLAMIC REPUBLIC OF</option><option value="IQ">IRAQ</option><option value="IE">IRELAND</option><option value="IM">ISLE OF MAN</option><option value="IL">ISRAEL</option><option value="IT">ITALY</option><option value="JM">JAMAICA</option><option value="JP">JAPAN</option><option value="JE">JERSEY</option><option value="JO">JORDAN</option><option value="KZ">KAZAKHSTAN</option><option value="KE">KENYA</option><option value="KI">KIRIBATI</option><option value="KP">KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF</option><option value="KR">KOREA, REPUBLIC OF</option><option value="KW">KUWAIT</option><option value="KG">KYRGYZSTAN</option><option value="LA">LAO PEOPLE'S DEMOCRATIC REPUBLIC</option><option value="LV">LATVIA</option><option value="LB">LEBANON</option><option value="LS">LESOTHO</option><option value="LR">LIBERIA</option><option value="LY">LIBYAN ARAB JAMAHIRIYA</option><option value="LI">LIECHTENSTEIN</option><option value="LT">LITHUANIA</option><option value="LU">LUXEMBOURG</option><option value="MO">MACAO</option><option value="MK">MACEDONIA, THE FORMER YUGOSLAV REPUBLIC OF</option><option value="MG">MADAGASCAR</option><option value="MW">MALAWI</option><option value="MY">MALAYSIA</option><option value="MV">MALDIVES</option><option value="ML">MALI</option><option value="MT">MALTA</option><option value="MH">MARSHALL ISLANDS</option><option value="MQ">MARTINIQUE</option><option value="MR">MAURITANIA</option><option value="MU">MAURITIUS</option><option value="YT">MAYOTTE</option><option value="MX">MEXICO</option><option value="FM">MICRONESIA, FEDERATED STATES OF</option><option value="MD">MOLDOVA, REPUBLIC OF</option><option value="MC">MONACO</option><option value="MN">MONGOLIA</option><option value="MS">MONTSERRAT</option><option value="MA">MOROCCO</option><option value="MZ">MOZAMBIQUE</option><option value="MM">MYANMAR</option><option value="NA">NAMIBIA</option><option value="NR">NAURU</option><option value="NP">NEPAL</option><option value="NL">NETHERLANDS</option><option value="AN">NETHERLANDS ANTILLES</option><option value="NC">NEW CALEDONIA</option><option value="NZ">NEW ZEALAND</option><option value="NI">NICARAGUA</option><option value="NE">NIGER</option><option value="NG">NIGERIA</option><option value="NU">NIUE</option><option value="NF">NORFOLK ISLAND</option><option value="MP">NORTHERN MARIANA ISLANDS</option><option value="NO">NORWAY</option><option value="OM">OMAN</option><option value="PK">PAKISTAN</option><option value="PW">PALAU</option><option value="PS">PALESTINIAN TERRITORY, OCCUPIED</option><option value="PA">PANAMA</option><option value="PG">PAPUA NEW GUINEA</option><option value="PY">PARAGUAY</option><option value="PE">PERU</option><option value="PH">PHILIPPINES</option><option value="PN">PITCAIRN</option><option value="PL">POLAND</option><option value="PT">PORTUGAL</option><option value="PR">PUERTO RICO</option><option value="QA">QATAR</option><option value="RE">REUNION</option><option value="RO">ROMANIA</option><option value="RU">RUSSIAN FEDERATION</option><option value="RW">RWANDA</option><option value="SH">SAINT HELENA</option><option value="KN">SAINT KITTS AND NEVIS</option><option value="LC">SAINT LUCIA</option><option value="PM">SAINT PIERRE AND MIQUELON</option><option value="VC">SAINT VINCENT AND THE GRENADINES</option><option value="WS">SAMOA</option><option value="SM">SAN MARINO</option><option value="ST">SAO TOME AND PRINCIPE</option><option value="SA">SAUDI ARABIA</option><option value="SN">SENEGAL</option><option value="CS">SERBIA AND MONTENEGRO</option><option value="SC">SEYCHELLES</option><option value="SL">SIERRA LEONE</option><option value="SG">SINGAPORE</option><option value="SK">SLOVAKIA</option><option value="SI">SLOVENIA</option><option value="SB">SOLOMON ISLANDS</option><option value="SO">SOMALIA</option><option value="ZA">SOUTH AFRICA</option><option value="GS">SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS</option><option value="ES">SPAIN</option><option value="LK">SRI LANKA</option><option value="SD">SUDAN</option><option value="SR">SURINAME</option><option value="SJ">SVALBARD AND JAN MAYEN</option><option value="SZ">SWAZILAND</option><option value="SE">SWEDEN</option><option value="CH">SWITZERLAND</option><option value="SY">SYRIAN ARAB REPUBLIC</option><option value="TW">TAIWAN, PROVINCE OF CHINA</option><option value="TJ">TAJIKISTAN</option><option value="TZ">TANZANIA, UNITED REPUBLIC OF</option><option value="TH">THAILAND</option><option value="TL">TIMOR-LESTE  </option><option value="TG">TOGO</option><option value="TK">TOKELAU</option><option value="TO">TONGA</option><option value="TT">TRINIDAD AND TOBAGO</option><option value="TN">TUNISIA</option><option value="TR">TURKEY</option><option value="TM">TURKMENISTAN</option><option value="TC">TURKS AND CAICOS ISLANDS</option><option value="TV">TUVALU</option><option value="UG">UGANDA</option><option value="UA">UKRAINE</option><option value="AE">UNITED ARAB EMIRATES</option><option value="GB">UNITED KINGDOM</option><option value="UM">UNITED STATES MINOR OUTLYING ISLANDS</option><option value="US">UNITED STATES OF AMERICA</option><option value="UY">URUGUAY</option><option value="UZ">UZBEKISTAN</option><option value="VU">VANUATU</option><option value="VE">VENEZUELA</option><option value="VN">VIET NAM</option><option value="VG">VIRGIN ISLANDS, BRITISH</option><option value="VI">VIRGIN ISLANDS, U.S.</option><option value="WF">WALLIS AND FUTUNA</option><option value="EH">WESTERN SAHARA</option><option value="YE">YEMEN</option><option value="ZM">ZAMBIA</option><option value="ZW">ZIMBABWE</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Currency</th>
                        <td>
                            <select name="dp_shop_currency">
                                <?php
                                $dpsc_currency_codes = array('AED','AFN','ALL','AMD','ANG','AOA','ARS','AUD','AWG','AZN','BAM','BBD','BDT','BGN','BHD','BIF','BMD','BND','BOB','BRL','BSD',
                                            'BTN','BWP','BYR','BZD','CAD','CDF','CHF','CLP','CNY','COP','CRC','CUP','CVE','CZK','DJF','DKK','DOP','DZD','EEK','EGP','ERN','ETB','EUR',
                                            'FJD','FKP','GBP','GEL','GGP','GHS','GIP','GMD','GNF','GTQ','GYD','HKD','HNL','HRK','HTG','HUF','IDR','ILS','IMP','INR','IQD','IRR','ISK',
                                            'JEP','JMD','JOD','JPY','KES','KGS','KHR','KMF','KPW','KRW','KWD','KYD','KZT','LAK','LBP','LKR','LRD','LSL','LTL','LVL','LYD','MAD','MDL',
                                            'MGA','MKD','MMK','MNT','MOP','MRO','MTL','MUR','MVR','MWK','MXN','MYR','MZN','NAD','NGN','NIO','NOK','NPR','NZD','OMR','PAB','PEN','PGK',
                                            'PHP','PKR','PLN','PYG','QAR','RON','RSD','RUB','RWF','SAR','SBD','SCR','SDG','SEK','SGD','SHP','SLL','SOS','SPL','SRD','STD','SVC','SYP',
                                            'SZL','THB','TJS','TMM','TND','TOP','TRY','TTD','TVD','TWD','TZS','UAH','UGX','USD','UYU','UZS','VEF','VND','VUV','WST','XAF','XAG','XAU',
                                            'XCD','XDR','XOF','XPD','XPF','XPT','YER','ZAR','ZMK','ZWD');
                                foreach ($dpsc_currency_codes as $dpsc_currency_code) {
                                    ?>
                                <option value="<?php echo $dpsc_currency_code;?>" <?php if ($dp_shopping_cart_settings['dp_shop_currency'] === $dpsc_currency_code) {echo 'selected="selected"';}?>><?php echo $dpsc_currency_code;?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Product Page Image Effect</th>
                        <td>
                            <select name="image_effect">
                                <option value="mz_effect" <?php if($dp_shopping_cart_settings['image_effect'] === 'mz_effect') {echo 'selected';}?>>Magic Zoom</option>
                                <option value="mzp_effect" <?php if($dp_shopping_cart_settings['image_effect'] === 'mzp_effect') {echo 'selected';}?>>Magic Zoom Plus</option>
                                <option value="jqzoom_effect" <?php if($dp_shopping_cart_settings['image_effect'] === 'jqzoom_effect') {echo 'selected';}?>>JQZoom</option>
                                <option value="lightbox" <?php if($dp_shopping_cart_settings['image_effect'] === 'lightbox') {echo 'selected';}?>>Lightbox</option>
                                <option value="no_effect" <?php if($dp_shopping_cart_settings['image_effect'] === 'no_effect') {echo 'selected';}?>>No Effect</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Main Product Image Size</th>
                        <td>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="dp_main_image_width">Width</label></th><td><input type="text" id="dp_main_image_width" name="dp_main_image_width" size="5" value="<?php echo $dp_shopping_cart_settings['m_w'];?>" /><i>px</i></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="dp_main_image_height">Height</label></th><td><input type="text" id="dp_main_image_height" name="dp_main_image_height" size="5" value="<?php echo $dp_shopping_cart_settings['m_h'];?>" /><i>px</i></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Product Thumbnail Size</th>
                        <td>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="dp_thumb_image_width">Width</label></th><td><input type="text" id="dp_thumb_image_width" name="dp_thumb_image_width" size="5" value="<?php echo $dp_shopping_cart_settings['t_w'];?>" /><i>px</i></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="dp_thumb_image_height">Height</label></th><td><input type="text" id="dp_thumb_image_height" name="dp_thumb_image_height" size="5" value="<?php echo $dp_shopping_cart_settings['t_h'];?>" /><i>px</i></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Grid Product Thumbnail Size</th>
                        <td>
                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="dp_thumb_grid_width">Width</label></th><td><input type="text" id="dp_thumb_grid_width" name="dp_thumb_grid_width" size="5" value="<?php echo $dp_shopping_cart_settings['g_w'];?>" /><i>px</i></td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="dp_thumb_grid_height">Height</label></th><td><input type="text" id="dp_thumb_grid_height" name="dp_thumb_grid_height" size="5" value="<?php echo $dp_shopping_cart_settings['g_h'];?>" /><i>px</i></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Currency Symbol</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['dp_currency_symbol'])) {echo $dp_shopping_cart_settings['dp_currency_symbol'];}?>" name="dp_currency_symbol">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Checkout Page URL</th>
                        <td>
                            <input size="50" type="text" value="<?php if(isset($dp_shopping_cart_settings['checkout'])) {echo $dp_shopping_cart_settings['checkout'];}?>" name="dp_checkout_url">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Thank You Page URL</th>
                        <td>
                            <input size="50" type="text" value="<?php if(isset($dp_shopping_cart_settings['thank_you'])) {echo $dp_shopping_cart_settings['thank_you'];}?>" name="dp_thank_you_url">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Tax</th>
                        <td>
                            <input type="text" value="<?php if(isset($dp_shopping_cart_settings['tax'])) {echo $dp_shopping_cart_settings['tax'];}?>" name="dp_tax">%
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Payment Options</th>
                        <td>
                            <input type="checkbox" name="dp_po[]" value="paypal" <?php if (in_array('paypal', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> PayPal <br />
                            <input type="checkbox" name="dp_po[]" value="authorize" <?php if (in_array('authorize', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> Authorize.net <br />
                            <input type="checkbox" name="dp_po[]" value="worldpay" <?php if (in_array('worldpay', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> WorldPay <br />
                            <input type="checkbox" name="dp_po[]" value="alertpay" <?php if (in_array('alertpay', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> AlertPay <br />
                            <input type="checkbox" name="dp_po[]" value="bank" <?php if (in_array('bank', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> Bank transfer in advance <br />
                            <input type="checkbox" name="dp_po[]" value="cash" <?php if (in_array('cash', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> Cash at store <br />
                            <input type="checkbox" name="dp_po[]" value="mobile" <?php if (in_array('mobile', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> Mobile Payment <br />
                            <input type="checkbox" name="dp_po[]" value="delivery" <?php if (in_array('delivery', $dp_shopping_cart_settings['dp_po'])) {echo "checked";} ?>/> Cash on delivery <br />
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <h3><a href="#">Product Management</a></h3>
        <div>
            <div id="product-management" class="tabdiv dukapress-settings">
                    <h3><a href="#">Inventory Settings</a></h3>
                    <div>
                        <div id="inventory" class="tabdiv">
                            <table class="form-table">
                                    <tr>
                                        <th scope="row">Active</th>
                                        <td>
                                            <select name="dp_shop_inventory_active">
                                                <option value="no" <?php if($dp_shopping_cart_settings['dp_shop_inventory_active'] === 'no') {echo 'selected';}?>>No</option>
                                                <option value="yes" <?php if($dp_shopping_cart_settings['dp_shop_inventory_active'] === 'yes') {echo 'selected';}?>>Yes</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Display Stocks Amounts</th>
                                        <td>
                                            <select name="dp_shop_inventory_stocks">
                                                <option value="no" <?php if($dp_shopping_cart_settings['dp_shop_inventory_stocks'] === 'no') {echo 'selected';}?>>No</option>
                                                <option value="yes" <?php if($dp_shopping_cart_settings['dp_shop_inventory_stocks'] === 'yes') {echo 'selected';}?>>Yes</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Sold Out Notice</th>
                                        <td>
                                            <select name="dp_shop_inventory_soldout">
                                                <option value="no" <?php if($dp_shopping_cart_settings['dp_shop_inventory_soldout'] === 'no') {echo 'selected';}?>>No</option>
                                                <option value="yes" <?php if($dp_shopping_cart_settings['dp_shop_inventory_soldout'] === 'yes') {echo 'selected';}?>>Yes</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Warning Threshold</th>
                                        <td>
                                            <select name="dp_shop_inventory_warning">
                                                <option value="no" <?php if($dp_shopping_cart_settings['dp_shop_inventory_warning'] === 'no') {echo 'selected';}?>>No</option>
                                                <option value="yes" <?php if($dp_shopping_cart_settings['dp_shop_inventory_warning'] === 'yes') {echo 'selected';}?>>Yes</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Inventory warning email</th>
                                        <td>
                                            <input type="text" value="<?php if (isset($dp_shopping_cart_settings['dp_shop_inventory_email'])) {echo $dp_shopping_cart_settings['dp_shop_inventory_email'];}?>" name="dp_shop_inventory_email"/>
                                        </td>
                                    </tr>
                                </table>
                        </div>
                    </div>
                    <h3><a href="#">Shipping Options</a></h3>
                    <div>
                        <div id="shipping" class="tabdiv">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Shipping calculation method</th>
                                    <td>
                                        <select name="dp_shipping_calc_method">
                                            <option value="free" <?php if ($dp_shopping_cart_settings['dp_shipping_calc_method'] === "free") {echo 'selected="selected"';}?>>Free</option>
                                            <option value="flat" <?php if ($dp_shopping_cart_settings['dp_shipping_calc_method'] === "flat") {echo 'selected="selected"';}?>>Flat</option>
                                            <option value="flat_limit" <?php if ($dp_shopping_cart_settings['dp_shipping_calc_method'] === "flat_limit") {echo 'selected="selected"';}?>>Flat Limit</option>
                                            <option value="weight_flat" <?php if ($dp_shopping_cart_settings['dp_shipping_calc_method'] === "weight_flat") {echo 'selected="selected"';}?>>Weight Flat</option>
                                            <option value="weight_class" <?php if ($dp_shopping_cart_settings['dp_shipping_calc_method'] === "weight_class") {echo 'selected="selected"';}?>>Weight Class</option>
                                            <option value="per_item" <?php if ($dp_shopping_cart_settings['dp_shipping_calc_method'] === "per_item") {echo 'selected="selected"';}?>>Per Item</option>
                                            <?php do_action('dp_shipping_dropdown_option');?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Flat Rate</th>
                                    <td>
                                        <input name="dp_shipping_flat_rate" value="<?php if(isset($dp_shopping_cart_settings['dp_shipping_flat_rate'])) {echo $dp_shopping_cart_settings['dp_shipping_flat_rate'];}?>"/>
                                    </td>

                                </tr>
                                <tr>
                                    <th scope="row">Flat Limit Rate</th>
                                    <td>
                                        <input name="dp_shipping_flat_limit_rate" value="<?php if(isset($dp_shopping_cart_settings['dp_shipping_flat_limit_rate'])) {echo $dp_shopping_cart_settings['dp_shipping_flat_limit_rate'];}?>"/>
                                    </td>

                                </tr>
                                <tr>
                                    <th scope="row">Weight Flat Rate</th>
                                    <td>
                                        <input name="dp_shipping_weight_flat_rate" value="<?php if(isset($dp_shopping_cart_settings['dp_shipping_weight_flat_rate'])) {echo $dp_shopping_cart_settings['dp_shipping_weight_flat_rate'];}?>"/>
                                    </td>

                                </tr>
                                <tr>
                                    <th scope="row">Weight Class Rate</th>
                                    <td>
                                        <input name="dp_shipping_weight_class_rate" value="<?php if(isset($dp_shopping_cart_settings['dp_shipping_weight_class_rate'])) {echo $dp_shopping_cart_settings['dp_shipping_weight_class_rate'];}?>"/>
                                    </td>

                                </tr>
                                <tr>
                                    <th scope="row">Per Item Rate</th>
                                    <td>
                                        <input name="dp_shipping_per_item_rate" value="<?php if(isset($dp_shopping_cart_settings['dp_shipping_per_item_rate'])) {echo $dp_shopping_cart_settings['dp_shipping_per_item_rate'];}?>"/>
                                    </td>

                                </tr>
                                <?php do_action('dp_shipping_field');?>
                            </table>
                        </div>
                    </div>
                    <h3><a href="#">Digital Products</a></h3>
                    <div>
                        <div id="digital" class="tabdiv">
                            <table class="form-table">
                                    <tr>
                                        <th scope="row">Download Links Duration:</th>
                                        <td>
                                            <input type="text" name="dp_shop_dl_duration" value="<?php echo $dp_digital_time;?>"/>
                                        </td>
                                    </tr>
                                </table>
                        </div>
                    </div>
            </div>
        </div>
        <h3><a href="#">Payment Options</a></h3>
        <div>
            <div id="po" class="tabdiv dukapress-settings">
                    <h3><a href="#">PayPal</a></h3>
                    <div>
                        <div id="paypal" class="tabdiv">
                            <table class="form-table">
                                    <tr>
                                        <th scope="row">Use PayPal Sandbox:</th>
                                        <td>
                                            <input type="checkbox" value="checked" name="dp_shop_paypal_use_sandbox" <?php echo $dp_shopping_cart_settings['dp_shop_paypal_use_sandbox']; ?>/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">PayPal ID</th>
                                        <td>
                                            <input name="dp_shop_paypal_id" value="<?php if(isset($dp_shopping_cart_settings['dp_shop_paypal_id'])) {echo $dp_shopping_cart_settings['dp_shop_paypal_id'];}?>"/>
                                        </td>

                                    </tr>
                                    <?php
                                    $paypal_currency_code = $dp_shopping_cart_settings['paypal_currency'];
                                    if(!$dp_shopping_cart_settings['dp_shop_currency']) {
                                        if ($paypal_currency_code === "" && in_array($dp_shopping_cart_settings['dp_shop_currency'], $paypal_supported_currency)) {
                                            ?>
                                            <input type="hidden" name="dp_paypal_currency" value="<?php echo $dp_shopping_cart_settings['dp_shop_currency']; ?>" />
                                            <?php
                                            $paypal_currency_code = $dp_shopping_cart_settings['dp_shop_currency'];
                                        }
                                    }
                                    if ($paypal_currency_code != $dp_shopping_cart_settings['dp_shop_currency']) {
                                        ?>
                                        <tr><td colspan="2">Your Shops' Currency Code is not compatible with PayPal. Please choose a Currency Code from the below list. Payments will be converted to the selected Currency Code, when Payments are sent to PayPal.</td></tr>
                                        <tr><th scope="row">PayPal Currency Code</th>
                                            <td>
                                                <select name="dp_paypal_currency">
                                                    <?php
                                                    foreach ($paypal_supported_currency as $p_code) {
                                                        $p_selected = '';
                                                        if ($paypal_currency_code === $p_code) {
                                                            $p_selected = 'selected="selected"';
                                                        }
                                                        ?>
                                                        <option value="<?php echo $p_code;?>" <?php echo $p_selected;?>><?php echo $p_code; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        <?php
                                    }
                                    ?>
                                </table>
                        </div>
                    </div>
                    <h3><a href="#">Authorize.net</a></h3>
                    <div>
                        <div id="authorize" class="tabdiv">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">API Login</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['authorize_api'])) {echo $dp_shopping_cart_settings['authorize_api'];}?>" name="authorize_api">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Transaction Key</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['authorize_transaction_key'])) {echo $dp_shopping_cart_settings['authorize_transaction_key'];}?>" name="authorize_transaction_key">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">URL</th>
                                    <td>
                                        <select name="authorize_url">
                                            <option value="live" <?php if($dp_shopping_cart_settings['authorize_url'] === "live") { echo 'selected="selected"';}?>>https://secure.authorize.net/gateway/transact.dll</option>
                                            <option value="test" <?php if($dp_shopping_cart_settings['authorize_url'] === "test") { echo 'selected="selected"';}?>>https://test.authorize.net/gateway/transact.dll</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Test-Request</th>
                                    <td>
                                        <select name="authorize_test_request">
                                            <option value="live" <?php if($dp_shopping_cart_settings['authorize_test_request'] === "live") { echo 'selected="selected"';}?>>False</option>
                                            <option value="test" <?php if($dp_shopping_cart_settings['authorize_test_request'] === "test") { echo 'selected="selected"';}?>>True</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <h3><a href="#">WorldPay</a></h3>
                    <div>
                        <div id="worldpay" class="tabdiv">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Installation-ID</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['worldpay_id'])) {echo $dp_shopping_cart_settings['worldpay_id'];}?>" name="worldpay_id">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Testmode</th>
                                    <td>
                                        <select name="worldpay_testmode">
                                            <option value="live" <?php if($dp_shopping_cart_settings['worldpay_testmode'] === "live") { echo 'selected="selected"';}?>>False</option>
                                            <option value="test" <?php if($dp_shopping_cart_settings['worldpay_testmode'] === "test") { echo 'selected="selected"';}?>>True</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                                    $worldpay_currency_code = $dp_shopping_cart_settings['worldpay_currency'];
                                    if(!$dp_shopping_cart_settings['dp_shop_currency']) {
                                        if ($worldpay_currency_code === "" && in_array($dp_shopping_cart_settings['dp_shop_currency'], $worldpay_supported_currency)) {
                                            ?>
                                            <input type="hidden" name="dp_worldpay_currency" value="<?php echo $dp_shopping_cart_settings['dp_shop_currency']; ?>" />
                                            <?php
                                            $worldpay_currency_code = $dp_shopping_cart_settings['dp_shop_currency'];
                                        }
                                    }
                                    if ($worldpay_currency_code != $dp_shopping_cart_settings['dp_shop_currency']) {
                                        ?>
                                        <tr><td colspan="2">Your Shops' Currency Code is not compatible with WorldPay. Please choose a Currency Code from the below list. Payments will be converted to the selected Currency Code, when Payments are sent to WorldPay.</td></tr>
                                        <tr><th scope="row">WorldPay Currency Code</th>
                                            <td>
                                                <select name="dp_worldpay_currency">
                                                    <?php
                                                    foreach ($worldpay_supported_currency as $w_code) {
                                                        $w_selected = '';
                                                        if ($worldpay_currency_code === $w_code) {
                                                            $w_selected = 'selected="selected"';
                                                        }
                                                        ?>
                                                        <option value="<?php echo $w_code;?>" <?php echo $w_selected;?>><?php echo $w_code; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        <?php
                                    }
                                    ?>
                            </table>
                        </div>
                    </div>
                    <h3><a href="#">AlertPay</a></h3>
                    <div>
                        <div id="alertpay" class="tabdiv">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">AlertPay ID</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['alertpay_id'])) {echo $dp_shopping_cart_settings['alertpay_id'];}?>" name="alertpay_id">
                                    </td>
                                </tr>
                                <?php
                                    $alertpay_currency_code = $dp_shopping_cart_settings['alertpay_currency'];
                                    if(!$dp_shopping_cart_settings['dp_shop_currency']) {
                                        if ($alertpay_currency_code === "" && in_array($dp_shopping_cart_settings['dp_shop_currency'], $alertpay_supported_currency)) {
                                            ?>
                                            <input type="hidden" name="dp_alertpay_currency" value="<?php echo $dp_shopping_cart_settings['dp_shop_currency']; ?>" />
                                            <?php
                                            $alertpay_currency_code = $dp_shopping_cart_settings['dp_shop_currency'];
                                        }
                                    }
                                    if ($alertpay_currency_code != $dp_shopping_cart_settings['dp_shop_currency']) {
                                        ?>
                                        <tr><td colspan="2">Your Shops' Currency Code is not compatible with AlertPay. Please choose a Currency Code from the below list. Payments will be converted to the selected Currency Code, when Payments are sent to AlertPay.</td></tr>
                                        <tr><th scope="row">AlertPay Currency Code</th>
                                            <td>
                                                <select name="dp_alertpay_currency">
                                                    <?php
                                                    foreach ($alertpay_supported_currency as $a_code) {
                                                        $a_selected = '';
                                                        if ($alertpay_currency_code === $a_code) {
                                                            $a_selected = 'selected="selected"';
                                                        }
                                                        ?>
                                                        <option value="<?php echo $a_code;?>" <?php echo $a_selected;?>><?php echo $a_code; ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        <?php
                                    }
                                    ?>
                            </table>
                        </div>
                    </div>
                    <h3><a href="#">Mobile</a></h3>
                    <div>
                        <div id="mobile" class="tabdiv">
                            <table class="form-table">
                                <thead>
                                    <tr>
                                        <th>Name</th><th>Number</th>
                                    </tr>
                                </thead>
                                <tbody class="mobile_payment">
                                    <?php
                                    if (is_array($dp_shopping_cart_settings['mobile_names'])) {
                                        $count_mp = count($dp_shopping_cart_settings['mobile_names']);
                                        for($mp_i = 0; $mp_i < $count_mp; $mp_i++) {
                                            ?>
                                            <tr class="row_block">
                                                <td>
                                                    <input type="text" value="<?php echo $dp_shopping_cart_settings['mobile_names'][$mp_i];?>" name="mobile_payment_name[]"/>
                                                </td>
                                                <td>
                                                    <input type="text" value="<?php echo $dp_shopping_cart_settings['mobile_number'][$mp_i];?>" name="mobile_payment_number[]"/>
                                                    <a style="cursor:pointer" onClick="return dp_m_rem(this);" class="remove_row">[-]</a>&nbsp;
                                                    <a style="cursor:pointer" onClick="return dp_m_add(this);" class="add_row">[+]</a>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                    else {
                                    ?>
                                    <tr class="row_block">
                                        <td>
                                            <input type="text" value="" name="mobile_payment_name[]"/>
                                        </td>
                                        <td>
                                            <input type="text" value="" name="mobile_payment_number[]"/>
                                            <a style="cursor:pointer" onClick="return dp_m_rem(this);" class="remove_row">[-]</a>&nbsp;
                                            <a style="cursor:pointer" onClick="return dp_m_add(this);" class="add_row">[+]</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <h3><a href="#">Bank Details</a></h3>
                    <div>
                        <div id="bank" class="tabdiv">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">Name of Bank</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_name'])) {echo $dp_shopping_cart_settings['bank_name'];}?>" name="bank_name">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Routing Number</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_routing'])) {echo $dp_shopping_cart_settings['bank_routing'];}?>" name="bank_routing">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Account Number</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_account'])) {echo $dp_shopping_cart_settings['bank_account'];}?>" name="bank_account">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Bank Account Owner</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_account_owner'])) {echo $dp_shopping_cart_settings['bank_account_owner'];}?>" name="bank_account_owner">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">IBAN</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_IBAN'])) {echo $dp_shopping_cart_settings['bank_IBAN'];}?>" name="bank_IBAN">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">BIC/SWIFT</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_bic'])) {echo $dp_shopping_cart_settings['bank_bic'];}?>" name="bank_bic">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">BIC/SWIFT</th>
                                    <td>
                                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['bank_bic'])) {echo $dp_shopping_cart_settings['bank_bic'];}?>" name="bank_bic">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

            </div>
        </div>
        <h3><a href="#">Discount Management</a></h3>
        <div>
            <div id="discount" class="tabdiv">
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Discount</th>
                        <td>
                            <input type="checkbox" value="true" name="discount_enable" <?php if ($dp_shopping_cart_settings['discount_enable'] === 'true') { echo 'checked';}?> />
                        </td>
                    </tr>
                </table>
                <div id="discount_code_confirmation"></div>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enter Discount Code</th>
                        <td>
                            <input type="text" value="" name="discount_code" id="discount_code" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Discount</th>
                        <td>
                            <input type="text" value="" name="discount_amount" id="discount_amount" />%
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">One Time Discount</th>
                        <td>
                            <input type="checkbox" value="true" name="discount_one_time" id="discount_one_time" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <input type="submit" id="dp_discount_submit" name="dp_discount_submit" value="Add Code"/>
                        </td>
                    </tr>
                </table>
                <div id="discount_code_layout">
                    <?php echo dpsc_get_discount_code_table();?>
                </div>
            </div>
        </div>
</div>
    <input type="submit" name="dp_submit" value="Save Settings" />
</form>
        <?php
}

/**
 * This function prints the table of Discount codes
 *
 */
function dpsc_get_discount_code_table() {
    $dpsc_discount_codes = get_option('dpsc_discount_codes');
    $output = '';
    if ($dpsc_discount_codes && count($dpsc_discount_codes) > 0 ) {
        $output .= '<table class="form-table">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Discount Code</th>
                                <th style="width: 32%;">Discount Amount (%)</th>
                                <th style="width: 22%;">Number of Times Used</th>
                                <th style="width: 22%;"></th>
                            </tr>
                        </thead>';
        foreach ($dpsc_discount_codes as $dpsc_discount_code) {
            $output .= '<tr>
                            <td>' . $dpsc_discount_code['code'] . '</td>
                            <td>' . $dpsc_discount_code['amount'] . '</td>
                            <td>' . $dpsc_discount_code['count'] . '</td>
                            <td><a><span class="dpsc_delete_discount_code" id="' . $dpsc_discount_code['id'] . '">Delete</span></a></td>
                        </tr>';
        }
        $output .= '</table>';
        if ($_REQUEST['ajax'] === 'true') {
            $output .= '<script type="text/javascript">jQuery("span.dpsc_delete_discount_code").live("click", function(){
                            var dpsc_delete_discount_code_id = jQuery(this).attr("id");
                            jQuery.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: "action=dpsc_delete_discount_code&id=" + dpsc_delete_discount_code_id + "&ajax=true",
                                success:function(msg){
                                    jQuery("div#discount_code_layout").html(msg);
                                }
                            });
                        });</script>';
        }
    }
    else {
        $output = 'No Discount Code added!';
    }
    return $output;
}

/**
 * This function saves Discount code
 *
 */
add_action('wp_ajax_save_dpsc_discount_code', 'dpsc_save_discount_code');
function dpsc_save_discount_code() {
    $discount_code = $_POST['dpsc_discount_code'];
    $discount_amount = $_POST['dpsc_discount_amount'];
    $discount_one_time = $_POST['dpsc_discount_one_time'];
    $output = '';
    $unique_discount = TRUE;
    $discount = get_option('dpsc_discount_codes') ? get_option('dpsc_discount_codes') : array();
    foreach ($discount as $check_code) {
        if ($check_code['code'] === $discount_code) {
            $unique_discount = FALSE;
        }
    }
    if ($unique_discount) {
        if (!empty($discount_code) && !empty($discount_amount)) {
            $dpsc_discount['code'] = $discount_code;
            $dpsc_discount['amount'] = $discount_amount;
            $dpsc_discount['count'] = 0;
            $dpsc_discount['one_time'] = $discount_one_time;
            $dpsc_discount['id'] = time();
            $discount[] = $dpsc_discount;
            update_option('dpsc_discount_codes', $discount);
        }
    }
    else {
        $output = '<span id="dpsc-same-discount-code-present">Please add another discount code as same discount code already exists.</span>';
    }
    if ($_REQUEST['ajax'] == 'true') {
        ob_start();
        echo dpsc_get_discount_code_table();
        $output .= ob_get_contents();
        ob_end_clean();
        die($output);
    }
}

/**
 * This function deletes the Discount code
 *
 */
add_action('wp_ajax_dpsc_delete_discount_code', 'dpsc_delete_discount_code');
function dpsc_delete_discount_code() {
    $dpsc_discount_code_id = intval($_POST['id']);
    $dpsc_discount_codes = get_option('dpsc_discount_codes');
    $dpsc_discount_codes_new = array();
    if (is_array($dpsc_discount_codes)) {
        foreach ($dpsc_discount_codes as $check_code) {
            if ($check_code['id'] === $dpsc_discount_code_id) {
                unset ($check_code);
            }
            else {
                $dpsc_discount_codes_new[] = $check_code;
            }
        }
    }
    update_option('dpsc_discount_codes', $dpsc_discount_codes_new);
    if ($_REQUEST['ajax'] == 'true') {
        ob_start();
        echo dpsc_get_discount_code_table();
        $output .= ob_get_contents();
        ob_end_clean();
        die($output);
    }
}

/**
 * This function returns the download links
 *
 */
function dpsc_pnj_get_download_links($products = FALSE) {
    if ($products) {
        if (is_array($products) && count($products) >0 ) {
            $temp_names = array();
            foreach ($products as $product) {
                $file_name = get_post_meta(intval($product),'digital_file', true);
                if ($file_name != '') {
                    $file_path = DP_DOWNLOAD_FILES_DIR . $file_name;

                    $temp_name = md5($file_name.time());
                    $newfile_path = DP_DOWNLOAD_FILES_DIR_TEMP . $temp_name ;

                    if (!copy($file_path, $newfile_path)) {
                        die("failed to copy $file...\n");
                    }else {
                        $temp_names[] = $temp_name.'@_@||@_@'.$file_name;
                        dpsc_pnj_update_download_table($temp_name, $file_name);
                    }
                }
            }
            return $temp_names;
        }
    }
    return FALSE;
}

/**
 * This function saves the download temp file in database
 *
 */
function dpsc_pnj_update_download_table($temp_name, $file_name) {
    global $wpdb;
    $table_name2 = $wpdb->prefix . "dpsc_temp_file_log";
    $sql = "INSERT INTO {$table_name2} (`real_name`, `saved_name`, `sent_time`) VALUES ('{$file_name}', '{$temp_name}', NOW())";
    $wpdb->query($sql);
}

/**
 * This function sends mail
 *
 */
function dpsc_pnj_send_mail($to, $from, $name, $subject, $msg, $attachment = FALSE) {
    global $wpdb;
    $headers  = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $name . ' <' . $from . '>' . "\r\n";
    if ($attachment) {
        $mail_attachment = array(DP_PLUGIN_DIR. '/pdf/invoice_' . $attachment . '.pdf');
        wp_mail($to, $subject, $msg, $headers,$mail_attachment);
    }
    else {
        wp_mail($to, $subject, $msg, $headers);
    }
}

/**
 * This function generates PDF
 *
 */
function make_pdf($invoice, $dpsc_discount_value, $tax, $dpsc_shipping_value, $dpsc_total, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $phone, $option='bill', $test=0) {

    define('FPDF_FONTPATH', 'font/');
    require_once('lib/fpdf16/fpdf.php');


    if ($option == 'bill') {

        class PDF extends FPDF {

            //Page header
            function Header() {
                $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
                $ad = array();
                $ad[f_name] = $dp_shopping_cart_settings['shop_name'];
                $ad[street] = $dp_shopping_cart_settings['shop_address'];
                $ad[zip] = $dp_shopping_cart_settings['shop_zip'];
                $ad[town] = $dp_shopping_cart_settings['shop_city'];
                $ad[state] = $dp_shopping_cart_settings['shop_state'];


                $biz_ad = implode("<br/>", $ad);
                $biz = str_replace("<br/>", "\n", $biz_ad);
                $biz = pdf_encode($biz);

                $this->SetFont('Arial', 'B', 12);

                //$url  = get_option('siteurl');
                $path = DP_PLUGIN_URL . '/images/pdf-logo-1.jpg';
                $this->Image($path);
                $this->SetXY(90, 7);
                $this->MultiCell(0, 7, "$biz", 0, 'L');
            }

            //Page footer
            function Footer() {
                $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
                //Position at xy mm from bottom
                $this->SetY(-25);
                //Arial italic 6
                $this->SetFont('Arial', '', 6);

                if (FALSE) {
                    $vat_id = ' - ' . get_option('wps_vat_id_label') . ': ' . get_option('wps_vat_id');
                } else {$vat_id = NULL;        }

                $footer_text = $dp_shopping_cart_settings['shop_name'] . $vat_id;
                $this->Cell(0, 10, "$footer_text", 1, 0, 'C');
            }

        }

        //Instanciation of inherited class
        $pdf = new PDF;
        $pdf->SetLeftMargin(10);
        $pdf->SetRightMargin(10);
        $pdf->SetTopMargin(5);

        // widths of columns
        $w1 = 20;
        $w2 = 64;
        $w3 = 30;
        $w4 = 38;
        $w5 = 38;

        $h2 = 3;


        $pdf->AddPage();
        $pdf->Ln(20);
        $pdf->SetFont('Arial', '', 10);
        // data for address
        $order = array();
        $order[f_name] = $bfname;
        $order[l_name] = $blname;
        $order[street] = $baddress;
        $order[town] = $bcity;
        $order[state] = $bstate;
        $order[zip] = $bzip;

        $order[country] = $bcountry;

        address_format($order, 'pdf_cust_address', $pdf);


        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 10);
        $phone_no = pdf_encode('Contact No. : ' . $phone);
        $pdf->Cell(0, 6, $phone_no, 0, 1);
        $bill_no = pdf_encode('Bill No. : ' . $invoice);
        $pdf->Cell(0, 6, $bill_no, 0, 1);

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 4, date(get_option('date_format')), 0, 1, 'R');

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($w1, 6, pdf_encode('Sr. No.'), 1, 0);
        $pdf->Cell($w2, 6, pdf_encode('Product Name'), 1, 0);
        $pdf->Cell($w3, 6, pdf_encode('Quantity'), 1, 0);
        $pdf->Cell($w4, 6, pdf_encode('Product Price'), 1, 0);
        $pdf->Cell($w5, 6, pdf_encode('Total'), 1, 1);
        $pdf->SetFont('Arial', '', 9);


        // get the cart content again
        $dpsc_products = $_SESSION['dpsc_products'];
        $dpsc_total = 0.00;
        $count = 1;
        foreach ($dpsc_products as $dpsc_product) {
            $dpsc_var = '';
            if (!empty($dpsc_product['var'])) {
                $dpsc_var = ' (' . $dpsc_product['var'] . ')';
            }
            $dpsc_total += floatval($dpsc_product['price'] * $dpsc_product['quantity']);
            $dis_price = number_format($dpsc_product['price'], 2);
            $dis_price_total = number_format($dpsc_product['price'] * $dpsc_product['quantity'], 2);
            $details = explode("|", $v);

            $pdf->SetFont('Arial', 'B', 9);

            $pdf->Cell($w1, 6, pdf_encode("$details[5]"), 'LTR', 0); // Art-no
            $pdf->Cell($w2, 6, pdf_encode("$details[2]"), 'LTR', 0); // Art-name
            $pdf->Cell($w3, 6, pdf_encode("$details[1]"), 'LTR', 0); // Amount
            $pdf->Cell($w4, 6, pdf_encode("$details[3]"), 'LTR', 0); // U - Price
            $pdf->Cell($w5, 6, pdf_encode("$details[4]"), 'LTR', 1); // Total price
            // any attributes?
            $pdf->SetFont('Arial', '', 7);

        //				foreach($ad as $v){
        //
        //					if(WPLANG == 'de_DE'){$v = utf8_decode($v);}
        //					pdf_encode($v);

            $pdf->Cell($w1, $h2, $count, 'LR', 0); // Art-no
            $pdf->Cell($w2, $h2, $dpsc_product['name'] . $dpsc_var, 'LR', 0); // Art-name
            $pdf->Cell($w3, $h2, $dpsc_product['quantity'], 'LR', 0); // Amount
            $pdf->Cell($w4, $h2, $dis_price, 'LR', 0); // U - Price
            $pdf->Cell($w5, $h2, $dis_price_total, 'LR', 1); // Total price
            //}
            // ending line of article row
            $pdf->Cell($w1, 1, "", 'LBR', 0); // Art-no
            $pdf->Cell($w2, 1, "", 'LBR', 0); // Art-name
            $pdf->Cell($w3, 1, "", 'LBR', 0); // Amount
            $pdf->Cell($w4, 1, "", 'LBR', 0); // U - Price
            $pdf->Cell($w5, 1, "", 'LBR', 1); // Total price
        }
        $pdf->SetFont('Arial', '', 9);

        // cart net sum
//        if ($discount > 0) {
//    $total_discount = $total*$discount/100;
//}
//else {
//    $total_discount = 0;
//}
//if ($tax > 0) {
//    $total_tax = ($total-$total_discount)*$tax/100;
//}
//else {
//    $total_tax = 0;
//}
//$amount = number_format($total+$shipping+$total_tax-$total_discount,2);
        $total = $dpsc_total;

        if ($dpsc_discount_value > 0) {
            $total_discount = $total * $dpsc_discount_value / 100;
        } else {
            $total_discount = 0.00;
        }
        if ($tax > 0) {
            $total_tax = ($total - $total_discount) * $tax / 100;
        } else {
            $total_tax = 0.00;
        }
        $shipping = $dpsc_shipping_value;
        $amount = number_format($total + $shipping + $total_tax - $total_discount, 2);
        $netsum_str = 'Subtotal:' . ' ' . number_format($total,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, pdf_encode($netsum_str), 0, 1, 'R');

        // discount
        $disf_str = pdf_encode('- Discount:') . ' ' . number_format($total_discount,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, "$disf_str", 0, 1, 'R');
        // discount
        $taxf_str = pdf_encode('+ Tax:') . ' ' . number_format($total_tax,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, "$taxf_str", 0, 1, 'R');
        // shipping fee
        $shipf_str = pdf_encode('+ Shipping fee:') . ' ' . number_format($shipping,2) . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(0, 6, "$shipf_str", 0, 1, 'R');


        $pdf->SetFont('Arial', 'B', 9);
        $totf_str = pdf_encode('Total:') . ' ' . $amount . ' ' . $dp_shopping_cart_settings['dp_shop_currency'];
        $pdf->Cell(00, 6, $totf_str, 0, 1, 'R');
    } else {

    }

    $file_name = 'invoice_' . $invoice . '.pdf';
    $pdf->SetDisplayMode(100);
    $output_path = DP_PLUGIN_DIR . '/pdf/' . $file_name;
    //$output_path_test	= PDF_PLUGIN_URL.'pdfinner/bills/test.pdf';

    if ($test == 0) {
        $pdf->Output($output_path, 'F');
    } else {
        $pdf->Output($output_path_test, 'F');
    }
}

function pdf_encode($data) {

$data = mb_convert_encoding($data, "iso-8859-1", "auto");
// utf8_decode() might be also interesting...

return $data;
}

function address_format($ad, $option='html', $pdf=0) {

$address = NULL;
$name = $ad[f_name] . ' ' . $ad[l_name];
if (strpos($address, 'NAME') !== false) {
$address = str_replace("NAME", strtoupper($name), $address);
}
if (strpos($address, 'name') !== false) {
$address = str_replace("name", $name, $address);
}

$address = address_token_replacer($address, 'STREET', $ad);
$address = address_token_replacer($address, 'HSNO', $ad);
$address = address_token_replacer($address, 'STRNO', $ad);
$address = address_token_replacer($address, 'STRNAM', $ad);
$address = address_token_replacer($address, 'PB', $ad);
$address = address_token_replacer($address, 'PO', $ad);
$address = address_token_replacer($address, 'PZONE', $ad);
$address = address_token_replacer($address, 'CROSSSTR', $ad);
$address = address_token_replacer($address, 'COLONYN', $ad);
$address = address_token_replacer($address, 'DISTRICT', $ad);
$address = address_token_replacer($address, 'REGION', $ad);
$address = address_token_replacer($address, 'PLACE', $ad);
$address = address_token_replacer($address, 'STATE', $ad);
$address = address_token_replacer($address, 'ZIP', $ad);
$address = address_token_replacer($address, 'COUNTRY', $ad);

foreach ($ad as $p) {
$pdf->Cell(0, 6, utf8_decode($p), 0, 1);
}
return $address;
}

function address_token_replacer($address, $needle, $replace) {
$needle_lower = strtolower($needle);
$key = $needle_lower;
if (($needle == 'PLACE') || ( $needle == 'place')) {
$key = 'town';
}

if (stripos($address, $needle) !== false) {
if (strpos($address, $needle) !== false) {
    $address = str_replace($needle, mb_strtoupper($replace["$key"]), $address);
} else {
    $address = str_replace($needle_lower, $replace["$key"], $address);
}
}
return $address;
}

/**
 * This function creates database table, schedules tasks for wp-cron and creates folder for download file and temporary files
 *
 */
register_activation_hook(__FILE__,'dpsc_install');
function dpsc_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "dpsc_transactions";
    $old_version = get_option('dpsc_version_info');
    if (!$old_version) {
        if($wpdb->get_var("show tables like '$table_name'") === $table_name) {
            $alter_sql = "ALTER TABLE `$table_name` ADD `phone` VARCHAR( 20 ) NOT NULL AFTER `billing_email`";
            $wpdb->query($alter_sql);
        }
    }
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    if ($dp_shopping_cart_settings && (!isset($dp_shopping_cart_settings['mobile_names']) && !isset($dp_shopping_cart_settings['mobile_number'])) && (isset($dp_shopping_cart_settings['safaricom_number']) || isset($dp_shopping_cart_settings['yu_number']) || isset($dp_shopping_cart_settings['zain_number']))) {
        $mobile_name = array();
        $mobile_number = array();
        $mobile_name[] = 'Safaricom M-PESA Number';
        $mobile_number[] = $dp_shopping_cart_settings['safaricom_number'];
        $mobile_name[] = 'YU yuCash Number';
        $mobile_number[] = $dp_shopping_cart_settings['yu_number'];
        $mobile_name[] = 'Zain ZAP Number';
        $mobile_number[] = $dp_shopping_cart_settings['zain_number'];
        $dp_shopping_cart_settings['mobile_names'] = $mobile_name;
        $dp_shopping_cart_settings['mobile_number'] = $mobile_number;
        unset($dp_shopping_cart_settings['safaricom_number']);
        unset($dp_shopping_cart_settings['yu_number']);
        unset($dp_shopping_cart_settings['zain_number']);
        update_option('dp_shopping_cart_settings', $dp_shopping_cart_settings);
    }
    if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
        $sql =  "CREATE TABLE `$table_name` (
                `id` INT( 5 ) NOT NULL AUTO_INCREMENT,
                `invoice` VARCHAR(50) NOT NULL,
                `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `order_time` FLOAT NOT NULL,
                `billing_first_name` VARCHAR( 100 ) NOT NULL,
                `billing_last_name` VARCHAR( 100 ) NOT NULL,
                `billing_country` VARCHAR( 10 ) NOT NULL,
                `billing_address` LONGTEXT NOT NULL,
                `billing_city` VARCHAR(100) NOT NULL,
                `billing_state` VARCHAR(200) NOT NULL,
                `billing_zipcode` VARCHAR(20) NOT NULL,
                `billing_email` VARCHAR(200) NOT NULL,
                `phone` VARCHAR( 20 ) NOT NULL,
                `shipping_first_name` VARCHAR( 100 ) NOT NULL,
                `shipping_last_name` VARCHAR( 100 ) NOT NULL,
                `shipping_country` VARCHAR( 10 ) NOT NULL,
                `shipping_address` LONGTEXT NOT NULL,
                `shipping_city` VARCHAR(100) NOT NULL,
                `shipping_state` VARCHAR(200) NOT NULL,
                `shipping_zipcode` VARCHAR(20) NOT NULL,
                `products` LONGTEXT NOT NULL,
                `payment_option` VARCHAR(100) NOT NULL,
                `discount` FLOAT NOT NULL,
                `tax` FLOAT NOT NULL,
                `shipping` FLOAT NOT NULL,
                `total` FLOAT NOT NULL,
                `tx_id` VARCHAR(50) NOT NULL,
                `payer_email` VARCHAR(200) NOT NULL,
                `payment_status` ENUM ('Pending', 'Paid', 'Canceled'),
                UNIQUE (`invoice`),
                PRIMARY KEY  (id)
                )";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    $table_name2 = $wpdb->prefix . "dpsc_temp_file_log";
    if($wpdb->get_var("show tables like '$table_name2'") != $table_name2) {

        $sql = "CREATE TABLE " . $table_name2 . " (
	  id int(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
          real_name VARCHAR(250) NOT NULL,
	  saved_name VARCHAR(250) NOT NULL,
          sent_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
          count int(10) DEFAULT 0
	);";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    $dp_dl_expiration_time = get_option('dp_dl_link_expiration_time');
    if (!$dp_dl_expiration_time) {
        $dp_expiration_time = '48';
        update_option('dp_dl_link_expiration_time', $dp_expiration_time);
    }
    if (!is_dir(DP_DOWNLOAD_FILES_DIR)) {
        mkdir(DP_DOWNLOAD_FILES_DIR);
        chmod(DP_DOWNLOAD_FILES_DIR, 0777);
    }
    if (!is_dir(DP_DOWNLOAD_FILES_DIR_TEMP)) {
        mkdir(DP_DOWNLOAD_FILES_DIR_TEMP);
        chmod(DP_DOWNLOAD_FILES_DIR_TEMP, 0777);
    }
    if(is_dir(DP_PLUGIN_DIR.'/cache')) {
        chmod(DP_PLUGIN_DIR.'/cache', 0777);
    }
    else {
        mkdir(DP_PLUGIN_DIR.'/cache');
        chmod(DP_PLUGIN_DIR.'/cache', 0777);
    }
    if(is_dir(DP_PLUGIN_DIR.'/temp')) {
        chmod(DP_PLUGIN_DIR.'/temp', 0777);
    }
    else {
        mkdir(DP_PLUGIN_DIR.'/temp');
        chmod(DP_PLUGIN_DIR.'/temp', 0777);
    }
    if(is_dir(DP_PLUGIN_DIR.'/pdf')) {
        chmod(DP_PLUGIN_DIR.'/pdf', 0777);
    }
    else {
        mkdir(DP_PLUGIN_DIR.'/pdf');
        chmod(DP_PLUGIN_DIR.'/pdf', 0777);
    }
    $date = date('M-d-Y', strtotime("+1 days"));
    $next_time_stamp = strtotime($date) + 18000;
    wp_schedule_event($next_time_stamp, 'dailly', 'dp_delete_files_daily');
    update_option('dpsc_version_info', 1.22);
}

function dp_delete_files_daily() {
    $files = glob(DP_DOWNLOAD_FILES_DIR.'/*', GLOB_BRACE);

    if (count($files) > 0) {
        $delete_time = floatval(get_option('dp_dl_link_expiration_time'));
        $yesterday = time() - ($delete_time * 60 * 60);

        usort($files, 'filemtime_compare');

        foreach ($files as $file) {

            if (@filemtime($file) > $yesterday) {
                return;
            }

            unlink($file);

        }

    }
}

register_deactivation_hook(__FILE__, 'dp_deactivate_delete_files');
function dp_deactivate_delete_files() {
    wp_clear_scheduled_hook('dp_delete_files_daily');
}

?>