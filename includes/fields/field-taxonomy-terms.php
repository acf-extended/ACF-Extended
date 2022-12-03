<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_taxonomy_terms')):

class acfe_field_taxonomy_terms extends acf_field{
    
    // vars
    var $save_post_terms = array();
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_taxonomy_terms';
        $this->label = __('Taxonomy Terms', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'taxonomy'              => array(),
            'allow_terms'           => array(),
            'field_type'            => 'checkbox',
            'choices'               => array(),
            'default_value'         => '',
            'return_format'         => 'id',
            'ui'                    => 0,
            'multiple'              => 0,
            'allow_null'            => 0,
            'ajax'                  => 0,
            'placeholder'           => '',
            'search_placeholder'    => '',
            'layout'                => '',
            'toggle'                => 0,
            'load_terms'            => 0,
            'save_terms'            => 0,
            'allow_custom'          => 0,
            'other_choice'          => 0,
        );
    
        // actions
        $this->add_action('acf/save_post',                                         array($this, 'save_post'), 15, 1);
        
        // ajax
        $this->add_action('wp_ajax_acfe/fields/taxonomy_terms/allow_query',        array($this, 'ajax_query_allowed_terms'));
        $this->add_action('wp_ajax_nopriv_acfe/fields/taxonomy_terms/allow_query', array($this, 'ajax_query_allowed_terms'));
    
