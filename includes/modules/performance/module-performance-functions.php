<?php

if(!defined('ABSPATH')){
    exit;
}


/**
 * acfe_get_performance_config
 *
 * @param $key
 *
 * @return mixed|null
 */
function acfe_get_performance_config($key = ''){
    
    // default config
    $config = array(
        'engine'     => 'ultra',      // ultra | hybrid
        'mode'       => 'production', // test | production | rollback
        'ui'         => false,        // sidebar metabox
        'post_types' => array(),      // allowed post types (all)
        'taxonomies' => array(),      // allowed taxonomies (all)
        'users'      => false,        // allowed user roles (none)
        'options'    => false,        // allowed option id  (none)
    );
    
    // deprecated single meta filters
    if($config['engine'] === 'ultra'){
        
        $config['post_types'] = acfe_apply_filters_deprecated('acfe/modules/single_meta/post_types', array($config['post_types']), '0.8.9.3', 'acfe/modules/performance/config');
        $config['taxonomies'] = acfe_apply_filters_deprecated('acfe/modules/single_meta/taxonomies', array($config['taxonomies']), '0.8.9.3', 'acfe/modules/performance/config');
        $config['users']      = acfe_apply_filters_deprecated('acfe/modules/single_meta/users',      array($config['users']),      '0.8.9.3', 'acfe/modules/performance/config');
        $config['options']    = acfe_apply_filters_deprecated('acfe/modules/single_meta/options',    array($config['options']),    '0.8.9.3', 'acfe/modules/performance/config');
        
    }
    
    // use setting
    $setting = acf_get_setting('acfe/modules/performance');
    
    // setting = ultra | hybrid
    if(is_string($setting) && !empty($setting)){
        $config['engine'] = $setting;
        
    // setting = array('engine' => 'ultra'...)
    }elseif(is_array($setting) && !empty($setting)){
        $config = array_merge($config, $setting);
    }
    
    // filter
    $config = apply_filters('acfe/modules/performance/config', $config);
    
    // return key
    if(!empty($key)){
        return acf_maybe_get($config, $key);
    }
    
    // return
    return $config;
    
}


/**
 * acfe_do_performance_bypass
 *
 * @param $func
 * @param $args
 *
 * @return mixed
 */
function acfe_do_performance_bypass($func, $args = array()){
    
    // get engine
    $engine = acfe_get_performance_config('engine');
    
    // get engine
    $engine = acfe_get_performance_engine($engine);
    
    // return
    return $engine->do_bypass($func, $args);
    
}


/**
 * acfe_is_performance_enabled
 *
 * @return bool
 */
function acfe_is_performance_enabled(){
    return (bool) acf_get_setting('acfe/modules/performance');
}


/**
 * acfe_is_object_performance_enabled
 *
 * @param $post_id
 *
 * @return bool
 */
function acfe_is_object_performance_enabled($post_id = 0){
    
    // check setting
    if(!acfe_is_performance_enabled()){
        return false;
    }
    
    // check post id
    if(!$post_id){
        return false;
    }
    
    // check local post id
    if(acfe_is_local_post_id($post_id)){
        return false;
    }
    
    // get config
    $config = acfe_get_performance_config();
    
    // validate engine exists
    if(!acfe_get_performance_engine($config['engine'])){
        return false;
    }
    
    /**
     * @var $type
     * @var $id
     */
    extract(acf_decode_post_id($post_id));
    
    // validate id
    if(!$id){
        return false;
    }
    
    switch($type){
        
        // post types
        case 'post': {
    
            // get post type
            $post_type = get_post_type($id);
            
            // validate
            if(!$post_type){
                return false;
            }
            
            return acfe_is_object_type_performance_enabled('post', $post_type);
            
        }
        
        // taxonomies
        case 'term': {
    
            // get term
            $term = get_term($id);
    
            // validate
            if(is_wp_error($term) || !is_a($term, 'WP_Term')){
                return false;
            }
    
            // get taxonomy
            $taxonomy = $term->taxonomy;
    
            return acfe_is_object_type_performance_enabled('term', $taxonomy);
            
        }
        
        // users
        case 'user': {
    
            // get user
            $user = get_userdata($id);
    
            // validate
            if(!($user) || !is_a($user, 'WP_User')){
                return false;
            }
    
            // get roles
            $roles = acf_get_array($user->roles);
    
            // array of users
            foreach($roles as $role){
                if(acfe_is_object_type_performance_enabled('user', $role)){
                    return true;
                }
            }
            
            return false;
            
        }
        
        // options pages
        case 'option': {
    
            return acfe_is_object_type_performance_enabled('option', $id);
            
        }
        
    }
    
    // type not supported
    // block types...
    return false;
    
}


