<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_taxonomies', true))
    return;

if(!class_exists('ACFE_Admin_Tool_Export_DT')):

class ACFE_Admin_Tool_Export_DT extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dt_export';
        $this->title = __('Export Taxonomies');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        // vars
		$choices = array();
        
        $dynamic_taxonomies = get_option('acfe_dynamic_taxonomies', array());
        
		if($dynamic_taxonomies){
			foreach($dynamic_taxonomies as $taxonomy_name => $args){
                
				$choices[$taxonomy_name] = esc_html($args['label']);
                
			}	
		}
        
        ?>
        <p><?php _e('Export Taxonomies', 'acf'); ?></p>
        
        <div class="acf-fields">
            <?php 
            
            if(!empty($choices)){
            
                // render
                acf_render_field_wrap(array(
                    'label'		=> __('Select Taxonomies', 'acf'),
                    'type'		=> 'checkbox',
                    'name'		=> 'keys',
                    'prefix'	=> false,
                    'value'		=> false,
                    'toggle'	=> true,
                    'choices'	=> $choices,
                ));
            
            }
            
            else{
                
                echo '<div style="padding:15px 12px;">';
                    _e('No dynamic taxonomy available.');
                echo '</div>'; 
                
            }
            
            ?>
        </div>
        
        <?php 
        
        $disabled = '';
        if(empty($choices))
            $disabled = 'disabled="disabled"';
        
        ?>
        
        <p class="acf-submit">
            <button type="submit" name="action" class="button button-primary" value="download" <?php echo $disabled; ?>><?php _e('Export File'); ?></button>
        </p>
        <?php
        
    }
    
    function load(){
        
        // check $_GET
		if($this->is_active() && acf_maybe_get_GET('keys')){
            
            $this->submit();
            
		}
        
    }
    
    function submit(){
        
        $json = $this->get_selected();
        
        // validate
		if($json === false)
			return acf_add_admin_notice(__('No taxonomies selected'), 'warning');
        
        $keys = array();
        foreach($json as $key => $args){
            
            $keys[] = $key;
            
        }
        
        // Prefix
        $prefix = (count($keys) > 1) ? 'taxonomies' : 'taxonomy';
        
        // Slugs
        $slugs = implode('-', $keys);
        
        // Date
        $date = date('Y-m-d');
        
        // file
		$file_name = 'acfe-export-' .  $prefix  . '-' . $slugs . '-' .  $date . '.json';
        
        // headers
		header("Content-Description: File Transfer");
		header("Content-Disposition: attachment; filename={$file_name}");
		header("Content-Type: application/json; charset=utf-8");
		
		// return
		echo acf_json_encode($json);
		die;
        
    }
    
	function get_selected(){
		
		// vars
		$selected = $this->get_selected_keys();
		$json = array();
        
		if(!$selected)
            return false;
        
        $dynamic_taxonomies = get_option('acfe_dynamic_taxonomies', array());
        if(empty($dynamic_taxonomies))
            return false;
		
		// construct JSON
		foreach($selected as $key){
            
            if(!isset($dynamic_taxonomies[$key]))
                continue;
			
			// add to json array
			$json[$key] = $dynamic_taxonomies[$key];
			
		}
		
		// return
		return $json;
		
	}
    
	function get_selected_keys(){
		
		// check $_POST
		if($keys = acf_maybe_get_POST('keys'))
			return (array) $keys;
		
		// check $_GET
		if($keys = acf_maybe_get_GET('keys')){
			$keys = str_replace(' ', '+', $keys);
			return explode('+', $keys);
		}
		
		// return
		return false;
		
	}
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Export_DT');

endif;