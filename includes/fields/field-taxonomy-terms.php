<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_taxonomy_terms')):

class acfe_field_taxonomy_terms extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_taxonomy_terms';
        $this->label = __('Taxonomy Terms', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'taxonomy'      => array(),
            'allow_terms'   => array(),
            'field_type'    => 'checkbox',
            'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'choices'		=> array(),
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
            'layout'        => '',
			'toggle'        => 0,
			'allow_custom'  => 0,
			'return_format' => 'id',
        );
        
        // ajax
		add_action('wp_ajax_acf/fields/acfe_field_taxonomy_allow_terms/query',				array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acf/fields/acfe_field_taxonomy_allow_terms/query',		array($this, 'ajax_query'));
        
        parent::__construct();
        
    }
    
    function ajax_query(){
		
		// validate
		if(!acf_verify_ajax())
            die();
		
		// get choices
		$response = $this->get_ajax_query($_POST);
		
		// return
		acf_send_ajax_results($response);
			
	}
    
	function get_ajax_query($options = array()){
		
   		// defaults
   		$options = acf_parse_args($options, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1,
            'taxonomies'    => array(),
            'level'         => false,
		));
        
        // get grouped terms
        $args = array('taxonomy' => $options['taxonomies']);
        
        $terms = acf_get_grouped_terms($args);
        
        if($options['level'] >= 1){
            
            // vars
            $terms_final = array();
            
            // loop over values
            foreach($terms as $group => $_terms){
                
                foreach($_terms as $term_id => $term){
                    
                    if(acfe_get_term_level($term_id, $term->taxonomy) !== $options['level'])
                        continue;
                    
                    $terms_final[$group][$term_id] = $term;
                    
                }
                
            }
            
            $terms = $terms_final;
            
        }
        
        $groups = acf_get_choices_from_grouped_terms($terms, 'name');
		
		// vars
   		$results = array();
        
        // loop
        foreach(array_keys($groups) as $group_title){
            
            // vars
            $terms = acf_extract_var($groups, $group_title);
            
            // data
            $data = array(
                'text'		=> $group_title,
                'children'	=> array()
            );
            
            // append to $data
            $i=0; foreach($terms as $term_id => $name){ $i++;
            
                $term = get_term($term_id);
                
                if($i === 1){
                    
                    $id = 'all_' . $term->taxonomy;
                    $text = 'All ';
                    $taxonomy = get_taxonomy($term->taxonomy);
                    
                    if($options['level'] >= 1){
                        
                        $id .= '|' . $options['level'];
                        $text .= acfe_number_suffix($options['level']) . ' Level ';
                        
                    }
                    
                    $text .= $taxonomy->label;
                    
                    $data['children'][] = array(
                        'id' => $id, 
                        'text' => '<strong>' . $text . '</strong>'
                    );
                    
                }
                
                if($term->parent !== 0){
                    
                    $_term = get_term($term->parent);
                    
                    $_term_choice = acf_get_choice_from_term($_term, 'name');
                    
                    $data['children'][] = array(
                        'id' => $_term->term_id . '_childs', 
                        'text' => '<strong>' . $_term_choice['text'] . ': Childs</strong>'
                    );
                    
                }
                
                $data['children'][] = array(
                    'id' => $term_id, 
                    'text' => $name
                );
                
            }
            
            // append to $results
            $results[] = $data;
            
        }
        
        // vars
        $response = array(
            'results'	=> $results
        );
        
		// return
		return $response;
			
	}

    function prepare_field($field){
        
        // Allow Terms
        $choices = array();
        
        $taxonomies = acf_get_taxonomy_labels(acf_get_array($field['taxonomy']));
        
        $all_terms = get_terms(array(
            'taxonomy'      => array_keys($taxonomies),
            'hide_empty'    => false,
        ));
        
        if(!empty($all_terms)){
            
            foreach($all_terms as $term_id => &$term){
                
                $level = acfe_get_term_level($term->term_id, $term->taxonomy);
                $term->level = $level;
                
            }
            
            if(!empty($field['allow_terms'])){
                
                $terms = array();
                
                foreach($field['allow_terms'] as $id){
                    
                    // All terms
                    if(acfe_starts_with($id, 'all_')){
                        
                        $taxonomy = substr($id, 4);
                        
                        $level = false;
                        
                        if(stripos($taxonomy, '|') !== false){
                            
                            $level = explode('|', $taxonomy);
                            $taxonomy = $level[0];
                            $level = $level[1];
                            
                        }
                        
                        if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                            continue;
                        
                        $keep = array();
                        
                        if($level){
                            
                            foreach($all_terms as $all_term){
                                
                                if((int) $all_term->level !== (int) $level || $all_term->taxonomy !== $taxonomy)
                                    continue;
                                
                                $keep[] = $all_term->term_id;
                                
                            }
                            
                        }else{
                            
                            foreach($all_terms as $all_term){
                                
                                if($all_term->taxonomy !== $taxonomy)
                                    continue;
                                
                                $keep[] = $all_term->term_id;
                                
                            }
                            
                        }
                        
                        $terms = array_merge($terms, acf_array($keep));
                        
                    }
                    
                    // Terms childs
                    elseif(acfe_ends_with($id, '_childs')){
                        
                        $term_id = substr($id, 0, -7);
                        $term = get_term($term_id);
                        $taxonomy = $term->taxonomy;
                        
                        if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                            continue;
                        
                        $keep = array();
                        
                        foreach($all_terms as $all_term){
                            
                            if((int) $all_term->parent !== (int) $term_id || $all_term->taxonomy !== $taxonomy)
                                continue;
                            
                            $keep[] = $all_term->term_id;
                            
                        }
                        
                        $terms = array_merge($terms, acf_array($keep));
                        
                        
                        
                    }
                    
                    // Term
                    else{
                    
                        $term = get_term($id);
                        $taxonomy = $term->taxonomy;
                        
                        if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                            continue;
                        
                        $keep = array();
                        $keep[] = $term->term_id;
                        
                        $terms = array_merge($terms, acf_array($keep));
                    
                    }
                    
                }
                
            }
            
            // All terms
            else{
                
                $terms = wp_list_pluck($all_terms, 'term_id');
                
            }
            
            $terms = array_unique($terms);
            
            if(!empty($terms)){
                
                $terms = acf_get_grouped_terms(array(
                    'include' => $terms
                ));
                
                $choices = acf_get_choices_from_grouped_terms($terms, 'name');
                
            }
            
        }
        
        //$field['choices'] = acfe_get_taxonomy_terms_ids($field['taxonomy']);
        $field['choices'] = $choices;
        
        // Set Field Type
        $field['type'] = $field['field_type'];
        
        return $field;
        
    }
    
    function render_field_settings($field){
        
        if(isset($field['default_value']))
            $field['default_value'] = acf_encode_choices($field['default_value'], false);
        
        // Allow Taxonomy
		acf_render_field_setting($field, array(
			'label'			=> __('Allow Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_labels(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'acf'),
		));
        
        // Allow Terms
        $choices = array();
        
        if(!empty($field['allow_terms'])){
            
            foreach($field['allow_terms'] as $id){
                
                // All terms
                if(acfe_starts_with($id, 'all_')){
                    
                    $taxonomy = substr($id, 4);
                    
                    $level = false;
                    
                    if(stripos($taxonomy, '|') !== false){
                        
                        $level = explode('|', $taxonomy);
                        $taxonomy = $level[0];
                        $level = $level[1];
                        
                        $level = acfe_number_suffix($level) . ' Level ';
                        
                    }
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                        continue;
                    
                    $taxonomy = get_taxonomy($taxonomy);
                    $value = '<strong>All ' . $level . $taxonomy->label . '</strong>';
                    
                }
                
                // Terms childs
                elseif(acfe_ends_with($id, '_childs')){
                    
                    $term_id = substr($id, 0, -7);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                        continue;
                    
                    $value = '<strong>' . $term->name . ': childs</strong>';
                    
                }
                
                // Term
                else{
                
                    $term = get_term($id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                        continue;
                    
                    $term_data = acf_get_choice_from_term($term, 'name');
                    
                    $value = $term_data['text'];
                
                }
                
                $choices[$id] = $value;
                
            }
            
        }
        
		acf_render_field_setting($field, array(
			'label'			=> __('Allow Terms','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'allow_terms',
			'choices'		=> $choices,
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
            'ajax'          => 1,
			'placeholder'	=> __("All terms",'acf'),
			'ajax_action'	=> 'acf/fields/acfe_field_taxonomy_allow_terms/query',
		));
        
        // Select: Terms level
		acf_render_field_setting($field, array(
			'label'			=> __('Terms_level','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_level',
			'type'			=> 'number',
			'append'        => 'levels',
			'min'           => 0,
			'placeholder'   => __('All','acf'),
            '_append'       => 'allow_terms',
            'value'         => false
		));
        
        // field_type
        acf_render_field_setting($field, array(
            'label'			=> __('Appearance','acf'),
            'instructions'	=> __('Select the appearance of this field', 'acf'),
            'type'			=> 'select',
            'name'			=> 'field_type',
            'optgroup'		=> true,
            'choices'		=> array(
                'checkbox'  => __('Checkbox', 'acf'),
                'select'    => _x('Select', 'noun', 'acf')
            )
        ));
        
        // default_value
		acf_render_field_setting($field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'name'			=> 'default_value',
			'type'			=> 'textarea',
		));
        
        // return_format
        acf_render_field_setting($field, array(
            'label'			=> __('Return Value', 'acf'),
            'instructions'	=> '',
            'type'			=> 'radio',
            'name'			=> 'return_format',
            'choices'		=> array(
                'object'    =>	__('Term object', 'acfe'),
                'name'      =>	__('Term name', 'acfe'),
                'id'      =>	__('Term ID', 'acfe'),
            ),
            'layout'	=>	'horizontal',
        ));
        
		// Select: allow_null
		acf_render_field_setting($field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                ),
            )
		));
        
        // placeholder
        acf_render_field_setting($field, array(
            'label'			=> __('Placeholder Text','acf'),
            'instructions'	=> __('Appears within the input','acf'),
            'type'			=> 'text',
            'name'			=> 'placeholder',
            'placeholder'   => _x('Select', 'verb', 'acf'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    
                )
            )
        ));
        
        // Select: multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                ),
            )
		));
        
        // Select: ui
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'name'			=> 'ui',
			'type'			=> 'true_false',
			'ui'			=> 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                ),
            )
		));
				
		
		// Select: ajax
		acf_render_field_setting( $field, array(
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'name'			=> 'ajax',
			'type'			=> 'true_false',
			'ui'			=> 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => 1,
                    ),
                ),
            )
		));
        
        // Checkbox: layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'vertical'		=> __("Vertical",'acf'), 
				'horizontal'	=> __("Horizontal",'acf')
			),
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
            )
		));
        
        // Checkbox: toggle
        acf_render_field_setting( $field, array(
			'label'			=> __('Toggle','acf'),
			'instructions'	=> __('Prepend an extra checkbox to toggle all choices','acf'),
			'name'			=> 'toggle',
			'type'			=> 'true_false',
			'ui'			=> 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
            )
		));
        
        // Checkbox: other_choice
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Custom','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_custom',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Allow 'custom' values to be added", 'acf'),
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
            )
		));
		
		
		// Checkbox: save_other_choice
		acf_render_field_setting( $field, array(
			'label'			=> __('Save Custom','acf'),
			'instructions'	=> '',
			'name'			=> 'save_custom',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Save 'custom' values to the field's choices", 'acf'),
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                    array(
                        'field'     => 'allow_custom',
                        'operator'  => '==',
                        'value'     => 1,
                    ),
                ),
            )
		));
        
    }
    
    function format_value($value, $post_id, $field){
        
        if(empty($value))
            return $value;
        
        // Return: object
		if($field['return_format'] === 'object' || $field['return_format'] === 'name'){
            
            // array
            if(acf_is_array($value)){
                
                foreach($value as $i => $v){
                    
                    $term = get_term($v);
                    
                    if($field['return_format'] === 'object'){
                        
                        $value[$i] = $term;
                        
                    }elseif($field['return_format'] === 'name'){
                        
                        $value[$i] = $term->name;
                        
                    }
                    
                }
            
            // string
            }else{
                
                $term = get_term($value);
                
                if($field['return_format'] === 'object'){
                    
                    $value = $term;
                    
                }elseif($field['return_format'] === 'name'){
                    
                    $value = $term->name;
                    
                }
                
            }
        
		}
        
		// return
		return $value;
        
    }

}

// initialize
acf_register_field_type('acfe_field_taxonomy_terms');

endif;