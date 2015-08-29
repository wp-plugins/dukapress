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
        var clone = $("table#variation_appends tr.variation_name").clone();
        clone = clone.removeClass('variation_name');
        clone = clone.html().replace("vname1","vname"+current);
        $('table#variation_appends').append("<tr>"+clone+"</tr>");
        
        clone = $("table#variation_appends tr.variation_price").clone();
        clone = clone.removeClass('variation_name');
        clone = clone.html().replace("vprice1","vprice"+current);
        $('table#variation_appends').append("<tr>"+clone+"</tr>");
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
                    $(".variation_results").html(msg);
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
                    $("table#variation_appends tr").each(function(){
                        if($(this).hasClass('variation_name') || $(this).hasClass('variation_price')){
                            
                        }else{
                            $(this).remove();
                        }
                    });
                    $('#optionname').val('');
                    $('#vname1').val('');
                    $('#vprice1').val('');
                    $(".variation_results").html(msg);
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