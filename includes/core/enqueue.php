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
    
        // ACF Extended: Admin
        wp_enqueue_style('acf-extended-admin', acfe_get_url('assets/acf-extended-admin' . $this->suffix . '.css'), false, $this->version);
        
        // ACF Extended: UI
        if(acf_get_setting('acfe/modules/ui') && $this->is_screen_ui()){
            
            wp_enqueue_style('acf-extended-ui', acfe_get_url('assets/acf-extended-ui' . $this->suffix . '.css'), false, $this->version);
            
        }
        
    }
    
    /**
     * ACF (Front + Back) Enqueue
     */
    function acf_enqueue(){
        
        // ACF Extended
        wp_enqueue_style('acf-extended', acfe_get_url('assets/acf-extended' . $this->suffix . '.css'), false, $this->version);
        wp_enqueue_script('acf-extended', acfe_get_url('assets/acf-extended' . $this->suffix . '.js'), array('jquery'), $this->version);
    
        // ACF Extended: Admin
        if($this->is_screen_admin()){
            
            wp_enqueue_script('acf-extended-admin', acfe_get_url('assets/acf-extended-admin' . $this->suffix . '.js'), array('jquery'), $this->version);
            
        }
        
        acf_localize_data(array(
            'is_admin' => is_admin()
        ));
        
        acf_localize_text(array(
            'Close'	=> __('Close', 'acf'),
        ));
        
    }
    
    function is_screen_admin(){
        
        return acf_is_screen(array('edit-acf-field-group', 'acf-field-group'));
        
    }
    
    function is_screen_ui(){
        
        return $this->is_screen(array('edit-tags', 'term', 'profile', 'user-edit', 'user', 'options-general', 'options-writing', 'options-reading', 'options-discussion', 'options-media', 'options-permalink'));
        
    }
    
    function is_screen($id = ''){
    
        // bail early if not defined
        if(!function_exists('get_current_screen'))
            return false;
    
        // vars
        $current_screen = get_current_screen();
    
        // no screen
        if(!$current_screen){
            
            return false;
        
        // array
        }elseif(is_array($id)){
            
            return in_array($current_screen->base, $id);
        
        // string
        }else{
            
            return ($id === $current_screen->base);
            
        }
    }
    
}

new acfe_enqueue();

endif;