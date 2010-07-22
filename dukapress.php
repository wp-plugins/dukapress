<?php
/*
Plugin Name: DukaPress Shopping Cart
Description: DukaPress Shopping Cart
Version: 1.0
Author: Parshwa Nemi Jain and Nickel Pro
Author URI: http://dukapress.org/
Plugin URI: http://dukapress.org/
*/
session_start();
define('DP_PLUGIN_URL', WP_PLUGIN_URL.'/'.dirname(plugin_basename(__FILE__)));
define('DP_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.dirname(plugin_basename(__FILE__)));
define( 'DP_DOWNLOAD_FILES_DIR', WP_CONTENT_DIR. '/uploads/dpsc_download_files/' );
define( 'DP_DOWNLOAD_FILES_DIR_TEMP', WP_CONTENT_DIR. '/uploads/dpsc_temp_download_files/' );

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
    add_object_page('DukaPress', 'DukaPress', 'manage_options', 'dukapress-shopping-cart-order-log', '');
    add_submenu_page('dukapress-shopping-cart-order-log', 'DukaPress Order Log', 'Order Log', 'manage_options', 'dukapress-shopping-cart-order-log', 'dukapress_shopping_cart_order_log');
    add_submenu_page('dukapress-shopping-cart-order-log', 'DukaPress Settings', 'Settings', 'manage_options', 'dukapress-shopping-cart-settings', 'dukapress_shopping_cart_setting');
}


/**
 * This part handles the CSS and JS
 *
 */
if (is_admin()) {
    wp_enqueue_style('dpsc_admin_css', DP_PLUGIN_URL.'/css/dp-admin.css');
    wp_enqueue_script('jquery-ui-tabs');
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
        
        default:
            break;
    }
    $tim_url = DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=';
    $tim_end = '&w=310&h=383&zc=1';
    wp_enqueue_script('dpsc_js_file', DP_PLUGIN_URL . '/js/dukapress.js', array('jquery'));
    wp_localize_script( 'dpsc_js_file', 'dpsc_js', array( 'tim_url' => $tim_url, 'tim_end' => $tim_end ) );
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
                dpsc_pnj_send_mail($to, $from, 'DukaPress Payment Notification', $subject, $message);
                dpsc_pnj_send_mail($from, $to, 'DukaPress Payment Notification', $subject, $subject);
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
        $dp_shopping_cart_settings = array();
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
    ?>
<script type="text/javascript">
    jQuery(function() {
        jQuery("#tabvanilla").tabs();
        jQuery("#po").tabs();
        jQuery("#product-management").tabs();
    });
</script>
<form action="" method="post" enctype="multipart/form-data">
<div id="tabvanilla" class="dukapress-settings">

    <ul class="tabnav">
        <li><a href="#basic">Basic Shop Settings</a></li>
        <li><a href="#product-management">Product Management</a></li>
        <li><a href="#po">Payment Options</a></li>
        <li><a href="#discount">Discount Management</a></li>
    </ul>

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
                            <!--<option value="jqzoom_effect" <?php if($dp_shopping_cart_settings['image_effect'] === 'jqzoom_effect') {echo 'selected';}?>>JQZoom</option>-->
                            <option value="lightbox" <?php if($dp_shopping_cart_settings['image_effect'] === 'lightbox') {echo 'selected';}?>>Lightbox</option>
                            <option value="no_effect" <?php if($dp_shopping_cart_settings['image_effect'] === 'no_effect') {echo 'selected';}?>>No Effect</option>
                        </select>
                    </td>
                </tr>
<!--                <tr>
                    <th scope="row">Display Currency Code</th>
                    <td>
                        <input type="checkbox" value="true" name="dp_currency_code_enable" <?php if($dp_shopping_cart_settings['dp_currency_code_enable'] === 'true') {echo 'checked';}?>>
                    </td>
                </tr>-->
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

    <div id="product-management" class="tabdiv dukapress-settings">
        <ul class="tabnav">
            <li><a href="#inventory">Inventory Settings</a></li>
            <li><a href="#shipping">Shipping Options</a></li>
            <li><a href="#digital">Digital Products</a></li>
        </ul>

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
            </table>
        </div>

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

    <div id="po" class="tabdiv dukapress-settings">
        <ul class="tabnav">
            <li><a href="#paypal">PayPal</a></li>
            <li><a href="#authorize">Authorize.net</a></li>
            <li><a href="#worldpay">WorldPay</a></li>
            <li><a href="#alertpay">AlertPay</a></li>
			<li><a href="#mobile">Mobile</a></li>
            <li><a href="#bank">Bank Details</a></li>
        </ul>

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
                </table>
        </div>

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
            </table>
        </div>

        <div id="alertpay" class="tabdiv">
            <table class="form-table">
                <tr>
                    <th scope="row">AlertPay ID</th>
                    <td>
                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['alertpay_id'])) {echo $dp_shopping_cart_settings['alertpay_id'];}?>" name="alertpay_id">
                    </td>
                </tr>
            </table>
        </div>

		<div id="mobile" class="tabdiv">
            <table class="form-table">
                <tr>
                    <th scope="row">Safaricom M-PESA Number</th>
                    <td>
                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['safaricom_number'])) {echo $dp_shopping_cart_settings['safaricom_number'];}?>" name="safaricom_number">
                    </td>
                </tr>
                <tr>
                    <th scope="row">YU yuCash Number</th>
                    <td>
                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['yu_number'])) {echo $dp_shopping_cart_settings['yu_number'];}?>" name="yu_number">
                    </td>
                </tr>
                <tr>
                    <th scope="row">Zain ZAP Number</th>
                    <td>
                        <input type="text" value="<?php if(isset($dp_shopping_cart_settings['zain_number'])) {echo $dp_shopping_cart_settings['zain_number'];}?>" name="zain_number">
                    </td>
                </tr>

            </table>
        </div>

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
    <input type="submit" name="dp_submit" value="Save Settings" />
</form>
</div>
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
            $output .= '<script type="text/javascript">jQuery("span.dpsc_delete_discount_code").click(function(){
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
 * This function outputs the Product detail
 *
 */
