<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_performance')):

class acfe_performance{
    
    // vars
    public $name            = '';
    public $meta_key        = '';
    public $option_key      = '';
    public $bypass          = false;
    public $compile         = false;
    
    /**
     * construct
     */
    function __construct(){
    
        // setup
        $this->initialize();
    
        // register store
        acf_register_store("acfe/performance_meta/{$this->name}")->prop('multisite', true);
    
        // check setting
        if(!acfe_is_performance_enabled()){
            return;
        }
        
        // hooks
        $this->add_filter('acf/pre_load_meta',       array($this, 'pre_load_meta'),       999, 2);
        $this->add_filter('acf/pre_load_metadata',   array($this, 'pre_load_metadata'),   999, 4);
        $this->add_filter('acf/pre_update_metadata', array($this, 'pre_update_metadata'), 999, 5);
        $this->add_filter('acf/pre_delete_metadata', array($this, 'pre_delete_metadata'), 999, 4);
        $this->add_filter('acf/update_value',        array($this, 'update_value'),        999, 3);
        $this->add_action('acf/save_post',           array($this, 'pre_save_post'),       1);
        $this->add_action('acf/save_post',           array($this, 'save_post'),           999);
        
    }
    
    
    /**
     * initialize
     */
    function initialize(){
        // ...
    }
    
    
    /**
     * add_action
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_action($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        if(is_callable($function_to_add)){
            add_action($tag, $function_to_add, $priority, $accepted_args);
        }
        
    }
    
    
    /**
     * add_filter
     *
     * @param $tag
     * @param $function_to_add
     * @param $priority
     * @param $accepted_args
     */
    function add_filter($tag = '', $function_to_add = '', $priority = 10, $accepted_args = 1){
        
        if(is_callable($function_to_add)){
            add_filter($tag, $function_to_add, $priority, $accepted_args);
        }
        
    }
    
    
    /**
     * pre_save_post
     *
     * the 'compile' logic allows to gather all meta into the $store
     * and udpate the 'acf' array only one time to avoid multiple db update calls
     *
     * @param $post_id
     *
     * @hook acf/save_post:1
     *
     * @function acf_save_post()
     */
    function pre_save_post($post_id = 0){
        
        // start compile
        if($this->is_enabled($post_id)){
            $this->compile = $post_id;
        }
        
    }
    
    
    /**
     * save_post
     *
     * @param $post_id
     *
     * @hook acf/save_post:999
     *
     * @function acf_save_post()
     */
    function save_post($post_id = 0){
        
        // compile enabled
        if($this->compile === $post_id){
            
            // get compiled store
            $acf = $this->get_store($post_id);
            
            // update with compiled data
            $this->update_meta($acf, $post_id);
            
            // end of compile
            // free compile for an eventual another acf_save_post() call
            $this->compile = false;
            
            // convert meta
            // has to be after compile reset to actually update meta
            acfe_do_performance_convert($post_id);
    
            // rollback
            if($this->get_config('mode') === 'rollback'){
                acfe_do_performance_rollback($post_id);
            }
            
        }
        
    }
    
    
    /**
     * get_store
     *
     * @param $post_id
     *
     * @return array|mixed|null
     */
    function get_store($post_id){
        
        // check store
        $store = acf_get_store("acfe/performance_meta/{$this->name}");
        
        // store found
        if(!$store->has($post_id)){
            
            // get meta
            $acf = $this->get_meta($post_id);
            $acf = acf_get_array($acf);
    
            // set store: acf meta
            $store->set($post_id, $acf);
            
        }
    
        return $store->get($post_id);
        
    }
    
    
    /**
     * update_store
     *
     * @param $post_id
     * @param $value
     */
    function update_store($post_id, $value){
        
        // get store
        $store = acf_get_store("acfe/performance_meta/{$this->name}");
        
        // update store
        $store->set($post_id, $value);
        
    }
    
    
    /**
     * get_meta_key
     *
     * @param $post_id
     *
     * @return array|mixed|string|string[]
     */
    function get_meta_key($post_id){
    
        /**
         * @var $type
         * @var $id
         */
        extract(acf_decode_post_id($post_id));
    
        // get option key
        if($type === 'option'){
        
            $option_key = $this->option_key;
            $option_key = str_replace('%id%', $id, $option_key);
            
            return $option_key;
        
        // get meta key
        }else{
            return $this->meta_key;
        }
        
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
        
        // meta key
        $meta_key = $this->get_meta_key($post_id);
    
        // get option
        if($type === 'option'){
            $value = get_option($meta_key, null);
            
        // get meta
        }else{
            $value = acf_get_metadata($post_id, $meta_key);
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
    
        // meta key
        $meta_key = $this->get_meta_key($post_id);
        
        // update option
        if($type === 'option'){
            
            $value = wp_unslash($value);
            $autoload = (bool) acf_get_setting('autoload');
            
            return update_option($meta_key, $value, $autoload);
        
        // update meta
        }else{
            return acf_update_metadata($post_id, $meta_key, $value);
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
    
        // meta key
        $meta_key = $this->get_meta_key($post_id);
        
        // delete option
        if($type === 'option'){
            return delete_option($meta_key);
            
        // delete meta
        }else{
            return delete_metadata($type, $id, $meta_key);
        }
        
    }
    
    
    /**
     * do_bypass
     *
     * executes a callback within a bypass scope
     * allowing to get/update/delete 'real' meta
     *
     * @param $func
     * @param $args
     */
    function do_bypass($func, $args = array()){
        
        if(is_callable($func)){
            
            $this->bypass = true;
            
            $return = call_user_func_array($func, $args);
            
            $this->bypass = false;
            
            return $return;
            
        }
        
        return false;
        
    }
    
    
    /**
     * is_enabled
     *
     * @param $post_id
     *
     * @return bool
     */
    function is_enabled($post_id = 0){
        return acfe_get_object_performance_engine_name($post_id) === $this->name;
    }
    
    
    /**
     * get_config
     *
     * @param $key
     *
     * @return mixed|null
     */
    function get_config($key = ''){
        return acfe_get_performance_config($key);
    }
    
}

endif;

// register store
acf_register_store('acfe-performance');


/**
 * acfe_register_performance_engine
 *
 * @param $class
 *
 * @return bool
 */
function acfe_register_performance_engine($class){
    
    // instantiate
    $engine = new $class();
    
    // add to store
    acf_get_store('acfe-performance')->set($engine->name, $engine);
    
    // return
    return true;
    
}


/**
 * acfe_get_performance_engines
 *
 * @return array|mixed|null
 */
function acfe_get_performance_engines(){
    return acf_get_store('acfe-performance')->get();
}


/**
 * acfe_get_performance_engine
 *
 * @param $engine
 *
 * @return acfe_performance|array|mixed|null
 */
function acfe_get_performance_engine($engine){
    
    if($engine instanceof acfe_performance){
        return $engine;
    }
    
    return acf_get_store('acfe-performance')->get($engine);
}


/**
 * acfe_query_performance_engine
 *
 * @param $query
 * @param $operator
 *
 * @return false|mixed
 */
function acfe_query_performance_engine($query = array(), $operator = 'AND'){
    
    $engines = acfe_query_performance_engines($query, $operator);
    return current($engines);
    
}


/**
 * acfe_query_performance_engines
 *
 * @param $query
 * @param $operator
 *
 * @return false|mixed
 */
function acfe_query_performance_engines($query = array(), $operator = 'AND'){
    return acf_get_store('acfe-performance')->query($query, $operator);
}