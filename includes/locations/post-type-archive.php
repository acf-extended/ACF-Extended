<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_location_post_type_archive')):

class acfe_location_post_type_archive{
    
    public $post_type = false;
    public $post_types = array();
    
    function __construct(){
        
        add_action('init',                                          array($this, 'init'), 99);
        add_action('current_screen',                                array($this, 'current_screen'));
        
        add_action('admin_bar_menu',                                array($this, 'admin_bar'), 90);
        
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
            
            // label
            $label = __('Archive <span class="count">(%s)</span>');
            $label = preg_replace('/ <span(.*?)<\/span>/', '', $label);
            
            // Capability
            $capability = acf_get_setting('capability');
            $capability = apply_filters("acfe/post_type_archive_capability",                $capability, $name);
            $capability = apply_filters("acfe/post_type_archive_capability/name={$name}",   $capability, $name);
            
            // Register
            acf_add_options_page(array(
                'page_title'                => $object->label . ' ' . $label,
                'menu_title'                => $label,
                'menu_slug'                 => $name . '-archive',
                'post_id'                   => $name . '_archive',
                'capability'                => $capability,
                'redirect'                  => false,
                'parent_slug'               => $parent_slug,
                'updated_message'           => $object->label . ' Archive Saved.',
                'acfe_post_type_archive'    => true
            ));
            
            $this->post_types[] = $name;
            
        }
        
    }
    
    function current_screen($screen){
        
        foreach($this->post_types as $post_type){
            
            if(!acf_is_screen("{$post_type}_page_{$post_type}-archive"))
                continue;
    
            $this->post_type = $post_type;
            
            break;
        
        }
        
        if(!$this->post_type)
            return;
        
        // Location screen
        add_action('acf/location/screen', array($this, 'location_screen'));
        
        // Get Post Type object
        $post_type_obj = get_post_type_object($post_type);
    
        if(isset($post_type_obj->has_archive) && !empty($post_type_obj->has_archive)){
            
            // Add "Permalink" under title
            add_action('admin_footer', array($this, 'admin_footer'));
            
        }
        
    }
    
    function location_screen($screen){
        
        $screen['acfe_dpt_admin_page'] = true;
        
        return $screen;
        
    }
    
    function admin_footer(){
        
        ?>
        <div id="tmpl-acf-after-title">
            <div style="margin-top:7px;">
                <strong><?php _e('Permalink:'); ?></strong> <span><a href="<?php echo get_post_type_archive_link($this->post_type); ?>" target="_blank"><?php echo get_post_type_archive_link($this->post_type); ?></a></span>
            </div>
        </div>
        <script type="text/javascript">
        (function($){
            
            // add after title
            $('.acf-settings-wrap > h1').after($('#tmpl-acf-after-title'));
            
        })(jQuery);
        </script>
        <?php
        
    }
    
    function admin_bar($wp_admin_bar){
        
        // Bail early
        if(is_admin() || !is_post_type_archive())
            return;
        
        // Get Post Type
        $post_type = get_query_var('post_type');
        
        if(!$post_type)
            return;
        
        // Object
        $object = get_post_type_object($post_type);
        
        // Check has archive
        $has_archive = acfe_maybe_get($object, 'has_archive');
        $has_archive_page = acfe_maybe_get($object, 'acfe_admin_archive');
        
        if(!$has_archive || !$has_archive_page)
            return;
    
        // Check capability
        $capability = acf_get_setting('capability');
        $capability = apply_filters("acfe/post_type_archive_capability",                    $capability, $post_type);
        $capability = apply_filters("acfe/post_type_archive_capability/name={$post_type}",  $capability, $post_type);
        
        if(!current_user_can($capability))
            return;
        
        // Add menu item
        $wp_admin_bar->add_node(array(
            'id'        => 'edit',
            'title'     => 'Edit ' . $object->label . ' ' . __('Archive'),
            'parent'    => false,
            'href'      => add_query_arg(array('post_type' => $object->name, 'page' => $object->name . '-archive'), admin_url('edit.php')),
            'meta'      => array('class' => 'ab-item')
        ));
        
    }
    
    function get_options_pages($pages){
        
        // Bail early if screen is not Field Group configuration & Ajax Calls
        if(!acfe_is_admin_screen() && !wp_doing_ajax())
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
        
        if(!acf_maybe_get($screen, 'options_page') || !acf_maybe_get($screen, 'acfe_dpt_admin_page') || !acf_maybe_get($rule, 'value'))
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