/**
 * acfe_is_object_type_performance_enabled
 *
 * @param $type
 * @param $object
 *
 * @return bool
 */
function acfe_is_object_type_performance_enabled($type, $object){
    
    // get config
    $config = acfe_get_performance_config();
    
    switch($type){
        
        // post types
        case 'post': {
            
            /**
             * $post_types
             *
             * false         = disallow all
             * true          = allow all
             * array()       = allow all
             * array('post') = allow specific
             */
            
            // vars
            $restricted = acfe_get_setting('reserved_post_types', array());
            $post_types = $config['post_types'];
            
            // no post types allowed
            if($post_types === false){
                return false;
            }
            
            // reserved post type
            if(in_array($object, $restricted)){
                return false;
            }
            
            // allow all post types
            if($post_types === true){
                return true;
            }
            
            // post type not allowed
            if(!empty($post_types) && !in_array($object, $post_types)){
                return false;
            }
    
            // allowed (empty array)
            return true;
            
        }
        
        // taxonomies
        case 'term': {
            
            /**
             * $taxonomies
             *
             * false        = disallow all
             * true         = allow all
             * array()      = allow all
             * array('cat') = allow specific
             */
            
            // vars
            $restricted = acfe_get_setting('reserved_taxonomies', array());
            $taxonomies = $config['taxonomies'];
            
            // no taxonomies allowed
            if($taxonomies === false){
                return false;
            }
            
            // reserved taxonomy
            if(in_array($object, $restricted)){
                return false;
            }
            
            // allow all taxonomies
            if($taxonomies === true){
                return true;
            }
            
            // taxonomy not allowed
            if(!empty($taxonomies) && !in_array($object, $taxonomies)){
                return false;
            }
            
            // allowed (empty array)
            return true;
            
        }
        
        // users
        case 'user': {
            
            /**
             * $users
             *
             * false           = disallow all
             * true            = allow all
             * array()         = allow all
             * array('editor') = allow specific
             */
            
            // vars
            $users = $config['users'];
            
            // no users allowed
            if($users === false){
                return false;
    
            // allow all users
            }elseif($users === true){
                return true;
            }
    
            // taxonomy not allowed
            if(!empty($users) && !in_array($object, $users)){
                return false;
            }
            
            // allowed (empty array)
            return true;
            
        }
        
        // options pages
        case 'option': {
            
            /**
             * $options
             *
             * false            = disallow all
             * true             = allow all
             * array()          = allow all
             * array('options') = allow specific
             */
            
            // vars
            $options = $config['options'];
            
            // no options allowed
            if($options === false){
                return false;
                
            // allow all options
            }elseif($options === true){
                return true;
            }
            
            // option not allowed
            if(!empty($options) && !in_array($object, $options)){
                return false;
            }
    
            // allowed (empty array)
            return true;
            
        }
        
    }
    
    // type not supported
    // block types...
    return false;
    
}


/**
 * acfe_get_object_performance_engine
 *
 * @param $post_id
 *
 * @return false|mixed|null
 */
function acfe_get_object_performance_engine($post_id){
    
    // get engine name
    $name = acfe_get_object_performance_engine_name($post_id);
    
    if(!$name){
        return false;
    }
    
    return acfe_get_performance_engine($name);
    
}


/**
 * acfe_get_object_performance_other_engines
 *
 * @param $post_id
 *
 * @return false|mixed
 */
function acfe_get_object_performance_other_engines($post_id){
    
    // get engine name
    $name = acfe_get_object_performance_engine_name($post_id);
    
    if(!$name){
        return false;
    }
    
    return acfe_query_performance_engines(array('name' => $name), 'NOT');
    
}


/**
 * acfe_get_object_performance_engine_name
 *
 * @param $post_id
 *
 * @return false|mixed|null
 */
function acfe_get_object_performance_engine_name($post_id){
    
    // check enabled
    $enabled = acfe_is_object_performance_enabled($post_id);
    
    if(!$enabled){
        return false;
    }
    
    return acfe_get_performance_config('engine');
    
}


/**
 * acfe_get_object_performance_mode
 *
 * @param $post_id
 *
 * @return false|mixed|null
 */
