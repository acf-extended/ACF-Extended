<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_location_post_type_archive')):

class acfe_location_post_type_archive{
    
    public $post_type = false;
    public $post_types = array();
    
    function __construct(){
        
        add_action('init',                                          array($this, 'init'), 99);
        add_action('current_screen',                                array($this, 'current_screen'));
        add_action('admin_bar_menu',                                array($this, 'admin_bar_menu'), 90);
        
        add_filter('acf/get_options_pages',                         array($this, 'get_options_pages'));
        add_filter('acf/location/rule_types',                       array($this, 'location_types'));
        add_filter('acf/location/rule_values/post_type_archive',    array($this, 'location_values'));
        add_filter('acf/location/rule_match/post_type_archive',     array($this, 'location_match'), 10, 3);
        
    }
    
    
    /**
     * init:99
     */
    function init(){
        
        // get post types with archive enabled
        $post_types = acfe_get_post_type_objects(array(
            'acfe_admin_archive' => true
        ));
        
        // loop
        foreach($post_types as $name => $object){
    
            $parent = "edit.php?post_type={$name}";
            $parent = $name === 'post' ? 'edit.php' : $parent;         // post
            $parent = $name === 'attachment' ? 'upload.php' : $parent; // attachment
            
            // label
            $label = __('Archive <span class="count">(%s)</span>');
            $label = preg_replace('/ <span(.*?)<\/span>/', '', $label);
            
            // capability
            $capability = acf_get_setting('capability');
            $capability = apply_filters("acfe/post_type_archive_capability",                $capability, $name);
            $capability = apply_filters("acfe/post_type_archive_capability/name={$name}",   $capability, $name);
            
            // add options page
            acf_add_options_page(array(
                'page_title'                => "{$object->label} {$label}",
                'menu_title'                => $label,
                'menu_slug'                 => "{$name}-archive",
                'post_id'                   => "{$name}_archive",
                'capability'                => $capability,
                'redirect'                  => false,
                'parent_slug'               => $parent,
                'updated_message'           => $object->label . ' Archive Saved.',
                'acfe_post_type_archive'    => true
            ));
            
            // add to collection
            $this->post_types[] = $name;
            
        }
        
    }
    
    
    /**
     * current_screen
     */
    function current_screen(){
        
        // bail early
        if(!$this->post_types){
            return;
        }
        
        // loop post types archives
        foreach($this->post_types as $post_type){
    
            $base = $post_type;
            $base = $post_type === 'post' ? 'posts' : $base;       // post
            $base = $post_type === 'page' ? 'pages' : $base;       // page
            $base = $post_type === 'attachment' ? 'media' : $base; // attachment
            
            if(!acf_is_screen("{$base}_page_{$post_type}-archive")){
                continue;
            }
            
            // assign post type
            $this->post_type = $post_type;
            break;
        
        }
        
        // check exists
        if(!$this->post_type){
            return;
        }
        
        // location screen
        add_filter('acf/location/screen', array($this, 'location_screen'));
        
        // get object
        $post_type_obj = get_post_type_object($this->post_type);
    
        if(acfe_maybe_get($post_type_obj, 'has_archive')){
            
            // add permalink under title
            add_action('admin_footer', array($this, 'admin_footer'));
            
        }
        
    }
    
    
    /**
     * location_screen
     *
     * acf/location/screen
     *
     * @param $screen
     *
     * @return mixed
     */
    function location_screen($screen){
        
        $screen['acfe_dpt_admin_page'] = true;
        return $screen;
        
    }
    
    
    /**
     * admin_footer
     */
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
    
    
    /**
     * admin_bar_menu
     *
     * @param $wp_admin_bar
     */
    function admin_bar_menu($wp_admin_bar){
        
        // bail early
        if(is_admin() || !is_post_type_archive()){
            return;
        }
        
        // get post type
        $post_type = get_query_var('post_type');
        if(!$post_type){
            return;
        }
        
        // get object
        $object = get_post_type_object($post_type);
        
        // check has archive
        $has_archive = acfe_maybe_get($object, 'has_archive');
        $has_archive_page = acfe_maybe_get($object, 'acfe_admin_archive');
        
        if(!$has_archive || !$has_archive_page){
            return;
        }
    
        // check capability
        $capability = acf_get_setting('capability');
        $capability = apply_filters("acfe/post_type_archive_capability",                    $capability, $post_type);
        $capability = apply_filters("acfe/post_type_archive_capability/name={$post_type}",  $capability, $post_type);
        
        if(current_user_can($capability)){
            
            // add menu item
            $wp_admin_bar->add_node(array(
                'id'        => 'edit',
                'title'     => 'Edit ' . $object->label . ' ' . __('Archive'),
                'parent'    => false,
                'href'      => add_query_arg(array('post_type' => $object->name, 'page' => $object->name . '-archive'), admin_url('edit.php')),
                'meta'      => array('class' => 'ab-item')
            ));
            
        }
        
    }
    
    
    /**
     * get_options_pages
     *
     * acf/get_options_pages
     *
     * @param $pages
     *
     * @return mixed
     */
    function get_options_pages($pages){
        
        // validate ACF admin or aajax call
        if(acfe_is_admin_screen() || wp_doing_ajax()){
        
            foreach($pages as $page => $args){
                
                // unset options page
                if(acf_maybe_get($args, 'acfe_post_type_archive')){
                    unset($pages[ $page ]);
                }
                
            }
            
        }
        
        return $pages;
        
    }
    
    
    /**
     * location_types
     *
     * acf/location/rule_types
     *
     * @param $choices
     *
     * @return mixed
     */
    function location_types($choices){
        
        $name = __('Post', 'acf');
        $choices[ $name ] = acfe_array_insert_after($choices[ $name ], 'post_type', 'post_type_archive', __('Post Type Archive'));

        return $choices;
        
    }
    
    
    /**
     * location_values
     *
     * acf/location/rule_values/post_type_archive
     *
     * @param $choices
     *
     * @return array
     */
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
    
    
    /**
     * location_match
     *
     * acf/location/rule_match/post_type_archive
     *
     * @param $match
     * @param $rule
     * @param $screen
     *
     * @return bool|mixed
     */
    function location_match($match, $rule, $screen){
        
        if(!acf_maybe_get($screen, 'options_page') || !acf_maybe_get($screen, 'acfe_dpt_admin_page') || !acf_maybe_get($rule, 'value')){
            return $match;
        }
        
        $match = ($screen['options_page'] === $rule['value'] . '-archive');
        
        if($rule['value'] === 'all'){
            $match = true;
        }
        
        if($rule['operator'] === '!='){
            $match = !$match;
        }
        
        return $match;

    }
    
}

new acfe_location_post_type_archive();

endif;