function dpsc_get_product_details($product_id) {
    global $wpdb;
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $all_custom_fields = get_post_custom($product_id);
    if (is_numeric($all_custom_fields['price'][0])) {
        $custom_fields_output = array();

        $custom_fields_output['start'] = '<form id="dpsc_product_form_' . $product_id . '" name="dpsc_product_form_' . $product_id . '" class="product_form" action="" method="post" enctype="multipart/form-data">';
        if($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
            if (is_numeric($all_custom_fields['new_price'][0])) {
                $product_price = $all_custom_fields['new_price'][0];
                $custom_fields_output['price'] = '<p class="dpsc_price">Price: <span class="was">' . $all_custom_fields['price'][0] . '</span>&nbsp;<span class="is">' . $all_custom_fields['new_price'][0] . '</span></p>';
            }
            else {
                $product_price = $all_custom_fields['price'][0];
                $custom_fields_output['price'] = '<p class="dpsc_price">Price: <span class="is">' . $all_custom_fields['price'][0] . '</span></p>';
            }
        }
        $item_weight = '';
        if (isset ($all_custom_fields['item_weight'][0])) {
            $item_weight = '<input type="hidden" name="product_weight" value="' . $all_custom_fields['item_weight'][0] .  '">';
        }

        $custom_fields_output['end'] = $item_weight . '
                                        <input type="hidden" name="action" value="dpsc_add_to_cart"/>
                                        <input type="hidden" name="product_id" value="' . $product_id . '"/>
                                        <input type="hidden" name="product" value="' . get_the_title($product_id) . '"/>
                                        <input id="dpsc_actual_price" type="hidden" name="price" value="'.$product_price.'"/>
                                    </form>';

        if (isset ($all_custom_fields['dropdown_option'][0])) {
            $dropdown_content .= '<div class="dpsc_variation_main">';
            $get_vars = explode('||',$all_custom_fields['dropdown_option'][0]);
            $div_var_id = 0;
            foreach ($get_vars as $get_var) {
                $pro_vars = explode('|', $get_var);
                $vari_name = $pro_vars[0];
                $dropdown_content .= '<div id="dpsc_variation_'.$div_var_id.'" class="dpsc_variation"><span class="dpsc_variation" for="var">Select '.$vari_name.' </span>';
                $pro_vars = array_slice($pro_vars, 1);
                $dropdown_content .= '<select id="dpsc_variation_'.$div_var_id.'_dpscVariant" name="var[]" onchange="getFinalPrice();">';
                foreach ($pro_vars as $pro_var) {
                    $get_var = explode(';',$pro_var);
                    $var_price_text = '';
                    if (isset($get_var[1])) {
                        $var_price = floatval($get_var[1]);
                        $var_price_display = number_format(floatval($get_var[1]),2);
                        if ($var_price != 0.00 && ($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry')) {
                            $var_price_text = ' ( ' . $dp_shopping_cart_settings['dp_currency_symbol'] . $var_price_display.' ) ';
                        }
                    }
                    $dropdown_content .= '<option id="'.$var_price.'" value="'.$get_var[0].',:_._:,'.$var_price.'">'.$get_var[0].$var_price_text.'</option>';
                }
                $dropdown_content .= '</select></div><div class="clear"></div>';
                $div_var_id++;
            }
            $dropdown_content .= '</div><div class="clear"></div>';
            $custom_fields_output['dropdown'] = $dropdown_content;
            if($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
                $custom_fields_output['end'] = '<script language="javascript" type="text/javascript">
                var flag=0; //whether ie or ff
                if(navigator.appName=="Microsoft Internet Explorer"){initialCost=SalePriceLabel1.value;flag=1;}
                //change value in variant and get final price
                function getFinalPrice(){
                    try{
                        var drpdown;
                        var drpdownID;
                        var selIndex;
                        var selText;
                        var costDiff;
                        //--
                        var SalePriceLabel1=document.getElementById("dpsc_actual_price");
                        var initialCost=SalePriceLabel1.value;
                        var SalePriceLabel=document.getElementById("dpsc_new_product_price");
                        var SalePrice=initialCost;//works diffent for IE and FF
                        //var promoOff=parseFloat(document.getElementById("ctl00_ctl00_Body_PageContent_uxPromoPerc").value);
                        //stores sale price; which is to be updated
                        //now getting the price from dropdowns
                        var check=document.dpsc_product_form_' . $product_id . '.elements;
                        for(i=0;i<document.dpsc_product_form_' . $product_id . '.elements.length;i++){
                            if(document.dpsc_product_form_' . $product_id . '.elements[i].type=="select-one"){
                                drpdown=document.dpsc_product_form_' . $product_id . '.elements[i];
                                selIndex=drpdown.selectedIndex;
                                srchIndex=drpdown.options[selIndex].id;
                                if(srchIndex!=0.00){
                                    costDiff=parseFloat(srchIndex);
                                    SalePrice=parseFloat(SalePrice);
                                    SalePrice=SalePrice+costDiff;
                                    SalePrice=Math.round(SalePrice*100)/100;
                                    //[Nibha : 20080229] Patch added for missing .00 in case of perfectly round numbers.
                                    var desPos=String(SalePrice).indexOf(".");
                                    if(desPos>0){
                                        totalLen=String(SalePrice).length;
                                        valAfterDec=String(SalePrice).substring(desPos+1,totalLen);
                                        if(valAfterDec.length==1){SalePrice=String(SalePrice)+"0";}
                                    }
                                    if(String(SalePrice).indexOf(".")<0){SalePrice=String(SalePrice)+".00";}
                                    //END [Nibha : 20080229] Patch added for missing .00 in case of perfectly round numbers.
                                }
                            }
                        }
                        if(flag==0){SalePriceLabel.textContent=SalePrice;}
                        if(flag==1){SalePriceLabel.innerText=SalePrice;}
                        document.dpsc_product_form_' . $product_id . '.dpsc_price_updated.value=SalePrice;
                    }catch(ex){}}
            </script><input type="hidden" id="dpsc_new_product_price_hidden" name="dpsc_price_updated" value="' . $product_price . '" />' . $custom_fields_output['end'] . '<script language="javascript" type="text/javascript">
                                                    getFinalPrice();
                                                </script>';
                $custom_fields_output['final_price'] = '<p class="dpsc_price">Price: <span id="dpsc_new_product_price">' . $product_price . '</span></p>';
            }
        }

        $attachment_images =&get_children('post_type=attachment&post_status=inherit&post_mime_type=image&post_parent=' . $product_id);
        if (is_array($attachment_images) && count($attachment_images) != 0) {
            $image_effect = $dp_shopping_cart_settings['image_effect'];
            $image_content = '';
            switch ($image_effect) {
                case 'mz_effect':
                        $image_content = dp_pnj_mz_effect($attachment_images, $product_id);
                break;

                case 'mzp_effect':
                        $image_content = dp_pnj_mzp_effect($attachment_images, $product_id);
                break;

                case 'lightbox':
                        $image_content = dp_pnj_lightbox_effect($attachment_images, $product_id);
                break;

                case 'no_effect':
                        $image_content = dp_pnj_no_effect($attachment_images, $product_id);
                break;
            }
            $custom_fields_output['image_output'] = $image_content;
        }

        $in_stock = '';
        if($dp_shopping_cart_settings['dp_shop_inventory_active'] === 'yes' && isset($all_custom_fields['currently_in_stock'][0])) {
            if($dp_shopping_cart_settings['dp_shop_inventory_stocks'] === 'yes' && $all_custom_fields['currently_in_stock'][0] > 0) {
                $in_stock = '<span class="dpsc_in_stock">Currently in Stock</span>';
            }
            elseif ($dp_shopping_cart_settings['dp_shop_inventory_soldout'] === 'yes' && $all_custom_fields['currently_in_stock'][0] < 1) {
                $in_stock = '<span class="dpsc_in_stock_sold_out">Out of Stock</span>';
            }
        }
        $custom_fields_output['in_stock'] = $in_stock;

        $value_atc = 'Add to Cart';
        if($dp_shopping_cart_settings['dp_shop_mode'] === 'inquiry') {
            $value_atc = 'Inquire';
        }
        $custom_fields_output['add_to_cart'] = '<input  type="submit" class="dpsc_submit_button" id="dpsc_submit_button_' . $product_id . '" name="dpsc_add_to_cart" value="' . $value_atc . '" />';
        return $custom_fields_output;
    }
    return FALSE;
}

/**
 * The functions below handle the image effect output
 *
 */
function dp_pnj_mz_effect($attachment_images, $product_id) {
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image">';
    $output .= '<a href="' . $main_image . '" class="MagicZoom" id="zoom1" rel="show-title: false; zoom-fade: true; zoom-position: inner; thumb-change: mouseover"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" ></a>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function dp_pnj_mzp_effect($attachment_images, $product_id) {
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image">';
    $output .= '<a href="' . $main_image . '" class="MagicZoom MagicThumb" id="zoom1" rel="show-title: false; zoom-fade: true; zoom-position: inner; thumb-change: mouseover"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" ></a>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function dp_pnj_lightbox_effect($attachment_images, $product_id) {
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab fancybox" id="' . $product_id . '" rel="imgGroup" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image main_' . $product_id . '">';
    $output .= '<a href="' . $main_image . '" class="fancybox theProdMedia theProdMedia_alt"  rel="imgGroup"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" ></a>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function dp_pnj_no_effect($attachment_images, $product_id) {
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image">';
    $output .= '<img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" >';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

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
                $item['quantity'] += $product_quantity;
                unset($dpsc_products[$key]);
                array_push($dpsc_products, $item);
            }
        }
    }
    else {
        $dpsc_products = array();
    }

    if ($dpsc_count == 1) {
        $dpsc_product = array('name' => $product_name, 'var'=> $product_variation_names, 'price' => $product_updated_price, 'quantity' => $product_quantity, 'item_number' => $product_id, 'item_weight' => $product_weight);
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
 * Widget to display Detailed Shopping Cart
 *
 */
add_action('widgets_init', create_function('', 'return register_widget("dpsc_detailed_shopping_cart_widget");'));
class dpsc_detailed_shopping_cart_widget extends WP_Widget {
    function dpsc_detailed_shopping_cart_widget() {
        $widget_ops = array('description' => 'Displays DukaPress Shopping Cart');
        $control_ops = array('width' => 100, 'height' => 300);
        parent::WP_Widget(false,$name='DukaPress Shopping Cart',$widget_ops,$control_ops);
    }

    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array(  'title' => '', 'Your DukaPress Shopping Cart' => '') );
        $title = esc_attr( $instance['title'] );
        ?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input type="text" value="<?php echo $title; ?>" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" class="widefat" />
</p>
<p>To change settings, <a href="<?php bloginfo('url')?>/wp-admin/admin.php?page=dukapress-shopping-cart-settings">click here</a>.</p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function widget($args, $instance) {
        extract($args);
        $title = empty( $instance['title'] ) ? 'DukaPress Shopping Cart' : $instance['title'];
        echo $before_widget;
        echo $before_title.$title.$after_title;
        ?>
<div class="dpsc-shopping-cart" id="dpsc-shopping-cart">
            <?php echo dpsc_print_cart_html();?>
</div>
        <?php
        echo $after_widget;
    }
}

