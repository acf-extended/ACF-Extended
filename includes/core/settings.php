<?php

acf_register_store('acfe/settings');

if(!class_exists('acfe_settings')):

class acfe_settings{

	public $settings = array();
	
	public $upgrades = array(
        '0_8_5' => '0.8.5',
        '0_8_6' => '0.8.6',
    );
	
	public $model = array(
		
		// Version
		'version' => ACFE_VERSION,
		
		// Modules
		'modules' => array(
			
			'author' => array(
				'active' => true,
			),
			
			'dev' => array(
				'active' => false,
			),
			
			'meta' => array(
				'active' => false,
			),
			
			'option' => array(
				'active' => true,
			),
			
			'ui' => array(
				'active' => true,
			),
			
			'dynamic_block_type' => array(
				'active' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_form' => array(
				'active' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_option' => array(
				'active' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_post_type' => array(
				'active' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_taxonomy' => array(
				'active' => true,
				'settings' => array(),
				'data' => array()
			),
		
		),
		
		// Upgrades
		'upgrades' => array(),
	);

	function __construct(){
	 
		$this->settings = acf_get_store('acfe/settings');
		
		if(empty($this->settings->get_data())){
            
            $option = get_option('acfe', array());
            
            if(!empty($option)){
                
                $this->settings->set($option);
                
                $this->version();
                
            }else{
                
                $this->reset();
                
            }
		    
        }

	}
	
	function get($selector = false){
		
		return $this->array_get($this->settings->get(), $selector);
		
	}
	
	function set($selector = false, $value = null, $update = true, $append = false){
		
		if($value === null)
			return false;
		
		$rows = $this->settings->get();
		
		if($append){
			
			$this->array_append($rows, $selector, $value);
			
		}else{
			
			$this->array_set($rows, $selector, $value);
			
		}
		
		$this->settings->set($rows);
		
		if($update)
			$this->update();
		
		return $this;
		
	}
	
	function clear($selector = false, $update = true){
		
		$rows = $this->settings->get();
		
		$this->array_clear($rows, $selector);
		
		$this->settings->set($rows);
		
		if($update)
			$this->update();
		
		return $this;
		
	}
	
	function delete($selector = false, $update = true){
		
		// Single
		if(strpos($selector, '.') === false){
			
			$this->settings->remove($selector);
		
		// Array
		}else{
			
			$rows = $this->settings->get();
			
			$this->array_remove($rows, $selector);
			
			$this->settings->set($rows);
			
		}
		
		if($update)
			$this->update();
		
		return $this;
		
	}
	
	function append($selector = false, $value = null, $update = true){
		
		if($selector === false && $value === null)
			return false;
		
		// Allow simple append without selector
		if($value === null){
			
			$value = $selector;
			$selector = false;
			
		}
		
		return $this->set($selector, $value, $update, true);
		
	}
	
	function array_get($array, $key, $default = null) {
		
		if(empty($key))
			return $array;
		
		if(!is_array($key))
			$key = explode('.', $key);
		
		$count = count($key);
		$i=-1;
		
		foreach($key as $segment){
			
			$i++;
			
			if(!isset($array[$segment]))
				continue;
			
			if($i+1 === $count){
				
				return $array[$segment];
				
			}
			
			unset($key[$i]);
			
			return $this->array_get($array[$segment], $key, $default);
			
			
		}
		
		return $default;
		
	}
	
	function array_set(&$array, $key, $value){
		
		if(empty($key))
			return $array = $value;
		
		$keys = explode('.', $key);
		
		while(count($keys) > 1){
			
			$key = array_shift($keys);
			
			if(!isset($array[$key]) || !is_array($array[$key])){
				
				$array[$key] = array();
				
			}
			
			$array =& $array[$key];
			
		}
		
		$array[array_shift($keys)] = $value;
		
		return $array;
		
	}
	
	function array_append(&$array, $key, $value){
		
		$get = $this->array_get($array, $key);
		
		$old = acf_get_array($get);
		$value = acf_get_array($value);
		
		$value = array_merge($old, $value);
		
		$this->array_set($array, $key, $value);
		
		return $array;
		
	}
	
	function array_clear(&$array, $key){
		
		$get = $this->array_get($array, $key);
		
		if($get === null)
			return $array;
		
		$value = null;
		
		if(is_array($get))
			$value = array();
		
		$this->array_set($array, $key, $value);
		
		return $array;
		
	}
	
