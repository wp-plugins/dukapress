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
?>