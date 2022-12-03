<?php

if(!defined('ABSPATH')){
    exit;
}

// Register store
acf_register_store('acfe/meta')->prop('multisite', true);

if(!class_exists('acfe_single_meta')):

class acfe_single_meta{
    
    // vars
    public $restricted = array();
    public $post_types = array();
    public $taxonomies = array();
    public $users = array();
    public $options = array();
    
    /**
     * construct
     */
    function __construct(){
        
        // vars
        $this->restricted = acfe_get_setting('reserved_post_types', array());
        $this->post_types = apply_filters('acfe/modules/single_meta/post_types', array());
        $this->taxonomies = apply_filters('acfe/modules/single_meta/taxonomies', array());
        $this->users = apply_filters('acfe/modules/single_meta/users', false);
        $this->options = apply_filters('acfe/modules/single_meta/options', false);
    
        // values
        add_filter('acf/pre_load_meta',             array($this, 'acf_get_meta'),           999, 2);
        add_filter('acf/pre_load_metadata',         array($this, 'acf_get_metadata'),       999, 4);
        add_filter('acf/pre_update_metadata',       array($this, 'acf_update_metadata'),    999, 5);
        add_filter('acf/pre_delete_metadata',       array($this, 'acf_delete_metadata'),    999, 4);
        add_filter('acf/update_value',              array($this, 'acf_update_value'),       999, 3);
        
        // save post
        add_action('acf/save_post',                 array($this, 'pre_save_post'),          1);
        add_action('acf/save_post',                 array($this, 'save_post'),              999);
        
        // field settings
        add_action('acf/render_field_settings',     array($this, 'render_field_settings'));
        
        // revisions
        add_filter('acf/pre_update_metadata',       array($this, 'revision_pre_update'),    10, 5);
        add_filter('_wp_post_revision_fields',      array($this, 'revision_fields'),        10, 2);
    
        // check setting
        if(acf_get_setting('acfe/modules/single_meta')){
            acf_enable_filter('acfe/single_meta');
        }
        
        
    }
    
    
    /**
     * acf_get_meta
     *
     * @param $return
     * @param $post_id
     *
     * @function acf_get_meta()
     *
     * @return array|mixed|null
     */
    function acf_get_meta($return, $post_id){
    
        // bail early if filter disabled
        if(!$this->is_enabled($post_id)){
            return $return;
        }
    
        // get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // return store data
        return $acf;
        
    }
    
    
    /**
     * acf_get_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $hidden
     *
     * @function acf_get_metadata()
     *
     * @return mixed
     */
    function acf_get_metadata($return, $post_id, $name, $hidden){
        
        // bail early if acf meta or disabled filter
        if($name === 'acf' || !$this->is_enabled($post_id)){
            return $return;
        }
        
        // get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // bail early if empty
        if(empty($acf)) return $return;
        
        // unslash values if needed
        if(acf_is_filter_enabled('acfe/single_meta/unslash')){
            $acf = wp_unslash($acf);
        }
        
        // prefix
        $prefix = $hidden ? '_' : '';
        
        // value
        if(isset($acf["{$prefix}{$name}"])){
            $return = $acf["{$prefix}{$name}"];
        }
        
        return $return;
        
    }
    
    
    /**
     * acf_update_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $value
     * @param $hidden
     *
     * @function acf_update_metadata()
     *
     * @return bool|mixed|null
     */
    function acf_update_metadata($return, $post_id, $name, $value, $hidden){
        
        // bail early if acf meta
        if($name === 'acf' || !$this->is_enabled($post_id)){
            return $return;
        }
        
        // get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // prefix
        $prefix = $hidden ? '_' : '';
        
        // value
        $acf["{$prefix}{$name}"] = $value;
    
        // update store
        $store->set("$post_id:acf", $acf);
        
        // unlash for preload on same page as update
        acf_enable_filter('acfe/single_meta/unslash');
        
        // update if not compiling (when using update_field() for example)
        if(!acf_is_filter_enabled("acfe/single_meta/compile/{$post_id}")){
            $this->update_meta($acf, $post_id);
        }
        
        // save as individual meta
        if(acf_is_filter_enabled('acfe/single_meta/normal_save')){
            return null;
        }
    
        // Do not save normal meta
        return true;
        
    }
    
    
    /**
     * acf_delete_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $hidden
     *
     * @function acf_delete_metadata()
     *
     * @return bool|mixed
     */
    function acf_delete_metadata($return, $post_id, $name, $hidden){
        
        // bail early if acf meta or native delete
        if($name === 'acf' || !$this->is_enabled($post_id)){
            return $return;
        }
    
        // get store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // bail early if empty
        if(empty($acf)) return $return;
        
        // prefix
        $prefix = $hidden ? '_' : '';
    
        if(isset($acf["{$prefix}{$name}"])){
            
            // value
            unset($acf["{$prefix}{$name}"]);
            
            // update store
            $store->set("$post_id:acf", $acf);
            
            // update meta
            $this->update_meta($acf, $post_id);
        
        }
        
        // delete acf meta if empty
        if(empty($acf)){
            $this->delete_meta($post_id);
        }
        
        // do not delete normal meta
        return true;
    
    }
    
    
    /**
     * acf_update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @function acf_update_value()
     *
     * @return mixed
     */
    function acf_update_value($value, $post_id, $field){
        
        // disabled by default
        acf_disable_filter('acfe/single_meta/normal_save');
        
        // check if save as individual meta
        if(acf_maybe_get($field, 'acfe_save_meta')){
            acf_enable_filter('acfe/single_meta/normal_save');
        }
        
        return $value;
        
    }
    
    
    /**
     * pre_save_post
     *
     * @param $post_id
     *
     * @hook acf/save_post:0
     */
    function pre_save_post($post_id = 0){
    
        // validate post id
        if(!$this->is_enabled($post_id)){
            return;
        }
    
        // enable compile
        acf_enable_filter("acfe/single_meta/compile/{$post_id}");
        
    }
    
    
    /**
     * save_post
     *
     * @param $post_id
     *
     * @hook acf/save_post:999
     */
    function save_post($post_id = 0){
        
        // check compile
        if(!acf_is_filter_enabled("acfe/single_meta/compile/{$post_id}")){
            return;
        }
        
        // get compiled store
        $store = $this->get_store($post_id);
        $acf = $store->get("$post_id:acf");
        
        // update compiled store
        $this->update_meta($acf, $post_id);
        
    }
    
    
    /**
     * get_store
     *
     * @param $post_id
     *
     * @return ACF_Data|false
     */
    function get_store($post_id){
        
        // check store
        $store = acf_get_store('acfe/meta');
        
        // store found
        if(!$store->has("$post_id:acf")){
            
            // get meta
            $acf = $this->get_meta($post_id);
    
            // set store: acf meta
            $store->set("$post_id:acf", $acf);
            
        }
        
        return $store;
        
    }
    
    
    /**
     * get_meta
     *
     * @param $post_id
     *
     * @return false|mixed|null
     */
    function get_meta($post_id){
        
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($post_id));
    
