<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_performance_ultra')):

class acfe_performance_ultra extends acfe_performance{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name       = 'ultra';
        $this->meta_key   = 'acf';
        $this->option_key = '%id%';
        
    }
    
    
    /**
     * pre_load_meta
     *
     * @param $return
     * @param $post_id
     *
     * @hook acf/pre_load_meta:999
     *
     * @function acf_get_meta() + get_fields()
     *
     * @return array|mixed|null
     */
    function pre_load_meta($return, $post_id){
    
        // disabled module or bypass
        if(!$this->is_enabled($post_id) || $this->bypass){
            return $return;
        }
    
        return $this->do_pre_load_meta($return, $post_id);
        
    }
    
    
    /**
     * do_pre_load_meta
     *
     * @param $return
     * @param $post_id
     *
     * @return array
     */
    function do_pre_load_meta($return, $post_id){
    
        // get store
        $acf = $this->get_store($post_id);
    
        // acf is empty
        // fallback to normal meta, just in case
        if(empty($acf)){
            return $return;
        }
    
        // return store data
        return $acf;
        
    }
    
    
    /**
     * pre_load_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $hidden
     *
     * @hook acf/pre_load_metadata:999
     *
     * @function acf_get_metadata() + acf_get_value()
     *
     * @return mixed
     */
    function pre_load_metadata($return, $post_id, $name, $hidden){
    
        // bail early
        // if acf
        //    or disabled module
        //    or bypass
        if($name === $this->meta_key || !$this->is_enabled($post_id) || $this->bypass){
            return $return;
        }
    
        return $this->do_pre_load_metadata($return, $post_id, $name, $hidden);
        
    }
    
    
    /**
     * do_pre_load_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $hidden
     *
     * @return mixed
     */
    function do_pre_load_metadata($return, $post_id, $name, $hidden){
    
        // get store
        $acf = $this->get_store($post_id);
    
        // acf is empty
        // fallback to normal meta
        if(empty($acf)){
            return $return;
        }
    
        // unslash values if required
        // this filter is enabled in pre_update_metadata()
        // it is used when page is not reloaded on save (ie: wp admin menu screen)
        if(acf_is_filter_enabled('acfe/performance_ultra/unslash')){
            $acf = wp_unslash($acf);
        }
    
        // prefix
        $prefix = $hidden ? '_' : '';
    
        // retrieve meta
        if(isset($acf["{$prefix}{$name}"])){
            $return = $acf["{$prefix}{$name}"];
        }
    
        // not found in acf
        // fallback to normal meta
        return $return;
        
    }
    
    
    /**
     * pre_update_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $value
     * @param $hidden
     *
     * @hook acf/pre_update_metadata:999
     *
     * @function acf_update_metadata() + acf_update_value() + acf_copy_metadata()
     *
     * @return bool|mixed|null
     */
    function pre_update_metadata($return, $post_id, $name, $value, $hidden){
    
        // bail early
        // if acf
        //    or disabled module
        //    or bypass
        if($name === $this->meta_key || !$this->is_enabled($post_id) || $this->bypass){
            return $return;
        }
        
        // get store
        $acf = $this->get_store($post_id);
        
        // prefix
        $prefix = $hidden ? '_' : '';
        
        // value
        $acf["{$prefix}{$name}"] = $value;
    
        // update store
        $this->update_store($post_id, $acf);
        
        // unlash for preload on same page as update
        acf_enable_filter('acfe/performance_ultra/unslash');
    
        // manual update
        // outside acf/save_post, probably in update_field()
        if($this->compile !== $post_id){
            $this->update_meta($acf, $post_id);
        }
        
        // disallow on revision
        // use 'save as individual meta' on post only
        if(!wp_is_post_revision($post_id)){
            
            // try to get meta reference
            $field = acf_maybe_get_field($name, $post_id);
            
            // in case the name was passed as "_textarea" with $hidden = false
            // like in acf_copy_metadata()
            // we must search for field key directly through value
            if(!$field && acfe_starts_with($name, '_') && acf_is_field_key($value)){
                $field = acf_get_field($value);
            }
            
            // save as individual meta
            if($field && !empty($field['acfe_save_meta'])){
                return $return;
            }
            
        }
    
        // get config
        $config = $this->get_config();
        
        switch($config['mode']){
    
            // test + rollback
            // save normal meta
            case 'test':
            case 'rollback': {
                return $return;
            }
    
            // production
            // delete normal meta
            case 'production': {
    
                // use normal acf logic
                $this->do_bypass(function($name, $post_id, $hidden){
        
                    // check if meta exists
                    // this will get meta cache instead of db call
                    if(acf_get_metadata($post_id, $name, $hidden) !== null){
                        acf_delete_metadata($post_id, $name, $hidden);
                    }
        
                }, array($name, $post_id, $hidden));
    
                // do not save normal meta
                return true;
                
            }
            
        }
    
        // return
        return $return;
        
    }
    
    
    /**
     * pre_delete_metadata
     *
     * @param $return
     * @param $post_id
     * @param $name
     * @param $hidden
     *
     * @hook acf/pre_delete_metadata:999
     *
     * @function acf_delete_metadata() + acf_delete_value()
     *
     * @return bool|mixed
     */
    function pre_delete_metadata($return, $post_id, $name, $hidden){
    
        // bail early
        // if acf
        //    or disabled module
        //    or bypass
        if($name === $this->meta_key || !$this->is_enabled($post_id) || $this->bypass){
            return $return;
        }
    
        // get store
        $acf = $this->get_store($post_id);
    
        // acf is empty
        // fallback to normal acf logic
        if(empty($acf)){
            return $return;
        }
        
        // prefix
        $prefix = $hidden ? '_' : '';
    
        // found in array
        if(isset($acf["{$prefix}{$name}"])){
            
            // unset
            unset($acf["{$prefix}{$name}"]);
            
            // update store
            $this->update_store($post_id, $acf);
            
            // update meta
            $this->update_meta($acf, $post_id);
        
        }
        
        // return
        return $return;
    
    }
    
}

acfe_register_performance_engine('acfe_performance_ultra');

endif;