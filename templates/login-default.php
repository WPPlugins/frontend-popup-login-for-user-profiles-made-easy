<?php
    global $ufpla_login_params;
    extract($ufpla_login_params);
?>
<div id="upme-popup-login-wrapper">
    <div class="upme-wrap upme-login  " style="width:<?php echo $form_width; ?>%;" >
        <div class="upme-inner upme-clearfix upme-login-wrapper">

            <!-- UPME Filters for before login head section -->
            <?php echo apply_filters( 'upme_popup_login_before_head', ''); ?>
            <!-- End Filters -->

            <!-- UPME Filters for customizing head section -->
            <?php 
                $login_head_params = array();
                echo apply_filters( 'upme_popup_login_head', $login_header , $login_head_params);
            ?>
            <!-- End Filters -->
            
        
            <!-- UPME Filters for after login head section -->
            <?php echo apply_filters( 'upme_popup_login_after_head', ''); ?>
            <!-- End Filters -->


            <div class="upme-main">
                <?php if(isset($common_msg) && $common_msg != ''){ ?>
                    <div id="upme-login-view-msg-holder" class="<?php echo $common_msg_status;?>"  >
                    <?php echo $common_msg; ?></div>
                <?php } ?>
                
                <?php echo $errors; ?>
                <div class="upme-errors" id="upme-popup-login-error" style="display:none;">

                </div>

                <?php if (!empty($act_status_message['msg'])) {    ?>
                    <div class="upme-<?php echo $act_status_message['status']; ?>">
                        <span class="upme-error upme-error-block">
                            <?php if($act_status_message['status'] == 'success'){ ?>
                                <i class="upme-icon upme-icon-check"></i>                
                            <?php }else{ ?>
                                <i class="upme-icon upme-icon-remove"></i>
                            <?php } ?>
                            
                            <?php echo $act_status_message['msg']; ?>
                        </span>
                    </div>
                <?php } ?>


                <?php echo $login_form_template; ?>

            </div>

            <!-- UPME Filters for after login fields section -->
            <?php echo apply_filters( 'upme_popup_login_after_fields', '', $args); ?>
            <!-- End Filters -->

        </div>
    </div>
</div>