<?php

acf_register_store('acfe/settings');

if(!class_exists('acfe_settings')):

class acfe_settings{

	public $settings = array();
	
	public $option = array();
	
	public $model = array(
		
		// Version
		'version' => ACFE_VERSION,
		
		// Modules
		'modules' => array(
			
			'author' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dev' => array(
				'enabled' => false,
				'settings' => array(),
				'data' => array()
			),
			
			'meta' => array(
				'enabled' => false,
				'settings' => array(),
				'data' => array()
			),
			
			'option' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'taxonomy' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_block_type' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_form' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_option' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_post_type' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
			
			'dynamic_taxonomy' => array(
				'enabled' => true,
				'settings' => array(),
				'data' => array()
			),
		
		),
		
		// Upgrades
		'upgrades' => array(
			
			// 0.8.5
			'0_8_5' => array(
				'todo' => true,
				'tasks' => array(
					'dynamic_block_type'    => true,
					'dynamic_form'          => true,
					'dynamic_option'        => true,
					'dynamic_post_type'     => true,
					'dynamic_taxonomy'      => true
				)
			),
		
		),
	);

	function __construct(){
		
		$this->option = get_option('acfe', array());

		$this->settings = acf_get_store('acfe/settings');
		
		$this->settings->set($this->option);
		
		if(empty($this->option)){
			
			$this->reset();
			
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
	
	function reset(){
		
		$model = $this->model;
		
		update_option('acfe', $model, 'true');
		
		$this->option = $model;
		
		$this->settings->set($model);
		
	}
	
	function update(){
		
		$settings = $this->settings->get();
		
		update_option('acfe', $settings, 'true');
		
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