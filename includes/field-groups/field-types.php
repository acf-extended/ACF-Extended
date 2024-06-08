<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_Field_Group_Field_Types')):

class ACFE_Field_Group_Field_Types{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acf/get_field_types',            array($this, 'get_field_types'));
        add_filter('acf/localized_field_categories', array($this, 'localized_field_categories'));
    }
    
    
    /**
     * get_field_types
     *
     * @param $groups
     *
     * @return array|mixed
     */
    function get_field_types($groups){
        
        // sort fields
        foreach($groups as $group => &$fields){
            asort($fields);
        }
        
        // before acf 6.1 category was 'jQuery'
        $category = acfe_is_acf_61() ? 'Advanced' : 'jQuery';
        
        if(isset($groups['E-Commerce'])){
            $groups = acfe_array_insert_after($groups, $category, 'E-Commerce', $groups['E-Commerce']);
        }
        
        if(isset($groups['ACF'])){
            $groups = acfe_array_insert_after($groups, $category, 'ACF', $groups['ACF']);
        }
        
        if(isset($groups['WordPress'])){
            $groups = acfe_array_insert_after($groups, $category, 'WordPress', $groups['WordPress']);
        }
        
        return $groups;
        
    }
    
    
    /**
     * localized_field_categories
     *
     * Added in ACF 6.1
     *
     * @param $categories_i18n
     *
     * @return array
     */
    function localized_field_categories($categories_i18n){
    
        $categories_i18n = acfe_array_insert_after($categories_i18n, 'advanced', 'E-Commerce', 'E-Commerce');
        $categories_i18n = acfe_array_insert_after($categories_i18n, 'advanced', 'ACF',        'ACF');
        $categories_i18n = acfe_array_insert_after($categories_i18n, 'advanced', 'WordPress',  'WordPress');
        
        unset($categories_i18n['pro']);
        
        return $categories_i18n;
        
    }
    
}

acf_new_instance('ACFE_Field_Group_Field_Types');

endif;