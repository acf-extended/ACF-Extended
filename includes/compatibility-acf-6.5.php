<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_compatibility_acf_65')):

class acfe_compatibility_acf_65{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acf/validate_field/type=flexible_content', array($this, 'validate_field'), 20);
        add_filter('acf/update_field/type=flexible_content',   array($this, 'update_field'), 20);
        add_filter('acf/load_value/type=flexible_content',     array($this, 'load_value_assign_legacy_value'), 10, 3);
        add_filter('acf/load_value/type=flexible_content',     array($this, 'load_value_compat_toggle'), 15, 3);
        add_filter('acf/update_value/type=flexible_content',   array($this, 'update_value_cleanup_legacy'), 15, 3);
        
        add_filter('acfe/flexible/layout_disabled',            array($this, 'layout_disabled'), 15, 3);
        add_filter('acfe/flexible/layout_renamed',             array($this, 'layout_renamed'), 15, 3);
        
    }
    
    
    /**
     * validate_field
     *
     * @param $field
     *
     * @return mixed
     */
    function validate_field($field){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $field;
        }
        
        // check if setting is enabled
        if(!acfe_get_setting('compatibility/legacy_title_toggle')){
            return $field;
        }
        
        // get acfe flexible content actions
        $actions = acf_maybe_get($field, 'acfe_flexible_add_actions');
        $actions = acf_get_array($actions); // cast as array
        
        // enable legacy title + toggle
        if(!in_array('title', $actions))  $actions[] = 'title';
        if(!in_array('toggle', $actions)) $actions[] = 'toggle';
        
        // assign
        $field['acfe_flexible_add_actions'] = $actions;
        
        // return
        return $field;
        
    }
    
    
    /**
     * update_field
     *
     * This logic ensure to pass the legacy title + toggle settings
     * when updating an already existing field group with a flexible content which used to have these settings
     *
     * @param $field
     *
     * @return void
     */
    function update_field($field){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $field;
        }
        
        // get original field
        $original_field = acf_get_field($field['key']);
        if(!$original_field){
            return $field;
        }
        
        // get original acfe actions
        $original_actions = acf_maybe_get($original_field, 'acfe_flexible_add_actions');
        $original_actions = acf_get_array($original_actions); // cast as array
        
        // no original actions
        if(empty($original_actions)){
            return $field;
        }
        
        // vars
        $pass_actions = array();
        
        // pass legacy title + toggle if they were already set
        if(in_array('title', $original_actions))  $pass_actions[] = 'title';
        if(in_array('toggle', $original_actions)) $pass_actions[] = 'toggle';
        
        // nothing to pass
        if(empty($pass_actions)){
            return $field;
        }
        
        // get new acfe actions
        $new_actions = acf_maybe_get($field, 'acfe_flexible_add_actions');
        $new_actions = acf_get_array($new_actions); // cast as array
        
        // loop on pass actions (title + toggle)
        foreach($pass_actions as $pass_action){
            
            // check if title/toggle doesn't already exist
            if(!in_array($pass_action, $new_actions)){
                $new_actions[] = $pass_action;
            }
        }
        
        // set new actions
        $field['acfe_flexible_add_actions'] = $new_actions;
        
        // return field
        return $field;
    
    }
    
    
    /**
     * update_value_cleanup_legacy
     *
     * Cleanup legacy ACFE metadata on save
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return void
     */
    function update_value_cleanup_legacy($value, $post_id, $field){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $value;
        }
        
        // check value
        if(empty($value)){
            return $value;
        }
        
        // check inline title + toggle
        if(!$this->has_title_action($field) && !$this->has_toggle_action($field)){
            return $value;
        }
        
        // loop through rows
        foreach(array_keys($value) as $i){
            
            // cleanup legacy ACFE title
            if($this->has_title_action($field)){
                acf_delete_metadata($post_id, "{$field['name']}_{$i}_acfe_flexible_layout_title");
                acf_delete_metadata($post_id, "_{$field['name']}_{$i}_acfe_flexible_layout_title");
            }
            
            // cleanup legacy ACFE toggle
            if($this->has_toggle_action($field)){
                acf_delete_metadata($post_id, "{$field['name']}_{$i}_acfe_flexible_toggle");
                acf_delete_metadata($post_id, "_{$field['name']}_{$i}_acfe_flexible_toggle");
            }
            
        }
        
        // return value
        return $value;
        
    }
    
    
    /**
     * load_value_assign_legacy_value
     *
     * Assign legacy ACFE inline title + toggle values to be used later in layout_renamed + layout_disabled
     *
     * acf/load_value/type=flexible_content
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|mixed
     */
    function load_value_assign_legacy_value($value, $post_id, $field){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $value;
        }
        
        // check value
        if(empty($value)){
            return $value;
        }
        
        // check layouts
        if(empty($field['layouts'])){
            return $value;
        }
        
        // check inline title + toggle
        if(!$this->has_title_action($field) && !$this->has_toggle_action($field)){
            return $value;
        }
        
        // sanitize
        $value = acf_get_array($value);
        
        // loop value
        foreach(array_keys($value) as $i){
            
            // assign legacy ACFE inline title value
            // this later used in get_layout_renamed() to fallback to this value
            if($this->has_title_action($field)){
                
                // get layout
                $layout = $this->get_layout($value[ $i ]['acf_fc_layout'], $field);
                
                // get legacy ACFE title
                $_acfe_flexible_layout_title = acf_get_metadata($post_id, $field['name'] . '_' . $i . '_acfe_flexible_layout_title');
                
                // check legacy ACFE inline title is different from layout label
                // inline title always saved the title, event when default
                if($_acfe_flexible_layout_title !== $layout['label']){
                    $value[ $i ]['_acfe_flexible_layout_title'] = $_acfe_flexible_layout_title;
                }
                
            }
            
            // assign legacy ACFE toggle value
            // this later used in get_layout_disabled() to fallback to this value
            if($this->has_toggle_action($field)){
                $value[ $i ]['_acfe_flexible_toggle'] = acf_get_metadata($post_id, $field['name'] . '_' . $i . '_acfe_flexible_toggle');
            }
            
        }
        
        return $value;
        
    }
    
    
    /**
     * load_value_compat_toggle
     *
     * acf/load_value/type=flexible_content
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|mixed
     */
    function load_value_compat_toggle($value, $post_id, $field){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $value;
        }
        
        // check setting
        if(!$this->has_toggle_action($field)){
            return $value;
        }
        
        // bail early in admin
        if(is_admin() && !wp_doing_ajax()){
            return $value;
        }
        
        // check value
        if(empty($value)){
            return $value;
        }
        
        // check layouts
        if(empty($field['layouts'])){
            return $value;
        }
        
        // bail early in preview
        if(acf_maybe_get_POST('action') === 'acfe/flexible/layout_preview'){
            return $value;
        }
        
        // vars
        $value = acf_get_array($value);
        
        // loop value
        foreach($value as $k => $row){
            
            // check toggle
            if(empty($row['_acfe_flexible_toggle'])){
                continue;
            }
            
            // vars
            $layout = $this->get_layout($row['acf_fc_layout'], $field);
            $name = $field['_name'];
            $key = $field['key'];
            $l_name = $layout['name'];
            
            // filters
            $hide = true;
            $hide = apply_filters("acfe/flexible/toggle_hide",                               $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/name={$name}",                  $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/key={$key}",                    $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/layout={$l_name}",              $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/name={$name}&layout={$l_name}", $hide, $row, $layout, $field);
            $hide = apply_filters("acfe/flexible/toggle_hide/key={$key}&layout={$l_name}",   $hide, $row, $layout, $field);
            
            // should hide
            if($hide){
                unset($value[ $k ]);
            }
            
        }
        
        // reorder keys
        $value = array_values($value);
        
        // return value
        return $value;
        
    }
    
    
    /**
     * layout_disabled
     *
     * Pre ACF 6.5 back-compatibility
     * Check if new layout_meta was ever saved
     * If not, try to get legacy ACFE disabled layout value
     *
     * @param $disabled
     * @param $field
     * @param $i
     *
     * @return bool|mixed
     */
    function layout_disabled($disabled, $field, $i){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $disabled;
        }
        
        // disabled already set
        if(!empty($disabled)){
            return $disabled;
        }
        
        // check legacy ACFE setting
        if(!$this->has_toggle_action($field)){
            return $disabled;
        }
        
        // get legacy ACFE toggle value
        // note: $i can be acfcloneindex here
        if(!empty($field['value'][ $i ]['_acfe_flexible_toggle'])){
            $disabled = (bool) $field['value'][ $i ]['_acfe_flexible_toggle'];
        }
        
        return $disabled;
        
    }
    
    
    /**
     * layout_renamed
     *
     * Pre ACF 6.5 back-compatibility
     * Check if new layout_meta was ever saved
     * If not, try to get legacy ACFE renamed layout value
     *
     * @param $renamed
     * @param $field
     * @param $i
     *
     * @return mixed
     */
    function layout_renamed($renamed, $field, $i){
        
        // pre-ACF 6.5+: bail early
        if(!acfe_is_acf_65()){
            return $renamed;
        }
        
        // disabled already set
        if(!empty($renamed)){
            return $renamed;
        }
        
        // check legacy ACFE setting
        if(!$this->has_title_action($field)){
            return $renamed;
        }
        
        // get legacy ACFE title value
        // note: $i can be acfcloneindex here
        if(!empty($field['value'][ $i ]['_acfe_flexible_layout_title'])){
            $renamed = $field['value'][ $i ]['_acfe_flexible_layout_title'];
        }
        
        return $renamed;
        
    }
    
    
    /**
     * has_legacy_title
     *
     * @param $field
     *
     * @return bool
     */
    function has_title_action($field){
        return in_array('title', $field['acfe_flexible_add_actions']);
    }
    
    
    /**
     * has_legacy_toggle
     *
     * @param $field
     *
     * @return bool
     */
    function has_toggle_action($field){
        return in_array('toggle', $field['acfe_flexible_add_actions']);
    }
    
    
    /**
     * get_layout
     *
     * @param $layout_name
     * @param $field
     *
     * @return mixed
     */
    function get_layout($layout_name, $field){
        return acf_get_field_type('flexible_content')->get_layout($layout_name, $field);
    }
    
    
    /**
     * get_layout_meta
     *
     * Proxy to ACF get_layout_meta method
     *
     * @param $field
     * @param $post_id
     *
     * @return mixed
     */
    function get_layout_meta($field, $post_id = false){
        $post_id = $post_id === false ? acf_get_field_type('flexible_content')->post_id : $post_id;
        return acf_get_field_type('flexible_content')->get_layout_meta($post_id, $field);
    }
    
    
    /**
     * has_layout_meta
     *
     * @param $field
     * @param $post_id
     *
     * @return bool
     */
    function has_layout_meta($field, $post_id = false){
        $layout_meta = $this->get_layout_meta($field, $post_id);
        return !empty($layout_meta);
    }
    
}

acf_new_instance('acfe_compatibility_acf_65');

endif;