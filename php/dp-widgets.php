<?php
/*
 * This file handles the functions related to widgets.
 */


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


?>