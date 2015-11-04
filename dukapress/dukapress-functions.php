<?php
/**
 * Functions used in Dukapress
 *
 */


 if (!function_exists('dukapress_category_list')) {

	/**
	 * Retrieve product's category list in either HTML list or custom format.
	 *
	 * @param int $product_id Optional. Post ID to retrieve categories.
	 * @param string $before Optional. Before list.
	 * @param string $sep Optional. Separate items using this.
	 * @param string $after Optional. After list.
	 */
	 function dukapress_category_list($product_id = false, $before = '', $sep = ', ', $after = ''){
		$terms = get_the_term_list($product_id, 'duka_category', $before, $sep, $after);
		if ($terms)
			return $terms;
		else
			return __('Uncategorized', 'dp-lang');
	 }

 }

if (!function_exists('dukapress_tag_list')) {
	/**
	 * Retrieve product's tag list in either HTML list or custom format.
	 *
	 * @param int $product_id Optional. Post ID to retrieve categories.
	 * @param string $before Optional. Before list.
	 * @param string $sep Optional. Separate items using this.
	 * @param string $after Optional. After list.
	 */
	function dukapress_tag_list($product_id = false, $before = '', $sep = ', ', $after = ''){
	 	$terms = get_the_term_list($product_id, 'duka_tag', $before, $sep, $after);
		if ($terms)
			return $terms;
		else
			return __('No Tags', 'dp-lang');
	}
}


if (!function_exists('dukapress_tag_cloud')) :
/**
 * Display product tag cloud.
 *
 * The text size is set by the 'smallest' and 'largest' arguments, which will
 * use the 'unit' argument value for the CSS text size unit. The 'format'
 * argument can be 'flat' (default), 'list', or 'array'. The flat value for the
 * 'format' argument will separate tags with spaces. The list value for the
 * 'format' argument will format the tags in a UL HTML list. The array value for
 * the 'format' argument will return in PHP array type format.
 *
 * The 'orderby' argument will accept 'name' or 'count' and defaults to 'name'.
 * The 'order' is the direction to sort, defaults to 'ASC' and can be 'DESC'.
 *
 * The 'number' argument is how many tags to return. By default, the limit will
 * be to return the top 45 tags in the tag cloud list.
 *
 * The 'topic_count_text_callback' argument is a function, which, given the count
 * of the posts	 with that tag, returns a text for the tooltip of the tag link.
 *
 * The 'exclude' and 'include' arguments are used for the {@link get_tags()}
 * function. Only one should be used, because only one will be used and the
 * other ignored, if they are both set.
 *
 * @param bool $echo Optional. Whether or not to echo.
 * @param array|string $args Optional. Override default arguments.
 */
function dukapress_tag_cloud($echo = true, $args = array()) {

		$args['echo'] = false;
		$args['taxonomy'] = 'duka_tag';

		$cloud = '<div id="dukapress_tag_cloud">' . wp_tag_cloud($args) . '</div>';

		$cloud = apply_filters('dukapress_tag_cloud', $cloud, $args);

		if ($echo)
				echo $cloud;
		else
				return $cloud;
}
endif;


if (!function_exists('dukapress_popular_products')) :
/**
 * Displays a list of popular products ordered by sales.
 *
 * @param bool $echo Optional, whether to echo or return
 * @param int $num Optional, max number of products to display. Defaults to 5
 */
function dukapress_popular_products($echo = true, $num = 5) {
		//The Query
		$custom_query = new WP_Query('post_type=duka&post_status=publish&posts_per_page=' . intval($num) . '&meta_key=duka_sales_count&meta_compare=>&meta_value=0&orderby=meta_value_num&order=DESC');

		$content = '<ul id="dukapress_popular_products">';

		if (count($custom_query->posts)) {
				foreach ($custom_query->posts as $post) {
						$content .= '<li><a href="' . get_permalink($post->ID) . '">' . $post->post_title . '</a></li>';
				}
		} else {
				$content .= '<li>' . __('No Products', 'dp-lang') . '</li>';
		}

		$content .= '</ul>';

		$content = apply_filters('dukapress_popular_products', $content, $num);

		if ($echo)
				echo $content;
		else
				return $content;
}
endif;


if (!function_exists('dukapress_cart_link')) :
/**
 * Echos the current shopping cart link. If global cart is on reflects global location
 * @param bool $echo Optional, whether to echo. Defaults to true
 * @param bool $url Optional, whether to return a link or url. Defaults to show link.
 * @param string $link_text Optional, text to show in link.
 */
function dukapress_cart_link($echo = true, $url = false, $link_text = '') {
		global $dukapress;
    $checkout_url = $dukapress->get_setting('page_urls->checkout_url');
    if(empty($checkout_url)){
      $checkout_url = get_page_link($dukapress->get_setting('page_urls->checkout_id'));
    }
		$link = $checkout_url;

		if (!$url) {
				$text = ($link_text) ? $link_text : __('Shopping Cart', 'dp-lang');
				$link = '<a href="' . $link . '" class="dpsc_cart_link">' . $text . '</a>';
		}

		$link = apply_filters('dukapress_cart_link', $link, $echo, $url, $link_text);

		if ($echo)
				echo $link;
		else
				return $link;
}
endif;


if (!function_exists('dukapress_checkout_step_url')) :
/**
 * Returns the current shopping cart link with checkout step.
 *
 * @param string $checkoutstep. Possible values: checkout-edit, shipping, checkout, confirm-checkout, confirmation
 */
function dukapress_checkout_step_url($checkout_step) {
		return apply_filters('dukapress_checkout_step_url', dukapress_cart_link(false, true) . trailingslashit($checkout_step), $checkout_step);
}
endif;


if (!function_exists('dukapress_page_url')) :

  /**
   * Echos the page url set up by the admin
   * @param bool $echo Required, whether to echo. Defaults to true
   * @param bool $name Required, type of url.
   */
  function dukapress_page_url($echo = true, $name = 'checkout'){
    global $dukapress;
    switch ($name) {
      case 'thankyou':
        $thankyou_url = $dukapress->get_setting('page_urls->thankyou_url');
        if(empty($thankyou_url)){
          $thankyou_url = get_page_link($dukapress->get_setting('page_urls->thankyou_id'));
        }
        if($echo){
          echo $thankyou_url;
        }else{
          return $thankyou_url;
        }
        break;

      default:
        # code...
        break;
    }
  }

endif;


if (!function_exists('dukapress_shop_currency_conversion_rate')) :

/**
 * Get the conversion rate
 *
 * @param string $gateway_currency. Currency symbol to be converted
 */
function dukapress_shop_currency_conversion_rate($gateway_currency){
  global $dukapress;
  $shop_currency = $dukapress->get_setting('shop_currency');
  $conversion_rate = 1;
  if($shop_currency != $gateway_currency){
    $conversion_rate = DP_CURRENCYCONVERTER::convert(1, $shop_currency, $gateway_currency);
  }
  return $conversion_rate;
}

endif;

if (!function_exists('dukapress_trim_name')) :

/**
 * Trim name
 *
 * @param string $name. Name to be trimmed
 * @param integer $length. length to trim to
 */
function dukapress_trim_name($name, $length = 127) {
  while (strlen(urlencode($name)) > $length)
    $name = substr($name, 0, -1);

  return urlencode($name);
}
endif;
?>
