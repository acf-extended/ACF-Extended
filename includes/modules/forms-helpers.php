<?php

if(!defined('ABSPATH'))
    exit;

acf_register_store('acfe/form')->prop('multisite', true);

if(!class_exists('acfe_dynamic_forms_helpers')):

class acfe_dynamic_forms_helpers{
    
    function get_field_groups($post_id = 0){
        
        $return = array();
        
        if(!$post_id)
            $post_id = acfe_get_post_id();
        
        if(!$post_id)
            return $return;
        
        // Field Groups
        $field_groups = get_field('acfe_form_field_groups', $post_id);
        
        if(!empty($field_groups)){
            
            acf_disable_filter('clone');
            
            foreach($field_groups as $field_group_key){
                
                $field_group = acf_get_field_group($field_group_key);
                
                if(!$field_group)
                    continue;
                
                $field_group['fields'] = acf_get_fields($field_group);
                
                $return[] = $field_group;
                
            }
            
            acf_enable_filter('clone');
            
        }
        
        // return
        return $return;
        
    }
    
    function get_field_groups_front($post_id = 0){
        
        $return = array();
        
        if(!$post_id)
            $post_id = acfe_get_post_id();
        
        if(!$post_id)
            return $return;
        
        // Field Groups
        $field_groups = get_field('acfe_form_field_groups', $post_id);
        
        if(!empty($field_groups)){
            
            foreach($field_groups as $field_group_key){
                
                $field_group = acf_get_field_group($field_group_key);
                
                if(!$field_group)
                    continue;
                
                $field_group['fields'] = acf_get_fields($field_group);
                
                $return[] = $field_group;
                
            }
            
        }
        
        // return
        return $return;
        
    }
    
    function map_fields_deep_no_custom($field){
        
        $choices = array();
        
        if(!empty($field['choices'])){
            
            $generic = true;
            
            if(is_array($field['choices']) && count($field['choices']) === 1){
                
                reset($field['choices']);
                $key = key($field['choices']);
                
                if(acf_is_field_key($key))
                    $generic = false;
                
            }
            
            if($generic)
                $choices['Generic'] = $field['choices'];
            
        }
        
        $fields_choices = $this->get_fields_choices(true, $field);
        
        if(!empty($fields_choices)){
            
            $field['choices'] = array_merge($choices, $fields_choices);
            
        }
        
        return $field;
        
    }
    
    function map_fields_deep($field){
        
        // Map Fields
        $fields_choices = $this->get_fields_choices(true, $field);
        
        if(!empty($fields_choices)){
            
            $field['choices'] = array_replace($field['choices'], $fields_choices);
            
        }
        
        // Clean Choices
        if(!empty($field['choices'])){
            
            $sub_values = array();
            
            foreach($field['choices'] as $category => $values){
                
                // Generate available values
                if(is_array($values)){
                    
                    $sub_values = array_merge($sub_values, $values);
                    
                    // Generate 'Generic'
                }else{
                    
                    unset($field['choices'][$category]);
                    
                    $field['choices']['Generic'][$category] = $values;
                    
                }
                
            }
            
            // Compare available vs Generic
            if(isset($field['choices']['Generic'])){
                
                foreach($field['choices']['Generic'] as $k => $generic){
                    
                    if(!isset($sub_values[$k]))
                        continue;
                    
                    // Cleanup
                    unset($field['choices']['Generic'][$k]);
                    
                }
                
                if(empty($field['choices']['Generic']))
                    unset($field['choices']['Generic']);
                
            }
            
            // Move Generic to Top
            if(isset($field['choices']['Generic'])){
                
                $new_generic = array(
                    'Generic' => $field['choices']['Generic']
                );
                
                unset($field['choices']['Generic']);
                
                $field['choices'] = array_merge($new_generic, $field['choices']);
                
            }
            
        }
        
        return $field;
        
    }
    
    function map_fields($field){
        
        $fields_choices = $this->get_fields_choices();
        
        if(empty($fields_choices))
            return false;
        
        $field['choices'] = $fields_choices;
        
        return $field;
        
    }
    
