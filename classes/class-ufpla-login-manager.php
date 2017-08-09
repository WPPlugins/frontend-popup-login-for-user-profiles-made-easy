<?php

class UFPLA_Login_Manager{
    
    public function __construct(){       
        add_shortcode('upme_popup_login', array($this,'upme_popup_login') );
        add_action('wp_ajax_nopriv_upme_popup_authenticate_user', array($this,'login'));
        add_action('wp_login', array($this,'save_login_timestamp'), 10, 2 );  
        add_action('wp_footer', array($this,'add_login_modal') );   
    }

    public function upme_popup_login($args) {
        global $upme_login, $upme_register, $ufpla_login_params, $ufpla;

        if(is_user_logged_in()){
            return  do_shortcode('[upme view=compact ]');
        }

        $default_redirect = "";
        if (isset($_GET['redirect_to']) && $_GET['redirect_to'] != '')
            $default_redirect = $_GET['redirect_to'];

        /* Arguments */
        $defaults = array(
            'redirect_to' => $default_redirect,
            'name' => wp_generate_password(12,false),
            'hide_social_login' => '',
            'template' => 'default',
            'form_width' => 100,
            'hide_header' => '',
        );

        $args = wp_parse_args($args, $defaults);
        extract($args, EXTR_SKIP);

        // Default set to no captcha
        $this->captcha = 'no';
        if (isset($captcha))
            $this->captcha = $captcha;

        /*  Set form name for allowing multiple registration forms through filters */
        $this->login_form_name = $name;

        /*  Set login template */
        $this->login_template = $template;

        // Check activation status to display the activation message
        $act_status_message = $upme_register->upme_user_activation_handler();

        $this->login_shortcode_args = $args;
        
        $ufpla_login_params['args'] = $args;
        $ufpla_login_params['form_width'] = $form_width;
        
        /* UPME Filters for before login head section */
        $login_form_title_params = array();
        $login_form_title = apply_filters( 'upme_popup_login_form_title', __('Login', 'ufpla') , $login_form_title_params);
        // End Filters
        $ufpla_login_params['login_form_title'] = $login_form_title;
        $ufpla_login_params['act_status_message'] = $act_status_message;
        
        ob_start();
        $ufpla->template_loader->get_template_part('login-header-' .$this->login_template);
        $ufpla_login_params['login_header'] = ob_get_clean();       
        
        /* Display errors */
        $display_errors = '';
        if (isset($_POST['upme-login']) || ( isset($upme_login->errors) && count($upme_login->errors) > 0) ) {
            $display_errors .= $upme_login->get_errors();
        }
        
        $ufpla_login_params['errors'] = $display_errors;

        $ufpla_login_params['login_form_template'] = $this->show_login_form($redirect_to);
        
        if(isset($upme_login->delete_profile_message) && $upme_login->delete_profile_message != ''){
            $ufpla_login_params['common_msg_status'] = $upme_login->delete_profile_message_status;
            $ufpla_login_params['common_msg'] = $upme_login->delete_profile_message;
        }
        
        ob_start();
        $ufpla->template_loader->get_template_part('login-' .$this->login_template);
        $display = ob_get_clean();

        return $display;
    }

    public function show_login_form($redirect_to=null) {        
        global $upme_login, $upme_captcha_loader, $ufpla_login_params, $ufpla,
            $upme_login_forgot_params,$upme;        

        /* UPME Filters for customizing login fields */
        $login_fields_params = array();
        $upme->login_fields  = apply_filters( 'upme_popup_login_fields', $upme->login_fields, $login_fields_params);
        // End Filters
        
        /* Display login fields inside the form */
        $ufpla_login_params['login_fields'] = $this->generate_login_fields($redirect_to);      

        /* Display captcha verification fields after the login fields */
        $ufpla_login_params['captcha_fields'] = $upme_captcha_loader->load_captcha($this->captcha,$this->login_template);

        /* Apply hash on login form name for verification */
        $upme_secret_key = get_option('upme_secret_key');
        if(! $upme_secret_key ){
            $upme_secret_key = wp_generate_password(20);
            update_option('upme_secret_key',$upme_secret_key);
        }

        $hash = hash('sha256', $this->login_form_name.$upme_secret_key);

        $ufpla_login_params['login_form_name'] = $this->login_form_name ;
        $ufpla_login_params['hash'] = $hash;
        $ufpla_login_params['redirect_to'] = $redirect_to;

        /* Load login form template */
        ob_start();
        $ufpla->template_loader->get_template_part('login-form-' .$this->login_template);
        $display = ob_get_clean();
        
        return $display;
    }

