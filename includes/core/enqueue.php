<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_enqueue')):

class acfe_enqueue{
    
    var $suffix = '';
    var $version = '';
    
    function __construct(){
    
        // Vars
        $this->suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        $this->version = ACFE_VERSION;
        
        // Hooks
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
        add_action('acf/input/admin_enqueue_scripts', array($this, 'acf_enqueue'));
    }
    
    /**
     * Admin Enqueue
     */
    function admin_enqueue(){
    
        // Admin
        wp_enqueue_style('acf-extended-admin', acfe_get_url('assets/css/acfe-admin' . $this->suffix . '.css'), false, $this->version);
    
        // Field Group
        if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group'))){
        
            wp_enqueue_style('acf-extended-field-group', acfe_get_url('assets/css/acfe-field-group' . $this->suffix . '.css'), false, $this->version);
        
        }
        
    }
    
    /**
     * ACF (Front + Back) Enqueue
     */
    function acf_enqueue(){
        
        // Global
        wp_enqueue_style('acf-extended', acfe_get_url('assets/css/acfe' . $this->suffix . '.css'), false, $this->version);
        wp_enqueue_script('acf-extended', acfe_get_url('assets/js/acfe' . $this->suffix . '.js'), array('acf'), $this->version);
    
        // Input
        wp_enqueue_style('acf-extended-input', acfe_get_url('assets/css/acfe-input' . $this->suffix . '.css'), false, $this->version);
        wp_enqueue_script('acf-extended-input', acfe_get_url('assets/js/acfe-input' . $this->suffix . '.js'), array('acf-input'), $this->version);
    
        // Admin
        if(is_admin()){
    
            wp_enqueue_script('acf-extended-admin', acfe_get_url('assets/js/acfe-admin' . $this->suffix . '.js'), array('acf'), $this->version);
            
        }
    
        // Field Group
        if(acf_is_screen(array('acf-field-group'))){
            
            wp_enqueue_script('acf-extended-field-group', acfe_get_url('assets/js/acfe-field-group' . $this->suffix . '.js'), array('acf-field-group'), $this->version);
        
        }
        
        acf_localize_data(array(
            'acfe_version' => ACFE_VERSION,
            'acfe' => array(
                'home_url'          => home_url(),
                'is_admin'          => is_admin(),
                'is_user_logged_in' => is_user_logged_in(),
            )
        ));
        
        $read_more = __('Read more...');
        $read_more = str_replace('…', '', $read_more);
        $read_more = str_replace('...', '', $read_more);
        
        acf_localize_text(array(
            'Close'     => __('Close', 'acf'),
            'Read more' => $read_more,
        ));
        
    }
    
}

new acfe_enqueue();

endif;