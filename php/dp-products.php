<?php
/*
 * This file handles the functions related to products and shortcode for product display and grid display
 */


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
                $custom_fields_output['price'] = '<p class="dpsc_price">' . __('Price:',"dp-lang") . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span class="was">' . $all_custom_fields['price'][0] . '</span>&nbsp;<span class="is">' . $all_custom_fields['new_price'][0] . '</span></p>';
            }
            else {
                $product_price = $all_custom_fields['price'][0];
                $custom_fields_output['price'] = '<p class="dpsc_price">' . __('Price:',"dp-lang") . ' ' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span class="is">' . $all_custom_fields['price'][0] . '</span></p>';
            }
        }
        $item_weight = '';
        if (isset ($all_custom_fields['item_weight'][0])) {
            $item_weight = '<input type="hidden" name="product_weight" value="' . $all_custom_fields['item_weight'][0] .  '">';
        }

        $custom_fields_output['end'] = $item_weight . '
                                        <input type="hidden" name="action" value="dpsc_add_to_cart"/><div class="dpsc_update_icon" id="dpsc_update_icon_' . $product_id . '" style="display:none;"><img src="' . DP_PLUGIN_URL . '/images/update.gif"></div>
                                        <input type="hidden" name="product_id" value="' . $product_id . '"/>
                                        <input type="hidden" name="product" value="' . get_the_title($product_id) . '"/>
                                        <input id="dpsc_actual_price_' . $product_id . '" type="hidden" name="price" value="'.$product_price.'"/>
                                    </form>';

        if (isset ($all_custom_fields['dropdown_option'][0])) {
            $dropdown_content .= '<div class="dpsc_variation_main">';
            $get_vars = explode('||',$all_custom_fields['dropdown_option'][0]);
            $div_var_id = 0;
            foreach ($get_vars as $get_var) {
                $pro_vars = explode('|', $get_var);
                $vari_name = $pro_vars[0];
                $dropdown_content .= '<div id="dpsc_variation_'.$div_var_id.'" class="dpsc_variation"><span class="dpsc_variation" for="var">' . __('Select') . ' '.__($vari_name).' </span>';
                $pro_vars = array_slice($pro_vars, 1);
                $dropdown_content .= '<select id="dpsc_variation_'.$div_var_id.'_dpscVariant" name="var[]" onchange="getFinalPrice_' . $product_id . '();">';
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
                function getFinalPrice_' . $product_id . '(){
                    try{
                        var drpdown;
                        var drpdownID;
                        var selIndex;
                        var selText;
                        var costDiff;
                        //--
                        var SalePriceLabel1=document.getElementById("dpsc_actual_price_' . $product_id . '");
                        var initialCost=SalePriceLabel1.value;
                        var SalePriceLabel=document.getElementById("dpsc_new_product_price_' . $product_id . '");
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
                                                    getFinalPrice_' . $product_id . '();
                                                </script>';
                $custom_fields_output['final_price'] = '<p class="dpsc_price">Price: ' . $dp_shopping_cart_settings['dp_currency_symbol'] . '<span id="dpsc_new_product_price_' . $product_id . '">' . $product_price . '</span></p>';
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

                case 'jqzoom_effect':
                    $image_content = dp_pnj_jqzoom_effect($attachment_images, $product_id);
                    break;

                default:
                    break;
            }
            $custom_fields_output['image_output'] = $image_content;
        }

        $in_stock = '';
        $available_in_stock = TRUE;
        if($dp_shopping_cart_settings['dp_shop_inventory_active'] === 'yes' && isset($all_custom_fields['currently_in_stock'][0])) {
            if($dp_shopping_cart_settings['dp_shop_inventory_stocks'] === 'yes' && $all_custom_fields['currently_in_stock'][0] > 0) {
                $custom_fields_output['end'] = '<input type="hidden" name="max_quantity" value="' . $all_custom_fields['currently_in_stock'][0] . '"/>' . $custom_fields_output['end'];
                $in_stock = '<span class="dpsc_in_stock">' . __('Currently in Stock',"dp-lang") . '</span>';
            }
            elseif ($dp_shopping_cart_settings['dp_shop_inventory_soldout'] === 'yes' && $all_custom_fields['currently_in_stock'][0] < 1) {
                $in_stock = '<span class="dpsc_in_stock_sold_out">' . __('Out of Stock',"dp-lang") . '</span>';
                $available_in_stock = FALSE;
            }
        }
        $custom_fields_output['in_stock'] = $in_stock;

        $value_atc = __('Add to Cart',"dp-lang");
        if($dp_shopping_cart_settings['dp_shop_mode'] === 'inquiry') {
            $value_atc = __('Inquire',"dp-lang");
        }
        $disabled_add_to_cart = '';
        if (!$available_in_stock) {
            $disabled_add_to_cart = 'disabled="disabled"';
        }
        $custom_fields_output['add_to_cart'] = '<input ' . $disabled_add_to_cart . ' type="submit" class="dpsc_submit_button" id="dpsc_submit_button_' . $product_id . '" name="dpsc_add_to_cart" value="' . $value_atc . '" />';
        return $custom_fields_output;
    }
    return FALSE;
}

