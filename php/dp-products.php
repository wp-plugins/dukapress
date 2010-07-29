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
        $available_in_stock = TRUE;
        if($dp_shopping_cart_settings['dp_shop_inventory_active'] === 'yes' && isset($all_custom_fields['currently_in_stock'][0])) {
            if($dp_shopping_cart_settings['dp_shop_inventory_stocks'] === 'yes' && $all_custom_fields['currently_in_stock'][0] > 0) {
                $custom_fields_output['end'] = '<input type="hidden" name="max_quantity" value="' . $all_custom_fields['currently_in_stock'][0] . '"/>' . $custom_fields_output['end'];
                $in_stock = '<span class="dpsc_in_stock">Currently in Stock</span>';
            }
            elseif ($dp_shopping_cart_settings['dp_shop_inventory_soldout'] === 'yes' && $all_custom_fields['currently_in_stock'][0] < 1) {
                $in_stock = '<span class="dpsc_in_stock_sold_out">Out of Stock</span>';
                $available_in_stock = FALSE;
            }
        }
        $custom_fields_output['in_stock'] = $in_stock;

        $value_atc = 'Add to Cart';
        if($dp_shopping_cart_settings['dp_shop_mode'] === 'inquiry') {
            $value_atc = 'Inquire';
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
    $output = '<div class="dpsc_image_section">';
    $output .= '<div class="dpsc_image_tab">';
    $output .= '<ul class="dpsc_tabs">';
    $count = 0;
    foreach ($attachment_images as $image) {
        if ($count === 0) {
            $main_image = $image->guid;
        }
        $output .= '<li><a class="dpsc_thumb_tab" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image">';
    $output .= '<a href="' . $main_image . '" class="MagicZoom" id="zoom1" rel="show-title: false; zoom-fade: true; zoom-position: inner; thumb-change: mouseover"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" ></a>';
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
        $output .= '<li><a class="dpsc_thumb_tab" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image">';
    $output .= '<a href="' . $main_image . '" class="MagicZoom MagicThumb" id="zoom1" rel="show-title: false; zoom-fade: true; zoom-position: inner; thumb-change: mouseover"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" ></a>';
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
        $output .= '<li><a class="dpsc_thumb_tab fancybox" id="' . $product_id . '" rel="imgGroup" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image main_' . $product_id . '">';
    $output .= '<a href="' . $main_image . '" class="fancybox theProdMedia theProdMedia_alt"  rel="imgGroup"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" ></a>';
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
        $output .= '<li><a class="dpsc_thumb_tab" href="' . $image->guid . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $image->guid . '&w=50&h=63&zc=1" ></a></li>';
        $count++;
    }
    $output .= '</ul>';
    $output .= '</div>';
    $output .= '<div class="dpsc_main_image">';
    $output .= '<img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=310&h=383&zc=1" >';
    $output .= '</div>';
    $output .= '</div>';
    return $output;
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
            'column' => '3',
            'per_page' => ''
            ), $atts));

    if (!empty($per_page)) {
        $pagenum = isset($_GET['dpage']) ? $_GET['dpage'] : 1;
        $count = $total;
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
    $products = get_posts('numberposts=' . $per_page . '&category=' . $category . $offset);
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
                $content .= '<a href="' . $prod_permalink . '" title="' .$product->post_title . '"><img src="' . DP_PLUGIN_URL . '/lib/timthumb.php?src=' . $main_image . '&w=160&h=120&zc=1" ></a>';
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
        $content .= '<div class="clear"></div>' . $page_links . '<div class="clear"></div>';
        $content .= '</div>';
        $content .= '<div class="clear"></div>';
    }
    return $content;
}


?>