    function get_fields_choices($deep = false, $original_field = array()){
        
        $data = $this->get_field_groups();
        $choices = array();
        
        if(empty($data))
            return false;
        
        $field_groups = array();
        
        foreach($data as $field_group){
            
            if(empty($field_group['fields']))
                continue;
            
            foreach($field_group['fields'] as $s_field){
                
                $field_groups[ $field_group['title'] ][] = $s_field;
                
            }
            
        }
        
        if(!empty($field_groups)){
            
            foreach($field_groups as $field_group_title => $fields){
                
                foreach($fields as $field){
                    
                    if(isset($choices[ $field_group_title ][ $field['key'] ])) continue;
                    
                    if($field['type'] === 'acfe_payment_cart' || $field['type'] === 'acfe_payment_selector') continue;
                    
                    // First level
                    if(!$deep){
                        
                        $label = !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
                        $label .= $field['required'] ? ' *' : '';
                        
                        $choices[ $field_group_title ][ $field['key'] ] = $label. ' (' . $field['key'] . ')';
                        
                    // Deep
                    }else{
                        
                        $this->get_fields_choices_recursive($choices[$field_group_title], $field, $original_field);
                        
                    }
                    
                }
                
            }
            
        }
        
        return $choices;
        
    }
    
    function get_fields_choices_recursive(&$choices, $field, $original_field){
        
        $label = '';
        
        $ancestors = isset($field['ancestors']) ? $field['ancestors'] : count(acf_get_field_ancestors($field));
        $label = str_repeat('- ', $ancestors) . $label;
        
        $label .= !empty($field['label']) ? $field['label'] : '(' . __('no label', 'acf') . ')';
        $label .= $field['required'] ? ' *' : '';
        
        /*
        if(acf_maybe_get($original_field, 'type') === 'select'){
            
            $label = $label . ' <code style="font-size:12px;">{field:' . $field['name'] . '}</code>';
            
        }else{
            
            $label = $label . ' (' . $field['key'] . ')';
            
        }
        */
        
        $label = $label . ' (' . $field['key'] . ')';
        
        $choices[$field['key']] = $label;
        
        if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
            
            foreach($field['sub_fields'] as $s_field){
                
                $this->get_fields_choices_recursive($choices, $s_field, $original_field);
                
            }
            
        }
        
    }
    
    function render_fields($content, $post_id, $args){
        
        // Mapping
        $form_id = $args['ID'];
        $form_name = $args['name'];
        
        $mapped_field_groups = $this->get_field_groups_front($form_id);
        $mapped_field_groups_keys = wp_list_pluck($mapped_field_groups, 'key');
        
        $mapped_fields = array();
        
        if(!empty($mapped_field_groups)){
            
            $post = acf_get_post_id_info($post_id);
            
            // Apply Field Groups Rules
            if($post['type'] === 'post' && $args['field_groups_rules']){
                
                $filter = array(
                    'post_id'   => $post_id,
                    'post_type' => get_post_type($post_id),
                );
                
                $filtered = array();
                
                foreach($mapped_field_groups as $field_group){
                    
                    // Deleted field group
                    if(!isset($field_group['location']))
                        continue;
                    
                    // Force active
                    $field_group['active'] = true;
                    
                    if(acf_get_field_group_visibility($field_group, $filter)){
                        
                        $filtered[] = $field_group;
                        
                    }
                    
                }
                
                $mapped_field_groups = $filtered;
                
            }
            
            if(!empty($mapped_field_groups)){
                
                $mapped_field_groups_keys = wp_list_pluck($mapped_field_groups, 'key');
                
                foreach($mapped_field_groups as $field_group){
                    
                    if(empty($field_group['fields']))
                        continue;
                    
                    foreach($field_group['fields'] as $field){
                        
                        $mapped_fields[] = $field;
                        
                    }
                    
                }
                
            }
            
        }
        
        // Match {field:key}
        if(preg_match_all('/{field:(.*?)}/', $content, $matches)){
            
            foreach($matches[1] as $i => $field_key){
                
                $field = false;
                
                // Field key
                if(strpos($field_key, 'field_') === 0){
                    
                    $field = acf_get_field($field_key);
                    
                    // Field name
                }else{
                    
                    if(!empty($mapped_fields)){
                        
                        foreach($mapped_fields as $mapped_field){
                            
                            if($mapped_field['name'] !== $field_key)
                                continue;
                            
                            $field = $mapped_field;
                            break;
                            
                        }
                        
                    }
                    
                }
                
                if(!$field){
                    
                    $content = str_replace('{field:' . $field_key . '}', '', $content);
                    continue;
                    
                }
                
                $fields = array();
                $fields[] = $field;
                
                ob_start();
                
                acf_render_fields($fields, acf_uniqid('acfe_form'), $args['field_el'], $args['instruction_placement']);
                
                $render_field = ob_get_clean();
                
                $content = str_replace('{field:' . $field_key . '}', $render_field, $content);
                
            }
            
        }
        
        // Match {field_group:key}
        if(preg_match_all('/{field_group:(.*?)}/', $content, $matches)){
            
            //$field_groups = acf_get_field_groups();
            
            foreach($matches[1] as $i => $field_group_key){
                
                $fields = false;
                
                if(!empty($mapped_field_groups)){
                    
                    // Field group key
                    if(strpos($field_group_key, 'group_') === 0){
                        
                        if(in_array($field_group_key, $mapped_field_groups_keys))
                            $fields = acf_get_fields($field_group_key);
                        
                        // Field group title
                    }else{
                        
                        foreach($mapped_field_groups as $field_group){
                            
                            if($field_group['title'] !== $field_group_key)
                                continue;
                            
                            $fields = acf_get_fields($field_group['key']);
                            break;
                            
                        }
                        
                    }
                    
                }
                
                if(!$fields){
                    
                    $content = str_replace('{field_group:' . $field_group_key . '}', '', $content);
                    continue;
                    
                }
                
                ob_start();
                
                acf_render_fields($fields, acf_uniqid('acfe_form'), $args['field_el'], $args['instruction_placement']);
                
                $render_fields = ob_get_clean();
                
                $content = str_replace('{field_group:' . $field_group_key . '}', $render_fields, $content);
                
            }
            
        }
        
        // Match current_post {current:post:id}
        $content = acfe_form_map_current($content, $post_id, $args);
        
        // Match {get_field:name} {get_field:name:123}
        $content = acfe_form_map_get_field($content, $post_id);
        
        // Match {get_option:name}
        $content = acfe_form_map_get_option($content);
        
        // Match {query_var:name} {query_var:name:key}
        $content = acfe_form_map_query_var($content);
        
        // Match {request:name}
        $content = acfe_form_map_request($content);
        
        return $content;
        
    }
    
    function map_fields_values(&$data = array(), $array = array()){
        
        if(empty($array)){
            
            if(!acf_maybe_get_POST('acf'))
                return array();
            
            $array = $_POST['acf'];
            $array = wp_unslash($array);
            
        }
        
        foreach($array as $field_key => $value){
            
            if(!acf_is_field_key($field_key))
                continue;
            
            $field = acf_get_field($field_key);
            
            // bypass _validate_email (honeypot)
            if(!$field || !isset($field['name']) || $field['name'] === '_validate_email')
                continue;
            
            $data[] = array(
                'label' => $field['label'],
                'name'  => $field['name'],
                'key'   => $field['key'],
                'field' => $field,
                'value' => $value,
            );
            
            if(is_array($value) && !empty($value)){
                
                $this->map_fields_values($data, $value);
                
            }
            
        }
        
        return $data;
        
    }
    
    function map_field_value($content, $post_id = 0, $form = array()){
        
        // Get store
        $store = acf_get_store('acfe/form');
        
        // Store found
        if(!$store->has('data')){
            
            $data = $this->map_fields_values();
            
            // Set Store: ACF meta
            $store->set('data', $data);
            
        }
        
        $is_array = is_array($content);
        
        $content = acf_array($content);
        
        foreach($content as &$c){
            
            // Match field_abcdef123456
            $c = acfe_form_map_field_key($c);
            
            // Match {field:name} {field:key}
            $c = acfe_form_map_field($c);
            
            // Match {fields}
            $c = acfe_form_map_fields($c, $post_id, $form);
            
            // Match current_post {current:post:id}
            $c = acfe_form_map_current($c, $post_id, $form);
    
            // Match {action:field}
            $c = acfe_form_map_action($c);
    
            // Match {request:name}
            $c = acfe_form_map_request($c);
            
            // Match {get_field:name} {get_field:name:123}
            $c = acfe_form_map_get_field($c, $post_id);
            
            // Match {get_option:name}
            $c = acfe_form_map_get_option($c);
            
            // Match {query_var:name} {query_var:name:key}
            $c = acfe_form_map_query_var($c);
            
        }
        
        if($is_array){
            return $content;
        }
        
        if(isset($content[0])){
            return $content[0];
        }
        
        return false;
        
    }
    
    function map_field_value_load($content, $post_id = 0, $form = array()){
        
        $is_array = is_array($content);
        
        $content = acf_array($content);
        
        foreach($content as &$c){
            
            // Match current_post {current:post:id}
            $c = acfe_form_map_current($c, $post_id, $form);
            
            // Match {get_field:name} {get_field:name:123}
            $c = acfe_form_map_get_field($c, $post_id);
            
            // Match {get_option:name}
            $c = acfe_form_map_get_option($c);
            
            // Match {query_var:name} {query_var:name:key}
            $c = acfe_form_map_query_var($c);
            
            // Match {request:name}
            $c = acfe_form_map_request($c);
            
        }
        
        if($is_array){
            return $content;
        }
        
        if(isset($content[0])){
            return $content[0];
        }
        
        return false;
        
    }
    
    function filter_meta($meta, $acf){
        
        if(empty($meta) || empty($acf))
            return false;
        
        foreach($acf as $field_key => $value){
            
            if(in_array($field_key, $meta))
                continue;
            
            unset($acf[$field_key]);
            
        }
        
        return $acf;
        
    }
    
    function format_value_array($value){
        
        if(!is_array($value)) return $value;
        
        $return = array();
        
        foreach($value as $i => $v){
            
            $key = !is_numeric($i) ? $i . ': ' : '';
            
            $return[] = $key . $this->format_value_array($v);
            
        }
        
        return implode(', ', $return);
        
    }
    
    function format_value($value, $field){
    
        $post_id = 0;
        $_value = $value;
        
        $value = acf_format_value($value, $post_id, $field);
        
        $value = apply_filters("acfe/form/format_value",                        $value, $_value, $post_id, $field);
        $value = apply_filters("acfe/form/format_value/type={$field['type']}",  $value, $_value, $post_id, $field);
        $value = apply_filters("acfe/form/format_value/key={$field['key']}",    $value, $_value, $post_id, $field);
        $value = apply_filters("acfe/form/format_value/name={$field['name']}",  $value, $_value, $post_id, $field);
        
        // Is Array? Fallback
        if(is_array($value)){
            
            $value = $this->format_value_array($value);
            
        }
        
        return $value;
        
    }
    
}