/**
 * The functions below handle the image effect output
 *
 */
function dp_pnj_mz_effect($attachment_images, $product_id) {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" id="' . $product_id . '" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=' . $dp_shopping_cart_settings['t_w'] . '&h=' . $dp_shopping_cart_settings['t_h'] . '&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image main_' . $product_id . '">';
    $output .= '<a href="' . $main_image . '" class="MagicZoom" id="zoom1" rel="show-title: false; zoom-fade: true; zoom-position: inner; thumb-change: mouseover"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=' . $dp_shopping_cart_settings['m_w'] . '&h=' . $dp_shopping_cart_settings['m_h'] . '&zc=1" ></a>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function dp_pnj_jqzoom_effect($attachment_images, $product_id) {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" id="' . $product_id . '" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=' . $dp_shopping_cart_settings['t_w'] . '&h=' . $dp_shopping_cart_settings['t_h'] . '&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image main_' . $product_id . '">';
    $output .= '<a href="' . $main_image . '" title="image" class="dp_jqzoom" ><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=' . $dp_shopping_cart_settings['m_w'] . '&h=' . $dp_shopping_cart_settings['m_h'] . '&zc=1" ></a>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function dp_pnj_lightbox_effect($attachment_images, $product_id) {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab fancybox" id="' . $product_id . '" rel="imgGroup" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=' . $dp_shopping_cart_settings['t_w'] . '&h=' . $dp_shopping_cart_settings['t_h'] . '&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image main_' . $product_id . '">';
    $output .= '<a href="' . $main_image . '" class="fancybox theProdMedia theProdMedia_alt"  rel="imgGroup"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=' . $dp_shopping_cart_settings['m_w'] . '&h=' . $dp_shopping_cart_settings['m_h'] . '&zc=1" ></a>';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

function dp_pnj_no_effect($attachment_images, $product_id) {
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" id="' . $product_id . '" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=' . $dp_shopping_cart_settings['t_w'] . '&h=' . $dp_shopping_cart_settings['t_h'] . '&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image main_' . $product_id . '">';
    $output .= '<img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=' . $dp_shopping_cart_settings['m_w'] . '&h=' . $dp_shopping_cart_settings['m_h'] . '&zc=1" >';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
}

/**
 * Shortcode to display product
 *
 */
add_shortcode('dpsc_display_product', 'dpsc_pnj_display_product_name');
function dpsc_pnj_display_product_name($atts, $content = null) {
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
    $dp_shopping_cart_settings = get_option('dp_shopping_cart_settings');
    extract(shortcode_atts( array(
            'category' => '1',
            'total' => '12',
            'column' => '3',
            'per_page' => '',
            'type' => 'post',
            'order' => 'DESC'
            ), $atts));

    if (!empty($per_page)) {
        $pagenum = isset($_GET['dpage']) ? $_GET['dpage'] : 1;
        $count = count(get_posts('numberposts=' . $total . '&post_type=' . $type . '&meta_key=price&category=' . $category));
        $page_links = paginate_links( array(
            'base' => add_query_arg( 'dpage', '%#%' ),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => ceil($count / $per_page),
            'current' => $pagenum
        ));
        $post_offset = ($pagenum-1) * $per_page;
        $offset = '&offset='.$post_offset;
        $page_links = '<div class="dpsc_grid_pagination">' . $page_links . '</div>';
    }
    else {
        $per_page = $total;
        $offset = '';
        $page_links = '';
    }
    if ($order != 'rand') {
        $order_string = 'orderby=ID&order=' . $order . '&';
    }
    else {
        $order_string = 'orderby=rand&';
    }
    $products = get_posts($order_string . 'numberposts=' . $per_page . '&post_type=' . $type . '&meta_key=price&category=' . $category . $offset);
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
                if ($main_image != '') {
                    $content .= '<a href="' . $prod_permalink . '" title="' .$product->post_title . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=' . $dp_shopping_cart_settings['g_w'] . '&h=' . $dp_shopping_cart_settings['g_h'] . '&zc=1" ></a>';
                }
                $content .= '</div>';
                $content .= '<div class="dpsc_grid_product_detail">';
                $content .= '<p class="title"><a href="' . $prod_permalink . '" title="' .$product->post_title . '">' . __($product->post_title) . '</a></p>';
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
        $content .= '<div class="clear"></div>' . $page_links . '<div class="clear"></div>';
        $content .= '</div>';
        $content .= '<div class="clear"></div>';
    }
    return $content;
}

