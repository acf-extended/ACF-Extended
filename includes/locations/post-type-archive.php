<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_location_post_type_archive')):

class acfe_location_post_type_archive{
    
    public $post_type = false;
    
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
            
            // parent
            $parent = $this->get_menu_parent_slug($object);
            
            // label
            $label = __('Archive <span class="count">(%s)</span>');
            $label = preg_replace('/ <span(.*?)<\/span>/', '', $label);
            
            // capability
            $capability = acf_get_setting('capability');
            $capability = apply_filters_deprecated("acfe/post_type_archive_capability",              array($capability, $name), '0.8.9.1', "acfe/validate_post_type_archive");
            $capability = apply_filters_deprecated("acfe/post_type_archive_capability/name={$name}", array($capability, $name), '0.8.9.1', "acfe/validate_post_type_archive/name={$name}");
            
            // arguments
            $args = array(
                'page_title'             => "{$object->label} {$label}",
                'menu_title'             => $label,
                'menu_slug'              => "{$name}-archive",
                'post_id'                => "{$name}_archive",
                'capability'             => $capability,
                'redirect'               => false,
                'parent_slug'            => $parent,
                'updated_message'        => $object->label . ' Archive Saved.',
                'acfe_post_type_archive' => $name,
            );
            
            // filters
            $args = apply_filters("acfe/validate_post_type_archive",              $args);
            $args = apply_filters("acfe/validate_post_type_archive/name={$name}", $args);
            
            // add options page
            acf_add_options_page($args);
            
        }
        
    }
    
    
    /**
     * current_screen
     */
    function current_screen(){
        
        // global
        global $plugin_page;
        
        if(empty($plugin_page)){
            return;
        }
        
        // get options pages
        $options_pages = acf_get_options_pages();
        
        if(!empty($options_pages) && isset($options_pages[ $plugin_page ]) && isset($options_pages[ $plugin_page ]['acfe_post_type_archive'])){
            
            // assign global post type for later hooks
            $this->post_type = $options_pages[ $plugin_page ]['acfe_post_type_archive'];
    
            // location screen
            add_filter('acf/location/screen', array($this, 'location_screen'));
    
            // get object
            $post_type_obj = get_post_type_object($this->post_type);
    
            if(acfe_maybe_get($post_type_obj, 'has_archive') || $post_type_obj->name === 'post'){
        
                // add permalink under title
                add_action('admin_footer', array($this, 'admin_footer'));
        
            }
            
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
        
        $screen['acfe_post_type_archive'] = true;
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
        if(is_admin()){
            return;
        }
        
        // validate front archive page
        // is_home() is for post type: post
        if(!is_post_type_archive() && !is_home()){
            return;
        }
        
        // try get_post_type()
        $post_type = get_post_type();
    
        if(!$post_type){
        
            // try get_queried_object()
            $object = get_queried_object();
        
            if(is_a($object, 'WP_Post_Type')){
                $post_type = $object->name;
            }
        
        }
        
        if(!$post_type){
            return;
        }
        
        // get object
        $object = get_post_type_object($post_type);
        
        // check has archive
        $has_archive = acfe_maybe_get($object, 'has_archive') || $object->name === 'post';
        $has_archive_page = acfe_maybe_get($object, 'acfe_admin_archive');
        
        if(!$has_archive || !$has_archive_page){
            return;
        }
        
        // get options pages
        $options_pages = acf_get_options_pages();
        
        if(empty($options_pages)){
            return;
        }
    
        $options_page = false;
        
        // loop options pages
        foreach($options_pages as $page){
            
            // validate page
            if(acfe_maybe_get($page, 'acfe_post_type_archive') === $post_type){
                $options_page = $page;
                break;
                
            }
            
        }
        
        if($options_page){
    
            // get capability
            $capability = $options_page['capability'];
    
            // check capability
            if(current_user_can($capability)){
        
                // parent
                $parent = $this->get_menu_parent_slug($object);
        
                // add menu item
                $wp_admin_bar->add_node(array(
                    'id'     => 'edit',
                    'title'  => __('Edit') . ' ' . $object->label . ' ' . __('Archive'),
                    'parent' => false,
                    'href'   => add_query_arg(array('page' => "{$object->name}-archive"), admin_url($parent)),
                    'meta'   => array('class' => 'ab-item')
                ));
        
            }
            
            
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
        
        if(!acf_maybe_get($screen, 'options_page') || !acf_maybe_get($screen, 'acfe_post_type_archive') || !acf_maybe_get($rule, 'value')){
            return $match;
        }
        
        $match = $screen['options_page'] === "{$rule['value']}-archive";
        
        if($rule['value'] === 'all'){
            $match = true;
        }
        
        if($rule['operator'] === '!='){
            $match = !$match;
        }
        
        return $match;

    }
    
    
    /**
     * get_menu_parent_slug
     *
     * @param $object
     *
     * @return string
     */
    function get_menu_parent_slug($object){
        
        $name = $object->name;
        
        // parent
        $parent = "edit.php?post_type={$name}";
        $parent = $name === 'post' ? 'edit.php' : $parent;         // post
        $parent = $name === 'attachment' ? 'upload.php' : $parent; // attachment
        
        // allow post type custom 'show_in_menu' like 'options-general.php'
        if(!empty($object->show_in_menu) && is_string($object->show_in_menu)){
            $parent = $object->show_in_menu;
        }
        
        return $parent;
        
    }
    
}

new acfe_location_post_type_archive();

endif;