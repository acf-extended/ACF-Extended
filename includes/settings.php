<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_settings')):

class acfe_settings{
    
    // vars
    public $settings = array();
    
    /**
     * construct
     */
    function __construct(){
        $this->settings = get_option('acfe', array());
    }
    
    
    /**
     * get
     *
     * @param $selector
     * @param $default
     *
     * @return mixed|null
     */
    function get($selector = null, $default = null){
        return $this->array_get($this->settings, $selector, $default);
    }
    
    
    /**
     * set
     *
     * @param $selector
     * @param $value
     * @param $append
     *
     * @return $this|false
     */
    function set($selector = null, $value = null, $append = false){
        
        if($value === null){
            return false;
        }
        
        if($append){
            $this->array_append($this->settings, $selector, $value);
            
        }else{
            $this->array_set($this->settings, $selector, $value);
        }
        
        $this->update();
        
        return $this;
        
    }
    
    
    /**
     * clear
     *
     * @param $selector
     *
     * @return $this
     */
    function clear($selector = null){
        
        $this->array_clear($this->settings, $selector);
        $this->update();
        
        return $this;
        
    }
    
    
    /**
     * delete
     *
     * @param $selector
     *
     * @return $this
     */
    function delete($selector = null){
        
        // single
        if(strpos($selector, '.') === false){
            unset($this->settings[ $selector ]);
        
        // array
        }else{
            $this->array_remove($this->settings, $selector);
        }
        
        $this->update();
        
        return $this;
        
    }
    
    
    /**
     * append
     *
     * @param $selector
     * @param $value
     *
     * @return $this|false
     */
    function append($selector = null, $value = null){
        
        if($selector === null && $value === null){
            return false;
        }
        
        // allow simple append without selector
        if($value === null){
            
            $value = $selector;
            $selector = null;
            
        }
        
        return $this->set($selector, $value, true);
        
    }
    
    
    /**
     * array_get
     *
     * @param $array
     * @param $key
     * @param $default
     *
     * @return mixed|null
     */
    function array_get($array, $key, $default = null){
        
        if(empty($key)){
            return $array;
        }
        
        if(!is_array($key)){
            $key = explode('.', $key);
        }
        
        $count = count($key);
        $i=-1;
        
        foreach($key as $segment){
            
            $i++;
            
            if(!isset($array[ $segment ])){
                continue;
            }
            
            if($i+1 === $count){
                return $array[ $segment ];
            }
            
            unset($key[$i]);
            
            return $this->array_get($array[ $segment ], $key, $default);
            
        }
        
        return $default;
        
    }
    
    
    /**
     * array_set
     *
     * @param $array
     * @param $key
     * @param $value
     *
     * @return array|mixed
     */
    function array_set(&$array, $key, $value){
        
        if(empty($key)){
            return $array = $value;
        }
        
        $keys = explode('.', $key);
        
        while(count($keys) > 1){
            
            $key = array_shift($keys);
            
            if(!isset($array[ $key ]) || !is_array($array[ $key ])){
                $array[ $key ] = array();
            }
            
            $array =& $array[ $key ];
            
        }
        
        $array[ array_shift($keys) ] = $value;
        
        return $array;
        
    }
    
    
    /**
     * array_append
     *
     * @param $array
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    function array_append(&$array, $key, $value){
        
        $get = $this->array_get($array, $key);
        
        $old = acf_get_array($get);
        $value = acf_get_array($value);
        
        $value = array_merge($old, $value);
        
        $this->array_set($array, $key, $value);
        
        return $array;
        
    }
    
    
    /**
     * array_clear
     *
     * @param $array
     * @param $key
     *
     * @return mixed
     */
    function array_clear(&$array, $key){
        
        $get = $this->array_get($array, $key);
        
        if($get === null){
            return $array;
        }
        
        $value = null;
        
        if(is_array($get)){
            $value = array();
        }
        
        $this->array_set($array, $key, $value);
        
        return $array;
        
    }
    
    
    /**
     * array_remove
     *
     * @param $array
     * @param $keys
     */
    function array_remove(&$array, $keys){
        
        $original =& $array;
        
        foreach((array) $keys as $key){
            
            $parts = explode('.', $key);
            
            while(count($parts) > 1){
                
                $part = array_shift($parts);
                
                if(isset($array[ $part ]) && is_array($array[ $part ])){
                    $array =& $array[ $part ];
                }
                
            }
            
            unset($array[ array_shift($parts) ]);
            
            // clean up after each pass
            $array =& $original;
            
        }
        
    }
    
    
    /**
     * update
     */
    function update(){
        update_option('acfe', $this->settings, 'true');
    }

}

endif;


/**
 * acfe_get_settings
 *
 * @param $selector
 * @param $default
 *
 * @return mixed
 */
function acfe_get_settings($selector = null, $default = null){
    return acf_get_instance('acfe_settings')->get($selector, $default);
}


/**
 * acfe_update_settings
 *
 * @param $selector
 * @param $value
 *
 * @return mixed
 */
function acfe_update_settings($selector = null, $value = null){
    
    if($value === null){
        $value = $selector;
        $selector = null;
    }

    return acf_get_instance('acfe_settings')->set($selector, $value);

}


/**
 * acfe_delete_settings
 *
 * @param $selector
 *
 * @return mixed
 */
function acfe_delete_settings($selector = null){
    return acf_get_instance('acfe_settings')->delete($selector);
}