acf_new_instance('acfe_dynamic_forms_helpers');

endif;

function acfe_form_render_fields($content, $post_id, $args){
    
    return acf_get_instance('acfe_dynamic_forms_helpers')->render_fields($content, $post_id, $args);
    
}

function acfe_form_map_field_value($field, $post_id = 0, $form = array()){
    
    return acf_get_instance('acfe_dynamic_forms_helpers')->map_field_value($field, $post_id, $form);
    
}

function acfe_form_map_field_value_load($field, $post_id = 0, $form = array()){
    
    return acf_get_instance('acfe_dynamic_forms_helpers')->map_field_value_load($field, $post_id, $form);
    
}

function acfe_form_filter_meta($meta, $acf){
    
    return acf_get_instance('acfe_dynamic_forms_helpers')->filter_meta($meta, $acf);
    
}

function acfe_form_format_value($value, $field, $deprecated = null){
    
    // compatibility for 0.8.7.6 old argument
    // second argument was $post_id
    if($deprecated !== null){
        $field = $deprecated;
    }
    
    return acf_get_instance('acfe_dynamic_forms_helpers')->format_value($value, $field);
    
}

// Match field_abcdef123456
function acfe_form_map_field_key($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(!acf_is_field_key($content))
        return $content;
    
    // Setup data
    $data = array();
    
    $store = acf_get_store('acfe/form');
    
    if($store->has('data')){
        
        $data = $store->get('data');
        
    }
    
    if(!empty($data)){
        
        foreach($data as $field){
            
            if($field['key'] !== $content)
                continue;
            
            return $field['value'];
            
        }
        
    }
    
    return false;
    
}

