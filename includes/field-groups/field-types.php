<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_Field_Group_Field_Types')):

class ACFE_Field_Group_Field_Types{
    
    var $categories = array();
    
    /**
     * construct
     */
    function __construct(){
        
        $this->categories = array('WordPress');
        
        if(defined('ACFE_PRO') && ACFE_PRO){
            array_unshift($this->categories, 'ACF');
            array_unshift($this->categories, 'E-Commerce');
        }
        
        add_filter('acf/get_field_types',            array($this, 'get_field_types'));
        add_filter('acf/get_field_types',            array($this, 'get_field_types_after'), 99);
        add_filter('acf/localized_field_categories', array($this, 'localized_field_categories'));
    }
    
    
    /**
     * get_field_types
     *
     * Reorder groups in "Add Field" dropdown and sort fields by name ASC
     *
     * @param $groups
     *
     * @return array|mixed
     */
    function get_field_types($groups){
        
        // sort fields by name ASC
        foreach($groups as $group => &$fields){
            asort($fields);
        }
        
        // before acf 6.1 category was 'jQuery'
        $advanced = acfe_is_acf('6.1') ? __('Advanced', 'acf') : 'jQuery';
        
        // extract custom groups
        $custom_groups = acfe_extract($groups, $this->categories);
        
        // loop custom groups
        foreach($custom_groups as $custom_group => $custom_fields){
            
            // insert custom groups after 'advanced'
            $groups = acfe_after($groups, $advanced, array($custom_group => $custom_fields));
            
        }
        
        // return groups
        return $groups;
        
    }
    
    
    /**
     * get_field_types_after
     *
     * Remove groups without field types
     *
     * @param $groups
     *
     * @return void
     */
    function get_field_types_after($groups){
        
        // loop groups
        foreach(array_keys($groups) as $group){
            
            $fields = $groups[ $group ];
            
            // remove empty groups
            if(empty($fields)){
                unset($groups[ $group ]);
            }
            
        }
        
        // return groups
        return $groups;
        
    }
    
    
    /**
     * localized_field_categories
     *
     * "Browse Fields" categories modal in ACF Field Group
     * No need to check for ACF version since this filter is only available in ACF 6.1+
     *
     * @since ACF 6.1
     *
     * @param $categories
     *
     * @return array
     */
    function localized_field_categories($categories){
        
        // loop categories
        foreach($this->categories as $category){
            $categories = acfe_after($categories, 'advanced', array($category => $category));
        }
        
        // remvove useless pro category
        unset($categories['pro']);
        
        // return
        return $categories;
        
    }
    
}

acf_new_instance('ACFE_Field_Group_Field_Types');

endif;