        // get option
        if($type === 'option'){
            $value = get_option($id, null);
            
        // get meta
        }else{
            $value = acf_get_metadata($post_id, 'acf');
        }
        
        return $value;
        
    }
    
    
    /**
     * update_meta
     *
     * @param $value
     * @param $post_id
     *
     * @return bool|int
     */
    function update_meta($value, $post_id){
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($post_id));
        
        // update option
        if($type === 'option'){
            
            $value = wp_unslash($value);
            $autoload = (bool) acf_get_setting('autoload');
            
            return update_option($id, $value, $autoload);
        
        // update meta
        }else{
            return acf_update_metadata($post_id, 'acf', $value);
        }
        
    }
    
    
    /**
     * delete_meta
     *
     * @param $post_id
     *
     * @return bool
     */
    function delete_meta($post_id){
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($post_id));
        
        // delete option
        if($type === 'option'){
            return delete_option($id);
            
        // delete meta
        }else{
            return delete_metadata($type, $id, 'acf');
        }
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        // validate
        // cannot use acf_is_enable_filter() because ACF disable filters in Field Group UI
        if(!acf_get_setting('acfe/modules/single_meta')){
            return;
        }
        
        // vars
        $exclude = array('acfe_button', 'acfe_column', 'acfe_recaptcha', 'acfe_dynamic_render', 'accordion', 'message', 'tab');
        
        // check if excluded
        if(in_array($field['type'], $exclude)){
            return;
        }
        
        // settings
        acf_render_field_setting($field, array(
            'label'             => __('Save as individual meta'),
            'key'               => 'acfe_save_meta',
            'name'              => 'acfe_save_meta',
            'instructions'      => __('Save the field as an individual meta.'),
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
    
    
    /**
     * revision_pre_update
     *
     * @param $null
     * @param $revision_id
     * @param $name
     * @param $value
     * @param $hidden
     *
     * @return bool|mixed
     */
    function revision_pre_update($null, $revision_id, $name, $value, $hidden){
        
        // bail early if not acf meta or not revision
        if($name !== 'acf' || !$this->is_enabled() || !wp_is_post_revision($revision_id)){
            return $null;
        }
        
        // get parent post id (original post)
        $post_id = wp_get_post_parent_id($revision_id);
        
        // check parent post has single meta
        if(!$this->is_enabled($post_id)) return $null;
        
        // enable compile to avoid multiple save
        acf_enable_filter("acfe/single_meta/compile/{$revision_id}");
    
        // get parent post id values when not in preview
        if(acf_maybe_get_POST('wp-preview') !== 'dopreview'){
        
            // get acf meta
            $value = acf_get_metadata($post_id, 'acf');
        
            // unslash for revision
            $value = wp_unslash($value);
        
        }
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($revision_id));
        
        $prefix = $hidden ? '_' : '';
        
        // update
        update_metadata($type, $id, "{$prefix}{$name}", $value);
        
        // do not save normally (already did it)
        return true;
        
    }
    
    
    /**
     * revision_fields
     *
     * @param $fields
     * @param $post
     *
     * @return mixed
     */
    function revision_fields($fields, $post = null){
        
        // validate page
        if(acf_is_screen('revision') || acf_is_ajax('get-revision-diffs')){
            
            // bail early if is restoring
            if(acf_maybe_get_GET('action') === 'restore'){
                return $fields;
            }
            
        // allow
        }else{
            
            // bail early (most likely saving a post)
            return $fields;
            
        }
        
        // vars
        $post_id = acf_maybe_get($post, 'ID', false);
        
        // compatibility with WP < 4.5 (test)
        if(!$post_id){
            
            global $post;
            $post_id = $post->ID;
            
        }
    
        // check post has single meta
        if(!$this->is_enabled($post_id)){
            return $fields;
        }
        
        // get all postmeta
        $meta = get_post_meta($post_id);
        
        // bail early if no meta
        if(!$meta || !isset($meta['acf'])){
            return $fields;
        }
        
        // hook into specific revision field filter and return local value
        add_filter('_wp_post_revision_field_acf', array($this, 'revision_field'), 10, 4);
        
        $fields['acf'] = 'ACF';
        
        // return
        return $fields;
        
    }
    
    
    /**
     * revision_field
     *
     * revision field for acf
     *
     * @param $value
     * @param $field_name
     * @param $post
     * @param $direction
     *
     * @return bool|mixed|string
     */
    function revision_field($value, $field_name, $post = null, $direction = false){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // value has not yet been 'maybe_unserialize'
        $value = maybe_unserialize($value);
        
        // formatting
        if(is_array($value)){
            $value = print_r($value, true);
        }
        
        // return
        return $value;
        
    }
    
    
    /**
     * is_enabled
     *
     * @param $post_id
     *
     * @return array|bool
     */
    function is_enabled($post_id = null){
        
        // check filter
        $is_enabled = acf_is_filter_enabled('acfe/single_meta');
        
        // return global filter
        if(!$is_enabled || $post_id === null){
            return $is_enabled;
        }
        
        // validate post id
        return $this->validate_post_id($post_id);
        
    }
    
    
    /**
     * validate_post_id
     *
     * @param $post_id
     *
     * @return bool
     */
    function validate_post_id($post_id){
        
        // do not process local post id
        if(acfe_is_local_post_id($post_id)){
            return false;
        }
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($post_id));
        
        // validate id
        if(!$id) return false;
        
        // post type
        if($type === 'post'){
            
            // no post types allowed
            if($this->post_types === false){
                return false;
            }
            
            $post_type = get_post_type($id);
            
            // reserved post type
            if(in_array($post_type, $this->restricted)){
                return false;
            }
            
            // post type not allowed
            if(!empty($this->post_types) && !in_array($post_type, $this->post_types)){
                return false;
            }
            
            return true;
            
        }
        
        // taxonomy
        elseif($type === 'term'){
            
            // no taxonomies allowed
            if($this->taxonomies === false){
                return false;
            }
            
            // get term
            $term = get_term($id);
            
            // term not found
            if(!is_a($term, 'WP_Term')){
                return false;
            }
            
            $taxonomy = $term->taxonomy;
            
            // taxonomy not allowed
            if(!empty($this->taxonomies) && !in_array($taxonomy, $this->taxonomies)){
                return false;
            }
            
            return true;
            
        }
        
        // user
        elseif($type === 'user'){
            
            // no users allowed
            if($this->users === false){
                return false;
            }
            
            // get user
            $user = get_userdata($id);
            
            // user not found
            if(!is_a($user, 'WP_User')){
                return false;
            }
            
            // get roles
            $roles = acf_get_array($user->roles);
            
            // user not allowed
            if(!empty($this->users)){
                
                $allowed = false;
                
                foreach($roles as $role){
                    
                    if(in_array($role, $this->users)){
                        $allowed = true;
                        break;
                    }
                    
                }
                
                if(!$allowed){
                    return false;
                }
                
            }
            
            return true;
            
        }
        
        // option
        elseif($type === 'option'){
            
            // no options allowed
            if($this->options === false){
                return false;
            }
            
            // option not allowed
            if(!empty($this->options) && !in_array($id, $this->options)){
                return false;
            }
            
            return true;
            
        }
        
        return false;
        
    }
    
}

acf_new_instance('acfe_single_meta');

endif;


/**
 * acfe_is_single_meta_enabled
 *
 * @param $post_id
 *
 * @return mixed
 */
function acfe_is_single_meta_enabled($post_id = null){
    return acf_get_instance('acfe_single_meta')->is_enabled($post_id);
}


/**
 * acfe_single_meta_validate_post_id
 *
 * @param $post_id
 *
 * @return mixed
 */
function acfe_single_meta_validate_post_id($post_id){
    return acf_get_instance('acfe_single_meta')->validate_post_id($post_id);
}


/**
 * acfe_get_single_meta
 *
 * @param $post_id
 *
 * @return mixed
 */
function acfe_get_single_meta($post_id){
    return acf_get_instance('acfe_single_meta')->get_meta($post_id);
}


/**
 * acfe_delete_single_meta
 *
 * @param $post_id
 *
 * @return mixed
 */
function acfe_delete_single_meta($post_id){
    return acf_get_instance('acfe_single_meta')->delete_meta($post_id);
}