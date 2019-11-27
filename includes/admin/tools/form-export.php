<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms'))
    return;

if(!class_exists('ACFE_Admin_Tool_Export_Form')):

class ACFE_Admin_Tool_Export_Form extends ACF_Admin_Tool{
    
    public $action = false;
    public $data = array();

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_form_export';
        $this->title = __('Export Forms');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        // Archive
        if(!$this->is_active()){
            
            $this->html_archive();
            
        }
        
    }
    
    function html_archive(){
        
        // vars
        $choices = array();
        
        $get_forms = get_posts(array(
            'post_type'         => 'acfe-form',
            'posts_per_page'    => -1,
            'fields'            => 'ids'
        ));
        
        if($get_forms){
            foreach($get_forms as $form_id){
                
                $name = get_field('acfe_form_name', $form_id);
                
                $choices[$name] = esc_html(get_the_title($form_id));
                
            }	
        }
        
        ?>
        <p><?php _e('Export Forms', 'acf'); ?></p>
        
        <div class="acf-fields">
            <?php 
            
            if(!empty($choices)){
            
                // render
                acf_render_field_wrap(array(
                    'label'		=> __('Select Forms', 'acf'),
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
                    _e('No dynamic form available.');
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
            <button type="submit" name="action" class="button button-primary" value="json" <?php echo $disabled; ?>><?php _e('Export File'); ?></button>
        </p>
        <?php
        
    }
    
    function load(){
        
		if($this->is_active()){
            
            $this->action = $this->get_action();
            $this->data = $this->get_selected();
            
            // Json submit
            if($this->action === 'json')
                $this->submit();

	    	// add notice
	    	if(!empty($this->data)){
                
		    	$count = count($this->data);
		    	$text = sprintf(_n( 'Exported 1 form.', 'Exported %s forms.', $count, 'acf' ), $count);
                
		    	acf_add_admin_notice($text, 'success');
                
	    	}
            
		}
        
    }
    
    function submit(){
        
        $this->action = $this->get_action();
        $this->data = $this->get_selected();
        
        // validate
		if($this->data === false)
			return acf_add_admin_notice(__('No forms selected'), 'warning');
        
        $keys = array();
        foreach($this->data as $key => $args){
            
            $keys[] = $key;
            
        }
        
        if($this->action === 'json'){
        
            // Prefix
            $prefix = (count($keys) > 1) ? 'forms' : 'forms';
            
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
            echo acf_json_encode($this->data);
            die;
        
        }
        
    }
    
	function get_selected(){
		
		// vars
		$selected = $this->get_selected_keys();
        
		if(!$selected)
            return false;
		
        $data = array();
        
        acf_enable_filter('local');
        
		// construct Data
		foreach($selected as $key){
            
            if(!$form = get_page_by_path($key, OBJECT, 'acfe-form'))
                continue;
            
			// add to data array
			$data[$key] = array_merge(array('title' => get_the_title($form->ID)), get_fields($form->ID, false));
            
		}
        
		acf_disable_filter('local');
        
		// return
		return $data;
		
	}
    
	function get_selected_keys(){
		
		// check $_POST
		if($keys = acf_maybe_get_POST('keys')){
            
			return (array) $keys;
            
        }
		
		// check $_GET
		if($keys = acf_maybe_get_GET('keys')){
            
			$keys = str_replace(' ', '+', $keys);
			return explode('+', $keys);
            
		}
		
		// return
		return false;
		
	}
    
    function get_action(){
        
        // init
        $type = 'json';
        
        // return
        return $type;
		
	}
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Export_Form');

endif;