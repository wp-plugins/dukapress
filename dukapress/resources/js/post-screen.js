jQuery(document).ready(function($) {
  var formfield;
  

  //open thickbox when button is clicked
  $('#duka_upload_button').click(function() {
    formfield = $('#digital_file');
		tb_show('Upload A Product File', 'media-upload.php?TB_iframe=true');
		return false;
	});

	// user inserts file into post. only run custom if user started process using the above process
	// window.send_to_editor(html) is how wp would normally handle the received data
	window.original_send_to_editor = window.send_to_editor;
	window.send_to_editor = function(html){
		if (formfield) {
			fileurl = $(html).attr('href');
			$(formfield).val(fileurl);
      formfield = false;
			tb_remove();
		} else {
			window.original_send_to_editor(html);
		}
	};

  //remove formfield whenever thickbox is closed
  $('a.thickbox, #TB_overlay, #TB_imageOff, #TB_closeWindowButton, #TB_TopCloseWindowButton').click(function(){
    formfield = false;
  });
  
  
  var current = 1;

    function dp_addVariation() {
        current++;
        var strToAdd = '<p><label for="vname'+current+'">'+dpsc_admin_js.variation_name+'</label><input id="vname'+current+'" name="vname'+current+'" size="15" />';
    
        strToAdd += '<p><label for="vprice'+current+'">'+dpsc_admin_js.variation_price+'</label>\n\
        <input id="vprice'+current+'" name="vprice'+current+'" size="15" /></p>';
        $('#dp_var_fields').append(strToAdd);
        return current;
    
    }
    

    function dp_m_rem(clickety){
    	$(clickety).parent().parent().remove();
    	return false;
    }
    function dp_m_add(clickety){
    	$('.row_block:last').after(
                    $('.row_block:last').clone()
            );
    	$('.row_block:last input').attr('value', '');
    	return false;
    }
  
  
        $('#dp_addVariation').on('click', function(){
            $(dp_addVariation);
            $('input[name=varitaionnumber]').val(current);
        });

        $('#dp_deletestring a').on('click', function(){
            var postid=$("#post_ID").val();
            var currentId = $(this).attr('id');
            var mix="#delete"+currentId;
            var substring=$(mix).val();

             $.ajax({
                type: "POST",
                url: ajaxurl,
                data:'action=delete_variationdata&name='+substring+'&postid='+postid,
                success:function(msg)
                {
                    $("#result").html(msg);
                }
            });


      });

        $('#dp_save').on('click', function(){
            var i;
            var actionstring='';

            var counter=$("#varitaionnumber").val();
            var postid=$("#post_ID").val();
            var oname=$('#optionname').val();
             actionstring+='optionname='+oname+'&counter='+counter;
            for(i=1;i<=counter;i++)
            {
                var vname='';
                var vprice='';
                vname=$('#vname'+i).val();
                vprice=$('#vprice'+i).val();
                actionstring+=('&vname'+i+'='+vname+'&vprice'+i+'='+vprice);
            }

            //alert(actionstring);
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data:'action=save_variationdata&'+actionstring+'&postid='+postid,
                success:function(msg)
                {
                    $('#dp_var_fields').html('');
                    $('#optionname').val('');
                    $('#vname1').val('');
                    $('#vprice1').val('');
                    $("#result").html(msg);
                    current = 1;
                    $('input[name=varitaionnumber]').val(current);
                }
            });

        });
    
    $('.deletethis').on('click',function(){
        var action = confirm("Delete ?");
        if(action == true){
            $(this).addClass('iwasdeleted');
            $(this).parent('p').parent('td').parent('tr').css('backgroundColor','#D65C5E');
            $(this).parent('p').parent('td').parent('tr').css('background-color','#D65C5E');
            var invoice = $(this).attr('rel');
                 var pp_delete_data = {action:'dp_delete_transaction',
                     'invoice':invoice
                 };
                 $.post(ajaxurl, pp_delete_data, function(response){
                    if(response == 'true'){
                        $('.iwasdeleted').parent('p').parent('td').parent('tr').slideUp().remove();
                    }
                });
        }
    });
});