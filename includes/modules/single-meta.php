<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/single_meta'))
    return;

// Register notices store.
acf_register_store('acfe/meta')->prop('multisite', true);

if(!class_exists('acfe_single_meta')):

class acfe_single_meta{
    
    public $data = array();
    public $restricted = array();
    public $post_types = array();
    public $taxonomies = array();
    public $options = array();
    
    function __construct(){
        
        $this->restricted = array('acf-field-group', 'acf-field', 'attachment', 'acfe-dbt', 'acfe-dop', 'acfe-dpt', 'acfe-dt', 'acfe-form');
        
        $this->post_types = apply_filters('acfe/modules/single_meta/post_types', array());
        $this->taxonomies = apply_filters('acfe/modules/single_meta/taxonomies', array());
        $this->options = apply_filters('acfe/modules/single_meta/options', false);
        
        // Field Objects
        add_filter('acf/pre_load_meta',         array($this, 'load_meta'),          999, 2);
        
        // Values
        add_filter('acf/pre_load_metadata',     array($this, 'load_metadata'), 		999, 4);
        add_filter('acf/update_value',          array($this, 'update_value'),       999, 3);
        add_filter('acf/pre_update_metadata',   array($this, 'update_metadata'),    999, 5);
        add_filter('acf/pre_delete_metadata',   array($this, 'delete_metadata'),    999, 4);
        
        // Save Post
        add_action('acf/save_post',             array($this, 'pre_save_post'),      0);
        add_action('acf/save_post',             array($this, 'save_post'),          999);
        
        // Settings
        add_action('acf/render_field_settings', array($this, 'field_setting'));
        
        // Post
        add_action('load-post.php',         	array($this, 'load_post'));
        add_action('load-post-new.php',     	array($this, 'load_post'));
        
        // Term
        add_action('load-edit-tags.php',    	array($this, 'load_term'));
        add_action('load-term.php',         	array($this, 'load_term'));
        
        // User
        add_action('load-user-new.php',     	array($this, 'load_user'));
        add_action('load-user-edit.php',    	array($this, 'load_user'));
        add_action('load-profile.php',      	array($this, 'load_user'));
    
        // Options
        //add_action('acf/options_page/submitbox_before_major_actions', array($this, 'load_options'));
        
    }
    
    function load_meta($return, $post_id){
    
        if(acf_is_filter_enabled('acfe/load_meta'))
            return $return;
    
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
    
        if(!$validate)
            return $return;
    
        // Get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        return $acf;
        
    }
    
    /*
     * Load Metadata
     */
    function load_metadata($return, $post_id, $name, $hidden){
        
        if($name === 'acf')
            return $return;
        
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
        
        if(!$validate)
            return $return;
        
        // Get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // Bail early if empty
        if(empty($acf))
            return $return;
        
        // Prefix
        $prefix = $hidden ? '_' : '';
        
        if(isset($acf["{$prefix}{$name}"])){
            
            // Value
            $return = $acf["{$prefix}{$name}"];
            
        }
        
        return $return;
        
    }
    
    /*
     * Update Value
     */
    function update_value($value, $post_id, $field){
    
        acf_disable_filter('acfe/save');
        
        if(acf_maybe_get($field, 'acfe_save_meta')){
        
            acf_enable_filter('acfe/save');
            
        }
        
        return $value;
        
    }
    
    /*
     * Update Metadata
     */
    function update_metadata($return, $post_id, $name, $value, $hidden){
    
        if($name === 'acf')
            return $return;
        
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
        
        if(!$validate)
            return $return;
        
        // Get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
    
        // Decode $post_id for $type and $id.
        extract(acf_decode_post_id($post_id));
        
        // Prefix
        $prefix = $hidden ? '_' : '';
        
        // Value
        $acf["{$prefix}{$name}"] = $value;
    
        // Update store
        $store->set("$post_id:acf", $acf);
    
        // Update option
        if($type === 'option'){
        
            $acf = wp_unslash($acf);
            $autoload = (bool) acf_get_setting('autoload');
        
            update_option($id, $acf, $autoload);
        
        // Update meta
        }else{
        
            acf_update_metadata($post_id, 'acf', $acf);
        
        }
        
        // Save normally
        if(acf_is_filter_enabled('acfe/save')){
            
            return null;
            
        }
    
        // Delete Native ACF field
        acf_enable_filter('acfe/delete');
    
            acf_delete_metadata($post_id, $name, $hidden);
    
        acf_disable_filter('acfe/delete');
    
        // Do not save as meta
        return true;
        
    }
    
    /*
     * Delete Metadata
     */
    function delete_metadata($return, $post_id, $name, $hidden){
        
        if($name === 'acf' || acf_is_filter_enabled('acfe/delete'))
            return $return;
    
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
    
        if(!$validate)
            return $return;
    
        // Get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // Bail early if empty
        if(empty($acf))
            return $return;
    
        // Decode $post_id for $type and $id.
        extract(acf_decode_post_id($post_id));
        
        // Prefix
        $prefix = $hidden ? '_' : '';
    
        if(isset($acf["{$prefix}{$name}"])){
            
            // Value
            unset($acf["{$prefix}{$name}"]);
            
            // Update store
            $store->set("$post_id:acf", $acf);
    
            // Update option
            if($type === 'option'){
    
                $acf = wp_unslash($acf);
                $autoload = (bool) acf_get_setting('autoload');
        
                update_option($id, $acf, $autoload);
        
            // Update meta
            }else{
        
                acf_update_metadata($post_id, 'acf', $acf);
        
            }
        
        }
    
        return $return;
    
    }
    
