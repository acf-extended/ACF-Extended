<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_taxonomy_terms')):

class acfe_field_taxonomy_terms extends acf_field{
    
    // vars
    var $save_post_terms = array();
    
    function __construct(){
        
        $this->name = 'acfe_taxonomy_terms';
        $this->label = __('Taxonomy Terms', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'taxonomy'      => array(),
            'allow_terms'   => array(),
            'field_type'    => 'checkbox',
            'choices'		=> array(),
            'default_value'	=> '',
            'return_format' => 'id',
            'ui'			=> 0,
            'multiple' 		=> 0,
            'allow_null' 	=> 0,
            'ajax'			=> 0,
            'placeholder'	=> '',
            'layout'        => '',
            'toggle'        => 0,
            'load_terms'    => 0,
            'save_terms'    => 0,
            'allow_custom'  => 0,
            'other_choice'  => 0,
        );
        
        // ajax
        add_action('wp_ajax_acfe/fields/taxonomy_terms/allow_query',        array($this, 'ajax_query_allowed_terms'));
        add_action('wp_ajax_nopriv_acfe/fields/taxonomy_terms/allow_query', array($this, 'ajax_query_allowed_terms'));
        
        add_action('wp_ajax_acf/fields/acfe_taxonomy_terms/query',          array($this, 'ajax_query'));
        add_action('wp_ajax_nopriv_acf/fields/acfe_taxonomy_terms/query',   array($this, 'ajax_query'));
        
        // actions
        add_action('acf/save_post',                                         array($this, 'save_post'), 15, 1);
        
        parent::__construct();
        
    }
    
    function ajax_query_allowed_terms(){
        
        // validate
        if(!acf_verify_ajax())
            die();
        
        // get choices
        $response = $this->get_ajax_query_allowed_terms($_POST);
        
        // return
        acf_send_ajax_results($response);
            
    }
    