function acfe_get_object_performance_mode($post_id){
    
    // check enabled
    $enabled = acfe_is_object_performance_enabled($post_id);
    
    if(!$enabled){
        return false;
    }
    
    return acfe_get_performance_config('mode');
    
}


/**
 * acfe_do_performance_convert
 *
 * Converts Ultra/Hybrid meta to the current engine
 *
 * @param $post_id
 */
function acfe_do_performance_convert($post_id){

    // validate enabled
    if(!acfe_is_object_performance_enabled($post_id)){
        return;
    }

    // get current engine data
    $engine = acfe_get_object_performance_engine($post_id);
    $acf = $engine->get_store($post_id);

    // other engines
    $other_engines = acfe_get_object_performance_other_engines($post_id);

    // loop
    foreach($other_engines as $other_engine){
        
        // get other meta
        $other_acf = $other_engine->get_store($post_id);
        
        // other meta found
        if(!empty($other_acf)){
            
            // loop other meta
            foreach($other_acf as $name => $value){
                
                // vars
                $hidden = acfe_starts_with($name, '_');
                $prefix = $hidden ? '_' : '';
                $name = ltrim($name, '_');
                
                switch($other_engine->name){
                    
                    // other engine: ultra
                    case 'ultra': {
                    
                        if(!isset($acf["{$prefix}{$name}"])){
                            acf_update_metadata($post_id, $name, $value, $hidden);
                        }
                    
                        break;
                    
                    }
                    
                    // other engine: hybrid
                    case 'hybrid': {
                    
                        if(!isset($acf["{$prefix}{$name}"])){
                        
                            // _my_field = field_5f9f9f9f9f9f9 exists
                            //  my_field = my value            doesn't exist
                            if(!isset($acf[ $name ])){
                            
                                // try to find in normal meta
                                $meta = $engine->do_bypass(function($post_id, $name){
                                    return acf_get_metadata($post_id, $name, false);
                                }, array($post_id, $name));
                            
                                // normal meta found
                                // update both meta in current engine
                                if($meta !== null){
                                
                                    acf_update_metadata($post_id, $name, $value, $hidden);
                                    acf_update_metadata($post_id, $name, $meta, false);
                                
                                }
                            
                            }else{
                                acf_update_metadata($post_id, $name, $value, $hidden);
                            
                            }
                            
                        }
                    
                        break;
                    
                    }
                
                }
            
                // delete other engine meta
                $other_engine->delete_meta($post_id);
            
            }
        }
    
    }
    
}


/**
 * acfe_do_performance_rollback
 *
 * Rollback Ultra/Hybrid meta to normal meta
 *
 * @param $post_id
 *
 * @return bool
 */
function acfe_do_performance_rollback($post_id = 0){
    
    // validate enabled
    if(!acfe_is_object_performance_enabled($post_id)){
        return false;
    }
    
    // check mode
    if(acfe_get_performance_config('mode') !== 'rollback'){
        return false;
    }
    
    // other engines
    $engines = acfe_get_object_performance_other_engines($post_id);
    
    // add current engine at the end
    // this fix an issue where the current engine would be processed first
    // and the second engine would regenerate again meta leaving residue
    $engines[] = acfe_get_object_performance_engine($post_id);
    
    // loop
    foreach($engines as $engine){
        
        // get compiled store
        $acf = $engine->get_store($post_id);
        
        if(!empty($acf)){
            foreach($acf as $name => $value){
                
                // check if _textarea => field_64148c317fcba
                $hidden = acfe_starts_with($name, '_');
                $name = ltrim($name, '_');
                
                // update as normal meta
                acf_update_metadata($post_id, $name, $value, $hidden);
                
            }
        }
        
        // clean acf
        $engine->delete_meta($post_id);
        
    }
    
    return true;
    
}


/**
 * acfe_get_object_performance_status
 *
 * @param $post_id
 *
 * @return array|false
 */
function acfe_get_object_performance_status($post_id){
    
    // check enabled
    $enabled = acfe_is_object_performance_enabled($post_id);
    
    if(!$enabled){
        return false;
    }
    
    // get meta
    $meta = acfe_get_object_performance_meta($post_id);
    
    // ready  = meta not found
    // active = meta found
    $name = $meta !== null ? 'active' : 'ready';
    $title = $meta !== null ? __('Active', 'acfe') : __('Ready', 'acfe');
    
    $config = acfe_get_performance_config();
    $meta_key = acfe_get_performance_engine($config['engine'])->get_meta_key($post_id);
    
    $return = array(
        'name'    => $name,
        'title'   => $title,
        'message' => '',
    );
    
    switch($name){
        
        case 'active': {
            $return['message'] = __('Performance Mode is active.', 'acfe') . "<br/>" . sprintf(__('The \'%s\' meta was found on this object and is effective.', 'acfe'), $meta_key);
            break;
        }
        
        case 'ready': {
            $return['message'] = __('Performance Mode is ready.', 'acfe') . "<br/>" . sprintf(__('The \'%s\' meta will be created when object will be saved.', 'acfe'), $meta_key);
            break;
        }
        
    }
    
    return $return;
    
}