/**
 * Add Meta Box for ease
 *
 */
add_action('submitpost_box', 'dp_add_meta_box');
add_action('submitpage_box', 'dp_add_meta_box');

function dp_add_meta_box() {
    add_meta_box('dp_gui_box', 'DukaPress Product options', 'dp_rm_content_visibility_meta_box', 'post', 'side', 'high');
    add_meta_box('dp_gui_box', 'DukaPress Product options', 'dp_rm_content_visibility_meta_box', 'page', 'side', 'high');
    add_meta_box('dp_gui_box', 'DukaPress Product options', 'dp_rm_content_visibility_meta_box', 'duka', 'side', 'high');
}

function dp_rm_content_visibility_meta_box() {
    $post_id = $_GET['post'];
    $content_price = get_post_meta($post_id, 'price', true);
    $new_price = get_post_meta($post_id, 'new_price', true);
    $content_stock = get_post_meta($post_id, 'currently_in_stock', true);
    $content_weight = get_post_meta($post_id, 'item_weight', true);
    $content_file = get_post_meta($post_id, 'digital_file', true);
    ?>
    <table>
        <tr><td><label for="price"><b><?php _e('Price:',"dp-lang"); ?></b></label></td><td><input id="price" type="text" name="price" value="<?php echo $content_price; ?>" /></td></tr>
        <tr><td><label for="new_price"><b><?php _e('New Price:',"dp-lang");?></b></label></td><td><input id="new_price" type="text" name="new_price" value="<?php echo $new_price; ?>" /></td></tr>
        <tr><td><label for="currently_in_stock"><b><?php _e('Currently In Stock:',"dp-lang");?></b></label></td><td><input id="currently_in_stock" type="text" name="currently_in_stock" value="<?php echo $content_stock; ?>" /></td></tr>
        <tr><td><label for="item_weight"><b><?php _e('Item Weight:',"dp-lang");?></b></label></td><td><input id="item_weight" type="text" name="item_weight" value="<?php echo $content_weight; ?>" /> (in grams)</td></tr>
        <tr><td><label for="digital_file"><b><?php _e('Digital File:',"dp-lang");?></b></label></td><td><input id="digital_file" type="text" name="digital_file" value="<?php echo $content_file; ?>" /></td></tr>
    </table>

    <b><?php _e('Dropdown Options',"dp-lang");?></b>
     <div id="result">
        <?php echo dp_get_dropdown_option_to_display($post_id); ?>
     </div>

    <div id="mainField" style="clear:both;">
        <p>
            <label for="optionname"><?php _e('Option Name',"dp-lang");?></label>
            <input id="optionname" name="optionname" size="15" />
        </p>
        <p>
            <label for="vname1"><?php _e('Variation Name',"dp-lang");?></label>
            <input id="vname1" name="vname1" size="15" />
        </p>
        <p>
            <label for="vprice1"><?php _e('Variation Price',"dp-lang");?></label>
            <input id="vprice1" name="vprice1" size="15" />
        </p>
        <div id="dp_var_fields"></div>
    </div>
    <p>
        <input type="button" id="dp_addVariation" value="+"/>
        <input type="hidden" name="varitaionnumber" id="varitaionnumber" value="1" />
        <input type="button" id="dp_save" value="Save"/>
    </p>

    <?php
}

