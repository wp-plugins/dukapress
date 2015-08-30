<?php
class DukaPress_Admin_Pages{
	
	/** 
	 * Email Settings
	 *
	 */
	static function email($settings){
		?>
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