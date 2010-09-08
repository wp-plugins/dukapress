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

    jQuery('#dp_addVariation').click(function(){
        jQuery(dp_addVariation);
        jQuery('input[name=varitaionnumber]').val(current);

    });

    jQuery('#dp_deletestring a').click(function(){
        var postid=jQuery("#post_ID").val();
        var currentId = jQuery(this).attr('id');
        var mix="#delete"+currentId;
        var substring=jQuery(mix).val();

         jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data:'action=delete_variationdata&name='+substring+'&postid='+postid,
            success:function(msg)
            {
                jQuery("#result").html(msg);
            }
        });


  });

    jQuery('#dp_save').click(function(){
        var i;
        var actionstring='';

        var counter=jQuery("#varitaionnumber").val();
        var postid=jQuery("#post_ID").val();
        var oname=jQuery('#optionname').val();
         actionstring+='optionname='+oname+'&counter='+counter;
        for(i=1;i<=counter;i++)
        {
            var vname='';
            var vprice='';
            vname=jQuery('#vname'+i).val();
            vprice=jQuery('#vprice'+i).val();
            actionstring+=('&vname'+i+'='+vname+'&vprice'+i+'='+vprice);
        }

        //alert(actionstring);
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data:'action=save_variationdata&'+actionstring+'&postid='+postid,
            success:function(msg)
            {
                jQuery('#dp_var_fields').html('');
                jQuery('#optionname').val('');
                jQuery('#vname1').val('');
                jQuery('#vprice1').val('');
                jQuery("#result").html(msg);
            }
        });

    });
});

var current = 1;

function dp_addVariation() {
    current++;
    var strToAdd = '<p><label for="vname'+current+'">Variation Name</label><input id="vname'+current+'" name="vname'+current+'" size="15" />';

    strToAdd += '<p><label for="vprice'+current+'">Variation price</label>\n\
    <input id="vprice'+current+'" name="vprice'+current+'" size="15" /></p>';
    jQuery('#dp_var_fields').append(strToAdd);
    return current;

}