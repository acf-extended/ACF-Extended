<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_bidirectional')):

class acfe_bidirectional{
    
    var $allowed_types = array('relationship', 'post_object', 'user', 'taxonomy');
    
    function __construct(){
        
        foreach($this->allowed_types as $allowed_type){
    
            add_action('acf/render_field_settings/type=' . $allowed_type,       array($this, 'field_settings_render'));
            add_filter('acf/update_field/type=' . $allowed_type,                array($this, 'field_settings_update'));
            add_action('acf/delete_field/type=' . $allowed_type,                array($this, 'field_settings_delete'));
    
            add_filter('acf/update_value/type=' . $allowed_type,                array($this, 'update_value'), 11, 3);
            
        }
    
        add_action('wp_ajax_acfe/fields_settings/bidirectional/query',          array($this, 'ajax_query'));
        add_action('wp_ajax_nopriv_acfe/fields_settings/bidirectional/query',   array($this, 'ajax_query'));
    
        add_filter('acf/prepare_field/name=acfe_bidirectional_related',         array($this, 'field_settings_default_value'));
        
    }
    
    /*
     * Field Settings
     */
    function field_settings_render($field){
        
        // Settings
        acf_render_field_setting($field, array(
            'label'             => __('Bidirectional'),
            'key'               => 'acfe_bidirectional',
            'name'              => 'acfe_bidirectional',
            'instructions'      => __('Set the field as bidirectional'),
            'type'              => 'group',
            'required'          => false,
            'conditional_logic' => false,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'layout' => 'block',
            'sub_fields' => array(
                array(
                    'label'             => false,
                    'key'               => 'acfe_bidirectional_enabled',
                    'name'              => 'acfe_bidirectional_enabled',
                    'type'              => 'true_false',
                    'instructions'      => '',
                    'required'          => false,
                    'wrapper' => array(
                        'width' => '15',
                        'class' => 'acfe_width_auto',
                        'id'    => '',
                    ),
                    'message'           => '',
                    'default_value'     => false,
                    'ui'                => true,
                    'ui_on_text'        => '',
                    'ui_off_text'       => '',
                    'conditional_logic' => false,
                ),
                array(
                    'label'             => false,
                    'key'               => 'acfe_bidirectional_related',
                    'name'              => 'acfe_bidirectional_related',
                    'type'              => 'select',
                    'instructions'      => '',
                    'required'          => false,
                    'wrapper' => array(
                        'width' => 50,
                        'class' => '',
                        'id'    => '',
                    ),
                    'choices'       => array(),
                    'default_value' => array(),
                    'allow_null'    => 1,
                    'multiple'      => 1,
                    'ui'            => 1,
                    'ajax'          => 1,
                    'ajax_action'   => 'acfe/fields_settings/bidirectional/query',
                    'return_format' => 'value',
                    'placeholder'   => '',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_bidirectional_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            ),
                        ),
                    ),
                ),
            ),
        ));
        
    }
    
    /*
     * Field Settings: Ajax Query
     */
    function ajax_query(){
    
        // validate
        if (!acf_verify_ajax()){
            die();
        }
    
        $options = acf_parse_args($_POST, array(
            'post_id'   => 0,
            's'         => '',
            'field_key' => '',
            'paged'     => 1
        ));
    
        $response = $this->ajax_results($options);
    
        acf_send_ajax_results($response);
    
    }
    
    /*
     * Field Settings: Ajax Results
     */
    function ajax_results($options = array()){
        
        // Disable Filters
        acf_disable_filters();
    
        // Get field groups
        $r_field_groups = acf_get_field_groups();
        
        if(empty($r_field_groups))
            return false;
    
        // Vars
        $hidden = acfe_get_setting('reserved_field_groups', array());
        $choices = array();
        
        foreach($r_field_groups as $r_field_group){
        
            // Bypass ACFE native groups
            if(in_array($r_field_group['key'], $hidden))
                continue;
        
            // Get related fields
            $r_fields = acf_get_fields($r_field_group['key']);
            if(empty($r_fields))
                continue;
        
            // Filter & find possible related fields
            foreach($r_fields as $r_field){
            
                $this->get_related_settings($r_field, $r_field_group, $choices);
            
            }
        
        }
    
        // vars
        $results = array();
        $s = null;
    
        if(!empty($choices)){
        
            // search
            if($options['s'] !== ''){
            
                // strip slashes (search may be integer)
                $s = strval($options['s']);
                $s = wp_unslash($s);
            
            }
        
            foreach($choices as $field_group_title => $childs){
            
                $field_group_title = strval($field_group_title);
            
                $childrens = array();
                foreach($childs as $child_key => $child_label){
                
                    $child_label = strval($child_label);
                
                    // if searching, but doesn't exist
                    if(is_string($s) && stripos($child_label, $s) === false && stripos($field_group_title, $s) === false)
                        continue(2);
                
                    $childrens[] = array(
                        'id' => $child_key,
                        'text' => $child_label,
                    );
                
                }
            
                $results[] = array(
                    'text' => $field_group_title,
                    'children' => $childrens
                );
            
            }
        
        }
    
        return array(
            'results' => $results
        );
        
    }
    
    /*
     * Field Setting: Get Related Settings
     */
    function get_related_settings($r_field, $r_field_group, &$choices){
    
        if(in_array($r_field['type'], array('repeater', 'flexible_content')))
            return;
    
        // Recursive search for sub_fields (groups & clones)
        if(isset($r_field['sub_fields']) && !empty($r_field['sub_fields'])){
        
            foreach($r_field['sub_fields'] as $r_sub_field){
            
                // Recursive call
                $this->get_related_settings($r_sub_field, $r_field_group, $choices);
            
            }
        
            return;
        
        }
    
        // Allow only specific fields
        if(!in_array($r_field['type'], $this->allowed_types))
            return false;
    
        $choices[$r_field_group['title']][$r_field['key']] = (!empty($r_field['label']) ? $r_field['label'] : $r_field['name']) . ' (' . $r_field['key'] . ')';
        
    }
    
    /*
     * Field Setting: Default Value
     */
    function field_settings_default_value($field){
        
        if(!isset($field['value']) || empty($field['value']))
            return $field;
        
        $values = acf_get_array($field['value']);
        $r_fields = array();
        $r_field_update = false;
        
        foreach($values as $i => $value){
            
            $r_field = acf_get_field($value);
            
            if($r_field){
                
                $r_fields[] = $r_field;
                
            }else{
                
                // Unset
                unset($values[$i]);
                
                $r_field_update = true;
                
            }
            
        }
        
        if($r_field_update){
            
            $field['value'] = $values;
            
            acf_update_field($field);
            
        }
        
        foreach($r_fields as $r_field){
            
            $field['choices'][$r_field['key']] = (!empty($r_field['label']) ? $r_field['label'] : $r_field['name']) . ' (' . $r_field['key'] . ')';
            
        }
        
        return $field;
        
    }
    
    /*
     * Field Setting Update
     */
    function field_settings_update($field){
        
        $bypass = acf_is_filter_enabled('acfe/bidirectional_setting');
        if($bypass)
            return $field;
        
        // Previous setting values
        $_field = acf_get_field($field['key']);
        
        // Turning off - Remove related field
        if($this->has_field_bidirectional($_field) && !$this->has_field_bidirectional($field)){
            
            // Get related bidirectional related
            $r_fields = acf_get_array($_field['acfe_bidirectional']['acfe_bidirectional_related']);
            
            foreach($r_fields as $r_field_key){
                
                $r_field = acf_get_field($r_field_key);
                
                if(!$this->has_field_bidirectional($r_field))
                    continue;
                
                $r_field_related = acf_get_array($r_field['acfe_bidirectional']['acfe_bidirectional_related']);
                
                if(in_array($field['key'], $r_field_related)){
                    
                    foreach($r_field_related as $i => $r_field_r){
                        
                        if($r_field_r !== $field['key'])
                            continue;
                        
                        unset($r_field_related[$i]);
                        
                    }
                    
                    $r_field['acfe_bidirectional']['acfe_bidirectional_related'] = $r_field_related;
                    
                    if(empty($r_field_related)){
                        
                        $r_field['acfe_bidirectional']['acfe_bidirectional_enabled'] = false;
                        $r_field['acfe_bidirectional']['acfe_bidirectional_related'] = false;
                        
                    }
                    
                    acf_enable_filter('acfe/bidirectional_setting');
                    
                        // Update related bidirectional
                        acf_update_field($r_field);
                    
                    acf_disable_filter('acfe/bidirectional_setting');
                    
                }
                
            }
            
        }
        
        // Turning on (or already on) - Add related field
        elseif(($this->has_field_bidirectional($_field) && $this->has_field_bidirectional($field)) || (!$this->has_field_bidirectional($_field) && $this->has_field_bidirectional($field))){
            
            // Get related bidirectional related
            $r_fields = acf_get_array($field['acfe_bidirectional']['acfe_bidirectional_related']);
            
            foreach($r_fields as $r_field_key){
                
                $r_field = acf_get_field($r_field_key);
                
                // Reset related bidirectional related
                $r_field['acfe_bidirectional']['acfe_bidirectional_enabled'] = true;
    
                $r_field_related = array();
    
                if(isset($r_field['acfe_bidirectional']['acfe_bidirectional_related']))
                    $r_field_related = acf_get_array($r_field['acfe_bidirectional']['acfe_bidirectional_related']);
                
                if(!in_array($field['key'], $r_field_related)){
                    
                    $r_field_related[] = $field['key'];
                    
                    $r_field['acfe_bidirectional']['acfe_bidirectional_related'] = $r_field_related;
                    
                }
                
                acf_enable_filter('acfe/bidirectional_setting');
                
                    // Update related bidirectional
                    acf_update_field($r_field);
                
                acf_disable_filter('acfe/bidirectional_setting');
                
            }
            
        }
        
        // Return
        return $field;
        
    }
    
    /*
     * Field Setting: Delete
     */
    function field_settings_delete($field){
        
        if(!$this->has_field_bidirectional($field))
            return;
        
        // Get related bidirectional related
        $r_fields = acf_get_array($field['acfe_bidirectional']['acfe_bidirectional_related']);
        
        foreach($r_fields as $r_field_key){
            
            $r_field = acf_get_field($r_field_key);
            
            $r_field_related = acf_get_array($r_field['acfe_bidirectional']['acfe_bidirectional_related']);
            
            if(in_array($field['key'], $r_field_related)){
                
                foreach($r_field_related as $i => $r_field_r){
                    
                    if($r_field_r !== $field['key'])
                        continue;
                    
                    unset($r_field_related[$i]);
                    
                }
                
                $r_field['acfe_bidirectional']['acfe_bidirectional_related'] = $r_field_related;
                
                if(empty($r_field_related)){
                    
                    $r_field['acfe_bidirectional']['acfe_bidirectional_enabled'] = false;
                    $r_field['acfe_bidirectional']['acfe_bidirectional_related'] = false;
                    
                }
                
                // Update related bidirectional
                acf_update_field($r_field);
                
            }
            
        }
        
    }
    
    /*
     * Update Value
     */
    function update_value($value, $post_id, $field){
        
        // Bail early if updating a relation
        if(acf_is_filter_enabled('acfe/bidirectional'))
            return $value;
        
        // Bail early if no bidirectional setting
        if(!$this->get_field_bidirectional($field))
            return $value;
    
        // Bail early if local meta
        if(acfe_is_local_post_id($post_id))
            return $value;
        
        // Decode current post_id (ie: user_1)
        $request = acf_decode_post_id($post_id);
        
        // Values
        $old_values = acf_get_array(acf_get_metadata($post_id, $field['name']));
        $new_values = acf_get_array($value);
        
        // Bail early if no difference
        // if($old_values === $new_values)
        //    return $value;
        
        // Values have been removed
        if(!empty($old_values)){
            foreach($old_values as $r_id){
                
                if(in_array($r_id, $new_values))
                    continue;
    
                $this->relationship('remove', $r_id, $field, $request['id']);
                
            }
        }
        
        // Values have been added
        if(!empty($new_values)){
            foreach($new_values as $r_id){
                
                if(in_array($r_id, $old_values))
                    continue;
    
                $this->relationship('add', $r_id, $field, $request['id']);
                
            }
        }
        
        $force_update = false;
        $force_update = apply_filters('acfe/bidirectional/force_update',                          $force_update, $field, $post_id);
        $force_update = apply_filters('acfe/bidirectional/force_update/type=' . $field['type'],   $force_update, $field, $post_id);
        $force_update = apply_filters('acfe/bidirectional/force_update/name=' . $field['name'],   $force_update, $field, $post_id);
        $force_update = apply_filters('acfe/bidirectional/force_update/key=' . $field['key'],     $force_update, $field, $post_id);
        
        if($force_update){
            
            // Force new values to be saved
            if(!empty($new_values)){
                foreach($new_values as $r_id){
                    
                    $this->relationship('add', $r_id, $field, $request['id']);
                    
                }
            }
            
        }
        
        return $value;
        
    }
    
    /**
     * Establish Relationship
     *
     * $type: add|remove
     * $r_id: the post_id to add the relationship to
     * $p_field: the parent field
     * $p_id: the relationship to add
     */
    function relationship($type, $r_id, $p_field, $p_value){
        
        // Get Related Field Configuration
        $r_fields = acf_get_array($p_field['acfe_bidirectional']['acfe_bidirectional_related']);
        
        foreach($r_fields as $r_field_key){
            
            $r_field = acf_get_field($r_field_key);
            
            // Get if bidirectional is active
            if(!$this->get_field_bidirectional($r_field))
                continue;
            
            // Get Related Data Type ({post_id}, user_{id} ...)
            $r_mtype = '';
            if($p_field['type'] === 'user')
                $r_mtype = 'user_';
            elseif($p_field['type'] === 'taxonomy')
                $r_mtype = 'term_';
            
            // Get Related Field Ancestors
            $r_field_ancestors = acf_get_field_ancestors($r_field);
            
            // Ancestors - Complexe field (group|clone)
            if(!empty($r_field_ancestors)){
                
                // Get ancestors
                $r_field_ancestors = array_reverse($r_field_ancestors);
                $r_field_ancestors_fields = array_map('acf_get_field', $r_field_ancestors);
                
                // Get top ancestor
                $r_ref_field = $r_field_ancestors_fields[0];
                $r_ref_values = acf_get_array(acf_get_value($r_mtype.$r_id, $r_ref_field));
                
                // Get values
                $r_values = acf_get_array($this->get_value_from_ancestor($r_ref_values, $r_field));
                
                // Unset top ancestor for update (not needed)
                unset($r_field_ancestors_fields[0]);
                
                // Add related field to get
                $r_values_query = array($r_field['key']);
                
                // If > 1 ancestors, return ancestors keys only
                if(!empty($r_field_ancestors_fields)){
                    
                    $r_field_ancestors_keys = array_map(function($field){
                        return $field['key'];
                    }, $r_field_ancestors_fields);
                    
                    // Add ancestors to get
                    $r_values_query = array_merge($r_field_ancestors_keys, $r_values_query);
                    
                }
                
            }
            
            // No Ancestors - Simple field
            else{
                
                // Refence field
                $r_ref_field = $r_field;
                
                // Values
                $r_values = acf_get_array(acf_get_value($r_mtype.$r_id, $r_field));
                
            }
            
            // Convert strings to integers
            $r_values = acf_parse_types($r_values);
            
            // Add Value
            if($type === 'add'){
                
                if(!in_array($p_value, $r_values))
                    $r_values[] = $p_value;
                
            }
            
            // Remove Value
            elseif($type === 'remove'){
                
                $r_new_values = array();
                foreach($r_values as $r_value){
                    
                    if($r_value === $p_value)
                        continue;
                    
                    $r_new_values[] = $r_value;
                    
                }
                
                $r_values = $r_new_values;
                
            }
            
            /*
             * Post Object & User 'Allow Multiple' Disabled
             * Value must not be inside array
             */
            if(($r_ref_field['type'] === 'post_object' || $r_ref_field['type'] === 'user') && empty($r_ref_field['multiple']) && isset($r_values[0])){
                
                // Get latest value
                $r_values = end($r_values);
                
            }
            
            /*
             * Remove potential empty serialized array in meta value 'a:0:{}'
             */
            if(empty($r_values))
                $r_values = false;
            
            /*
             * Construct a value array in case of ancestors. ie:
             * $related_values = Array(
             *     [field_aaa] => Array(
             *         [field_bbb] => Array(
             *             [0] => xxxx
             *         )
             *     )
             * )
             */
            if(!empty($r_field_ancestors)){
                
                for($i = count($r_values_query)-1; $i>=0; $i--){
                    $r_values = array($r_values_query[$i] => $r_values);
                }
                
            }
            
            // Filter acf_update_value (to avoid infinite loop)
            acf_enable_filter('acfe/bidirectional');
            
                // Update Related Field
                acf_update_value($r_values, $r_mtype.$r_id, $r_ref_field);
            
            // Remove acf_update_value filter
            acf_disable_filter('acfe/bidirectional');
            
        }
        
    }
    
    function get_value_from_ancestor($r_ref_values, $r_field){
        
        foreach($r_ref_values as $r_ref_key => $r_ref_value){
            
            if($r_ref_key != $r_field['key']){
                
                if(is_array($r_ref_value))
                    return $this->get_value_from_ancestor($r_ref_value, $r_field);
                
                return false;
                
            }
            
            return $r_ref_value;
            
        }
        
    }
    
    function is_field_bidirectional($field){
        
        return isset($field['acfe_bidirectional']['acfe_bidirectional_enabled']) && !empty($field['acfe_bidirectional']['acfe_bidirectional_enabled']);
        
    }
    
    function has_field_bidirectional($field){
        
        return isset($field['acfe_bidirectional']['acfe_bidirectional_related']) && !empty($field['acfe_bidirectional']['acfe_bidirectional_related']);
        
    }
    
    function get_field_bidirectional($field){
        
        if(!$this->is_field_bidirectional($field) || !$this->has_field_bidirectional($field))
            return false;
        
        return $field['acfe_bidirectional']['acfe_bidirectional_related'];
        
    }
    
}

new acfe_bidirectional();

endif;