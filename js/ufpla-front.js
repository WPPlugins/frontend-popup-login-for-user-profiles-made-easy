jQuery(document).ready(function($) {
	jQuery('#upme-popup-login-btn').click(function(){
        var username_obj = jQuery('#upme-popup-login-form #user_login');
        var username = username_obj.val();
        var password_obj = jQuery('#upme-popup-login-form #login_user_pass');
        var password = password_obj.val();
        
        var message;
        var err = false;
        var err_msg = '';

        jQuery(".upme-login-wrapper #upme-popup-login-error").css('display','none');

        if('' == username){
            message = UFPLA.Messages.LoginEmptyUsername;
            jQuery(username_obj).addClass('error');
            err = true;
            err_msg+='<span class="upme-error upme-error-block"><i class="upme-icon upme-icon-remove"></i>' + message +'</span>';
        }

        if('' == password){
            message = UFPLA.Messages.LoginEmptyPassword;
            jQuery(password_obj).addClass('error');
            err = true;
            err_msg+='<span class="upme-error upme-error-block"><i class="upme-icon upme-icon-remove"></i>' + message +'</span>';
        }


        if(err == true && err_msg!=''){
                
            jQuery(".upme-login-wrapper #upme-popup-login-error").css('display','block');
            jQuery(".upme-login-wrapper #upme-popup-login-error").html(err_msg);

            return false;
                            
        }else{
        	jQuery.post(
            UFPLA.AdminAjax,
            {
                'action': 'upme_popup_authenticate_user',
                'username':  username,
                'password' : password,
                'security' : jQuery('#ufpla_login_nonce_field').val()
            },
            function(response){
            	
                if(response.status == 'fail'){
                	err_msg = '';
                	jQuery.each(response.errors, function( index, value ) {
					  err_msg+='<span class="upme-error upme-error-block"><i class="upme-icon upme-icon-remove"></i>' + value +'</span>';
     
					});

					jQuery(".upme-login-wrapper #upme-popup-login-error").css('display','block');
            		jQuery(".upme-login-wrapper #upme-popup-login-error").html(err_msg);
                }else{
                	jQuery("#upme-popup-login-wrapper").html(response.html);
                }
                

            },"json");
        }
    });	


	var ufpla_login_modal_status = '0';
    jQuery('a[href="#ufpla_login_modal"]').click(function (e)
    {
            jQuery.fancybox.close();
            e.preventDefault();
            if(ufpla_login_modal_status == '0'){
                jQuery('#uifpla_login_modal_loader').show();
                //jQuery('#ufpla_login_modal').html('');
                ufpla_login_modal_status = 1;
                ufpla_load_login_modal(this);
                
            }else{
                ufpla_login_modal_status = 0;
            }
            
            return false;
    });
});

function ufpla_load_login_modal(modal)
{

    jQuery(modal).fancybox({
        'maxHeight' : 450,
        'minWidth' : '90%',
        'maxWidth' : 600,
        'autoSize': true,

    }).click();
}