    public function generate_login_fields($redirect_to){
        global $ufpla_login_field_params,$ufpla,$upme;
        
        $display = '';
        
        foreach ($upme->login_fields as $key => $field) {
            $field_meta = $field;

            extract($field);

            if ($type == 'usermeta') {

                /* Show the label */
                $placeholder = '';
                $icon_name = '';
                $input_ele_class = '';
                $upme_login_label = '';
                $upme_login_input_field = '';
                $field_label_meta = '';
                
                if (isset($upme->login_fields[$key]['name']) && $name) {
                    if (isset($upme->login_fields[$key]['icon']) && $icon) {
                        $display_icon = '<i class="upme-icon upme-icon-' . $icon . '"></i>';
                    } else {
                        $display_icon = '<i class="upme-icon upme-icon-none"></i>';
                    }

                    /* UPME Filters for customizing login fields icons */
                    $login_field_icon_params = array('meta'=> $meta, 'name'=>$name, 'field_meta'=> $field_meta);
                    $upme_login_field_icon = apply_filters( 'upme_login_field_icon',$display_icon, $login_field_icon_params);
                    // End Filters

                    $upme_login_label = apply_filters('upme_login_label_' . $meta, $name);
                    $field_label_meta = $meta;
                }

                switch($this->login_template){
                    case 'classic':
                        $placeholder = ' placeholder="' . $name . '"';
                        break;
                    case 'elegant':
                        $placeholder = ' placeholder="' . $name . '"';
                        break;
                }

                switch ($field) {
                    case 'textarea':
                        $upme_login_input_field = '<textarea class="upme-input' . $input_ele_class . '" name="' . $meta . '" id="' . $meta . '" ' . $placeholder . '>' . $upme->post_value($meta) . '</textarea>';
                        break;
                    case 'text':
                        $upme_login_input_field = '<input type="text" class="upme-input' . $input_ele_class . '" name="' . $meta . '" id="' . $meta . '" value="' . $upme->post_value($meta) . '" ' . $placeholder . ' />';

                        if (isset($upme->login_fields[$key]['help']) && $help != '') {
                            $upme_login_input_field .= '<div class="upme-help">' . $help . '</div><div class="upme-clear"></div>';
                        }

                        break;
                    case 'password':
                        $upme_login_input_field = '<input type="password" class="upme-input' . $input_ele_class . '" name="' . $meta . '" id="' . $meta . '" value="" ' . $placeholder . ' />';
                        break;
                    default:
                        /* UPME Filters for adding custom login field types */
                        $login_custom_field_types_params = array();
                        $upme_login_input_field = apply_filters( 'upme_login_custom_field_types','', $login_custom_field_types_params);
                        // End Filters 
                }

                ob_start();
                
                $ufpla_login_field_params['meta'] = $meta;
                $ufpla_login_field_params['upme_login_field_icon'] = $upme_login_field_icon;
                $ufpla_login_field_params['upme_login_label'] = $upme_login_label;
                $ufpla_login_field_params['name'] = $name;
                $ufpla_login_field_params['icon'] = $icon;
                $ufpla_login_field_params['upme_login_input_field'] = $upme_login_input_field;
                // $upme_login_field_params['sidebar_class'] = $sidebar_class;
                $ufpla_login_field_params['field_label_meta'] = $field_label_meta;                
                    
                $ufpla->template_loader->get_template_part('login-fields-'. $this->login_template);                
                $display .= ob_get_clean();
            }
        }
        
        return $display;
    }

