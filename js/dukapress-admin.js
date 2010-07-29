jQuery(document).ready(function () {
    jQuery('#dp_discount_submit').click(function(){
        var dpsc_discount_code = jQuery("#discount_code").val();
        var dpsc_discount_amount = jQuery("#discount_amount").val();
        var check_one_time = jQuery('input[name=discount_one_time]').is(':checked');
        if (check_one_time) {
            var dpsc_discount_one_time = jQuery("#discount_one_time").val();
        }
        else {
            var dpsc_discount_one_time = 'false';
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: 'action=save_dpsc_discount_code&ajax=true&dpsc_discount_code=' + dpsc_discount_code + '&dpsc_discount_amount=' + dpsc_discount_amount + '&dpsc_discount_one_time=' + dpsc_discount_one_time,
            success: function(msg){
                jQuery("#discount_code_confirmation").css('display','block').html('Discount Code Successfully Added.');
                jQuery("#discount_code").val('');
                jQuery("#discount_amount").val('');
                jQuery("#discount_one_time").val('');
                jQuery("div#discount_code_layout").html(msg);
            }
        });
        return false;
    });

    jQuery('span.dpsc_delete_discount_code').click(function(){
        var dpsc_delete_discount_code_id = jQuery(this).attr("id");
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: 'action=dpsc_delete_discount_code&id=' + dpsc_delete_discount_code_id + '&ajax=true',
            success:function(msg){
                jQuery("div#discount_code_layout").html(msg);
            }
        });
    });
});