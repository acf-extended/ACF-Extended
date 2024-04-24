<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_performance_connector')):

class acfe_performance_connector{
    
    var $bypass = false;
    
    /**
     * construct
     */
    function __construct(){
        
        // hooks
        add_filter('acf/pre_load_meta',       array($this, 'pre_load_meta'),      1000, 2);
        add_filter('acf/pre_load_metadata',   array($this, 'pre_load_metadata'),  1000, 4);
        add_filter('acfe/delete_orphan_meta', array($this, 'delete_orphan_meta'), 10, 3);
        
    }
    
    
    /**
     * pre_load_meta
     *
     * Preload get_fields() from other engines if meta of current engine are missing
     *
     * @param $return
     * @param $post_id
     *
     * @hook acf/pre_load_meta:1000
     *
     * @function acf_get_meta() + get_fields()
     *
     * @return array|mixed|null
     */
    function pre_load_meta($return, $post_id){
    
        // value already found
        if($return !== null){
            return $return;
        }
        
        // disabled module
        if(!$this->is_enabled($post_id)){
            return $return;
        }
    
        // get engines
        $engines = acfe_get_object_performance_other_engines($post_id);
    
        // loop
        foreach($engines as $engine){
    
            // 'acf' meta found on object
            if($engine->get_meta($post_id)){
        
                $preload = $engine->do_pre_load_meta($return, $post_id);
                
                if($preload !== null){
                    return $preload;
                }
                
            }
        
        }
        
        return $return;
        
    }
    
    
    /**
     * pre_load_metadata
     *
     * Preload metadata from other engines if meta value from current engine is missing
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $hidden
     *
     * @hook acf/pre_load_metadata:1000
     *
     * @function acf_get_metadata() + acf_get_value()
     *
     * @return mixed
     */
    function pre_load_metadata($return, $post_id, $name, $hidden){
    
        // value already found
        if($return !== null){
            return $return;
        }
        
        // disabled module
        if(!$this->is_enabled($post_id) || $this->bypass){
            return $return;
        }
    
        // vars
        $this->bypass = true;
        $engines = acfe_get_object_performance_other_engines($post_id);
    
        // loop
        foreach($engines as $engine){
    
            // 'acf' meta found on object
            if($engine->get_meta($post_id)){
    
                $preload = $engine->do_pre_load_metadata($return, $post_id, $name, $hidden);
    
                if($preload !== null){
                    $return = $preload;
                    break;
                }
                
            }
        
        }
    
        $this->bypass = false;
        
        // return
        return $return;
        
    }
    
    
    /**
     * delete_orphan_meta
     *
     * @param $return
     * @param $post_id
     * @param $confirm
     *
     * @return array[]
     */
    function delete_orphan_meta($return, $post_id, $confirm){
        
        // engine name
        $engine_name = acfe_get_object_performance_engine_name($post_id);
        
        // performance enabled
        if(acfe_is_object_performance_enabled($post_id)){
            
            // engine
            $performance_mode = acfe_get_object_performance_mode($post_id);
            $engine = acfe_get_performance_engine($engine_name);
            
            // performance deleted
            $performance_deleted = $return['normal'];
            $performance_key = $engine->get_meta_key($post_id);
            
            // reset return
            $return = array(
                'normal' => array(),
            );
            
            // if engine = hybrid
            // previous 'clean meta' cleaned reference + meta in the whole '$performance_deleted'
            // we must fix that by moving non prefixed meta into normal return
            if($engine_name === 'hybrid'){
                
                foreach(array_keys($performance_deleted) as $key){
                    
                    $value = $performance_deleted[ $key ];
                    
                    // prefixed meta
                    if(strpos($key, '_') !== 0){
                        
                        $return['normal'][ $key ] = $value;
                        unset($performance_deleted[ $key ]);
                        
                    }
                    
                }
                
            }
            
            if(!empty($performance_deleted)){
                $return[ $performance_key ] = $performance_deleted;
            }
        
            // clean normal orphan
            // this will clean orphan from normal meta
            $normal_deleted = acfe_do_performance_bypass(function($post_id, $confirm){
            
                // get orphan
                $meta = acfe_get_orphan_meta($post_id);
                $deleted = array();
            
                // loop
                foreach($meta as $row){
                
                    // vars
                    $key = $row['key'];
                    $name = $row['name'];
                    $value = $row['value'];
                
                    // delete
                    if($confirm){
                    
                        acf_delete_metadata($post_id, $name, true);  // prefix
                        acf_delete_metadata($post_id, $name);        // normal
                    
                    }
                
                    // store
                    $deleted[ "_{$name}" ] = $key;
                    $deleted[ $name ] = $value;
                
                }
                
                return $deleted;
                
            }, array($post_id, $confirm));
            
            // return
            $return['normal'] = array_merge($return['normal'], $normal_deleted);
            
            // clean normal meta
            // this will clean normal meta which should be removed in mode=production
            if($performance_mode === 'production'){
                
                $normal_deleted = acfe_do_performance_bypass(function($post_id, $confirm){
                    
                    // meta (acf / _acf)
                    $acf = acfe_get_object_performance_meta($post_id);
        
                    $engine_name = acfe_get_object_performance_engine_name($post_id);
                    
                    // performance meta doesn't exist
                    // mode=rollback was probably used
                    if($acf === null){
                        return array();
                    }
                
                    // get orphan
                    $meta = acfe_get_meta($post_id);
                    $deleted = array();
                
                    // loop
                    foreach($meta as $row){
                    
                        // vars
                        $field = $row['field'];
                        $key = $row['key'];
                        $name = $row['name'];
                        $value = $row['value'];
                        
                        switch($engine_name){
                            
                            case 'ultra': {
        
                                // check if field has 'save as individual meta' and already in 'acf' meta
                                if(!acf_maybe_get($field, 'acfe_save_meta') || !isset($acf[ $name ])){
            
                                    // delete
                                    if($confirm){
                
                                        acf_delete_metadata($post_id, $name, true); // prefix
                                        acf_delete_metadata($post_id, $name);       // normal
                
                                    }
            
                                    // store
                                    $deleted[ "_{$name}" ] = $key;
                                    $deleted[ $name ] = $value;
            
                                }
                                
                                break;
                            }
                            
                            case 'hybrid': {
        
                                // delete
                                if($confirm){
                                    acf_delete_metadata($post_id, $name, true); // prefix
                                }
        
                                // store
                                $deleted[ "_{$name}" ] = $key;
                                
                                break;
                            }
                            
                        }
                    
                    }
                
                    return $deleted;
                
                }, array($post_id, $confirm));
                
                // return
                $return['normal'] = array_merge($return['normal'], $normal_deleted);
        
            }
            
        }
        
        // this is done on
        // performance enabled +
        // performance disabled
        
        // not ultra
        if($engine_name !== 'ultra'){
            
            // ultra engine
            $ultra = acfe_get_performance_engine('ultra');
            
            if($ultra){
    
                // meta key
                $meta_key = $ultra->get_meta_key($post_id);
    
                // clean ultra residue (acf)
                $meta = $ultra->get_meta($post_id);
    
                if($meta !== null){
                    $ultra->delete_meta($post_id);
                    $return['normal'][ $meta_key ] = $meta;
                }
                
            }
            
        }
        
        // not hybrid
        if($engine_name !== 'hybrid'){
        
            // hybrid engine
            $hybrid = acfe_get_performance_engine('hybrid');
            
            if($hybrid){
    
                // meta key
                $meta_key = $hybrid->get_meta_key($post_id);
    
                // clean hybrid residue (_acf)
                $meta = $hybrid->get_meta($post_id);
    
                if($meta !== null){
                    $hybrid->delete_meta($post_id);
                    $return['normal'][ $meta_key ] = $meta;
                }
                
            }
            
        }
        
        // return
        return $return;
        
    }
    
    
    /**
     * is_enabled
     *
     * @param $post_id
     *
     * @return bool
     */
    function is_enabled($post_id = 0){
        return acfe_is_object_performance_enabled($post_id);
    }
    
}

acf_new_instance('acfe_performance_connector');

endif;