<?php
class DukaPress_Admin_Pages{
	
	/** 
	 * Main Settings
	 */
	static function main($settings){
		global $dukapress;
		$pages = get_pages();
		?>
		<h2><?php _e("General Settings","dp-lang");?></h2>
		<div id="dpsc_main">
			<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
				<p class="submit">
					<input class='button button-primary' type='submit' name='dukapress_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
				</p>
				<div class="product-accordion">
					<h3><?php _e('Shop Settings','dp-lang'); ?></h3>
					<div>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="shop_name"><?php _e("Name of shop","dp-lang"); ?>: </label></th>
								<td>
									<input name="dpsc[shop_name]" type="text" class="regular-text" value="<?php echo $settings['shop_name']; ?>" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="shop_address"><?php _e("Shop Address","dp-lang"); ?>: </label></th>
								<td>
									<textarea rows="3" class="large-text code" name="dpsc[shop_address]"><?php echo $settings['shop_address']; ?></textarea>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("State / Province","dp-lang");?></th>
								<td>
									<input name="dpsc[shop_state]" type="text" class="regular-text" value="<?php echo $settings['shop_state']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Postal Code","dp-lang");?></th>
								<td>
									<input name="dpsc[shop_zip]" type="text" class="regular-text" value="<?php echo $settings['shop_zip']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("City / Town","dp-lang");?></th>
								<td>
									<input name="dpsc[shop_city]" type="text" class="regular-text" value="<?php echo $settings['shop_city']; ?>" />
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Shop Mode","dp-lang");?></th>
								<td>
									<select name="dpsc[dp_shop_mode]">
										<option value="regular" <?php if($settings['dp_shop_mode'] === 'regular') {echo 'selected';}?>><?php _e("Regular Shop Mode","dp-lang");?></option>
										<option value="inquiry" <?php if($settings['dp_shop_mode'] === 'inquiry') {echo 'selected';}?>><?php _e("Inquiry Email Mode","dp-lang");?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Product Listings Only', 'dp-lang' ) ?></th>
								<td>
									<label><input value="1" name="dpsc[disable_cart]" type="radio"<?php checked( $settings['disable_cart'], 1 ) ?> /> <?php _e( 'Yes', 'dp-lang' ) ?></label>
									<label><input value="0" name="dpsc[disable_cart]" type="radio"<?php checked( $settings['disable_cart'], 0 ) ?> /> <?php _e( 'No', 'dp-lang' ) ?></label>
									<br /><span class="description"><?php _e( 'This option turns MarketPress into more of a product listing plugin, disabling shopping carts, checkout, and order management. This is useful if you simply want to list items you can buy in a store somewhere else, optionally linking the "Buy Now" buttons to an external site. Some examples are a car dealership, or linking to songs/albums in itunes, or linking to products on another site with your own affiliate links.', 'dp-lang' ) ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Country of your shop","dp-lang");?></th>
								<td>
									<select name="dpsc[shop_country]" style="width: 240px;">
										<?php
										foreach ($dukapress->country_code_name as $country_code => $country_name) {
											$cont_selected = '';
											if ($settings['shop_country'] === $country_code) {
												$cont_selected = 'selected="selected"';
											}
											echo '<option value="' . $country_code . '" ' . $cont_selected . '>' . $country_name . '</option>';
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Shop Currency","dp-lang");?></th>
								<td>
									<select name="dpsc[shop_currency]" style="width: 240px;">
										<?php
										foreach ( $dukapress->currencies as $key => $value ) {
											?><option value="<?php echo $key; ?>"<?php selected( $settings['shop_currency'], $key ); ?>><?php echo esc_attr( $value[ 0 ] ) . ' - ' . $dukapress->format_currency( $key ); ?></option><?php
										}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Currency Symbol Position', 'dp-lang' ) ?></th>
								<td>
									<label>
										<input value="1" name="dpsc[curr_symbol_position]" type="radio"<?php checked( $settings['shop_currency_position'], 1 ); ?>>
										<?php echo $dukapress->format_currency( $settings['shop_currency'] ); ?>100
									</label><br />
									<label>
										<input value="2" name="dpsc[curr_symbol_position]" type="radio"<?php checked( $settings['shop_currency_position'], 2 ); ?>>
										<?php echo $dukapress->format_currency( $settings['shop_currency'] ); ?> 100</label><br />
									<label>
										<input value="3" name="dpsc[curr_symbol_position]" type="radio"<?php checked( $settings['shop_currency_position'], 3 ); ?>>
										100<?php echo $dukapress->format_currency( $settings['shop_currency'] ); ?>
									</label><br />
									<label>
										<input value="4" name="dpsc[curr_symbol_position]" type="radio"<?php checked( $settings['shop_currency_position'], 4 ); ?>>
										100 <?php echo $dukapress->format_currency( $settings['shop_currency'] ); ?>
									</label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Enable User Registration:","dp-lang");?></th>
								<td>
									<input type="checkbox" value="checked" name="dpsc[user_registration]" <?php echo $settings['user_registration']; ?>/>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Enable Invoice PDF Generation:","dp-lang");?></th>
								<td>
									<input type="checkbox" value="checked" name="dpsc[pdf_generation]" <?php echo $settings['pdf_generation']; ?>/>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Tax Rate:","dp-lang");?></th>
								<td>
									<input type="text" value="<?php echo $settings['tax']['rate']; ?>" name="dpsc[tax][rate]">%
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Thank You Page","dp-lang");?></th>
								<td>
									<table class="form-table">
										<tr>
											<th scope="row"><label><?php _e("URL","dp-lang");?></label></th><td><input type="text" name="dpsc[page_urls][thankyou_url]" value="<?php echo $settings['page_urls']['thankyou_url'];?>" /></td>
										</tr>
										<tr>
											<th scope="row"><label><?php _e("Page","dp-lang");?></label></th>
											<td>
												<select name="dpsc[page_urls][thankyou_id]">
													<?php 
													foreach ( $pages as $pagg ) {
														$cont_selected = '';
														if (intval($settings['page_urls']['thankyou_id']) === $pagg->ID) {
															$cont_selected = 'selected="selected"';
														}
														$option = '<option value="' .$pagg->ID. '" '.$cont_selected.'>';
														$option .= $pagg->post_title;
														$option .= '</option>';
														echo $option;
													}
													?>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Affiliate Page","dp-lang");?></th>
								<td>
									<table class="form-table">
										<tr>
											<th scope="row"><label><?php _e("URL","dp-lang");?></label></th><td><input type="text" name="dpsc[page_urls][affiliate_url]" value="<?php echo $settings['page_urls']['affiliate_url'];?>" /></td>
										</tr>
										<tr>
											<th scope="row"><label><?php _e("Page","dp-lang");?></label></th>
											<td>
												<select name="dpsc[page_urls][affiliate_id]">
													<?php 
													foreach ( $pages as $pagg ) {
														$cont_selected = '';
														if (intval($settings['page_urls']['affiliate_id']) === $pagg->ID) {
															$cont_selected = 'selected="selected"';
														}
														$option = '<option value="' .$pagg->ID. '" '.$cont_selected.'>';
														$option .= $pagg->post_title;
														$option .= '</option>';
														echo $option;
													}
													?>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Terms and Conditions Page","dp-lang");?></th>
								<td>
									<table class="form-table">
										<tr>
											<th scope="row"><label><?php _e("URL","dp-lang");?></label></th><td><input type="text" name="dpsc[page_urls][terms_url]" value="<?php echo $settings['page_urls']['terms_url'];?>" /></td>
										</tr>
										<tr>
											<th scope="row"><label><?php _e("Page","dp-lang");?></label></th>
											<td>
												<select name="dpsc[page_urls][terms_id]">
													<?php 
													foreach ( $pages as $pagg ) {
														$cont_selected = '';
														if (intval($settings['page_urls']['terms_id']) === $pagg->ID) {
															$cont_selected = 'selected="selected"';
														}
														$option = '<option value="' .$pagg->ID. '" '.$cont_selected.'>';
														$option .= $pagg->post_title;
														$option .= '</option>';
														echo $option;
													}
													?>
												</select>
											</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Maximum Downloads', 'dp-lang' ) ?></th>
								<td>
									<span class="description"><?php _e( 'How many times may a customer download a file they have purchased? (It\'s best to set this higher than one in case they have any problems downloading)', 'dp-lang' ) ?></span><br />
									<select name="dpsc[max_downloads]">
										<?php
										$max_downloads = $settings['max_downloads'];
										for ( $i = 1; $i <= 100; $i++ ) {
											$selected = ($max_downloads == $i) ? ' selected="selected"' : '';
											echo '<option value="' . $i . '"' . $selected . '">' . $i . '</option>';
										}
										?>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<h3><?php _e("Product Management","dp-lang");?></h3>
				<div>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><?php _e( 'Default Products Per Page', 'dp-lang' ) ?></th>
								<td>
									<span class="description"><?php _e( 'Set the default number of products to be shown per page', 'dp-lang' ) ?></span><br />
									<select name="dpsc[per_page]">
										<?php
										$per_page = $settings['per_page'];
										for ( $i = 1; $i <= 100; $i++ ) {
											$selected = ($per_page == $i) ? ' selected="selected"' : '';
											echo '<option value="' . $i . '"' . $selected . '">' . $i . '</option>';
										}
										?>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Product Page Image Effect","dp-lang"); ?></th>
								<td>
									<select name="dpsc[image_effect]">
										<option value="mz_effect" <?php if($settings['image_effect'] === 'mz_effect') {echo 'selected';}?>><?php _e("Magic Zoom","dp-lang");?></option>
										<option value="mzp_effect" <?php if($settings['image_effect'] === 'mzp_effect') {echo 'selected';}?>><?php _e("Magic Zoom Plus","dp-lang");?></option>
										<option value="jqzoom_effect" <?php if($settings['image_effect'] === 'jqzoom_effect') {echo 'selected';}?>><?php _e("JQZoom","dp-lang");?></option>
										<option value="lightbox" <?php if($settings['image_effect'] === 'lightbox') {echo 'selected';}?>><?php _e("Lightbox","dp-lang");?></option>
										<option value="no_effect" <?php if($settings['image_effect'] === 'no_effect') {echo 'selected';}?>><?php _e("No Effect","dp-lang");?></option>
									</select>
								</td>
							</tr>
							<tr>
							<th scope="row"><?php _e("Main Product Image Size","dp-lang");?></th>
								<td>
									<table class="form-table">
										<tr>
											<th scope="row"><label for="dp_main_image_width"><?php _e("Width","dp-lang");?></label></th><td><input type="text" name="dpsc[product_img_width]" size="5" value="<?php echo $settings['product_img_width'];?>" /><i>px</i></td>
										</tr>
										<tr>
											<th scope="row"><label for="dp_main_image_height"><?php _e("Height","dp-lang");?></label></th><td><input type="text" name="dpsc[product_img_height]" size="5" value="<?php echo $settings['product_img_height'];?>" /><i>px</i></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Product Thumbnail Size","dp-lang");?></th>
								<td>
									<table class="form-table">
										<tr>
											<th scope="row"><label for="dp_thumb_image_width"><?php _e("Width","dp-lang");?></label></th><td><input type="text" name="dpsc[thumb_img_width]" size="5" value="<?php echo $settings['t_w'];?>" /><i>px</i></td>
										</tr>
										<tr>
											<th scope="row"><label for="dp_thumb_image_height"><?php _e("Height","dp-lang");?></label></th><td><input type="text" name="dpsc[thumb_img_height]" size="5" value="<?php echo $settings['t_h'];?>" /><i>px</i></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e("Grid Product Thumbnail Size","dp-lang");?></th>
								<td>
									<table class="form-table">
										<tr>
											<th scope="row"><label for="dp_thumb_grid_width"><?php _e("Width","dp-lang");?></label></th><td><input type="text" name="dpsc[grid_img_width]" size="5" value="<?php echo $settings['grid_img_width'];?>" /><i>px</i></td>
										</tr>
										<tr>
											<th scope="row"><label for="dp_thumb_grid_height"><?php _e("Height","dp-lang");?></label></th><td><input type="text" name="dpsc[thumb_img_height]" size="5" value="<?php echo $settings['grid_img_height'];?>" /><i>px</i></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Show Related Products', 'dp-lang' ) ?></th>
								<td>
									<label><input value="1" name="dpsc[related_products][show]" type="radio"<?php checked( $settings['related_products']['show'], 1 ) ?> /> <?php _e( 'Yes', 'dp-lang' ) ?></label>
									<label><input value="0" name="dpsc[related_products][show]" type="radio"<?php checked( $settings['related_products']['show'], 0 ) ?> /> <?php _e( 'No', 'dp-lang' ) ?></label>
								</td>
							</tr>
							<?php if($settings['related_products']['show']){?>
							<tr><th colspan="2"><?php _e("Related Products","dp-lang");?></th></tr>
							<tr>
								<th scope="row"><?php _e( 'Related Product Limit', 'dp-lang' ) ?></th>
								<td>
									<label><input name="dpsc[related_products][show_limit]" type="text" size="2" value="<?php echo intval( $settings['related_products']['show_limit']); ?>" /></label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Relate Products By', 'dp-lang' ) ?></th>
								<td>
									<select name="dpsc[related_products][relate_by]">
										<option value="both" <?php selected( $settings['related_products']['relate_by'], 'both' ); ?>><?php _e( 'Category &amp; Tags', 'dp-lang' ); ?></option>
										<option value="category" <?php selected( $settings['related_products']['relate_by'], 'category' ); ?>><?php _e( 'Category Only', 'dp-lang' ); ?></option>
										<option value="tags" <?php selected( $settings['related_products']['relate_by'], 'tags' ); ?>><?php _e( 'Tags Only', 'dp-lang' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Show Related Products As Simple List', 'dp-lang' ) ?></th>
								<td>
									<label><input value="1" name="dpsc[related_products][simple_list]" type="radio"<?php checked( $settings['related_products']['simple_list'], 1 ) ?> /> <?php _e( 'Yes', 'dp-lang' ) ?></label>
									<label><input value="0" name="dpsc[related_products][simple_list]" type="radio"<?php checked( $settings['related_products']['simple_list'], 0 ) ?> /> <?php _e( 'No', 'dp-lang' ) ?></label>
									<br /><span class="description"><?php _e( 'Setting to "No" will use the List/Grid View setting.', 'dp-lang' ) ?></span>
								</td>
							</tr>
							<?php }?>
							<tr><th colspan="2"><?php _e("Inventory Settings","dp-lang");?></th></tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Inventory Warning Threshold', 'dp-lang' ) ?></th>
								<td>
									<span class="description"><?php _e( 'At what low stock count do you want to be warned for products you have enabled inventory tracking for?', 'dp-lang' ) ?></span><br />
									<select name="dpsc[inventory_threshhold]">
										<?php
										$inventory_threshhold = $settings['inventory_threshhold'];
										for ( $i = 0; $i <= 100; $i++ ) {
											$selected = ($inventory_threshhold == $i) ? ' selected="selected"' : '';
											echo '<option value="' . $i . '"' . $selected . '">' . $i . '</option>';
										}
										?>
									</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Hide Out of Stock Products', 'mp' ) ?></th>
								<td>
									<label><input value="1" name="dpsc[inventory_remove]" type="radio"<?php checked( $settings['inventory_remove'], 1 ); ?>> <?php _e( 'Yes', 'dp-lang' ) ?></label>
									<label><input value="0" name="dpsc[inventory_remove]" type="radio"<?php checked( $settings['inventory_remove'], 0 ); ?>> <?php _e( 'No', 'dp-lang' ) ?></label>
									<br /><span class="description"><?php _e( 'This will set the product to an "out_of_stock" status if inventory of all variations is gone.', 'dp-lang' ) ?></span>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Google Analytics Ecommerce Tracking', 'mp' ) ?></th>
								<td>
									<select name="dpsc[ga_ecommerce]">
										<option value="none"<?php selected( $settings['ga_ecommerce'], 'none' ) ?>><?php _e( 'None', 'dp-lang' ) ?></option>
										<option value="new"<?php selected( $settings['ga_ecommerce'], 'new' ) ?>><?php _e( 'Asynchronous Tracking Code', 'dp-lang' ) ?></option>
										<option value="old"<?php selected( $settings['ga_ecommerce'], 'old' ) ?>><?php _e( 'Old Tracking Code', 'dp-lang' ) ?></option>
										<option value="universal"<?php selected( $settings['ga_ecommerce'], 'universal' ) ?>><?php _e( 'Universal Analytics', 'dp-lang' ) ?></option>
									</select>
									<br /><span class="description"><?php _e( 'If you already use Google Analytics for your website, you can track detailed ecommerce information by enabling this setting. Choose whether you are using the new asynchronous or old tracking code. Before Google Analytics can report ecommerce activity for your website, you must enable ecommerce tracking on the profile settings page for your website. Also keep in mind that some gateways do not reliably show the receipt page, so tracking may not be accurate in those cases. It is recommended to use the PayPal gateway for the most accurate data. <a href="http://analytics.blogspot.com/2009/05/how-to-use-ecommerce-tracking-in-google.html" target="_blank">More information &raquo;</a>', 'dp-lang' ) ?></span>
								</td>
							</tr>
							<tr><th colspan="2"><?php _e("Store URL Slugs","dp-lang");?></th></tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Products', 'dp-lang' ) ?></th>
								<td><input type="text" name="dpsc[slugs][products]" value="<?php echo esc_attr($settings['slugs']['products']); ?>" size="20" maxlength="50" />/</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Product Category', 'dp-lang' ) ?></th>
								<td><?php echo esc_attr($settings['slugs']['products']); ?>/<input type="text" name="dpsc[slugs][category]" value="<?php echo esc_attr($settings['slugs']['category']); ?>" size="20" maxlength="50" />/</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Product Tag', 'dp-lang' ) ?></th>
								<td><?php echo esc_attr($settings['slugs']['products']); ?>/<input type="text" name="dpsc[slugs][tag]" value="<?php echo esc_attr($settings['slugs']['tag']); ?>" size="20" maxlength="50" />/</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<p class="submit">
				<input class='button button-primary' type='submit' name='dukapress_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
			</p>
			</form>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(".product-accordion").accordion({
					collapsible: true, heightStyle: "content"
				});
			});
		</script>
		