/**
 * This function displays the varitions.
 *
 */
function dp_get_dropdown_option_to_display($post_id) {
    $content_opname = get_post_meta($post_id, 'dropdown_option', true);
    $show_state_result = '';

    if ($content_opname) {

        $optionnames = explode("||", $content_opname);

        foreach ($optionnames as $optionname) {
            $j++;
            $optionname1 = explode("|", $optionname);
            $show_state_result.=' <div style="border:1px solid;clear:both;width:250px;word-wrap: break-word">';
            $show_state_result.= '<p><b>' . ($optionname1[0]) . '</b></p>';
            for ($i = 1; $optionname1[$i]; $i++) {
                $show_state_result.='<div style="float:left;width:200px;clear:both">';
                $optionname2 = explode(";", $optionname1[$i]);
                foreach ($optionname2 as $value) {
                    $show_state_result.= '<div style="width:90px;float:left">' . $value . '</div>';
                }
                $show_state_result.='</div>';
            }

            $show_state_result.='
                    <div id="dp_deletestring"><a href="#" id="' . $j . '">Delete</a>
                        <input id="delete' . $j . '" name="delete' . $j . '" type="hidden" value="' . $optionname . '" />
                      </div>
                    <div style="clear:both"></div></div>';
        }
    }
    return $show_state_result;
}

/**
 * This function saves the meta box details.
 *
 */
add_action('save_post', 'dp_save_meta_box');

function dp_save_meta_box($post_id) {
// verify if this is an auto save routine.
    $content_counter = 1;
    //$content_opname='';
    $varition_type = '';

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return $post_id;

    // Check permissions
    if ('page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id))
            return $post_id;
    }else {
        if (!current_user_can('edit_post', $post_id))
            return $post_id;
    }

    // for price
    if (NULL == $_POST['price']) {
        //do nothing
    } else {
        $content_price = $_POST['price'];

        update_post_meta($post_id, 'price', $content_price);
    }

    // for new price
     if (NULL == $_POST['new_price']) {
        //do nothing
    } else {
        $content_price = $_POST['new_price'];

        update_post_meta($post_id, 'new_price', $content_price);
    }

    // for stocks
    if (NULL == $_POST['currently_in_stock']) {
        //do nothing
    } else {
        $content_stock = $_POST['currently_in_stock'];
        update_post_meta($post_id, 'currently_in_stock', $content_stock);
    }

    // for weights
    if (NULL == $_POST['item_weight']) {
        //do nothing
    } else {
        $content_weight = $_POST['item_weight'];
        update_post_meta($post_id, 'item_weight', $content_weight);
    }

    //for file
    if (NULL == $_POST['digital_file']) {
        //do nothing
    } else {
        $content_file = $_POST['digital_file'];
        update_post_meta($post_id, 'digital_file', $content_file);
    }

    // for option name
    if (NULL == $_POST['varitaionnumber']) {
        //do nothing
    } else {
        $content_counter = $_POST['varitaionnumber'];
        update_post_meta($post_id, '_Content_Counter', $content_counter);
    }
}

