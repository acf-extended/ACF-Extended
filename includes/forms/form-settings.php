<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_screen_settings')):

class acfe_screen_settings{
    
    // vars
    var $page;
    
    /*
     * Construct
     */
    function __construct(){
        
        /*
         * acfe/load_settings               $page
         * acfe/add_settings_meta_boxes     $page
         */
        
        // load
        add_action('load-options-general.php',      array($this, 'load'));
        add_action('load-options-writing.php',      array($this, 'load'));
        add_action('load-options-reading.php',      array($this, 'load'));
        add_action('load-options-discussion.php',   array($this, 'load'));
        add_action('load-options-media.php',        array($this, 'load'));
        add_action('load-options-permalink.php',    array($this, 'load'));
        
    }
    
    /*
     * Load
     */
    function load(){
        
        global $pagenow;
        
        $page = str_replace('.php', '', $pagenow);
        
        $this->page = $page;
        
        // actions
        do_action("acfe/load_settings",                 $page);
        do_action("acfe/load_settings/page={$page}",    $page);
        
        // hooks
        add_action('admin_footer', array($this, 'admin_footer'));
        
    }
    
    /*
     * Admin Footer
     */
    function admin_footer(){
        
        do_action('acfe/add_settings_meta_boxes', $this->page);
    
        // enhanced ui
        if(acf_get_setting('acfe/modules/ui')){
        
            $screen = get_current_screen();
    
            do_meta_boxes($screen, 'acf_after_title', $this->page);
            do_meta_boxes($screen, 'normal', $this->page);
            do_meta_boxes($screen, 'side', $this->page);
        
        }
        
    }
    
}

new acfe_screen_settings();

endif;