jQuery(document).ready(function () {
    jQuery("form[ID^=dpsc_product_form_]").submit(function() {
        // we cannot submit a file through AJAX, so this needs to return true to submit the form normally if a file formfield is present
        file_upload_elements = jQuery.makeArray(jQuery("input[type=file]", jQuery(this)));
        if(file_upload_elements.length > 0) {
            return true;
        } else {
            form_values = jQuery(this).serialize();
            jQuery.post( "index.php?ajax=true", form_values, function(returned_data) {
                eval(returned_data);
            });
            return false;
        }
    });

    jQuery("form.dpsc_empty_cart span.emptycart a").livequery(function(){
        jQuery(this).click(function() {
            parent_form = jQuery(this).parents("form.dpsc_empty_cart");
            form_values = "ajax=true&";
            form_values += jQuery(parent_form).serialize();
            jQuery.post( 'index.php', form_values, function(returned_data) {
                eval(returned_data);
            });
            return false;
        });
    });

    jQuery("form.product_update").livequery(function(){
        jQuery(this).submit(function() {
            form_values = "ajax=true&";
            form_values += jQuery(this).serialize();
            jQuery.post( 'index.php', form_values, function(returned_data) {
                eval(returned_data);
            });
            return false;
        });
    });

    var validateCode = jQuery('#dpsc_check_discount_code');
    jQuery("#dpsc_validate_discount_code").click(function(){
        var dpsc_code = jQuery("#dpsc_discount_code").val();
        validateCode.removeClass('dpsc_discount_code_invalid').css('display', 'block').html('Checking...');
        var dpsc_discount_code = "ajax=true&";
        dpsc_discount_code += "dpsc_ajax_action=validate_discount_code&dpsc_check_code=" + dpsc_code;
        jQuery.post('index.php', dpsc_discount_code, function(code_returned_data){
            eval(code_returned_data);
        });
        return false;
    });

    jQuery('#dpsc_make_payment').click(function(){
        jQuery('#dpsc_po_error').css('display', 'none');
        var check = jQuery('input[name=dpsc_po]').is(':checked');
        var check_hidden = jQuery('#dpsc_po_hidden').length;
        if (check_hidden == 0) {
            if (check) {
                var payment_option = jQuery('input:radio[name=dpsc_po]:checked').val();
            }
            else {
                jQuery('#dpsc_po_error').css('display', 'block').html('Please select one of the Payment option.');
                return;
            }
        }
        else {
            var payment_option = jQuery('#dpsc_po_hidden').val();
        }
        var payment_discount_check = jQuery('input[name=dpsc_discount_code_payment]').length;
        var payment_discount_string = '';
        if (payment_discount_check != 0) {
            var payment_discount = jQuery('input[name=dpsc_discount_code_payment]').val();
            payment_discount_string = '&discount=' + payment_discount;
        }
        var dpsc_b_fname = jQuery('#b_firstname').val();
        var dpsc_b_lname = jQuery('#b_lastname').val();
        var dpsc_b_country = jQuery('#b_country').val();
        var dpsc_b_address = jQuery('#b_address').val();
        var dpsc_b_city = jQuery('#b_city').val();
        var dpsc_b_state = jQuery('#b_state').val();
        var dpsc_b_zipcode = jQuery('#b_zipcode').val();
        var dpsc_b_email = jQuery('#b_email').val();
        var dpsc_s_fname = jQuery('#s_firstname').val();
        var dpsc_s_lname = jQuery('#s_lastname').val();
        var dpsc_s_country = jQuery('#s_country').val();
        var dpsc_s_address = jQuery('#s_address').val();
        var dpsc_s_city = jQuery('#s_city').val();
        var dpsc_s_state = jQuery('#s_state').val();
        var dpsc_s_zipcode = jQuery('#s_zipcode').val();
        var dpsc_diff_ship = jQuery('input[name=dpsc_contact_different_ship_address]').is(':checked');
        if (dpsc_diff_ship) {
            var diff_ship = 'true';
        }
        else {
            var diff_ship = 'false';
        }
        var dpsc_payment = "ajax=true&dpsc_ajax_action=dpsc_payment_option&payment_selected=" + payment_option + payment_discount_string + '&b_fname=' + dpsc_b_fname + '&b_lname=' + dpsc_b_lname + '&b_country=' + dpsc_b_country + '&b_address=' + dpsc_b_address + '&b_city=' + dpsc_b_city + '&b_state=' + dpsc_b_state + '&b_zip=' + dpsc_b_zipcode + '&b_email=' + dpsc_b_email + '&ship_present=' + diff_ship + '&s_fname=' + dpsc_s_fname + '&s_lname=' + dpsc_s_lname + '&s_country=' + dpsc_s_country + '&s_address=' + dpsc_s_address + '&s_city=' + dpsc_s_city + '&s_state=' + dpsc_s_state + '&s_zip=' + dpsc_s_zipcode;
        jQuery.post('index.php', dpsc_payment, function(data) {
            eval(data);
        });
    });

    jQuery('#dpsc_contact_different_ship_address').click(function(){
       var shipping_check =  jQuery('input[name=dpsc_contact_different_ship_address]').is(':checked');
       if (shipping_check) {
           jQuery('#dpsc_shipping_details').css('display', 'block');
       }
       else {
           jQuery('#dpsc_shipping_details').css('display', 'none');
       }
    });
    
    jQuery(".dpsc_image_section .dpsc_image_tab .dpsc_tabs li:first-child .dpsc_thumb_tab").addClass('current');
    jQuery(".dpsc_image_section .dpsc_image_tab .dpsc_tabs .dpsc_thumb_tab").mouseover(function() {
            jQuery(this).addClass('current').parent().siblings().children().removeClass('current');
            var prod_id = jQuery(this).attr('id');
            var href = jQuery(this).attr('href');
            var new_src = dpsc_js.tim_url + href + '&w=310&h=383&zc=1';
            var check_no_effect = jQuery('.dpsc_main_image a').length;
            if (check_no_effect > 0) {
                jQuery('.main_' + prod_id + ' a').attr('href', href).children().attr('src', new_src);
                jQuery('.MagicZoomBigImageCont img').attr('src', href);
            }
            else {
                jQuery('.main_' + prod_id).children().attr('src', new_src);
            }
    });
});