/**
 * Widget to display Go to Checkout Widget
 *
 */
add_action('widgets_init', create_function('', 'return register_widget("dpsc_show_checkout_link_widget");'));
class dpsc_show_checkout_link_widget extends WP_Widget {
    function dpsc_show_checkout_link_widget() {
        $widget_ops = array('description' => 'Displays DukaPress Checkout Link');
        $control_ops = array('width' => 100, 'height' => 300);
        parent::WP_Widget(false,$name='DukaPress Checkout',$widget_ops,$control_ops);
    }

    function form($instance) {
        $instance = wp_parse_args( (array) $instance, array(  'title' => '', 'DukaPress Checkout' => '') );
        $title = esc_attr( $instance['title'] );
        ?>
<p>
    <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
    <input type="text" value="<?php echo $title; ?>" name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" class="widefat" />
</p>
<p>To change checkout url, <a href="<?php bloginfo('url')?>/wp-admin/admin.php?page=dukapress-shopping-cart-settings">click here</a>.</p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    function widget($args, $instance) {
        extract($args);
        $title = empty( $instance['title'] ) ? 'DukaPress Checkout' : $instance['title'];
        echo $before_widget;
        echo $before_title.$title.$after_title;
        $dpsc_output = dpsc_go_to_checkout_link();
        ?>
<div class="dpsc-checkout_url-widget" id="dpsc-checkout_url-widget">
            <?php echo $dpsc_output;?>
</div>
        <?php
        echo $after_widget;
    }
}

/**
 * Widget to display Mni Shopping Cart
 *
 */
add_action('widgets_init', create_function('', 'return register_widget("dpsc_mini_shopping_cart_widget");'));

class dpsc_mini_shopping_cart_widget extends WP_Widget {
    function dpsc_mini_shopping_cart_widget() {
        $widget_ops = array('description' => 'Displays Mini DukaPress Shopping Cart');
        $control_ops = array('width' => 100, 'height' => 300);
        parent::WP_Widget(false,$name='Mini DukaPress Shopping Cart',$widget_ops,$control_ops);
    }