/**
 * acfe_get_object_performance_conflict
 *
 * @param $post_id
 *
 * @return false|mixed|null
 */
function acfe_get_object_performance_conflict($post_id){
    
    // check enabled
    $enabled = acfe_is_object_performance_enabled($post_id);
    
    if(!$enabled){
        return false;
    }
    
    $return = false;
    
    // get object engine name
    $engine = acfe_get_object_performance_engine_name($post_id);
    
    switch($engine){
        
        // ultra
        case 'ultra': {
            
            $hybrid = acfe_get_performance_engine('hybrid');
            if($hybrid){
                
                // vars
                $meta = $hybrid->get_meta($post_id);
                $meta_key = $hybrid->get_meta_key($post_id);
    
                // hybrid meta found
                if($meta !== null){
        
                    $return = array(
                        'engine'  => 'hybrid',
                        'meta'    => $meta,
                        'title'   => __('Hybrid meta found', 'acfe'),
                        'message' => sprintf(__('Hybrid engine \'%s\' meta found.', 'acfe'), $meta_key). "<br/>" . __("This meta will be converted to Ultra engine upon save.", 'acfe'),
                    );
                }
                
            }
            
            break;
        }
        
        // hybrid
        case 'hybrid': {
            
            $ultra = acfe_get_performance_engine('ultra');
            if($ultra){
                
                // vars
                $meta = $ultra->get_meta($post_id);
                $meta_key = $ultra->get_meta_key($post_id);
    
                // ultra meta found
                if($meta !== null){
        
                    $return = array(
                        'engine'  => 'ultra',
                        'meta'    => $meta,
                        'title'   => __('Ultra meta found', 'acfe'),
                        'message' => sprintf(__('Ultra engine \'%s\' meta found.', 'acfe'), $meta_key). "<br/>" . __("This meta will be converted to Hybrid engine upon save.", 'acfe'),
                    );
        
                }
                
            }
            
            
            break;
        }
        
    }
    
    return $return;
    
}


/**
 * acfe_get_object_performance_meta
 *
 * @param $post_id
 *
 * @return false|mixed|null
 */
function acfe_get_object_performance_meta($post_id){
    
    // get engine
    $engine = acfe_get_object_performance_engine($post_id);
    
    // validate
    if(empty($engine)){
        return null;
    }
    
    // return
    return $engine->get_meta($post_id);
    
}


/**
 * acfe_delete_object_performance_meta
 *
 * @param $post_id
 *
 * @return false|mixed|null
 */
function acfe_delete_object_performance_meta($post_id){
    
    // get engine
    $engine = acfe_get_object_performance_engine($post_id);
    
    // validate
    if(empty($engine)){
        return false;
    }
    
    // return
    return $engine->delete_meta($post_id);
    
}


/**
 * acfe_is_single_meta_enabled
 *
 * @param $post_id
 *
 * @return bool
 * @deprecated
 */
function acfe_is_single_meta_enabled($post_id = 0){
    
    acfe_deprecated_function('acfe_is_single_meta_enabled()', '0.8.9.3', 'acfe_is_object_performance_enabled()');
    return acfe_get_object_performance_engine_name($post_id) === 'ultra';
    
}


/**
 * acfe_get_single_meta
 *
 * @param $post_id
 *
 * @return mixed
 * @deprecated
 */
function acfe_get_single_meta($post_id){
    
    acfe_deprecated_function('acfe_get_single_meta()', '0.8.9.3', 'acfe_get_object_performance_meta()');
    return acfe_get_performance_engine('ultra')->get_meta($post_id);
    
}


/**
 * acfe_delete_single_meta
 *
 * @param $post_id
 *
 * @return bool
 * @deprecated
 */
function acfe_delete_single_meta($post_id){
    
    acfe_deprecated_function('acfe_delete_single_meta()', '0.8.9.3', 'acfe_delete_object_performance_meta()');
    return acfe_get_performance_engine('ultra')->delete_meta($post_id);
    
}