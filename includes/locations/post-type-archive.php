<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_location_post_type_archive')):

class acfe_location_post_type_archive{
    
	function __construct(){
        
        add_action('init',                                          array($this, 'init'), 99);
        
        add_filter('acf/get_options_pages',                         array($this, 'get_options_pages'));
        
        add_filter('acf/location/rule_types',                       array($this, 'location_types'));
        add_filter('acf/location/rule_values/post_type_archive',    array($this, 'location_values'));
        add_filter('acf/location/rule_match/post_type_archive',     array($this, 'location_match'), 10, 3);
        
	}
    
    function init(){
        
        $post_types = acfe_get_post_type_objects(array(
            'acfe_admin_archive' => true
        ));
        
        if(empty($post_types))
            return;
        
        foreach($post_types as $name => $object){
            
            $parent_slug = 'edit.php?post_type=' . $name;
            
            // Post Type: Post
            if($name === 'post')
                $parent_slug = 'edit.php';
            
            acf_add_options_page(array(
                'page_title' 	            => $object->label . ' Archive',
                'menu_title'	            => 'Archive',
                'menu_slug' 	            => $name . '-archive',
                'post_id'                   => $name . '_archive',
                'capability'	            => acf_get_setting('capability'),
                'redirect'		            => false,
                'parent_slug'               => $parent_slug,
                'updated_message'           => $object->label . ' Archive Saved.',
                'acfe_post_type_archive'    => true
            ));
            
        }
        
    }
    
    function get_options_pages($pages){
        
        $check_current_screen = acf_is_screen(array(
            'edit-acf-field-group',
            'acf-field-group',
            'acf_page_acf-tools'
        ));
        
        // Bail early if screen is Field Group configuration & Ajax Calls
        if(!$check_current_screen && !wp_doing_ajax())
            return $pages;
        
        foreach($pages as $page => $args){
            
            if(!acf_maybe_get($args, 'acfe_post_type_archive'))
                continue;
            
            // Unset option page
            unset($pages[$page]);
            
        }
        
        return $pages;
        
    }
    
    function location_types($choices){
        
        $name = __('Post', 'acf');
        
        $choices[$name] = acfe_array_insert_after('post_type', $choices[$name], 'post_type_archive', __('Post Type Archive'));

        return $choices;
        
    }
    
    function location_values($choices){
        
        $post_types = acf_get_post_types(array(
            'acfe_admin_archive' => true
        ));
        
        $pretty_post_types = array();
        
        if(!empty($post_types)){
            
            $pretty_post_types = acf_get_pretty_post_types($post_types);
            
        }
        
        $choices = array('all' => __('All', 'acf'));
		$choices = array_merge($choices, $pretty_post_types);
        
        return $choices;
        
    }
    
    function location_match($match, $rule, $screen){
        
        if(!acf_maybe_get($screen, 'options_page') || !acf_maybe_get($rule, 'value'))
            return $match;
        
        $match = ($screen['options_page'] === $rule['value'] . '-archive');
        
        if($rule['value'] === 'all')
            $match = true;
        
        if($rule['operator'] === '!=')
            $match = !$match;
        
        return $match;

    }
    
}

new acfe_location_post_type_archive();

endif;