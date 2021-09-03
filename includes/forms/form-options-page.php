<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_screen_options_page')):

class acfe_screen_options_page{
    
    // vars
    var $page;
    
    /*
     * Construct
     */
    function __construct(){
        
        /*
         * acfe/load_option             $page
         * acfe/add_option_meta_boxes   $page
         */
        
        // load
        add_action('admin_init', array($this, 'load'));
        
    }
    
    /*
     * Load
     */
    function load(){
        
        global $plugin_page;
        
        if(!isset($plugin_page)){
            return;
        }
    
        $this->page = acf_get_options_page($plugin_page);
        
        if(!$this->page){
            return;
        }
        
        // actions
        do_action("acfe/load_option",                                   $this->page);
        do_action("acfe/load_option/page={$this->page['menu_slug']}",   $this->page);
        
        // hooks
        add_action('admin_head', array($this, 'admin_head'));
        
    }
    
    /*
     * Admin Head
     */
    function admin_head(){
        
        do_action('acfe/add_option_meta_boxes', $this->page);
        
    }
    
}

new acfe_screen_options_page();

endif;