        $this->add_action('wp_ajax_acf/fields/acfe_taxonomy_terms/query',          array($this, 'ajax_query'));
        $this->add_action('wp_ajax_nopriv_acf/fields/acfe_taxonomy_terms/query',   array($this, 'ajax_query'));
        
    }
    
    
    /**
     * ajax_query_allowed_terms
     *
     * wp_ajax_acfe/fields/taxonomy_terms/allow_query
     */
    function ajax_query_allowed_terms(){
        
        // validate
        if(!acf_verify_ajax()){
            die();
        }
        
        // get choices
        $response = $this->get_ajax_query_allowed_terms($_POST);
        
        // return
        acf_send_ajax_results($response);
            
    }
    
    
    /**
     * get_ajax_query_allowed_terms
     *
     * @param $options
     *
     * @return array[]
     */
    function get_ajax_query_allowed_terms($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'       => 0,
            's'             => '',
            'field_key'     => '',
            'paged'         => 1,
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
                    
                    if(acfe_get_term_level($term_id, $term->taxonomy) === $options['level']){
                        $terms_final[ $group ][ $term_id ] = $term;
                    }
                    
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
                'text'      => $group_title,
                'children'  => array()
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
                        'id'    => $id,
                        'text'  => '(' . $text . ')'
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
            'results' => $results
        );
        
        // return
        return $response;
            
    }
    
    
    /**
     * ajax_query
     *
     * wp_ajax_acf/fields/acfe_taxonomy_terms/query
     */
    function ajax_query(){
        
        // validate
        if(!acf_verify_ajax()){
            die();
        }
        
        // get choices
        $response = $this->get_ajax_query($_POST);
        
        // return
        acf_send_ajax_results($response);
        
    }
    
    
    /**
     * get_ajax_query
     *
     * @param $options
     *
     * @return array|array[]|false
     */
    function get_ajax_query($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'   => 0,
            's'         => '',
            'field_key' => '',
            'paged'     => 0
        ));
        
        // load field
        $field = acf_get_field($options['field_key']);
        
        if(!$field){
            return false;
        }
        
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
        
        //vars
        $name = $field['name'];
        $key = $field['key'];
        $post_id = $options['post_id'];
    
        // filters
        $args = apply_filters("acfe/fields/taxonomy_terms/query",               $args, $field, $post_id);
        $args = apply_filters("acfe/fields/taxonomy_terms/query/name={$name}",  $args, $field, $post_id);
        $args = apply_filters("acfe/fields/taxonomy_terms/query/key={$key}",    $args, $field, $post_id);
        
        $terms = $this->get_terms($field, $args);
        
        if(!empty($terms)){
            
            $keys = array_keys($terms);
            $single_taxonomy = false;
            
            if(count($keys) === 1){
                $single_taxonomy = true;
            }
            
            foreach($terms as $taxonomy => $term){
    
                $data = array(
                    'text'      => $taxonomy,
                    'children'  => array()
                );
    
                foreach($term as $term_id => $term_name){
        
                    $data['children'][] = array(
                        'id'    => $term_id,
                        'text'  => $term_name
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
            'results' => $results
        );
        
        // return
        return $response;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        if(isset($field['default_value'])){
            $field['default_value'] = acf_encode_choices($field['default_value'], false);
        }
        
        // Allow Taxonomy
        acf_render_field_setting($field, array(
            'label'         => __('Allow Taxonomy','acf'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'taxonomy',
            'choices'       => acf_get_taxonomy_labels(),
            'multiple'      => 1,
            'ui'            => 1,
            'allow_null'    => 1,
            'placeholder'   => __("All taxonomies",'acf'),
        ));
        
        // Allow Terms
        $choices = array();
        $field['taxonomy'] = acf_get_array($field['taxonomy']);
        
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
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $taxonomy = get_taxonomy($taxonomy);
                    $value = '(All ' . $level . $taxonomy->label . ')';
                    
                }

                // Terms all childs
                elseif(acfe_ends_with($id, '_all_childs')){
    
                    $term_id = substr($id, 0, -11);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
    
                    $value = $term->name . ' (All childs)';
    
                }
                
                // Terms childs
                elseif(acfe_ends_with($id, '_childs')){
                    
                    $term_id = substr($id, 0, -7);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $value = $term->name . ' (Direct childs)';
                    
                }
                
                // Term
                else{
                
                    $term = get_term($id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $term_data = acf_get_choice_from_term($term, 'name');
                    
                    $value = $term_data['text'];
                
                }
                
                // append to choices
                $choices[ $id ] = $value;
                
            }
            
        }
        
        acf_render_field_setting($field, array(
            'label'         => __('Allow Terms','acf'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'allow_terms',
            'choices'       => $choices,
            'multiple'      => 1,
            'ui'            => 1,
            'allow_null'    => 1,
            'ajax'          => 1,
            'placeholder'   => __("All terms",'acf'),
            'ajax_action'   => 'acfe/fields/taxonomy_terms/allow_query',
        ));
        
        // Select: Terms level
        acf_render_field_setting($field, array(
            'label'         => __('Terms_level','acf'),
            'instructions'  => '',
            'name'          => 'allow_level',
            'type'          => 'number',
            'append'        => 'levels',
            'min'           => 0,
            'placeholder'   => __('All','acf'),
            '_append'       => 'allow_terms',
            'value'         => false
        ));
        
        // field_type
        acf_render_field_setting($field, array(
            'label'         => __('Appearance','acf'),
            'instructions'  => __('Select the appearance of this field', 'acf'),
            'type'          => 'select',
            'name'          => 'field_type',
            'optgroup'      => true,
            'choices'       => array(
                'checkbox'      => __('Checkbox', 'acf'),
                'radio'         => __('Radio Buttons', 'acf'),
                'select'        => _x('Select', 'noun', 'acf')
            )
        ));
        
        // default_value
        acf_render_field_setting($field, array(
            'label'         => __('Default Value','acf'),
            'instructions'  => __('Enter each default value on a new line','acf'),
            'name'          => 'default_value',
            'type'          => 'textarea',
        ));
        
        // return_format
        acf_render_field_setting($field, array(
            'label'         => __('Return Value', 'acf'),
            'instructions'  => '',
            'type'          => 'radio',
            'name'          => 'return_format',
            'choices'       => array(
                'object'        => __('Term object', 'acfe'),
                'name'          => __('Term name', 'acfe'),
                'id'            => __('Term ID', 'acfe'),
            ),
            'layout'        => 'horizontal',
        ));
        
        // Select: ui
        acf_render_field_setting($field, array(
            'label'         => __('Stylised UI','acf'),
            'instructions'  => '',
            'name'          => 'ui',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions'    => array(
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
            'label'         => __('Allow Null?','acf'),
            'instructions'  => '',
            'name'          => 'allow_null',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions'    => array(
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
    
        // Select: Placeholder
        acf_render_field_setting($field, array(
            'label'             => __('Placeholder','acf'),
            'instructions'      => __('Appears within the input','acf'),
            'type'              => 'text',
            'name'              => 'placeholder',
            'placeholder'       => _x('Select', 'verb', 'acf'),
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
                        'value'     => '0',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                ),
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
                    array(
                        'field'     => 'allow_null',
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
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                ),
            )
        ));
    
        // Select: Search Placeholder
        acf_render_field_setting($field, array(
            'label'             => __('Search Input Placeholder','acf'),
            'instructions'      => __('Appears within the search input','acf'),
            'type'              => 'text',
            'name'              => 'search_placeholder',
            'placeholder'       => '',
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
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                ),
            )
        ));
        
        // Select: multiple
        acf_render_field_setting($field, array(
            'label'         => __('Select multiple values?','acf'),
            'instructions'  => '',
            'name'          => 'multiple',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions'    => array(
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
        acf_render_field_setting($field, array(
            'label'         => __('Use AJAX to lazy load choices?','acf'),
            'instructions'  => '',
            'name'          => 'ajax',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions'    => array(
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
        acf_render_field_setting($field, array(
            'label'         => __('Layout','acf'),
            'instructions'  => '',
            'type'          => 'radio',
            'name'          => 'layout',
            'layout'        => 'horizontal', 
            'choices'       => array(
                'vertical'      => __("Vertical",'acf'),
                'horizontal'    => __("Horizontal",'acf')
            ),
            'conditions'    => array(
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
        acf_render_field_setting($field, array(
            'label'         => __('Toggle','acf'),
            'instructions'  => __('Prepend an extra checkbox to toggle all choices','acf'),
            'name'          => 'toggle',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions'    => array(
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
        acf_render_field_setting($field, array(
            'label'         => __('Save Terms','acf'),
            'instructions'  => __('Connect selected terms to the post','acf'),
            'name'          => 'save_terms',
            'type'          => 'true_false',
            'ui'            => 1,
        ));
        
        // load_terms
        acf_render_field_setting($field, array(
            'label'         => __('Load Terms','acf'),
            'instructions'  => __('Load value from posts terms','acf'),
            'name'          => 'load_terms',
            'type'          => 'true_false',
            'ui'            => 1,
        ));
        
    }
    
    
    /**
     * update_field
     *
     * @param $field
     *
     * @return mixed
     */
    function update_field($field){
        
        $field['default_value'] = acf_decode_choices($field['default_value'], true);
        
        if($field['field_type'] === 'radio'){
            $field['default_value'] = acfe_unarray($field['default_value']);
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field($field){
        
        // value
        $value = acf_maybe_get($field, 'value');
        $value = acf_get_array($value);
        
        // choices
        $field['choices'] = array();
    
        // field type
        $field['type'] = $field['field_type'];
    
        // normal choices
        if($field['type'] !== 'select' || !$field['ui'] || !$field['ajax'] || isset($field['ajax_action'])){
            
            //vars
            $name = $field['_name'];
            $key = $field['key'];
            $post_id = acfe_get_post_id();
    
            // filters
            $args = array();
            $args = apply_filters("acfe/fields/taxonomy_terms/query",               $args, $field, $post_id);
            $args = apply_filters("acfe/fields/taxonomy_terms/query/name={$name}",  $args, $field, $post_id);
            $args = apply_filters("acfe/fields/taxonomy_terms/query/key={$key}",    $args, $field, $post_id);
    
            $choices = $this->get_terms($field, $args);
    
            $keys = array_keys($choices);
            
            // Single Term
            if(count($keys) === 1){
                $choices = $choices[ $keys[0] ];
            }
            
            $field['choices'] = $choices;
            
        // ajax choices
        }else{
            
            if(!isset($field['ajax_action'])){
                $field['ajax_action'] = 'acf/fields/acfe_taxonomy_terms/query';
            }
    
            $all_terms = array();
            $terms = array_unique($value);
    
            foreach($terms as $term_id){
        
                $term = get_term($term_id);
        
                if(is_a($term, 'WP_Term')){
                    $all_terms[] = $term;
                }
        
            }
    
            if(!empty($all_terms)){
        
                $terms = $this->filter_terms($all_terms, $field);
        
                foreach($terms as $taxonomy => $term){
                    foreach($term as $term_id => $term_name){
                
                        $field['choices'][ $term_id ] = $term_name;
                
                    }
                }
        
            }
            
        }
    
        // allow custom
        if($field['allow_custom']){
            
            foreach($value as $v){
                
                $found = false;
                
                foreach($field['choices'] as $taxonomy => $term){
                    if(isset($term[ $v ])){
                        $found = true;
                        break;
                    }
                }
                
                if(!$found){
                    $field['choices'][ $v ] = $v;
                    $field['custom_choices'][ $v ] = $v;
                }
            
            }
        
        }
        
        // unarray values if radio
        if($field['type'] === 'radio'){
            
            $values = acf_get_array($field['value']);
            
            // check if value exists in choices and select it (in case of allowed terms)
            foreach($values as $value){
                
                if(isset($field['choices'][ $value ])){
                    $field['value'] = $value;
                    break;
                }
                
            }
            
            // unarray
            $field['value'] = acfe_unarray($field['value']);
        
        }
        
        return $field;
        
    }
    
    
    /**
     * load_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return false|mixed|WP_Error|WP_Term
     */
    function load_value($value, $post_id, $field){
    
        // bail early if there is no post id
        if(!$post_id){
            return $value;
        }
    
        // bail early when local meta
        if(acfe_is_local_post_id($post_id)){
            return $value;
        }
    
        // bail early front-end form
        if(acfe_starts_with($post_id, 'acfe_form-')){
            return $value;
        }
        
        // load_terms
        if($field['load_terms']){
            
            // get valid terms
            $value = acf_get_array($value);
            
            $taxonomy = $field['taxonomy'];
            
            if(empty($taxonomy)){
                $taxonomy = acf_get_taxonomies();
            }
            
            // get terms
            $info = acf_get_post_id_info($post_id);
            $term_ids = wp_get_object_terms($info['id'], $taxonomy, array('fields' => 'ids', 'orderby' => 'none'));
            
            // bail early if no terms
            if(empty($term_ids) || is_wp_error($term_ids)){
                return false;
            }
            
            // sort
            if(!empty($value)){
                
                $order = array();
                
                foreach($term_ids as $i => $v){
                    $order[ $i ] = array_search($v, $value);
                }
                
                array_multisort($order, $term_ids);
                
            }
            
            // update value
            $value = $term_ids;
            
        }
        
        // return
        return $value;
        
    }
    
    
    /**
     * update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|mixed
     */
    function update_value($value, $post_id, $field){
        
        // vars
        if(is_array($value)){
            $value = array_filter($value);
        }
    
        // bail early if there is no post id
        if(!$post_id){
            return $value;
        }
        
        // bail early when local meta
        if(acfe_is_local_post_id($post_id)){
            return $value;
        }
    
        // bail early front-end form
        if(acfe_starts_with($post_id, 'acfe_form-')){
            return $value;
        }
        
        // save_terms
        if($field['save_terms']){
            
            // vars
            $taxonomies = $field['taxonomy'];
            
            if(empty($taxonomies)){
                $taxonomies = acf_get_taxonomies();
            }
            
            // force value to array
            $term_ids = acf_get_array($value);
            
            // convert to int
            $term_ids = array_map('intval', $term_ids);
            
            foreach($taxonomies as $taxonomy){
                
                $terms = array();
                
                foreach($term_ids as $term_id){
                    
                    $term = get_term($term_id);
                    
                    if($term && !is_wp_error($term) && $term->taxonomy === $taxonomy){
                        $terms[] = $term_id;
                    }
                    
                }
                
                // get existing term id's (from a previously saved field)
                $old_term_ids = isset($this->save_post_terms[ $taxonomy ]) ? $this->save_post_terms[ $taxonomy ] : array();
                
                // append
                $this->save_post_terms[ $taxonomy ] = array_merge($old_term_ids, $terms);
                
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
    
    
    /**
     * format_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|false|mixed|string[]
     */
    function format_value($value, $post_id, $field){
    
        // Bail early
        if(empty($value)){
            return $value;
        }
    
        // Vars
        $is_array = is_array($value);
        $value = acf_get_array($value);
    
        // Loop
        foreach($value as &$v){
        
            // Retrieve Object
            $object = get_term($v);
        
            if(!$object || is_wp_error($object)) continue;
        
            // Return: Object
            if($field['return_format'] === 'object'){
                $v = $object;
    
            // Return: Name
            }elseif($field['return_format'] === 'name'){
                $v = $object->name;
            }
        
        }
    
        // Do not return array
        if(!$is_array){
            $value = acfe_unarray($value);
        }
    
        // Return
        return $value;
        
    }
    
    
    /**
     * save_post
     *
     * @param $post_id
     */
    function save_post($post_id){
        
        // bail ealry if no terms
        if(empty($this->save_post_terms)){
            return;
        }
    
        // bail early if not post
        $data = acf_get_post_id_info($post_id);
        
        // loop
        foreach($this->save_post_terms as $taxonomy => $term_ids){
            
            // save
            wp_set_object_terms($data['id'], $term_ids, $taxonomy, false);
            
        }
        
        // reset array ( WP saves twice )
        $this->save_post_terms = array();
        
    }
    
    
    /**
     * get_terms
     *
     * @param $field
     * @param $args
     *
     * @return array
     */
    function get_terms($field, $args = array()){
        
        // taxonomy
        $field['taxonomy'] = acf_get_array($field['taxonomy']);
        
        // choices
        $choices = array();
        
        // get allowed taxonomies
        $taxonomies = $field['taxonomy'];
        $taxonomies = array_filter($taxonomies, 'taxonomy_exists');
        $taxonomies = array_values($taxonomies);
        
        // append all taxonomies if empty
        $taxonomies = acf_get_taxonomy_labels($taxonomies);
        $taxonomies = array_keys($taxonomies);
        
        // parse args
        $args = wp_parse_args($args, array(
            'taxonomy' => $taxonomies
        ));
        
        // get terms
        $all_terms = acf_get_terms($args);
        
        if(empty($all_terms)){
            return $choices;
        }
        
        // add to choices
        $choices = $this->filter_terms($all_terms, $field);
        
        return $choices;
        
    }
    
    
    /**
     * filter_terms
     *
     * @param $all_terms
     * @param $field
     *
     * @return array
     */
    function filter_terms($all_terms, $field){
        
        if(empty($field['taxonomy']) && empty($field['allow_terms'])){
            
            $terms = wp_list_pluck($all_terms, 'term_id');
            $terms = array_unique($terms);
            
            $choices = $this->convert_terms_to_choices($terms, $field);
            
            return $choices;
            
        }
        
        $terms = array();
        
        // Filter taxonomy terms
        if(!empty($field['taxonomy'])){
            
            $allowed_tax_terms = array();
            
            foreach($all_terms as $term){
                
                if(in_array($term->taxonomy, $field['taxonomy'])){
                    $allowed_tax_terms[] = $term;
                }
                
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
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $keep = array();
                    
                    if($level){
                        
                        foreach($all_terms as $all_term){
                            if((int) $all_term->level === (int) $level && $all_term->taxonomy === $taxonomy){
                                $keep[] = $all_term;
                            }
                        }
                        
                    }else{
                        
                        foreach($all_terms as $all_term){
                            if($all_term->taxonomy === $taxonomy){
                                $keep[] = $all_term;
                            }
                        }
                        
                    }
                    
                    $terms = array_merge($terms, $keep);
                    
                }
                
                // Terms all childs
                elseif(acfe_ends_with($id, '_all_childs')){
                    
                    $term_id = substr($id, 0, -11);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $keep = array();
                    
                    foreach($all_terms as $all_term){
                        
                        if($all_term->taxonomy === $taxonomy){
                            
                            $term_childs = get_term_children($term_id, $taxonomy);
                            
                            if(in_array($all_term->term_id, $term_childs)){
                                $keep[] = $all_term;
                            }
                            
                        }
                        
                    }
                    
                    // sort into hierachial order
                    if(is_taxonomy_hierarchical($taxonomy)){
                        $keep = _get_term_children($id, $keep, $taxonomy);
                        $keep = acf_get_array($keep);
                    }
                    
                    $terms = array_merge($terms, $keep);
                    
                }
                
                // Terms direct childs
                elseif(acfe_ends_with($id, '_childs')){
                    
                    $term_id = substr($id, 0, -7);
                    $term = get_term($term_id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $keep = array();
                    
                    foreach($all_terms as $all_term){
                        if((int) $all_term->parent === (int) $term_id && $all_term->taxonomy === $taxonomy){
                            $keep[] = $all_term;
                        }
                    }
                    
                    $terms = array_merge($terms, $keep);
                    
                }
                
                // Term
                else{
                    
                    $term = get_term($id);
                    $taxonomy = $term->taxonomy;
                    
                    if(!empty($field['taxonomy']) && !in_array($taxonomy, $field['taxonomy'])){
                        continue;
                    }
                    
                    $keep = array();
                    $keep[] = $term;
                    
                    $terms = array_merge($terms, $keep);
                    
                }
                
            }
            
        }
        
        $terms = wp_list_pluck($terms, 'term_id');
        
        $terms = array_unique($terms);
        
        $choices = $this->convert_terms_to_choices($terms, $field);
        
        return $choices;
        
    }
    
    
    /**
     * convert_terms_to_choices
     *
     * @param $terms
     * @param $field
     *
     * @return array
     */
    function convert_terms_to_choices($terms, $field){
        
        $choices = array();
        
        if(empty($terms)){
            return $choices;
        }
        
        // get terms grouped by taxonomy
        //
        // array(
        //     Category => array(
        //         25 => WP_Term Object(...),
        //         26 => WP_Term Object(...),
        //     )
        // )
        $terms = acf_get_grouped_terms(array(
            'include' => $terms,
            'orderby' => 'include'
        ));
        
        // list terms grouped by taxonomy with names
        //
        // array(
        //     Category => array(
        //         25 => Category 1,
        //         26 => - Sub Category A,
        //     )
        // )
        $choices = acf_get_choices_from_grouped_terms($terms, 'name');
        
        //vars
        $name = $field['_name'];
        $key = $field['key'];
        $post_id = acfe_get_post_id();
        
        foreach($choices as $taxonomy => &$terms){
            
            foreach($terms as $term_id => &$text){
                
                //vars
                $term = get_term($term_id);
                
                // filters
                $text = apply_filters("acfe/fields/taxonomy_terms/result",              $text, $term, $field, $post_id);
                $text = apply_filters("acfe/fields/taxonomy_terms/result/name={$name}", $text, $term, $field, $post_id);
                $text = apply_filters("acfe/fields/taxonomy_terms/result/key={$key}",   $text, $term, $field, $post_id);
                
            }
            
        }
        
        return $choices;
        
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     *
     * @return mixed
     */
    function translate_field($field){
        
        $field['placeholder'] = acf_translate($field['placeholder']);
        $field['search_placeholder'] = acf_translate($field['search_placeholder']);
        
        return $field;
        
    }

}

// initialize
acf_register_field_type('acfe_field_taxonomy_terms');

endif;