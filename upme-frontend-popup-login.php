<?php
/*
  Plugin Name: Frontend Popup Login for User Profiles Made Easy
  Plugin URI: http://www.profileplugin.com/upme-frontend-popup-login
  Description: Show online status of WordPress users
  Version: 1.0
  Author: Rakhitha Nimesh
  Author URI: http://www.wpexpertdeveloper.com
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

function ufpla_get_plugin_version() {
    $default_headers = array('Version' => 'Version');
    $plugin_data = get_file_data(__FILE__, $default_headers, 'plugin');
    return $plugin_data['Version'];
}

/* Validating existence of required plugins */
add_action( 'plugins_loaded', 'ufpla_plugin_init' );

function ufpla_plugin_init(){
    if(!class_exists('UPME')){
        add_action( 'admin_notices', 'ufpla_plugin_admin_notice' );
    }else{
        
    }
}

function ufpla_plugin_admin_notice() {
   $message = __('<strong>Frontend Popup Login for User Profiles Made Easy</strong> requires <strong>User Profiles Made Easy</strong> plugin to function properly','upmeinc');
   echo '<div class="error"><p>'.$message.'</p></div>';
}

if( !class_exists( 'UPME_Frontend_Popup_Login' ) ) {
    
    class UPME_Frontend_Popup_Login{
    
        private static $instance;

        public static function instance() {
            
            if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UPME_Frontend_Popup_Login ) ) {
                self::$instance = new UPME_Frontend_Popup_Login();
                self::$instance->setup_constants();

                add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
                self::$instance->includes();
                
                add_action('admin_enqueue_scripts',array(self::$instance,'load_admin_scripts'),9);
                add_action('wp_enqueue_scripts',array(self::$instance,'load_scripts'),9);
                 
                self::$instance->template_loader    = new UFPLA_Template_Loader();
                self::$instance->settings           = new UFPLA_Settings();
                self::$instance->login_manager      = new UFPLA_Login_Manager();
            }
            return self::$instance;
        }

        public function setup_constants() { }
        
        public function load_scripts(){ 
            wp_register_style('ufpla-front-css', UFPLA_PLUGIN_URL . 'css/ufpla-front.css');
            wp_enqueue_style('ufpla-front-css');

            $upme_settings = get_option('upme_options');
            if (!wp_script_is('upme_fancy_box') && '0' == $upme_settings['disable_fancybox_script_styles']) {
                wp_register_script('upme_fancy_box', upme_url . 'js/upme-fancybox.js', array('jquery'));
                wp_enqueue_script('upme_fancy_box');
            }
            
            if (!wp_style_is('upme_fancy_box_styles') && '0' == $upme_settings['disable_fancybox_script_styles']) {
                wp_register_style('upme_fancy_box_styles', upme_url . 'css/jquery.fancybox.css');
                wp_enqueue_style('upme_fancy_box_styles');
            }
            
            wp_register_script('ufpla-front', UFPLA_PLUGIN_URL . 'js/ufpla-front.js', array('jquery'));
            wp_enqueue_script('ufpla-front');

            $custom_js_strings = array(
                'Messages' => array(
                    'LoginEmptyUsername' => __('The username field is empty.', 'upme'),
                    'LoginEmptyPassword' => __('The password field is empty.', 'upme'),
                    // 'ValidEmail' => __('Please enter valid username or email address.', 'upme'),
                    
                ),
                'AdminAjax' => admin_url('admin-ajax.php'),
            );

            wp_localize_script('ufpla-front', 'UFPLA', $custom_js_strings);
        }
        
        public function load_admin_scripts(){
            
        }
        
        private function includes() {
            
            require_once UFPLA_PLUGIN_DIR . 'functions.php';
            require_once UFPLA_PLUGIN_DIR . 'classes/class-ufpla-template-loader.php';      
            require_once UFPLA_PLUGIN_DIR . 'classes/class-ufpla-settings.php'; 
            require_once UFPLA_PLUGIN_DIR . 'classes/class-ufpla-login-manager.php'; 

            if ( is_admin() ) {
            }
        }

        public function load_textdomain() {
            
        }
        
    }
}

// Plugin version
if ( ! defined( 'UFPLA_VERSION' ) ) {
    define( 'UFPLA_VERSION', '1.0' );
}

// Plugin Folder Path
if ( ! defined( 'UFPLA_PLUGIN_DIR' ) ) {
    define( 'UFPLA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin Folder URL
if ( ! defined( 'UFPLA_PLUGIN_URL' ) ) {
    define( 'UFPLA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}




function UPME_Frontend_Popup_Login() {
    global $ufpla;
    $ufpla = UPME_Frontend_Popup_Login::instance();
}

UPME_Frontend_Popup_Login();