    function form($instance) {
        //Nothing to do here.
    }

    function update($new_instance, $old_instance) {
        //Nothing to do here.
    }

    function widget($args, $instance) {
        extract($args);
        echo $before_widget;
        echo $before_title.$after_title;
        echo $before_widget;
        ?>
<div class="dpsc-mini-shopping-cart" id="dpsc-mini-shopping-cart">
            <?php echo dpsc_print_cart_html(TRUE);?>
</div>
        <?php
        echo $after_widget;
    }
}

/**
 * This function returns the checkout link.
 *
 */
function dpsc_go_to_checkout_link() {
    $dpsc_output = '';
    if (!dpsc_cart_full()) {
        $dpsc_output .= 'Your cart is empty.';
    }
    else {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        $dpsc_output = '<a href="' . $dp_shopping_cart_settings['checkout'] . '">Go to Checkout</a>';
    }
    return $dpsc_output;
}

/**
 * This function returns the number of products in cart
 *
 */
function dpsc_cart_full() {
    $count = 0;
    if (isset($_SESSION['dpsc_products']) && is_array($_SESSION['dpsc_products'])) {
        foreach ($_SESSION['dpsc_products'] as $item) {
            $count++;
        }
        return $count;
    }
    else
        return 0;
}

/**
 * This function returns the HTML for cart
 *
 */
function dpsc_print_cart_html($mini=FALSE) {
    $dpsc_output = '';
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $dpsc_total_products = 0;
    if (!dpsc_cart_full()) {
        $dpsc_output .= 'Your cart is empty.';
    }
    else {
        $dpsc_total = 0.00;
        $dpsc_products_in_cart = $_SESSION['dpsc_products'];
        $dpsc_total_discount = 0.00;
        if (is_array($dpsc_products_in_cart)) {
           if($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
               $price_head = '<th id="price">Price</th>';
           }
           else {
               $price_head = '';
           }
            $dpsc_output .= '<table class="shoppingcart">
                <tr><th id="product">Product</th>
                <th id="dpsc-cart-quantity">Qty</th>
                ' . $price_head . '</tr>';
            foreach ($dpsc_products_in_cart as $dpsc_product_in_cart) {
                $dpsc_discount_on_this_product = 0.00;
                $dpsc_discount_value = $_SESSION['dpsc_discount_value'];
                if($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
                    $dpsc_at_checkout_to_be_displayed_price = $dp_shopping_cart_settings['dp_currency_symbol'] . ' '.number_format(floatval(($dpsc_product_in_cart['price']*$dpsc_product_in_cart['quantity'])),2);
                }
                else {
                    $dpsc_at_checkout_to_be_displayed_price = '';
                }
                $dpsc_total_products += $dpsc_product_in_cart['quantity'];
                $dpsc_total += floatval($dpsc_product_in_cart['price']*$dpsc_product_in_cart['quantity']);
                $dpsc_output .= '<tr><td>'.$dpsc_product_in_cart['name'].'</td>
                    <td>'.$dpsc_product_in_cart['quantity'].'</td>
                        <td>'.$dpsc_at_checkout_to_be_displayed_price.'</td></tr>';
            }
            $dpsc_output .= '</table>';
            if($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
                $dpsc_output .= '<strong>Total:' . $dp_shopping_cart_settings['dp_currency_symbol'] . ' ' . number_format($dpsc_total,2) . '</strong>';
            }
            $dpsc_output .= '<form action="" method="post" class="dpsc_empty_cart">
                <input type="hidden" name="dpsc_ajax_action" value="empty_cart" />
                <span class="emptycart">
			<a href="'.htmlentities(add_query_arg("dpsc_ajax_action", "empty_cart", remove_query_arg("ajax")), ENT_QUOTES).'">Empty your cart</a>
                </span>
                </form>';
            $dpsc_output .= "<span class='gocheckout'>" . dpsc_go_to_checkout_link() . "</span>";
        }
    }
    if ($mini) {
        $dpsc_at_mini_price = '';
        if($dp_shopping_cart_settings['dp_shop_mode'] != 'inquiry') {
            $dpsc_at_mini_price = $dp_shopping_cart_settings['dp_currency_symbol'].number_format($dpsc_total,2);
        }
        return '<a href="' . $dp_shopping_cart_settings["checkout"] . '">'. $dpsc_total_products . ' Products ' . $dpsc_at_mini_price . '</a>';
    }
    else {
        return $dpsc_output;
    }
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
 * This function handles the payments
 *
 */
if ($_REQUEST['dpsc_ajax_action'] === 'dpsc_payment_option') {
    add_action('init', 'dpsc_payment_option');
}

function dpsc_payment_option() {
    $dpsc_payment_option = $_POST['payment_selected'];
    $dpsc_discount_value = isset ($_POST['discount']) ? $_POST['discount'] : FALSE;
    list($dpsc_total, $dpsc_shipping_weight, $products, $number_of_items_in_cart) = dpsc_pnj_calculate_cart_price();
    $dpsc_shipping_value = dpsc_pnj_calculate_shipping_price($dpsc_shipping_weight, $dpsc_total, $number_of_items_in_cart);
    if ($products) {
        list($invoice, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $bemail) = dpsc_on_payment_save($dpsc_total, $dpsc_shipping_value, $products, $dpsc_discount_value, $dpsc_payment_option);
        switch ($dpsc_payment_option) {
            case 'paypal':
                $output = dpsc_paypal_payment($dpsc_total, $dpsc_shipping_value, $dpsc_discount_value, $invoice);
                break;

            case 'authorize':
                $output = dpsc_authorize_payment($dpsc_total, $dpsc_shipping_value, $dpsc_discount_value, $invoice, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $bemail);
                break;

            case 'worldpay':
                $output = dpsc_worldpay_payment($dpsc_total, $dpsc_shipping_value, $dpsc_discount_value, $invoice, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $bemail);
                break;

            case 'alertpay':
                $output = dpsc_alertpay_payment($dpsc_total, $dpsc_shipping_value, $dpsc_discount_value, $invoice, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $bemail);
                break;

            case 'bank':
                $output = dpsc_other_payment($invoice);
                break;

            case 'cash':
                $output = dpsc_other_payment($invoice);
                break;

			case 'mobile':
                $output = dpsc_other_payment($invoice);
                break;

            case 'delivery':
                $output = dpsc_other_payment($invoice);
                break;

            default:
                break;

        }
    }
    else {
        $output = 'There are no product in your cart.';
        $output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
        echo "jQuery('div.dpsc-checkout').html('$output');";
        exit ();
    }
    $output = str_replace(Array("\n","\r") , Array("\\n","\\r"),addslashes($output));
    echo "jQuery('div#dpsc_hidden_payment_form').html('$output');";
    $products = $_SESSION['dpsc_products'];
    foreach ($products as $key => $item) {
        unset($products[$key]);
    }
    $_SESSION['dpsc_products'] = $products;
    echo "jQuery('#dpsc_payment_form').submit();";
    exit ();
}

/**
 * This function generates PayPal form
 *
 */
function dpsc_paypal_payment($dpsc_total = FALSE, $dpsc_shipping_value = FALSE, $dpsc_discount_value = FALSE, $invoice = FALSE) {
    $dpsc_products = $_SESSION['dpsc_products'];
    $output = '';
    if (is_array($dpsc_products) && count($dpsc_products) > 0) {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        if ($dp_shopping_cart_settings['dp_shop_paypal_use_sandbox'] == "checked") {
            $dpsc_form_action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        else {
            $dpsc_form_action = 'https://www.paypal.com/cgi-bin/webscr';
        }
        $ipn_path = get_option('siteurl') . "/?paypal_ipn=true";
        $return_path = $dp_shopping_cart_settings['thank_you'];
        $check_return_path = explode('?', $return_path);
        if (count($check_return_path) > 1) {
            $return_path .= '&id='.$invoice;
        }
        else {
            $return_path .= '?id='.$invoice;
        }
        $output = '<form name="dpsc_paypal_form" id="dpsc_payment_form" action="'.$dpsc_form_action.'" method="post">';
        $output .= '<input type="hidden" name="return" value="' . $return_path . '"/>
                     <input type="hidden" name="cmd" value="_ext-enter" />
                     <input type="hidden" name="notify_url" value="' . $ipn_path . '"/>
                     <input type="hidden" name="redirect_cmd" value="_cart" />
                     <input type="hidden" name="business" value="' . $dp_shopping_cart_settings['dp_shop_paypal_id'] . '"/>
                     <input type="hidden" name="cancel_return" value="' . $return_path . '&status=cancel"/>
                     <input type="hidden" name="rm" value="2" />
                     <input type="hidden" name="upload" value="1" />
                     <input type="hidden" name="currency_code" value="' . $dp_shopping_cart_settings['dp_shop_currency'] . '"/>
                     <input type="hidden" name="no_note" value="1" />
                     <input type="hidden" name="invoice" value="' . $invoice . '">';
        $dpsc_count_product = 1;
        $tax_rate = 0;
        $dpsc_shipping_total = 0.00;
        if ($dp_shopping_cart_settings['tax'] > 0) {
            $tax_rate = $dp_shopping_cart_settings['tax'];
        }
        foreach ($dpsc_products as $dpsc_product) {
            $dpsc_var = '';
            $var_paypal_field = '';
            if (!empty($dpsc_product['var'])) {
                $dpsc_var = ' (' . $dpsc_product['var'] . ')';
                $var_paypal_field = '<input type="hidden" name="on0_' . $dpsc_count_product . '" value="Variation Selected" />
                                     <input type="hidden" name="os0_' . $dpsc_count_product . '" value="' . $dpsc_var . '"  />';
            }
            $output .= '<input type="hidden" name="item_name_' . $dpsc_count_product . '" value="' . $dpsc_product['name'] . $dpsc_var . '"/>
                             <input type="hidden" name="amount_' . $dpsc_count_product . '" value="' . $dpsc_product['price'] . '"/>
                             <input type="hidden" name="quantity_'.$dpsc_count_product.'" value="' . $dpsc_product['quantity'] . '"/>
                             <input type="hidden" name="item_number_' . $dpsc_count_product . '" value="' . $dpsc_product['item_number'] . '"/>
                             <input type="hidden" name="tax_rate_'.$dpsc_count_product.'" value="' . $tax_rate . '"/>'
                            . $var_paypal_field;
            if ($dp_shopping_cart_settings['discount_enable'] === 'true' && $dpsc_discount_value) {
                $output .= '<input type="hidden" name="discount_rate_' . $dpsc_count_product . '" value="' . $dpsc_discount_value . '">';
            }
            $dpsc_count_product++;
        }
        if ($dpsc_shipping_value > 0) {
            $dpsc_shipping_total = $dpsc_shipping_value;
        }
        $output .= '<input type="hidden" name="handling_cart" value="' . number_format($dpsc_shipping_total,2) . '"/></form>';
    }
    return $output;
}

/**
 * This function generates authorize.net form
 *
 */
function dpsc_authorize_payment($dpsc_total = FALSE, $dpsc_shipping_value = FALSE, $dpsc_discount_value = FALSE, $invoice = FALSE, $bfname = FALSE, $blname = FALSE, $bcity = FALSE, $baddress = FALSE, $bstate = FALSE, $bzip = FALSE, $bcountry = FALSE, $bemail = FALSE) {
    $dpsc_products = $_SESSION['dpsc_products'];
    $output = '';
    if ($dpsc_total) {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        if ($dp_shopping_cart_settings['authorize_url'] == "live") {
            $dpsc_form_action = 'https://secure.authorize.net/gateway/transact.dll';
        }
        else {
            $dpsc_form_action = 'https://test.authorize.net/gateway/transact.dll';
        }
        $total_tax = 0.00;
        $total_discount = 0.00;
        $total_shipping = 0.00;
        if ($dp_shopping_cart_settings['discount_enable'] === 'true' && $dpsc_discount_value) {
            $total_discount = $dpsc_total*$dpsc_discount_value/100;
        }
        if ($dp_shopping_cart_settings['tax'] > 0) {
            $tax_rate = $dp_shopping_cart_settings['tax'];
            $total_tax = ($dpsc_total-$total_discount)*$tax_rate/100;
        }
        if ($dpsc_shipping_value) {
            $total_shipping = $dpsc_shipping_value;
        }

        $dpsc_total = number_format($dpsc_total+$total_tax+$total_shipping-$total_discount,2);
        $sequence = rand(1, 1000);
        $timeStamp = time();
        if( phpversion() >= '5.1.2' ) {
            $fingerprint = hash_hmac("md5", $dp_shopping_cart_settings['authorize_api'] . "^" . $sequence . "^" . $timeStamp . "^" . $dpsc_total . "^", $dp_shopping_cart_settings['authorize_transaction_key']);
        }
        else {
            $fingerprint = bin2hex(mhash(MHASH_MD5, $dp_shopping_cart_settings['authorize_api'] . "^" . $sequence . "^" . $timeStamp . "^" . $dpsc_total . "^", $dp_shopping_cart_settings['authorize_transaction_key']));
        }
        $ipn_path = get_option('siteurl') . "/?auth_ipn=true";
        $output .= '<form name="dpsc_authorize_form" id="dpsc_payment_form" action="'.$dpsc_form_action.'" method="post">';
        $output .= '<input type="hidden" name="x_login" value="' . $dp_shopping_cart_settings['authorize_api'] . '" />';
        $output .= '<input type="hidden" name="x_version" value="3.1" />';
        $output .= '<input type="hidden" name="x_method" value="CC" />';
        $output .= '<input type="hidden" name="x_type" value="AUTH_CAPTURE" />';
        $output .= '<input type="hidden" name="x_amount" value="' . $dpsc_total . '" />';
        $output .= '<input type="hidden" name="x_description" value="Your Order No.: ' . $invoice . '" />';
        $output .= '<input type="hidden" name="x_invoice_num" value="' . $invoice . '" />';
        $output .= '<input type="hidden" name="x_fp_sequence" value="' . $sequence . '" />';
        $output .= '<input type="hidden" name="x_fp_timestamp" value="' . $timeStamp . '" />';
        $output .= '<input type="hidden" name="x_fp_hash" value="' . $fingerprint . '" />';
        $output .= '<input type="hidden" name="x_test_request" value="' . $dp_shopping_cart_settings['authorize_test_request'] . '" />';
        $output .= '<input type="hidden" name="x_show_form" value="PAYMENT_FORM" />';
        $output .= '<input type="hidden" name="x_relay_response" value="TRUE" />';
        $output .= '<input type="hidden" name="x_relay_url" value="' . $ipn_path . '" />';

        $output .= '<input type="hidden" name="x_first_name" value="' . $bfname . '" />';
        $output .= '<input type="hidden" name="x_last_name" value="' . $blname . '" />';
        $output .= '<input type="hidden" name="x_address" value="' . $baddress . '" />';
        $output .= '<input type="hidden" name="x_zip" value="' . $bzip . '" />';
        $output .= '<input type="hidden" name="x_city" value="' . $bcity . '" />';
        $output .= '<input type="hidden" name="x_country" value="' . $bcountry . '" />';
        $output .= '<input type="hidden" name="x_email" value="' . $bemail . '" />';
        $output .= '</form>';
    }
    return $output;
}

/**
 * This function generates WorldPay form
 *
 */
function dpsc_worldpay_payment($dpsc_total = FALSE, $dpsc_shipping_value = FALSE, $dpsc_discount_value = FALSE, $invoice = FALSE, $bfname = FALSE, $blname = FALSE, $bcity = FALSE, $baddress = FALSE, $bstate = FALSE, $bzip = FALSE, $bcountry = FALSE, $bemail = FALSE) {
    $output = '';
    if ($dpsc_total) {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        if($dp_shopping_cart_settings['worldpay_testmode'] === 'test'){
            $dpsc_form_action = 'https://select-test.worldpay.com/wcc/purchase';
            $testModeVal = '100';
            $name = 'AUTHORISED';
        }
        else {
            $dpsc_form_action = 'https://select.worldpay.com/wcc/purchase';
            $testModeVal = '0';
            $name = $bfname . ' ' . $blname;
        }
        $total_tax = 0.00;
        $total_discount = 0.00;
        $total_shipping = 0.00;
        if ($dp_shopping_cart_settings['tax'] > 0) {
            $tax_rate = $dp_shopping_cart_settings['tax'];
            $total_tax = $dpsc_total*$tax_rate/100;
        }
        if ($dp_shopping_cart_settings['discount_enable'] === 'true' && $dpsc_discount_value) {
            $total_discount = $dpsc_total*$dpsc_discount_value/100;
        }
        if ($dpsc_shipping_value) {
            $total_shipping = $dpsc_shipping_value;
        }
        $return_path = $dp_shopping_cart_settings['thank_you'];
        $check_return_path = explode('?', $return_path);
        if (count($check_return_path) > 1) {
            $return_path .= '&id='.$invoice;
        }
        else {
            $return_path .= '?id='.$invoice;
        }

        $dpsc_total = number_format($dpsc_total+$total_tax+$total_shipping-$total_discount,2);
        $lang = (strlen(WPLANG) > 0 ? substr(WPLANG,0,2) : 'en');
        $output = '<form name="dpsc_worldpay_form" id="dpsc_payment_form" action="' . $dpsc_form_action . '" method="post">
                        <input type="hidden" name="instId" value="' . $dp_shopping_cart_settings['worldpay_id'] . '" />
                        <input type="hidden" name="currency" value="' . $dp_shopping_cart_settings['dp_shop_currency'] . '" />
                        <input type="hidden" name="desc" value="Your Order No.: ' . $invoice . '" />
                        <input type="hidden" name="cartId" value="101KT0098" />
                        <input type="hidden" name="amount" value="' . $dpsc_total . '" />
                        <input type="hidden" name="testMode" value="' . $dp_shopping_cart_settings['worldpay_testmode'] . '" />
                        <input type="hidden" name="name" value="' . $name . '" />
                        <input type="hidden" name="address" value="' . $baddress . ' ' . $bcity . ' ' . $bstate . '" />
                        <input type="hidden" name="postcode" value="' . $bzip . '" />
                        <input type="hidden" name="country" value="' . $bcountry . '" />
                        <input type="hidden" name="tel" value="" />
                        <input type="hidden" name="email" value="' . $bemail . '" />
                        <input type="hidden" name="lang" value="' . $lang . '" />
                        <input type="hidden" name="MC_invoice" value="' . $invoice . '" />
                        <input type="hidden" name="MC_callback" value="' . $return_path . '" />
                    </form>';
    }
    return $output;
}

/**
 * This function generates AlertPay form
 *
 */
function dpsc_alertpay_payment($dpsc_total = FALSE, $dpsc_shipping_value = FALSE, $dpsc_discount_value = FALSE, $invoice = FALSE, $bfname = FALSE, $blname = FALSE, $bcity = FALSE, $baddress = FALSE, $bstate = FALSE, $bzip = FALSE, $bcountry = FALSE, $bemail = FALSE) {
    $output = '';
    if ($dpsc_total) {
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        $total_tax = 0.00;
        $total_discount = 0.00;
        $total_shipping = 0.00;
        if ($dp_shopping_cart_settings['tax'] > 0) {
            $tax_rate = $dp_shopping_cart_settings['tax'];
            $total_tax = $dpsc_total*$tax_rate/100;
        }
        if ($dp_shopping_cart_settings['discount_enable'] === 'true' && $dpsc_discount_value) {
            $total_discount = $dpsc_total*$dpsc_discount_value/100;
        }
        if ($dpsc_shipping_value) {
            $total_shipping = $dpsc_shipping_value;
        }

//        $dpsc_total = number_format($dpsc_total+$total_tax+$total_shipping-$total_discount,2);
        $dpsc_total = number_format($dpsc_total,2);
        $total_shipping = number_format($total_shipping,2);
        $total_tax = number_format($total_tax,2);
        $total_discount = number_format($total_discount,2);
        $return_path = $dp_shopping_cart_settings['thank_you'];
        $check_return_path = explode('?', $return_path);
        if (count($check_return_path) > 1) {
            $return_path .= '&id='.$invoice;
        }
        else {
            $return_path .= '?id='.$invoice;
        }
        $output ='<form name="dpsc_alertpay_form" id="dpsc_payment_form"  method="post" action="https://www.alertpay.com/PayProcess.aspx" >
                        <input type="hidden" name="ap_merchant" value="' . $dp_shopping_cart_settings['alertpay_id'] . '" />
                        <input type="hidden" name="ap_purchasetype" value="item-goods" />
                        <input type="hidden" name="ap_currency" value="' . $dp_shopping_cart_settings['dp_shop_currency'] . '" />
                        <input type="hidden" name="ap_itemname" value="' . $invoice . '" />
                        <input type="hidden" name="ap_amount" value="' . $dpsc_total . '" />
                        <input type="hidden" name="ap_shippingcharges" value="' . $total_shipping . '" />
                        <input type="hidden" name="ap_taxamount" value="' . $total_tax . '" />
                        <input type="hidden" name="ap_discountamount" value="' . $total_discount . '" />
                        <input type="hidden" name="ap_returnurl" value="' . $return_path . '" />
                        <input type="hidden" name="ap_cancelurl" value="' . $return_path . '&status=cancel"/>
                        <input type="hidden" name="ap_fname" value="' . $bfname . '" />
                        <input type="hidden" name="ap_lname" value="' . $blname . '" />
                        <input type="hidden" name="ap_contactemail" value="' . $bemail . '" />
                        <input type="hidden" name="ap_addressline1" value="' . $baddress . '" />
                        <input type="hidden" name="ap_city" value="' . $bcity . '" />
                        <input type="hidden" name="ap_stateprovince" value="QC" />
                        <input type="hidden" name="ap_zippostalcode" value="' . $bzip . '" />
                        <input type="hidden" name="ap_country" value="' . $bcountry . '" />
                  </form>';

    }
    return $output;
}

/**
 * This function generates form for the other payment form
 *
 */
function dpsc_other_payment($invoice = FALSE) {
    $output = '';
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $return_path = $dp_shopping_cart_settings['thank_you'];
    $check_return_path = explode('?', $return_path);
    if (count($check_return_path) > 1) {
        $return_path .= '&id='.$invoice;
    }
    else {
        $return_path .= '?id='.$invoice;
    }
    $output = '<form name="dpsc_other_form" id="dpsc_payment_form" action="' . $return_path . '" method="post">
                    <input type="hidden" name="just_for_the_sake_of_it" value="hmm" />
                </form>';
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
function dpsc_pnj_calculate_cart_price() {
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
 * This function handles the IPN from PayPal
 *
 */
if ($_REQUEST['paypal_ipn'] === 'true') {
    add_action('init', 'dpsc_paypal_ipn');
}

function dpsc_paypal_ipn() {
    global $wpdb;
    if ($_REQUEST['paypal_ipn'] === 'true') {
        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
        if ($dp_shopping_cart_settings['dp_shop_paypal_use_sandbox'] == "checked") {
            $dpsc_form_action = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        else {
            $dpsc_form_action = 'https://www.paypal.com/cgi-bin/webscr';
        }

        $ch = curl_init($dpsc_form_action);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        $result = curl_exec($ch);
        curl_close($ch);

        if (strcmp ($result, "VERIFIED") == 0) {
            $invoice = $_POST['invoice'];
            $tx_id = $_POST['txn_id'];
            $payer_email = $_POST['payer_email'];
            $payment_status = $_POST['payment_status'];
            switch ($payment_status) {
                case 'Processed':
                    $updated_status = 'Paid';
                    break;

                case 'Completed':
                    $updated_status = 'Paid';
                    break;

                case 'Pending':
                    $updated_status = 'Pending';
                    break;

                default:
                    $updated_status = 'Canceled';
                    break;
            }
            $table_name = $wpdb->prefix . "dpsc_transactions";
            $update_query = "UPDATE {$table_name} SET `tx_id`='{$tx_id}', `payer_email`='{$payer_email}', `payment_status`='{$updated_status}'
                            WHERE `invoice`='{$invoice}'";
            $wpdb->query($update_query);
            if ($payment_status === 'Processed' || $payment_status === 'Completed') {
                $message = '';
                $digital_message = '';
                $check_query = "SELECT * FROM {$table_name} WHERE `invoice`='{$invoice}'";
                $result = $wpdb->get_row($check_query);
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
                            We have received the payment for Invoice No.: '. $invoice . '.<br/>
                            We will start processing your order soon.<br/>' . $digital_message . '
                            Thanks,<br/>
                            '. $dp_shopping_cart_settings['shop_name'];

                $subject = 'Payment Received For Invoice No: ' . $invoice;
                $to = $result->billing_email;
                $from = get_option('admin_email');
                dpsc_pnj_send_mail($to, $from, 'DukaPress Payment Notification', $subject, $message);
                dpsc_pnj_send_mail($from, $to, 'DukaPress Payment Notification', $subject, $subject);
            }
        }
    }
}


/**
 * This function handles the IPN from Authorize.net
 *
 */
if ($_REQUEST['auth_ipn'] === 'true') {
    add_action('init', 'dpsc_auth_ipn');
}

function dpsc_auth_ipn() {
    global $wpdb;
    $payment_status = intval($_POST['x_response_code']);
    $invoice = $_POST['x_invoice_num'];
    $payer_email = $_POST['x_email'];
    switch ($payment_status) {
        case 1:
            $updated_status = 'Paid';
            break;

        case 2:
            $updated_status = 'Canceled';
            break;

        case 3:
            $updated_status = 'Canceled';
            break;

        case 4:
            $updated_status = 'Pending';
            break;

        default:
            $updated_status = 'Canceled';
            break;
    }
    $table_name = $wpdb->prefix . "dpsc_transactions";
    $update_query = "UPDATE {$table_name} SET `payer_email`='{$payer_email}', `payment_status`='{$updated_status}'
                    WHERE `invoice`='{$invoice}'";
    $wpdb->query($update_query);
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    if ($payment_status === 1) {
        $message = '';
        $digital_message = '';
        $check_query = "SELECT * FROM {$table_name} WHERE `invoice`='{$invoice}'";
        $result = $wpdb->get_row($check_query);
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
                    We have received the payment for Invoice No.: '. $invoice . '.<br/>
                    We will start processing your order soon.<br/>' . $digital_message . '
                    Thanks,<br/>
                    '. $dp_shopping_cart_settings['shop_name'];

        $subject = 'Payment Received For Invoice No: ' . $invoice;
        $to = $result->billing_email;
        $from = get_option('admin_email');
        dpsc_pnj_send_mail($to, $from, 'DukaPress Payment Notification', $subject, $message);
        dpsc_pnj_send_mail($from, $to, 'DukaPress Payment Notification', $subject, $subject);
    }
    $return_path = $dp_shopping_cart_settings['thank_you'];
    $check_return_path = explode('?', $return_path);
    if (count($check_return_path) > 1) {
        $return_path .= '&id='.$invoice;
    }
    else {
        $return_path .= '?id='.$invoice;
    }
    header("Location: $return_path");
    echo 'zzzzzzzzzz';
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
function make_pdf($invoice, $dpsc_discount_value, $tax, $dpsc_shipping_value, $dpsc_total, $bfname, $blname, $bcity, $baddress, $bstate, $bzip, $bcountry, $option='bill', $test=0) {

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
                $path = DP_PLUGIN_URL . '/pdf/pdf-logo-1.jpg';
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
    $date = date('M-d-Y', strtotime("+1 days"));
    $next_time_stamp = strtotime($date) + 18000;
    wp_schedule_event($next_time_stamp, 'dailly', 'dp_delete_files_daily');
}

function dp_delete_expired_files_daily() {
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

/**
 * Shortcode to display product
 *
 */
add_shortcode('dpsc_display_product', 'dpsc_pnj_display_product_name');
function dpsc_pnj_display_product_name($content = null) {
    $product_id = get_the_ID();
    $output = dpsc_get_product_details($product_id);
    $content .= '<div class="dpsc_product_main_container">';
    $content .= '<div class="dpsc_image_container">';
    $content .= $output['image_output'];
    $content .= '</div>';
    $content .= '<div class="dpsc_content_container">';
    if ($output['final_price']) {
        $content .= $output['final_price'];
    }
    else {
        $content .= $output['price'];
    }
    $content .= $output['in_stock'];
    $content .= $output['start'];
    $content .= $output['dropdown'];
    $content .= $output['add_to_cart'];
    $content .= $output['end'];
    $content .= '</div>';
    $content .= '</div><div class="clear"></div>';
    return $content;
}

/**
 * Shortcode to display product in grid views
 *
 */
add_shortcode('dpsc_grid_display', 'dpsc_pnj_grid_display');
function dpsc_pnj_grid_display($atts, $content=null) {
    extract(shortcode_atts( array(
            'category' => '1',
            'total' => '12',
            'column' => '3'
            ), $atts));

    $products = get_posts('numberposts=' . $total . '&category=' . $category);
    if (is_array($products) && count($products) > 0) {
        $content .= '<div class="dpsc_grid_display">';
        $count = 1;
        $all_count = 0;
        foreach ($products as $product) {
            $output = dpsc_get_product_details($product->ID);
            if ($output) {
                $attachment_images =&get_children('post_type=attachment&post_status=inherit&post_mime_type=image&post_parent=' . $product->ID);
                $main_image = '';
                foreach ($attachment_images as $image) {
                    $main_image = $image->guid;
                    break;
                }
                $prod_permalink = get_permalink($product->ID);
                $content .= '<div class="dpsc_grid_product">';
                $content .= '<div class="dpsc_grid_product_image">';
                $content .= '<a href="' . $prod_permalink . '" title="' .$product->post_title . '"><img src="' . DP_PLUGIN_URL . '/lib/rt-timthumb.php?src=' . $main_image . '&w=160&h=120&zc=1" ></a>';
                $content .= '</div>';
                $content .= '<div class="dpsc_grid_product_detail">';
                $content .= '<p class="title"><a href="' . $prod_permalink . '" title="' .$product->post_title . '">' . $product->post_title . '</a></p>';
                $content .= '<p class="price">' . $output['price'] . '</p>';
                $content .= $output['start'];
                $content .= $output['add_to_cart'];
                $content .= $output['end'];
                $content .= '</div>';
                $content .= '</div>';
                if ($count === intval($column)) {
                    $content .= '<div class="clear"></div>';
                    $count = 0;
                }
                $count++;
                $all_count++;
            }
        }
        $content .= '</div>';
        $content .= '<div class="clear"></div>';
    }
    return $content;
}

?>