    function get_ajax_query_allowed_terms($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'		=> 0,
            's'				=> '',
            'field_key'		=> '',
            'paged'			=> 1,
            'taxonomies'    => array(),
            'level'         => false,
        ));
        
        // Get grouped terms
        $terms = acf_get_grouped_terms(array(
            'taxonomy' => $options['taxonomies']
        ));
        
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
            
            $done = array();
            
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
                        'text' => '(' . $text . ')'
                    );
                    
                }
                
                if($term->parent !== 0){
                    
                    $_term = get_term($term->parent);
                    
                    if(!in_array($_term->term_id . '_childs', $done)){
    
                        $_term_choice = acf_get_choice_from_term($_term, 'name');
    
                        $data['children'][] = array(
                            'id' => $_term->term_id . '_childs',
                            'text' => $_term_choice['text'] . ' (Direct childs)'
                        );
    
                        $done[] = $_term->term_id . '_childs';
                        
                    }
    
                    if(!in_array($_term->term_id . '_all_childs', $done)){
        
                        $_term_choice = acf_get_choice_from_term($_term, 'name');
        
                        $data['children'][] = array(
                            'id' => $_term->term_id . '_all_childs',
                            'text' => $_term_choice['text'] . ' (All childs)'
                        );
        
                        $done[] = $_term->term_id . '_all_childs';
        
                    }
                    
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
    
    function get_terms($field, $args = array()){
        
        // Allow Terms
        $choices = array();
        
        // Get allowed taxonomies
        $taxonomies = acf_get_taxonomy_labels(acf_get_array($field['taxonomy']));
        
        $args['taxonomy'] = array_keys($taxonomies);
        
        // Get terms
        $all_terms = acf_get_terms($args);
        
        if(empty($all_terms))
            return $choices;
        
        $choices = $this->filter_terms($all_terms, $field);
        
        return $choices;
        
    }
    
    function filter_terms($all_terms, $field){
        
        if(empty($field['taxonomy']) && empty($field['allow_terms'])){
    
            $terms = wp_list_pluck($all_terms, 'term_id');
    
            $terms = array_unique($terms);
    
            $choices = $this->convert_terms_to_choices($terms);
    
            return $choices;
            
        }
    
        $terms = array();
        
        // Filter taxonomy terms
        if(!empty($field['taxonomy'])){
    
            $allowed_tax_terms = array();
    
            foreach($all_terms as $term){
                
                if(!in_array($term->taxonomy, $field['taxonomy']))
                    continue;
    
                $allowed_tax_terms[] = $term;
        
            }
            
            $all_terms = $allowed_tax_terms;
            
        }
        
        if(empty($field['allow_terms'])){
    
            $terms = $all_terms;
            
        // Filter allowed terms
        }else{
    
            // Add term level
            foreach($all_terms as $term_id => &$_term){
        
                $level = acfe_get_term_level($_term->term_id, $_term->taxonomy);
                $_term->level = $level;
        
            }
    
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
                    
                            $keep[] = $all_term;
                    
                        }
                
                    }else{
                
                        foreach($all_terms as $all_term){
                    
                            if($all_term->taxonomy !== $taxonomy)
                                continue;
                    
                            $keep[] = $all_term;
                    
                        }
                
                    }
            
                    $terms = array_merge($terms, acf_array($keep));
            
                }

                // Terms all childs
                elseif(acfe_ends_with($id, '_all_childs')){
    
                    $term_id = substr($id, 0, -11);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                        continue;
    
                    $keep = array();
    
                    foreach($all_terms as $all_term){
                        
                        if($all_term->taxonomy !== $taxonomy)
                            continue;
    
                        $term_childs = get_term_children($term_id, $taxonomy);
                        
                        if(!in_array($all_term->term_id, $term_childs))
                            continue;
        
                        $keep[] = $all_term;
        
                    }
                    
                    $is_hierarchical = is_taxonomy_hierarchical($taxonomy);
                    
                    // sort into hierachial order
                    if($is_hierarchical){
        
                        $keep = _get_term_children($id, $keep, $taxonomy);
        
                    }
    
                    $terms = array_merge($terms, acf_array($keep));
    
                }
        
                // Terms direct childs
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
                
                        $keep[] = $all_term;
                
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
                    $keep[] = $term;
            
                    $terms = array_merge($terms, acf_array($keep));
            
                }
        
            }
            
        }
    
        $terms = wp_list_pluck($terms, 'term_id');
        
        $terms = array_unique($terms);
    
        $choices = $this->convert_terms_to_choices($terms);
        
        return $choices;
        
    }
    
    function convert_terms_to_choices($terms = array()){
        
        $choices = array();
        
        if(!empty($terms)){
            
            $terms = acf_get_grouped_terms(array(
                'include' => $terms,
                'orderby' => 'include'
            ));
            
            $choices = acf_get_choices_from_grouped_terms($terms, 'name');
            
        }
        
        return $choices;
        
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
            'paged'			=> 0
        ));
        
        // load field
        $field = acf_get_field($options['field_key']);
        
        if(!$field)
            return false;
        
        // Args
        $args = array();
        
        // vars
        $results = array();
    
        // search
        if($options['s'] !== '') {
        
            // strip slashes (search may be integer)
            $s = wp_unslash(strval($options['s']));
            
            // update vars
            $args['search'] = $s;
        
        }
        
        $terms = $this->get_terms($field, $args);
        
        if(!empty($terms)){
            
            $keys = array_keys($terms);
            $single_taxonomy = false;
            
            if(count($keys) === 1)
                $single_taxonomy = true;
            
            foreach($terms as $taxonomy => $term){
    
                $data = array(
                    'text'		=> $taxonomy,
                    'children'	=> array()
                );
    
                foreach($term as $term_id => $term_name){
        
                    $data['children'][] = array(
                        'id' => $term_id,
                        'text' => $term_name
                    );
        
                }
                
                $results[] = $data;
                
            }
    
            if($single_taxonomy){
    
                $results = $results[0]['children'];
        
            }
            
        }
        
        // vars
        $response = array(
            'results'	=> $results
        );
        
        // return
        return $response;
        
    }

    function prepare_field($field){
        
        // Value
        $value = acf_maybe_get($field, 'value');
        $value = acf_get_array($value);
        
        // Choices
        $field['choices'] = array();
        
        // Allow custom
        $allow_custom = acf_maybe_get($field, 'allow_custom');
    
        // Field Type
        $field['type'] = $field['field_type'];
    
        // Normal choices
        if($field['type'] !== 'select' || !$field['ui'] || !$field['ajax']){
    
            $choices = $this->get_terms($field);
    
            $keys = array_keys($choices);
            
            // Single Term
            if(count($keys) === 1){
    
                $choices = $choices[$keys[0]];
                
            }
            
            $field['choices'] = $choices;
            
        // Ajax choices
        }else{
            
            $field['ajax_action'] = 'acf/fields/acfe_taxonomy_terms/query';
    
            $all_terms = array();
            $terms = array_unique($value);
    
            foreach($terms as $term_id){
        
                $term = get_term($term_id);
        
                if(!is_a($term, 'WP_Term'))
                    continue;
        
                $all_terms[] = $term;
        
            }
    
            if(!empty($all_terms)){
        
                $terms = $this->filter_terms($all_terms, $field);
        
                foreach($terms as $taxonomy => $term){
                    foreach($term as $term_id => $term_name){
                
                        $field['choices'][$term_id] = $term_name;
                
                    }
                }
        
            }
            
        }
	
	    // Allow Custom
	    if($allow_custom){
			
            foreach($value as $v){
                
                $found = false;
                
                foreach($field['choices'] as $taxonomy => $term){
                    
                    if(isset($term[$v])){
                        
                        $found = true;
                        break;
                        
                    }
                    
                }
                
                if(!$found)
                    $field['choices'][$v] = $v;
            
            }
		
	    }
        
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
                    $value = '(All ' . $level . $taxonomy->label . ')';
                    
                }

                // Terms all childs
                elseif(acfe_ends_with($id, '_all_childs')){
    
                    $term_id = substr($id, 0, -11);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                        continue;
    
                    $value = $term->name . ' (All childs)';
    
                }
                
                // Terms childs
                elseif(acfe_ends_with($id, '_childs')){
                    
                    $term_id = substr($id, 0, -7);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, acf_array($field['taxonomy'])))
                        continue;
                    
                    $value = $term->name . ' (Direct childs)';
                    
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
            'ajax_action'	=> 'acfe/fields/taxonomy_terms/allow_query',
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
                'radio'     => __('Radio Buttons', 'acf'),
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
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'radio',
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
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    
                ),
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
                    
                ),
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
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'radio',
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
        
        // save_terms
        acf_render_field_setting( $field, array(
            'label'			=> __('Save Terms','acf'),
            'instructions'	=> __('Connect selected terms to the post','acf'),
            'name'			=> 'save_terms',
            'type'			=> 'true_false',
            'ui'			=> 1,
        ));
        
        // load_terms
        acf_render_field_setting( $field, array(
            'label'			=> __('Load Terms','acf'),
            'instructions'	=> __('Load value from posts terms','acf'),
            'name'			=> 'load_terms',
            'type'			=> 'true_false',
            'ui'			=> 1,
        ));
        
    }
    
    function load_value($value, $post_id, $field){
        
        // load_terms
        if($field['load_terms']){
            
            // get valid terms
            $value = acf_get_array($value);
            
            $taxonomy = $field['taxonomy'];
            
            if(empty($taxonomy))
                $taxonomy = acf_get_taxonomies();
            
            // get terms
            $info = acf_get_post_id_info($post_id);
            $term_ids = wp_get_object_terms($info['id'], $taxonomy, array('fields' => 'ids', 'orderby' => 'none'));
            
            // bail early if no terms
            if(empty($term_ids) || is_wp_error($term_ids))
                return false;
            
            // sort
            if(!empty($value)){
                
                $order = array();
                
                foreach($term_ids as $i => $v){
                    
                    $order[$i] = array_search($v, $value);
                    
                }
                
                array_multisort($order, $term_ids);
                
            }
            
            // update value
            $value = $term_ids;
            
        }
        
        // return
        return $value;
        
    }
    
    function update_value($value, $post_id, $field){
        
        // vars
        if(is_array($value)){
        
            $value = array_filter($value);
            
        }
        
        
        // save_terms
        if($field['save_terms']){
            
            // vars
            $taxonomies = $field['taxonomy'];
            
            if(empty($taxonomies))
                $taxonomies = acf_get_taxonomies();
            
            // force value to array
            $term_ids = acf_get_array($value);
            
            // convert to int
            $term_ids = array_map('intval', $term_ids);
            
            foreach($taxonomies as $taxonomy){
                
                $terms = array();
                
                foreach($term_ids as $term_id){
                    
                    $term = get_term($term_id);
                    $term_taxonomy = $term->taxonomy;
                    
                    if($term_taxonomy !== $taxonomy)
                        continue;
                    
                    $terms[] = $term_id;
                    
                }
                
                // get existing term id's (from a previously saved field)
                $old_term_ids = isset($this->save_post_terms[$taxonomy]) ? $this->save_post_terms[$taxonomy] : array();
                
                // append
                $this->save_post_terms[$taxonomy] = array_merge($old_term_ids, $terms);
                
            }
            
            // if called directly from frontend update_field()
            if(!did_action('acf/save_post')){
                
                $this->save_post($post_id);
                
                return $value;
                
            }
            
        }
        
        // return
        return $value;
        
    }
    
    function save_post($post_id){
        
        // bail ealry if no terms
        if(empty($this->save_post_terms))
            return;
        
        // vars
        $info = acf_get_post_id_info($post_id);
        
        // loop
        foreach($this->save_post_terms as $taxonomy => $term_ids){
            
            // save
            wp_set_object_terms($info['id'], $term_ids, $taxonomy, false);
            
        }
        
        // reset array ( WP saves twice )
        $this->save_post_terms = array();
        
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