    public function verify_login_form_hash(){
        // Verify whether login form name is modified
        if(isset($_POST['upme-popup-hidden-login-form-name'])){

            $upme_secret_key = get_option('upme_secret_key');
            $login_form_name = $_POST['upme-popup-hidden-login-form-name'];
            $login_form_name_hash = $_POST['upme-popup-hidden-login-form-name-hash'];

            if($login_form_name_hash != hash('sha256', $login_form_name.$upme_secret_key) ){
                // Invailid form name was defined by manually editing
                $this->errors[] = __('Invalid login form.','ufpla');
                return false;
            }
            $this->login_form_name = $login_form_name;
        }
        
        return true;
    }

    public function login(){
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? ($_POST['password']) : '';

        if(check_ajax_referer( 'ufpla_login', 'security' , false )){
            if(is_email($username)){
                $user = get_user_by( 'email', $username );
                if($user){
                    if(isset($user->data->user_login))
                        $creds['user_login'] = $user->data->user_login;
                    else
                        $creds['user_login'] = '';
                }else{
                    $creds['user_login'] = sanitize_user($username,TRUE);
                }
                
            }
            // User is trying to login using username
            else{
                $creds['user_login'] = sanitize_user($username,TRUE);
            }


            $creds['user_password'] = $password;
            
            $secure_cookie = false;
            if(is_ssl()){
                $secure_cookie = true;
            }

            if('INACTIVE' == get_user_meta($user_data->ID, 'upme_approval_status' , true)){
                $this->errors[] = $this->upme_settings['html_profile_approval_pending_msg'];            
            }else if('INACTIVE' == get_user_meta($user_data->ID, 'upme_activation_status' , true)){
                $this->errors[] = __('Please confirm your email to activate your account.','ufpla');
            }else{
                $user = wp_signon( $creds, $secure_cookie );
                
                if ( is_wp_error($user) ) {
                    if ($user->get_error_code() == 'invalid_username') {
                        $this->errors[] = __('Invalid Username or Email','ufpla');
                    }
                    if ($user->get_error_code() == 'incorrect_password') {
                        $this->errors[] = __('Incorrect Username or Password','ufpla');
                    }
                    
                    if ($user->get_error_code() == 'empty_password') {
                        $this->errors[] = __('Please enter a password.','ufpla');
                    }

                    /* UPME action for adding actions after unsuccessfull login */
                    $login_failed_params = array();
                    do_action('upme_login_failed', $this->usermeta, $user, $login_failed_params);
                    /* END action */ 

                    $response['status'] = 'fail';
                    $response['msg'] = __('Login failed','upcm');  
                    if(count($this->errors) > 0)  { 
                        $response['errors'] = $this->errors;      
                    }         
                
                }else{
                    do_action('wp_login', $user->user_login ,$user);

                    /* UPME action for adding actions after successfull login */
                    $login_sucess_params = array();
                    do_action('upme_login_sucess', $this->usermeta, $user, $login_sucess_params);
                    /* END action */

                    $response['status'] = 'success';
                    $response['msg'] = __('Login successfull.','upcm');
                    $response['html'] = do_shortcode('[upme view=compact ]');
                }
            }

        }else{
            $response['status'] = 'fail';
            $response['msg'] = __('Nonce validation failed','ufpla');
        }
        
        echo json_encode($response);exit;

        
    }
    
    public function save_login_timestamp($user_login, $user  = null){
        $user_id = isset($user->ID) ? $user->ID : 0;
        if($user_id){
            update_user_meta($user_id,'upme_last_login_time', time() );
        }        
    }

    public function add_login_modal(){
        $display  .= '<div id="ufpla_login_modal" style="display:none">'.do_shortcode('[upme_popup_login]').'</div>';
        $display  .= '<div id="ufpla_login_modal_loader" style="display:none"><img src="'.upme_url.'css/images/fancybox/fancybox_loading.gif" /></div>';
        
        echo $display;
    }
}