    /*
     * acf/save_post:0
     */
    function pre_save_post($post_id = 0){
        
        if(!acf_maybe_get_POST('acfe_clean_meta'))
            return;
        
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
        
        if(!$validate)
            return;
        
        // Reset ACF
        $acf = array();
    
        // Check store.
        $store = acf_get_store('acfe/meta');
        $store->set("$post_id:acf", $acf);
        
    }

    /*
     * acf/save_post:999
     */
    function save_post($post_id = 0){
        
        if(!acf_maybe_get_POST('acfe_clean_meta'))
            return;
    
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
    
        if(!$validate)
            return;
    
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        acf_enable_filter('acfe/load_meta');
        
            $meta = acf_get_meta($post_id);
        
        acf_disable_filter('acfe/load_meta');
        
        if(empty($meta))
            return;

        acf_enable_filter('acfe/delete');
        
        foreach($meta as $key => $value){
            
            // bail if not ACF field
            if(!isset($meta["_$key"]))
                continue;
            
            // Bail early if exists in Single Value array
            if(isset($acf[$key]))
                continue;
            
            acf_delete_metadata($post_id, $key);
            acf_delete_metadata($post_id, $key, true);
            
        }

        acf_disable_filter('acfe/delete');
        
    }
    
    function validate_post_id($post_id){
        
        // Type + ID
        extract(acf_decode_post_id($post_id));
        
        // Validate ID
        if(!$id)
            return false;
        
        // Post Type
        if($type === 'post'){
    
            if($this->post_types === false)
                return false;
            
            $post_type = get_post_type($id);
    
            if(in_array($post_type, $this->restricted))
                return false;
    
            if(!empty($this->post_types) && !in_array($post_type, $this->post_types))
                return false;
            
            return true;
            
        // Taxonomy
        }elseif($type === 'term'){
    
            if($this->taxonomies === false)
                return false;
            
            $term = get_term($id);
            
            if(is_a($term, 'WP_Term')){
                
                $taxonomy = $term->taxonomy;
    
                if(!empty($this->taxonomies) && !in_array($taxonomy, $this->taxonomies))
                    return false;
    
                return true;
                
            }
    
        // Option
        }elseif($type === 'option'){
    
            if($this->options === false)
                return false;
    
            if(!empty($this->options) && !in_array($id, $this->options))
                return false;
    
            return true;
    
        }
        
        
        
        return false;
        
    }
    
    function get_store($post_id){
        
        // Check store.
        $store = acf_get_store('acfe/meta');
        
        // Store found
        if(!$store->has("$post_id:acf")){
    
            // Decode $post_id for $type and $id.
            extract(acf_decode_post_id($post_id));
    
            // Get option
            if($type === 'option'){
    
                $acf = get_option($id, null);
        
            // Get meta
            }else{
                
                $acf = acf_get_metadata($post_id, 'acf');
                
            }
            
            // Set Store: ACF meta
            $store->set("$post_id:acf", $acf);
            
        }
        
        return $store;
        
    }
    
    /*
     * Field Setting
     */
    function field_setting($field){
        
        // Settings
        acf_render_field_setting($field, array(
            'label'             => __('Save as meta'),
            'key'               => 'acfe_save_meta',
            'name'              => 'acfe_save_meta',
            'instructions'      => __('Save the field an individual meta (useful for WP_Query).'),
            'type'              => 'true_false',
            'required'          => false,
            'conditional_logic' => false,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
        ));
        
    }

    function load_post(){
        
        if($this->post_types === false)
            return;
    
        // globals
        global $typenow;
    
        $post_type = $typenow;
    
        if(in_array($post_type, $this->restricted))
            return;
    
        if(!empty($this->post_types) && !in_array($post_type, $this->post_types))
            return;
        
        // actions
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
        
    }
    
    function add_meta_boxes($post_type, $post){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), $post_type, 'side', 'core');
        
    }
    
    function load_term(){
    
        if($this->taxonomies === false)
            return;
        
        $screen = get_current_screen();
        $taxonomy = $screen->taxonomy;
    
        if(!empty($this->taxonomies) && !in_array($taxonomy, $this->taxonomies))
            return;
        
        // actions
        add_action("{$taxonomy}_edit_form", array($this, 'edit_term'), 20, 2);
        
    }
    
    function edit_term($term, $taxonomy){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), 'edit-' . $term->taxonomy, 'side', 'core');
        
    }
    
    function load_user(){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), 'user-edit', 'side', 'default');
        
    }
    
    /*
    function load_options($page){
    
        if($this->options === false)
            return;
    
        if(!empty($this->options) && !in_array($page['post_id'], $this->options))
            return;
    
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), 'acf_options_page', 'side', 'default');
        
    }
    */
    
    function render_metabox($post, $metabox){
        
        $field = array(
            'key' => false,
            'label' => false,
            'name' => 'acfe_clean_meta',
            'prefix' => false,
            'type' => 'true_false',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'message' => 'Clean orphan meta data',
            'default_value' => 0,
            'ui' => 0,
            'ui_on_text' => '',
            'ui_off_text' => '',
        );
        
        acf_render_field_wrap($field);
        
        ?>
        <script type="text/javascript">
        if( typeof acf !== 'undefined' ) {
            
            acf.newPostbox({
                'id': 'acfe-clean-meta',
                'label': 'top'
            });

        }
        </script>
        <?php
        
    }
    
}

new acfe_single_meta();

endif;