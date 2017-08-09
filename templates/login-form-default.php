<?php
    global $ufpla_login_params;
    extract($ufpla_login_params);
?>

<form action="" method="post" id="upme-popup-login-form">

    
    <!-- Display login fields inside the form -->
    <?php echo $login_fields; ?>


    <!-- Display captcha verification fields after the login fields -->
    <?php echo $captcha_fields; ?>

    <div class="upme-field upme-edit upme-edit-show">
        <label class="upme-field-type ">&nbsp;</label>
        <div class="upme-field-value">            

            <input type="hidden" id='upme-popup-hidden-login-form-name'  name="upme-hidden-login-form-name" value="<?php echo $login_form_name; ?>" />
            <input type="hidden" id='upme-popup-hidden-login-form-name-hash'  name="upme-hidden-login-form-name-hash" value="<?php echo $hash; ?>" />            
            <input type="button" id='upme-popup-login-btn' name="upme-login" class="upme-button upme-login " value="<?php echo  __('Log In', 'upme'); ?>" /><br />

            

        </div>
    </div>
    <div class="upme-clear"></div>

    <?php wp_nonce_field( 'ufpla_login', 'ufpla_login_nonce_field' ); ?>
    <input id='upme-popup-redirect-to' type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />

    <!-- UPME Filters for social login buttons section -->
    <?php echo apply_filters( 'upme_social_logins', ''); ?>
    <!-- End Filters -->

</form>       

        
<!-- UPME Filters for adding extra fields or hidden data in login form -->
<?php echo  apply_filters('upme_popup_login_form_extra_fields',''); ?>
<!-- End Filter -->