	function array_remove(&$array, $keys){
		
		$original =& $array;
		
		foreach((array)$keys as $key){
			
			$parts = explode('.', $key);
			
			while(count($parts) > 1){
				
				$part = array_shift($parts);
				
				if(isset($array[$part]) && is_array($array[$part])){
					
					$array =& $array[$part];
					
				}
				
			}
			
			unset($array[array_shift($parts)]);
			
			// clean up after each pass
			$array =& $original;
			
		}
		
	}
	
	function reset(){
        
        $this->model['upgrades'] = $this->upgrades;
		
		$this->set('', $this->model, true);
		
        new acfe_upgrades();
		
		add_action('init', array($this, 'reset_modules'));
		
	}
	
	function reset_modules(){
		
		// Reset Post Types
		$post_types = get_posts(array(
			'post_type'         => 'acfe-dpt',
			'posts_per_page'    => -1,
			'fields'            => 'ids'
		));
		
		if(!empty($post_types)){
			
			foreach($post_types as $post_id){
				
				acfe_dpt_filter_save($post_id);
				
				acf_log('[ACF Extended] Reset: Dynamic Post Type "' . get_post_field('post_title', $post_id) . '"');
				
			}
			
		}
		
		// Reset Taxonomies
		$taxonomies = get_posts(array(
			'post_type'         => 'acfe-dt',
			'posts_per_page'    => -1,
			'fields'            => 'ids'
		));
		
		if(!empty($taxonomies)){
			
			foreach($taxonomies as $post_id){
				
				acfe_dt_filter_save($post_id);
				
				acf_log('[ACF Extended] Reset: Dynamic Taxonomy "' . get_post_field('post_title', $post_id) . '"');
				
			}
			
		}
		
		// Reset Block Types
		$block_types = get_posts(array(
			'post_type'         => 'acfe-dbt',
			'posts_per_page'    => -1,
			'fields'            => 'ids'
		));
		
		if(!empty($block_types)){
			
			foreach($block_types as $post_id){
				
				acfe_dbt_filter_save($post_id);
				
				acf_log('[ACF Extended] Reset: Dynamic Block Type "' . get_post_field('post_title', $post_id) . '"');
				
			}
			
		}
		
		// Reset Options Pages
		$options_pages = get_posts(array(
			'post_type'         => 'acfe-dop',
			'posts_per_page'    => -1,
			'fields'            => 'ids'
		));
		
		if(!empty($options_pages)){
			
			foreach($options_pages as $post_id){
				
				acfe_dop_filter_save($post_id);
				
				acf_log('[ACF Extended] Reset: Dynamic Options Page "' . get_post_field('post_title', $post_id) . '"');
				
			}
			
		}
		
	}
	
	function version(){
		
		$version = $this->get('version');
		
		if(acf_version_compare($version, '<', ACFE_VERSION)){
		    
		    if(!empty($this->upgrades)){
		        
		        $do_upgrades = false;
            
                foreach($this->upgrades as $function => $v){
                    
                    if(acf_version_compare($v, '<=', $version))
                        continue;
    
                    $do_upgrades = true;

                    $this->model['upgrades'][$function] = true;
                    
                }
		        
            }
			
			$data = $this->get();
			$model = $this->model;
			
			$new_model = $this->parse_args_r($data, $model);
			
			$new_model['version'] = ACFE_VERSION;
			
			$this->set('', $new_model, true);
            
            if($do_upgrades){
                
                new acfe_upgrades();
                
            }
			
		}
		
	}
	
	function update(){
		
		$settings = $this->settings->get();
		
		update_option('acfe', $settings, 'true');
		
	}
	
	function parse_args_r(&$a, $b){
		
		$a = (array) $a;
		$b = (array) $b;
		$r = $b;
		
		foreach($a as $k => &$v){
			
			if(is_array($v) && isset($r[ $k ])){
				$r[$k] = $this->parse_args_r($v, $r[ $k ]);
			}else{
				$r[$k] = $v;
			}
			
		}
		
		return $r;
		
	}

}

endif;

function acfe_settings($selector = null, $value = null, $update = true){
	
	$instance = acf_get_instance('acfe_settings');
	
	// Set
	if($selector !== null && $value !== null){
			
		return $instance->set($selector, $value, $update);
		
	}
	
	// Get
	elseif($selector !== null && $value === null){
		
		return $instance->get($selector);
		
	}
	
	return $instance;
	
}