		<?php
	}
	
	/** 
	 * Email Settings
	 *
	 */
	static function email($settings){
		?>
		<h2><?php _e("Mail Settings","dp-lang");?></h2>
		<div id="dpsc_email">
			<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
				<p class="submit">
					<input class='button button-primary' type='submit' name='dukapress_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
				</p>
				<div class="mail-accordion">
					<h3><?php _e('Order Placed','dp-lang'); ?></h3>
					<div>
						<table width="100%" border="0">
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Admin','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Admin Email To','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_placed][admin][to]" value="<?php echo $settings['mail']['order_placed']['admin']['to']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address of the admin that will receive all notifications of orders placed','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_placed][admin][subject]" value="<?php echo $settings['mail']['order_placed']['admin']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject that will come in the mail of the orders placedto the admin','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][order_placed][admin][body]" class="large-text code"><?php echo $settings['mail']['order_placed']['admin']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the message body the mail of the orders placed to the admin','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%userdetails%</strong>, <strong>%inv%</strong>, <strong>%siteurl%</strong>, <strong>%shop%</strong> ,<strong>%order-details%</strong> ,<strong>%order-log-transaction%</strong> 
										<?php _e("as User Details, Invoice, Site URL, Shop Name, Order Details, Order Log Transaction","dp-lang");?>
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr/></td>
							</tr>
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Customer','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Email From','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_placed][user][from]" value="<?php echo $settings['mail']['order_placed']['user']['from']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will be shown to the customer as the originator of the mails of the orders placed','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_placed][user][subject]" value="<?php echo $settings['mail']['order_placed']['user']['subject']; ?>" class="regular-text ltr" />
									<p class="description">
										<?php _e('This is the subject of the message the customer will receive','dp-lang'); ?>
									</p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][order_placed][user][body]" class="large-text code"><?php echo $settings['mail']['order_placed']['user']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the body of the message the customer will receive','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%fname%</strong>, <strong>%lname%</strong>, <strong>%inv%</strong>, <strong>%shop%</strong>, <strong>%siteurl%</strong>, <strong>%order-log-transaction%</strong> <?php _e("As First Name,  Last Name, Invoice, Shop Name, site URL and Transacrion URL","dp-lang");?>
									</p>
								</td>
							</tr>
						</table>
					</div>
					<h3><?php _e('Order Cancelled','dp-lang'); ?></h3>
					<div>
						<table width="100%" border="0">
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Admin','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Admin Email To','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_cancelled][admin][to]" value="<?php echo $settings['mail']['order_cancelled']['admin']['to']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will receive all notifications of orders cancelled','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_cancelled][admin][subject]" value="<?php echo $settings['mail']['order_cancelled']['admin']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of orders cancelled mail to the admin','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][order_cancelled][admin][body]" class="large-text code"><?php echo $settings['mail']['order_cancelled']['admin']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of orders cancelled mail to the admin','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%fname%</strong>, <strong>%lname%</strong>, <strong>%userdetails%</strong>, <strong>%inv%</strong>, <strong>%status%</strong>, <strong>%digi%</strong>,<strong>%siteurl%</strong>, <strong>%shop%</strong> ,<strong>%order-log-transaction%</strong> <?php _e("First Name,Last Name, User Details, Invoice, Status,Digital File Download URL ,Site URL, Shop Name, Order Log Transaction","dp-lang");?>
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr/></td>
							</tr>
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Customer','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Email From','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_cancelled][user][from]" value="<?php echo $settings['mail']['order_cancelled']['user']['from']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will be shown to the customer as the originator of the mails of the orders cancelled','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][order_cancelled][user][subject]" value="<?php echo $settings['mail']['order_cancelled']['user']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of orders cancelled mail to the customer','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][order_cancelled][user][body]" class="large-text code"><?php echo $settings['mail']['order_cancelled']['user']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of orders cancelled mail to the customer','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%fname%</strong>, <strong>%lanme%</strong>, <strong>%inv%</strong>,<strong>%status%</strong>, <strong>%shop%</strong>, <strong>%siteurl%</strong> <?php _e("As Billing First Name, Billing Last Name, Invoice,Status, Shop Name and site URL","dp-lang");?>
									</p>
								</td>
							</tr>
						</table>
					</div>
					<h3><?php _e('User Registered','dp-lang'); ?></h3>
					<div>
						<table width="100%" border="0">
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Admin','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Admin Email To','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][user_registered][admin][to]" value="<?php echo $settings['mail']['user_registered']['admin']['to']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will receive all notifications of new customers','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][user_registered][admin][subject]" value="<?php echo $settings['mail']['user_registered']['admin']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of the new customers mail to the admin','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][user_registered][admin][body]" class="large-text code"><?php echo $settings['mail']['user_registered']['admin']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of the new customers mail to the admin','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%uname%</strong>, <strong>%email%</strong>, <strong>%shop%</strong>  <?php _e("as User Name, email, and ShopName","dp-lang");?>
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr/></td>
							</tr>
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Customer','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Email From','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][user_registered][user][from]" value="<?php echo $settings['mail']['user_registered']['user']['from']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will be shown to the customer as the originator of the mails of the new account','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][user_registered][user][subject]" value="<?php echo $settings['mail']['user_registered']['user']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of new account mail to the customer','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][user_registered][user][body]" class="large-text code"><?php echo $settings['mail']['user_registered']['user']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of new account mail to the customer','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%uname%</strong>, <strong>%pass%</strong>, <strong>%email%</strong>, <strong>%login%</strong>, <strong>%shop%</strong>  <?php _e("as User Name, Password, email, Login URL, and ShopName","dp-lang");?>
									</p>
								</td>
							</tr>
						</table>
					</div>
					<h3><?php _e('Order Status','dp-lang'); ?></h3>
					<div>
						<table width="100%" border="0">
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Admin','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Admin Email To','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][payments][admin][to]" value="<?php echo $settings['mail']['payments']['admin']['to']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will receive all payment notifications','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][payments][admin][subject]" value="<?php echo $settings['mail']['payments']['admin']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of the new payments mail to the admin','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][payments][admin][body]" class="large-text code"><?php echo $settings['mail']['payments']['admin']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of the new payments mail to the admin','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%fname%</strong>, <strong>%email%</strong>, <strong>%inv%</strong>, <strong>%status%</strong>, <strong>%digi%</strong>, <strong>%shop%</strong>, <?php _e("as Payers First Name, Payers email, Invoice, Payment status, Digital Products, and Shop Name","dp-lang");?>
									</p>
								</td>
							</tr>
							<tr>
								<td colspan="2"><hr/></td>
							</tr>
							<tr>
								<td colspan="2"><h3 class="title"><?php _e('To Customer','dp-lang'); ?></h3></td>
							</tr>
							<tr>
								<td><?php _e('Email From','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][payments][user][from]" value="<?php echo $settings['mail']['payments']['user']['from']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will be shown to the customer as the originator of the mails of the payment status','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][payments][user][subject]" value="<?php echo $settings['mail']['payments']['user']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of payment status mail to the customer','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('User Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][payments][user][body]" class="large-text code"><?php echo $settings['mail']['payments']['user']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of payment status mail to the customer','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%fname%</strong>, <strong>%lname%</strong>,  <strong>%email%</strong>, <strong>%inv%</strong>, <strong>%status%</strong>, <strong>%digi%</strong>, <strong>%shop%</strong>, <?php _e("as Payers First Name, Payers Last Name,  Payers email, Invoice, Payment Status, Digital Products, and Shop Name","dp-lang");?>
									</p>
								</td>
							</tr>
						</table>
					</div>
					<h3><?php _e('Inventory','dp-lang'); ?></h3>
					<div>
						<table width="100%" border="0">
							<tr>
								<td><?php _e('Admin Email To','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][inventory][admin][to]" value="<?php echo $settings['mail']['inventory']['admin']['to']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will receive all inventory notifications','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][inventory][admin][subject]" value="<?php echo $settings['mail']['inventory']['admin']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of the inventory mail to the admin','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][inventory][admin][body]" class="large-text code"><?php echo $settings['mail']['inventory']['admin']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of the inventory mail to the admin','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%pno%</strong>, <strong>%pname%</strong>, <strong>%stock%</strong>, <strong>%footer%</strong>, <?php _e("as Product No., Product Name, Currently in Stock Quantity, and footer","dp-lang");?>
									</p>
								</td>
							</tr>
						</table>
					</div>
					<h3><?php _e('Enquiry','dp-lang'); ?></h3>
					<div>
						<table width="100%" border="0">
							<tr>
								<td><?php _e('Admin Email To','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][enquiry][admin][to]" value="<?php echo $settings['mail']['enquiry']['admin']['to']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the email address that will receive all enquiry mails','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Subject','dp-lang'); ?></td>
								<td>
									<input type="text" name="dpsc[mail][enquiry][admin][subject]" value="<?php echo $settings['mail']['enquiry']['admin']['subject']; ?>" class="regular-text ltr" />
									<p class="description"><?php _e('This is the subject of the enquiry mail to the admin','dp-lang'); ?></p>
								</td>
							</tr>
							<tr>
								<td><?php _e('Admin Email Body','dp-lang'); ?></td>
								<td>
									<textarea rows="4" name="dpsc[mail][enquiry][admin][body]" class="large-text code"><?php echo $settings['mail']['enquiry']['admin']['body']; ?></textarea>
									<p class="description">
										<?php _e('This is the content of the enquiry mail to the admin','dp-lang'); ?><br/>
										<?php _e("Use","dp-lang");?> <strong>%from%</strong>, <strong>%from_email%</strong>, <strong>%enq_subject%</strong>, <strong>%details%</strong>, <strong>%custom_message%</strong>, <?php _e("as Enquirers Name, Enquirers email, Enquiry Subject, Enquiry Details, and Enquiry custom message","dp-lang");?>
									</p>
								</td>
							</tr>
						</table>
					</div>
				</div>
				<p class="submit">
					<input class='button button-primary' type='submit' name='dukapress_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
				</p>
			</form>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery(".mail-accordion").accordion({
					collapsible: true, active: false,heightStyle: "content"
				});
			});
		</script>
		<?php
	}
	
	/** 
	 * Checkout page
	 */
	static function checkout(){
		global $dukapress;
		if ( isset( $_POST[ 'dpsc_checkout_settings' ] ) ) {
			update_option( 'dukapress_checkout_settings', $_POST[ 'dpsc' ]);
			echo '<div class="updated fade"><p>' . __( 'Checkout settings saved.', 'dp-lang' ) . '</p></div>';
		}
		$checkout_settings = get_option( 'dukapress_checkout_settings' );
		$count = 0;
		?>
		<h2><?php _e("Checkout Settings","dp-lang");?></h2>
		<div id="dpsc_checkout">
			<div class="dpsc_checkout_fields">
				<form method="POST" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
					<?php wp_nonce_field('dukapress_checkout_settings','dukapress_checkout_noncename'); ?>
					<input type="hidden" name="sort_order" id="sort_order" value=""/>
					<p class="submit">
						<a class="button button-secondary" onclick="add_checkout_element()"><?php _e('Add Checkout Field','dp-lang'); ?></a>&nbsp;&nbsp;&nbsp;<input class='button button-primary' type='submit' name='dpsc_checkout_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
					</p>
					<table width="100%" border="0" class="widefat">
						<thead>
							<tr>
								<th width="1%" align="left" scope="col"></th>
								<th width="20%" align="left" scope="col"><?php _e('Name','dp-lang'); ?></th>
								<th width="10%" align="left" scope="col"><?php _e('Type','dp-lang'); ?></th>
								<th width="10%" align="left" scope="col"><?php _e('Unique Name','dp-lang'); ?></th>
								<th width="39%" align="left" scope="col"><?php _e('Initial Value','dp-lang'); ?></th>
								<th width="10%" align="left" scope="col"><?php _e('Mandatory','dp-lang'); ?></th>
								<th width="10%" align="left" scope="col"><?php _e('Visible','dp-lang'); ?></th>
								<th width="1%" align="left" scope="col"></th>
							</tr>
						</thead>
						
						<tfoot>
							<tr>
								<th align="left" scope="col"></th>
								<th align="left" scope="col"><?php _e('Name','dp-lang'); ?></th>
								<th align="left" scope="col"><?php _e('Type','dp-lang'); ?></th>
								<th align="left" scope="col"><?php _e('Unique Name','dp-lang'); ?></th>
								<th align="left" scope="col"><?php _e('Initial Value','dp-lang'); ?></th>
								<th align="left" scope="col"><?php _e('Mandatory','dp-lang'); ?></th>
								<th align="left" scope="col"><?php _e('Visible','dp-lang'); ?></th>
								<th align="left" scope="col"></th>
							</tr>
						</tfoot>
						<tbody class='sort-checkout ui-sortable'>
							<?php
							if (is_array($checkout_settings) && count($checkout_settings) > 0) {
								
								foreach ($checkout_settings as $checkout_row) {
									?>
									<tr id="<?php echo $checkout_row['name']; ?>">
										<td><span style="cursor:move" class="dashicons dashicons-sort"></span></td>
										<?php if(!isset($checkout_row['delete'])) { ?>
										<td><input type="text" name="dpsc[<?php echo $count; ?>][name]" value="<?php echo $checkout_row['name']; ?>"/></td>
										<td>
											<select name="dpsc[<?php echo $count; ?>][type]">
												<?php
													foreach ($dukapress->form_elements as $forms => $form) {
														$cont_selected = '';
														if ($checkout_row['type'] === $form) {
															$cont_selected = 'selected="selected"';
														}
														?>
														<option value="<?php echo $form; ?>" <?php echo $cont_selected; ?> ><?php _e($forms); ?></option>
														<?php
													}
												?>
											</select>
										</td>
										<td><input type="text" name="dpsc[<?php echo $count; ?>][uname]" value="<?php echo $checkout_row['uname']; ?>" /></td>
										<td><input type="text" name="dpsc[<?php echo $count; ?>][initial]" value="<?php echo @$checkout_row['initial']; ?>" style="width:100%"/></td>
										<?php } else { ?>
										<td><input type="hidden" name="dpsc[<?php echo $count; ?>][name]" value="<?php echo $checkout_row['name']; ?>" /><?php _e($checkout_row['name'],'dp-lang'); ?></td>
										<td><input type="hidden" name="dpsc[<?php echo $count; ?>][type]" value="<?php echo $checkout_row['type']; ?>" /><?php _e($checkout_row['type'],'dp-lang'); ?></td>
										<td><input type="hidden" name="dpsc[<?php echo $count; ?>][uname]" value="<?php echo $checkout_row['uname']; ?>" /><?php _e($checkout_row['uname']); ?></td>
										<td><input type="hidden" name="dpsc[<?php echo $count; ?>][initial]" value="<?php echo $checkout_row['initial']; ?>" /><?php _e($checkout_row['initial'],'dp-lang'); ?></td>
										<?php } ?>
										<td><input type="checkbox" value="checked" name="dpsc[<?php echo $count; ?>][mandatory]" <?php echo ($checkout_row['mandatory'] == 'checked') ? "checked='checked'": ""; ?> /></td>
										<td><input type="checkbox" value="checked" name="dpsc[<?php echo $count; ?>][visible]" <?php echo ($checkout_row['visible'] == 'checked') ? "checked='checked'": ""; ?> /></td>
										<?php if(!isset($checkout_row['delete'])) {?>
											<td><span style="cursor:pointer" class="dashicons dashicons-no-alt" onclick="delete_checkout_element(this)"></span></td>
										<?php } else { ?>
											<td><input type="hidden" name="dpsc[<?php echo $count; ?>][delete]" value="false" /></td>
										<?php } ?>
									</tr>
									<?php
									$count++;
								}	
							}
							?>
							
						</tbody>
					</table>
					<p class="submit">
						<a class="button button-secondary" onclick="add_checkout_element()"><?php _e('Add Checkout Field','dp-lang'); ?></a>&nbsp;&nbsp;&nbsp;<input class='button button-primary' type='submit' name='dpsc_checkout_settings' value='<?php _e('Save Options','dp-lang'); ?>'/><br/>
					</p>
				</form>
				<input type="hidden" id="dpsc_item_count" value="<?php echo $count; ?>" />
				<div style="display:none" class="dpsc_append_row">
					<script type="dpsc_checkout_row">
						<tr class="ui-sortable-handle">
							<td><span style="cursor:move" class="dashicons dashicons-sort"></span></td>
							<td><input type="text" name="dpsc[CURRENTCOUNT][name]" value=""/></td>
							<td>
								<select name="dpsc[CURRENTCOUNT][type]">
									<?php
										foreach ($dukapress->form_elements as $forms => $form) {
											?>
											<option value="<?php echo $form; ?>"><?php _e($forms); ?></option>
											<?php
										}
									?>
								</select>
							</td>
							<td><input type="text" name="dpsc[CURRENTCOUNT][uname]" value="" /></td>
							<td><input type="text" name="dpsc[CURRENTCOUNT][initial]" value="" style="width:100%"/></td>
							<td><input type="checkbox" value="checked" name="dpsc[CURRENTCOUNT][manadatory]" /></td>
							<td><input type="checkbox" value="checked" name="dpsc[CURRENTCOUNT][visible]" /></td>
							<td><span style="cursor:pointer" class="dashicons dashicons-no-alt" onclick="delete_checkout_element(this)"></span></td>
						</tr>
					</script>
				</div>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						var idsInOrder = [];
						jQuery("tbody.sort-checkout").sortable({
							update: function( event, ui ) {
								idsInOrder = [];
								jQuery('tbody.sort-checkout tr').each(function() {
									idsInOrder.push(jQuery(this).attr('id'));
								});
								jQuery('#sort_order').val(idsInOrder);
							}
						});
					});
				</script>
			</div>
		</div>
		<?php
	}
}	
?>