function acfe_form_map_current($content, $post_id = 0, $form = array()){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    // init
    $value = 0;
    
    // Post
    $post = acf_get_post_id_info($post_id);
    
    // Match current_post
    if($content === 'current_post'){
        
        if($post['type'] === 'post')
            $value = $post['id'];
        
        return $value;
        
    }
    
    // Match current_post_parent
    elseif($content === 'current_post_parent'){
        
        if($post['type'] === 'post')
            $value = get_post_field('post_parent', $post['id']);
        
        return $value;
        
    }
    
    // Match current_post_author
    elseif($content === 'current_post_author'){
        
        if($post['type'] === 'post')
            $value = get_post_field('post_author', $post['id']);
        
        return $value;
        
    }
    
    // Match current_term
    if($content === 'current_term'){
        
        if($post['type'] === 'term')
            $value = $post['id'];
        
        return $value;
        
    }
    
    // Match current_term_parent
    elseif($content === 'current_term_parent'){
        
        if($post['type'] === 'term')
            $value = get_term_field('parent', $post['id']);
        
        return $value;
        
    }
    
    // Match current_user
    elseif($content === 'current_user'){
        
        return get_current_user_id();
        
    }
    
    // Match generate_password
    elseif($content === 'generate_password'){
        
        return wp_generate_password(8, false);
        
    }
    
    // Deprecated Match {current:post|term|user|author|form:field}
    elseif(strpos($content, '{current:') !== false){
        
        if(preg_match_all('/{current:(.*?)}/', $content, $matches)){
            
            foreach($matches[1] as $i => $name){
                
                if($name === 'form' || stripos($name, 'form:') === 0){
                    
                    _deprecated_function('ACF Extended - Dynamic Forms: "{current:' . $name . '}" template tag', '0.8.7.5', "the new {" . $name . "} Template Tag (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms/form-cheatsheet)");
                    
                }else{
                    
                    _deprecated_function('ACF Extended - Dynamic Forms: "{current:' . $name . '}" template tag', '0.8.8', "the new {" . $name . "} Template Tag (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms/form-cheatsheet)");
                    
                }
                
                $value = acfe_form_map_current_value($name, $post, $form);
                
                $content = str_replace('{current:' . $name . '}', $value, $content);
                
            }
            
        }
        
    }
    
    // Match {post|term|user|author|form:field}
    elseif(stripos($content, '{post') !== false || stripos($content, '{term') !== false || stripos($content, '{user') !== false || stripos($content, '{author') !== false || stripos($content, '{form') !== false){
        
        // Old regex: '/{(form|form:.*?)}/'
        if(preg_match_all('/{(post(:?)(.*?)|(term(:?)(.*?))|(user(:?)(.*?))|(author(:?)(.*?))|(form(:?)(.*?)))}/', $content, $matches)){
            
            foreach($matches[1] as $i => $name){
                
                $value = acfe_form_map_current_value($name, $post, $form);
                
                $content = str_replace('{' . $name . '}', $value, $content);
                
            }
            
        }
        
    }
    
    return $content;
    
}

