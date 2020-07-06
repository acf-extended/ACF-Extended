<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_multilang')):

class acfe_multilang{
    
    var $is_wpml = false;
    var $is_polylang = false;
    var $is_multilang = false;
    
	function __construct(){
        
        // Check setting
        if(!acf_get_setting('acfe/modules/multilang'))
            return;
	    
        // WPML
        if(defined('ICL_SITEPRESS_VERSION')){
    
            $this->is_wpml = true;
            $this->is_multilang = true;
            
            $this->wpml();
            
        }
        
        // PolyLang
        if(defined('POLYLANG_VERSION') && function_exists('pll_default_language')){
    
            $this->is_polylang = true;
            $this->is_multilang = true;
    
            $this->polylang();
            
        }
        
    }
    
    /**
     * WPML
     * https://wpml.org/documentation/support/wpml-coding-api/wpml-hooks-reference/
     */
    function wpml(){
    
        // ACF Options Post ID
        add_filter('acf/validate_post_id', array($this, 'set_options_post_id'), 10, 2);
	    
    }
    
    /**
     * PolyLang
     * https://polylang.pro/doc/filter-reference/
     * https://polylang.pro/doc/developpers-how-to/
     * https://polylang.wordpress.com/documentation/documentation-for-developers/general/
     * https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
     */
    function polylang(){
        
        // Default/Current Language
        $dl = pll_default_language('locale');
        $cl = pll_current_language('locale');
    
        // Update settings
        acf_update_setting('default_language', $dl);
        acf_update_setting('current_language', $cl);
    
        // Localize data
        acf_localize_data(array(
            'language' => $cl
        ));
        
        // ACF Options Post ID
        add_filter('acf/validate_post_id', array($this, 'set_options_post_id'), 99, 2);
	   
    }
    
    function polylang_get_languages($pluck = 'locale'){
        
        require_once ABSPATH . 'wp-admin/includes/translation-install.php';
    
        $languages    = include PLL_SETTINGS_INC . '/languages.php';
        $translations = wp_get_available_translations();
    
        // Keep only languages with existing WP language pack
        // Unless the transient has expired and we don't have an internet connection to refresh it
        if ( ! empty( $translations ) ) {
            $translations['en_US'] = ''; // Languages packs don't include en_US
            $languages = array_intersect_key( $languages, $translations );
        }
    
        /**
         * Filter the list of predefined languages
         *
         * @since 1.7.10
         * @since 2.3 The languages arrays use associative keys instead of numerical keys
         * @see settings/languages.php
         *
         * @param array $languages
         */
        $languages = apply_filters( 'pll_predefined_languages', $languages );
    
        // Keep only languages with all necessary informations
        foreach ( $languages as $k => $lang ) {
            if ( ! isset( $lang['code'], $lang['locale'], $lang['name'], $lang['dir'], $lang['flag'] ) ) {
                unset( $languages[ $k ] );
            }
        }
        
        if($pluck){
    
            $languages = wp_list_pluck($languages, $pluck, true);
            
        }
    
        return $languages;
        
    }
    
    /**
     * ACF Options Post ID
     */
    function set_options_post_id($post_id, $original_post_id){
        
        // ACF already take care of 'options' post ID
        if($original_post_id === 'options' || $original_post_id === 'option')
            return $post_id;
        
        $data = acf_get_post_id_info($post_id);
    
        // Check if Post ID is an option
        if($data['type'] !== 'option')
            return $post_id;
        
        // Check if Post ID is already localized
        if($this->is_post_id_localized($post_id))
            return $post_id;
        
        $dl = acf_get_setting('default_language');
        $cl = acf_get_setting('current_language');
        
        // Add Language
        if($cl && $cl !== $dl){
            
            $post_id .= '_' . $cl;
            
        }
        
        return $post_id;
        
    }
    
    function is_post_id_localized($post_id){
        
        // Polylang
        if($this->is_polylang){
    
            // Check var'_en_US'
            preg_match('/_[a-z]{2}_[A-Z]{2}$/', $post_id, $found_locale);
            
            if(!empty($found_locale)){
                
                // Remove first '_'
                $found_locale = substr($found_locale[0], 1);
                
                // Get Polylang Languages List
                $languages = $this->polylang_get_languages('locale');
                
                // Language Locale found in Post ID
                if(in_array($found_locale, $languages))
                    return true;
                
                
            }
            
            // Check var'_en'
            preg_match('/_[a-z]{2}$/', $post_id, $found_code);
            
            if(!empty($found_code)){
    
                // Remove first '_'
                $found_code = substr($found_code[0], 1);
    
                // Get Polylang Languages List
                $languages = $this->polylang_get_languages('code');
    
                // Language Locale found in Post ID
                if(in_array($found_code, $languages))
                    return true;
            
            }
            
        }
        
        // WPML
        elseif($this->is_wpml){
    
            // Check var'_en'
            preg_match('/_[a-z]{2}$/', $post_id, $found_code);
    
            if(!empty($found_code)){
        
                // Remove first '_'
                $found_code = substr($found_code[0], 1);
        
                // Get WPML Languages List
                $languages = icl_get_languages_codes();
        
                // Language Locale found in Post ID
                if(in_array($found_code, $languages))
                    return true;
        
            }
        
        }
        
        return false;
        
    }
    
}

acf_new_instance('acfe_multilang');

endif;

function acfe_is_multilang(){
    
    return acf_get_instance('acfe_multilang')->is_multilang;
    
}

function acfe_get_multilang(){
    
    $data = array(
        'dl'        => acf_get_setting('default_language'),
        'cl'        => acf_get_setting('current_language'),
        'wpml'      => acf_get_instance('acfe_multilang')->is_wpml,
        'polylang'  => acf_get_instance('acfe_multilang')->is_polylang,
    );
    
    return $data;
    
}

function acfe_is_polylang(){
    
    return acf_get_instance('acfe_multilang')->is_polylang;
    
}

function acfe_is_wpml(){
    
    return acf_get_instance('acfe_multilang')->is_wpml;
    
}

function acfe_get_post_lang($post_id, $field = false){
    
    // Polylang
    if(acfe_is_polylang()){
    
        // Default field
        if(!$field)
            $field = 'locale';
    
        return pll_get_post_language($post_id, $field);
        
    // WPML
    }elseif(acfe_is_wpml()){
    
        $post_lang = apply_filters('wpml_post_language_details', NULL, $post_id);
        
        // Default field
        if(!$field)
            $field = 'slug';
        
        if($field === 'locale'){
            
            return $post_lang['locale'];
        
        }elseif($field === 'slug'){
    
            return $post_lang['language_code'];
        
        }elseif($field === 'name'){
    
            return $post_lang['display_name'];
        
        }
    
        return false;
    
    }
    
    return false;
    
}

function acfe__(&$string, $name = false, $textdomain = 'acfe'){
    
    if(!acfe_is_multilang() || empty($string))
        return __($string, $textdomain);
    
    if(empty($name))
        $name = $string;
    
    // WPML
    if(acfe_is_wpml()){
        
        do_action( 'wpml_register_single_string', $textdomain, $name, $string);
        
        $string = __($string, $textdomain);
    
        return $string;
        
    }
    
    // PolyLang
    if(acfe_is_polylang()){
        
        pll_register_string($name, $string, $textdomain);
    
        $string = pll__($string);
        
        return $string;
    
    }
    
    $string = __($string, $textdomain);
    
    return $string;
    
}

function acfe__e($string, $name = false, $textdomain = 'acfe'){

    echo acfe__($string, $name, $textdomain);
    
}