/**
 * This function saves variations.
 *
 */
add_action('wp_ajax_save_variationdata', 'dp_rm_varition_save_data');

function dp_rm_varition_save_data() {

    if ($_POST && $_POST['action'] == "save_variationdata") {
        $counter = $_POST['counter'];
        $postid = $_POST['postid'];
        $prev_option = get_post_meta($postid, 'dropdown_option', true);

// making || in each option name

        if ($prev_option) {
            $prev_option_new = $prev_option;
            $varition_type .= $prev_option_new . '||';
        }

// check for the validation that, option name should not be null
        if($_POST['optionname'])
        {
            $varition_type.=$_POST['optionname'] . '|';
            for ($i = 1; $i <= $counter; $i++)
            {
                   if($_POST['vname' . $i])
                       {
                          if($_POST['vprice' . $i]==null)
                              {
                                 $varition_type.=$_POST['vname' . $i] . ';' . '0' . '|';
                              }
                              else
                              {
                               $varition_type.=$_POST['vname' . $i] . ';' . $_POST['vprice' . $i] . '|';
                              }

                        }
            }
            $varition_type = substr($varition_type, 0, ($len - 1));
            update_post_meta($postid, 'dropdown_option', $varition_type);
        }
        else
        {
            echo "Enter the variation data";
        }
        $prev_option = get_post_meta($postid, 'dropdown_option'); ?>
        <div style="clear:both;width:250px;word-wrap: break-word">
            <!--for showing the data -->
              <?php echo dp_get_dropdown_option_to_display($postid); ?>
        </div>
        <?php  die();
        }
}

/**
 * This function deletes variations.
 *
 */
add_action('wp_ajax_delete_variationdata', 'dp_rm_varition_delete_data');
function dp_rm_varition_delete_data() {
     if($_POST && $_POST['action'] == "delete_variationdata")
     {
        $postid = $_POST['postid'];
        $substr=  $_POST['name'];
       // echo $substr;

        $delete_prev_option = get_post_meta($postid, 'dropdown_option', true);
        $result_string=str_replace($substr,'',$delete_prev_option);
        $result_string=str_replace("||||","||",$result_string);
        if($result_string=="||")
        {
            $result_string='';
        }
        if ($result_string === '') {
            delete_post_meta($postid, 'dropdown_option');
        }
        else {
            update_post_meta($postid, 'dropdown_option', $result_string);
        }
        echo dp_get_dropdown_option_to_display($postid);
     }
     die();
}

add_action( 'init', 'dp_create_post_type' );

function dp_create_post_type() {
    register_post_type('duka', array(
	'labels' => array(
            'name' => __( 'Products',"dp-lang" ),
            'singular_name' => __( 'Product',"dp-lang" ),
            'add_new' => __( 'Add New Product',"dp-lang" ),
            'add_new_item' => __( 'Add New Product',"dp-lang" ),
            'edit' => __( 'Edit' ,"dp-lang"),
            'edit_item' => __( 'Edit Product',"dp-lang" ),
            'new_item' => __( 'New Product' ,"dp-lang"),
            'view' => __( 'View Product',"dp-lang" ),
            'view_item' => __( 'View Product' ,"dp-lang"),
            'search_items' => __( 'Search Products' ,"dp-lang"),
            'not_found' => __( 'No products found' ,"dp-lang"),
            'not_found_in_trash' => __( 'No products found in Trash',"dp-lang" )
        ),
        'description' => __('Products for use with DukaPress',"dp-lang"),
	'public' => true,
	'show_ui' => true,
	'capability_type' => 'post',
        'taxonomies' => array( 'category', 'post_tag'),
        'menu_position' => 30,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'query_var' => true,
	'hierarchical' => false,
        'menu_icon' => DP_PLUGIN_URL . '/images/dp_icon.png',
	'rewrite' => array('slug' => 'products', 'with_front' => false),
	'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'posts', 'revisions', 'trackbacks' )
    ));
}
?>