// Match {query_var:name} {query_var:name:key}
function acfe_form_map_query_var($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{query_var:') === false)
        return $content;
    
    // Match {query_var:name}
    if(preg_match_all('/{query_var:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            if(stripos($name, '-post') !== false || stripos($name, '-term') !== false || stripos($name, '-user') !== false || stripos($name, '-email') !== false){
                
                // global
                global $wp_query;
                
                // check if the query var exists
                if(!isset($wp_query->query_vars) || !isset($wp_query->query_vars[$name])){
                    
                    _deprecated_function('ACF Extended - Dynamic Forms: "{query_var:' . $name . '}" template tag', '0.8.7.5', "the new {action} Template Tag (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms)");
                    
                }
                
            }
            
            $query_var = get_query_var($name);
            
            if(strpos($name, ':') !== false){
                
                $explode = explode(':', $name);
                
                $query_var = get_query_var($explode[0]);
                
                if(is_array($query_var) && isset($query_var[$explode[1]])){
                    
                    $query_var = $query_var[$explode[1]];
                    
                    if(is_array($query_var) && isset($query_var[$explode[2]])){
                        
                        $query_var = $query_var[$explode[2]];
                        
                    }
                    
                }
                
            }
            
            $content = str_replace('{query_var:' . $name . '}', $query_var, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {action:field}
function acfe_form_map_action($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{action:') === false)
        return $content;
    
    // Match {action:field}
    if(preg_match_all('/{action:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            $value = false;
            $last_action = get_query_var('acfe_form_actions', array());
            
            if(is_array($last_action) && !empty($last_action)){
                
                if(isset($last_action[$name])){
                    
                    $value = $last_action[$name];
                    
                }
                
                if(strpos($name, ':') !== false){
                    
                    $explode = explode(':', $name);
                    
                    if(isset($last_action[$explode[0]]) && is_array($last_action[$explode[0]]) && isset($last_action[$explode[0]][$explode[1]])){
                        
                        $value = $last_action[$explode[0]][$explode[1]];
                        
                        if(is_array($value) && isset($value[$explode[2]])){
                            
                            $value = $value[$explode[2]];
                            
                        }
                        
                    }
                    
                }
                
            }
            
            $content = str_replace('{action:' . $name . '}', $value, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {request:name} {request:name:key}
function acfe_form_map_request($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{request:') === false)
        return $content;
    
    // Match {request:name}
    if(preg_match_all('/{request:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            $request = false;
            
            if(isset($_REQUEST[$name]))
                $request = $_REQUEST[$name];
            
            if(strpos($name, ':') !== false){
                
                $explode = explode(':', $name);
                
                if(isset($_REQUEST[$explode[0]]))
                    $request = $_REQUEST[$explode[0]];
                
                if(is_array($request) && isset($request[$explode[1]])){
                    
                    $request = $request[$explode[1]];
                    
                }
                
            }
            
            $content = str_replace('{request:' . $name . '}', $request, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {get_field:name} {get_field:name:123}
function acfe_form_map_get_field($content, $post_id = 0){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{get_field:') === false)
        return $content;
    
    // Match {get_field:name}
    if(preg_match_all('/{get_field:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            if(strpos($name, ':') === false){
                
                $get_field = get_field($name, $post_id);
                
            }else{
                
                $explode = explode(':', $name);
                
                // Field
                $field = $explode[0];
                
                // ID
                $id = $explode[1];
                
                if($id === 'current')
                    $id = $post_id;
                
                // Format
                $format = true;
                
                if(acf_maybe_get($explode, 2) === 'false')
                    $format = false;
                
                $get_field = get_field($field, $id, $format);
                
            }
            
            $content = str_replace('{get_field:' . $name . '}', $get_field, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {get_option:name} {get_option:name:key}
function acfe_form_map_get_option($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{get_option:') === false)
        return $content;
    
    // Match {get_option:name}
    if(preg_match_all('/{get_option:(.*?)}/', $content, $matches)){
        
        foreach($matches[1] as $i => $name){
            
            if(strpos($name, ':') === false){
                
                $get_option = get_option($name);
                
            }else{
                
                $explode = explode(':', $name);
                
                $get_option = get_option($explode[0]);
                
                if(is_array($get_option) && isset($get_option[$explode[1]])){
                    
                    $get_option = $get_option[$explode[1]];
                    
                }
                
            }
            
            $content = str_replace('{get_option:' . $name . '}', $get_option, $content);
            
        }
        
    }
    
    return $content;
    
}

// Match {field:name} {field:key}
function acfe_form_map_field($content){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{field:') === false)
        return $content;
    
    if(preg_match_all('/{field:(.*?)}/', $content, $matches)){
        
        // Setup data
        $data = array();
        
        $store = acf_get_store('acfe/form');
        
        if($store->has('data')){
            
            $data = $store->get('data');
            
        }
        
        foreach($matches[1] as $i => $field_key){
            
            $key = $field_key;
            $format = true;
            
            if(strpos($key, ':') !== false){
                
                $explode = explode(':', $key);
                
                $key = $explode[0]; // field_123abc
                
                if($explode[1] === 'false')
                    $format = false;
                
            }
            
            if(!empty($data)){
                
                foreach($data as $field){
                    
                    if($field['name'] !== $key && $field['key'] !== $key)
                        continue;
                    
                    // Value
                    $value = $field['value'];
                    
                    if($format)
                        $value = acfe_form_format_value($field['value'], $field['field']);
                    
                    // Replace
                    $content = str_replace('{field:' . $field_key . '}', $value, $content);
                    
                    break;
                    
                }
                
            }
            
            // Fallback (clean)
            $content = str_replace('{field:' . $field_key . '}', '', $content);
            
        }
        
    }
    
    // Return
    return $content;
    
}

// Match {fields}
function acfe_form_map_fields($content, $post_id, $form){
    
    if(empty($content) || !is_string($content))
        return $content;
    
    if(strpos($content, '{fields}') === false)
        return $content;
    
    // Match {fields}
    if(preg_match('/{fields}/', $content, $matches)){
        
        // Setup data
        $data = array();
        
        $store = acf_get_store('acfe/form');
        
        if($store->has('data')){
            
            $data = $store->get('data');
            
        }
        
        $content_html = '';
        
        if(!empty($data)){
            
            foreach($data as $field){
                
                // Exclude recaptcha
                if($field['field']['type'] === 'acfe_recaptcha') continue;
                
                // Label
                $label = !empty($field['label']) ? $field['label'] : $field['name'];
                
                // Value
                $value = acfe_form_format_value($field['value'], $field['field']);
                
                // Add
                $content_html .= $label . ': ' . $value . "<br/>\n";
                
            }
            
        }
        
        $content_html = apply_filters("acfe/form/template_tag/fields",                      $content_html, $data, $form);
        $content_html = apply_filters("acfe/form/template_tag/fields/form={$form['name']}", $content_html, $data, $form);
        
        // Replace
        $content = str_replace('{fields}', $content_html, $content);
        
    }
    
    // Return
    return $content;
    
}

function acfe_form_map_current_value($name, $post, $form = false){
    
    $value = false;
    
    // post
    if($name === 'post' && $post['type'] === 'post'){
        
        $value = $post['id'];
        
    }
    
    // term
    elseif($name === 'term' && $post['type'] === 'term'){
        
        $value = $post['id'];
        
    }
    
    // user
    elseif($name === 'user'){
        
        $value = get_current_user_id();
        
    }
    
    // author
    elseif($name === 'author' && $post['type'] === 'post'){
        
        $value = get_post_field('post_author', $post['id']);
        
    }
    
    // form
    elseif($name === 'form'){
        
        $value = acf_maybe_get($form, 'ID');
        
    }
    
    // post|term|user|author|form:field
    elseif(strpos($name, ':') !== false){
        
        $explode = explode(':', $name);
        
        $type = $explode[0]; // post|term|user|author|form
        $field = $explode[1]; // id|post_parent|post_title|field
        
        // post:field
        if($type === 'post' && $post['type'] === 'post'){
            
            // post:id
            if(strtolower($field) === 'id' || strtolower($field) === 'post_id'){
                
                $value = $post['id'];
                
            }
            
            // post:permalink
            elseif(strtolower($field) === 'permalink'){
                
                $value = get_permalink($post['id']);
                
            }
            
            // post:admin url
            elseif(strtolower($field) === 'admin_url'){
                
                $value = admin_url('post.php?post=' . $post['id'] . '&action=edit');
                
            }
            
            // post:post_author_data
            elseif(strtolower($field) === 'post_author_data'){
                
                // Retrieve Post Author data
                $post_author = get_post_field('post_author', $post['id']);
                $user_object = get_user_by('ID', $post_author);
                
                if(isset($user_object->data)){
                    
                    $user = json_decode(json_encode($user_object->data), true);
                    
                    $user_object_meta = get_user_meta($user['ID']);
                    
                    $user_meta = array();
                    
                    foreach($user_object_meta as $k => $v){
                        
                        if(!isset($v[0]))
                            continue;
                        
                        $user_meta[$k] = $v[0];
                        
                    }
                    
                    $user_array = array_merge($user, $user_meta);
                    
                    $user_array['permalink'] = get_author_posts_url($post_author);
                    $user_array['admin_url'] = admin_url('user-edit.php?user_id=' . $post_author);
                    
                    $post_author_data = $user_array;
                    
                    // post:post_author_data:id
                    if(isset($explode[2])){
                        
                        $field_author = $explode[2];
                        
                        $value = $post_author_data[$field_author];
                        
                    }
                    
                    
                }
                
            }
            
            // post:field
            else{
                
                $value = get_post_field($field, $post['id']);
                
            }
            
        }
        
        // term:field
        elseif($type === 'term' && $post['type'] === 'term'){
            
            // term:id
            if(strtolower($field) === 'id' || strtolower($field) === 'term_id'){
                
                $value = $post['id'];
                
            }
            
            // term:permalink
            elseif(strtolower($field) === 'permalink'){
                
                $value = get_term_link($post['id']);
                
            }
            
            // term:admin url
            elseif(strtolower($field) === 'admin_url'){
                
                $value = admin_url('term.php?tag_ID=' . $post['id']);
                
            }
            
            // term:field
            else{
                
                $value = get_term_field($field, $post['id']);
                
            }
            
        }
        
        // user:field
        elseif($type === 'user'){
            
            if(is_user_logged_in()){
                
                $user_id = get_current_user_id();
                
                // user:id
                if(strtolower($field) === 'id' || strtolower($field) === 'user_id'){
                    
                    $value = $user_id;
                    
                }
                
                // user:permalink
                elseif(strtolower($field) === 'permalink'){
                    
                    $value = get_author_posts_url($user_id);
                    
                }
                
                // user:admin url
                elseif(strtolower($field) === 'admin_url'){
                    
                    $value = admin_url('user-edit.php?user_id=' . $user_id);
                    
                }
                
                // user:field
                else{
                    
                    $value = false;
                    
                    $user_object = get_user_by('ID', $user_id);
                    
                    if(isset($user_object->data)){
                        
                        // return array
                        $user = json_decode(json_encode($user_object->data), true);
                        
                        $user_object_meta = get_user_meta($user_id);
                        
                        $user_meta = array();
                        
                        foreach($user_object_meta as $k => $v){
                            
                            if(!isset($v[0]))
                                continue;
                            
                            $user_meta[$k] = $v[0];
                            
                        }
                        
                        $user = array_merge($user, $user_meta);
                        
                        $value = acf_maybe_get($user, $field);
                        
                    }
                    
                }
                
            }
            
        }
        
        // author:field
        elseif($type === 'author' && $post['type'] === 'post'){
            
            $user_id = get_post_field('post_author', $post['id']);
            
            if($user_id){
                
                // author:id
                if(strtolower($field) === 'id' || strtolower($field) === 'user_id'){
                    
                    $value = $user_id;
                    
                }
                
                // author:permalink
                elseif(strtolower($field) === 'permalink'){
                    
                    $value = get_author_posts_url($user_id);
                    
                }
                
                // author:admin url
                elseif(strtolower($field) === 'admin_url'){
                    
                    $value = admin_url('user-edit.php?user_id=' . $user_id);
                    
                }
                
                // author:field
                else{
                    
                    $value = false;
                    
                    $user_object = get_user_by('ID', $user_id);
                    
                    if(isset($user_object->data)){
                        
                        // return array
                        $user = json_decode(json_encode($user_object->data), true);
                        
                        $user_object_meta = get_user_meta($user_id);
                        
                        $user_meta = array();
                        
                        foreach($user_object_meta as $k => $v){
                            
                            if(!isset($v[0]))
                                continue;
                            
                            $user_meta[$k] = $v[0];
                            
                        }
                        
                        $user = array_merge($user, $user_meta);
                        
                        $value = acf_maybe_get($user, $field);
                        
                    }
                    
                }
                
            }
            
        }
        
        // form:field
        elseif($type === 'form'){
            
            // form:id
            if(strtolower($field) === 'id' || strtolower($field) === 'form_id'){
                
                $value = acf_maybe_get($form, 'ID');
                
            }
            
            // form:name
            elseif(strtolower($field) === 'name' || strtolower($field) === 'form_name'){
                
                $value = acf_maybe_get($form, 'name');
                
            }
            
            // form:title
            elseif(strtolower($field) === 'title' || strtolower($field) === 'form_title'){
                
                $value = acf_maybe_get($form, 'title');
                
            }
            
            // form:field
            else{
                
                $value = acf_maybe_get($form, $field);
                
            }
            
        }
        
        
    }
    
    return $value;
    
}

function acfe_form_map_vs_fields($map, $fields, $post_id = 0, $form = array()){
    
    $return = array();
    
    foreach($map as $mkey => $mval){
        
        if(empty($mval))
            continue;
        
        $return[$mkey] = acfe_form_map_field_value($mval, $post_id, $form);
        
    }
    
    foreach($fields as $fkey => $fvalue){
        
        if(isset($return[$fkey]))
            continue;
        
        $return[$fkey] = acfe_form_map_field_value($fvalue, $post_id, $form);
        
    }
    
    return $return;
    
}