<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_multilang')):
    
class acfe_multilang{
    
    var $is_wpml = false;
    var $is_polylang = false;
    var $is_multilang = false;
    var $options_pages = false;
    
    function __construct(){
        
        // WPML
        if(defined('ICL_SITEPRESS_VERSION')){
            
            $this->is_wpml = true;
            $this->is_multilang = true;
            
        }
        
        // PolyLang
        if(defined('POLYLANG_VERSION') && function_exists('pll_default_language')){
            
            $this->is_polylang = true;
            $this->is_multilang = true;
            
        }
        
        if($this->is_multilang){
    
            add_action('acf/init', array($this, 'init'), 99);
            
        }
        
    }
    
    function init(){
    
        // Check setting
        if(!acf_get_setting('acfe/modules/multilang'))
            return;
        
        // Polylang specific
        if($this->is_polylang){
    
            // Default/Current Language
            $dl = pll_default_language('locale');
            $cl = pll_current_language('locale');
    
            // Update settings
            acf_update_setting('default_language', $dl);
            acf_update_setting('current_language', $cl);
        
        }
    
        // ACF Options Post ID
        add_filter('acf/validate_post_id', array($this, 'set_options_post_id'), 99, 2);
        
    }
    
    /**
     * WPML
     * https://wpml.org/documentation/support/wpml-coding-api/wpml-hooks-reference/
     */
    function wpml_get_languages($pluck, $type = 'all'){
        
        // Pluck
        $pluck_filter = $pluck;
    
        if($pluck === 'locale')
            $pluck_filter = 'default_locale';
        
        // Vars
        $languages = array();
    
        switch($type){
        
            // Active
            case 'active':
                
                // Active Languages
                // https://wpml.org/wpml-hook/wpml_active_languages/
                $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
                
                $languages = wp_list_pluck($languages, $pluck_filter, true);
                
                return $languages;
                
            // All
            case 'all':
    
                // Active Languages
                // https://wpml.org/wpml-hook/wpml_active_languages/
                $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    
                $languages = wp_list_pluck($languages, $pluck_filter, true);
    
                // Plugin Languages
                $plugin_languages = icl_get_languages_locales();
    
                if(!empty($plugin_languages)){
        
                    if($pluck === 'code'){
            
                        $plugin_languages = array_keys($plugin_languages);
            
                    }elseif($pluck === 'locale'){
            
                        $plugin_languages = array_values($plugin_languages);
            
                    }
        
                    // Merge
                    $languages = array_merge($languages, $plugin_languages);
                    $languages = array_unique($languages);
        
                }
                
                return $languages;
                
        }
    
        return $languages;
        
    }
    
    /**
     * PolyLang
     * https://polylang.pro/doc/filter-reference/
     * https://polylang.pro/doc/developpers-how-to/
     * https://polylang.wordpress.com/documentation/documentation-for-developers/general/
     * https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
     */
    function polylang_get_languages($pluck, $type = 'all'){
        
        // Vars
        $languages = array();
        
        switch($type){
            
            // Active
            case 'active':
        
                $pluck_filter = $pluck;
                if($pluck === 'code')
                    $pluck_filter = 'slug';
        
                // https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
                $languages = pll_languages_list(array(
                    'hide_empty'    => false,
                    'fields'        => $pluck_filter
                ));
        
                return $languages;
    
            // All
            case 'all':
        
                // Copy from wp-content/plugins/polylang-pro/settings/settings.php:363
                require_once ABSPATH . 'wp-admin/includes/translation-install.php';
    
                $languages    = include POLYLANG_DIR . '/settings/languages.php';
                $translations = wp_get_available_translations();
        
                if (!empty($translations)){
            
                    $translations['en_US'] = '';
                    $languages = array_intersect_key($languages, $translations);
            
                }
        
                $languages = apply_filters('pll_predefined_languages', $languages);
        
                foreach($languages as $k => $lang){
            
                    if(isset($lang['code'], $lang['locale'], $lang['name'], $lang['dir'], $lang['flag']))
                        continue;
            
                    unset($languages[$k]);
            
                }
        
                $languages = wp_list_pluck($languages, $pluck, true);
        
                return $languages;
                
        }
        
        return $languages;
        
    }
    
    /**
     * ACF Options Post ID
     */
    function set_options_post_id($post_id, $original_post_id){
        
        // Bail early if original post id is 'options' ||'option'
        if(!is_string($post_id) || in_array($original_post_id, array('options', 'option')))
            return $post_id;
        
        $data = acf_get_post_id_info($post_id);
        
        // Bail early if post id isn't an option type
        if($data['type'] !== 'option')
            return $post_id;

        // Bail early if no Options Page found with that post id
        if(!$this->is_options_page($post_id))
            return $post_id;
        
        // Bail early if already localized: 'my-options_en_US'
        if($this->is_localized($post_id))
            return $post_id;

        // Append current language to post id
        $dl = acf_get_setting('default_language');
        $cl = acf_get_setting('current_language');

        // Add Language
        if($cl && $cl !== $dl){

            $post_id .= '_' . $cl;

        }

        return $post_id;
        
    }
    
    function is_localized($post_id){
        
        // Check if post id ends with '-en_US' || '_en_US' || '-en' || '_en'
        // https://regex101.com/r/oMsyeL/4
        preg_match('/(?P<locale>[_\-][A-Za-z]{2}_[A-Za-z]{2})$|(?P<code>[_\-][A-Za-z]{2})$/', $post_id, $matches);
        
        if(empty($matches))
            return false;

        // Cleanup matches
        $lang = array();
        
        foreach($matches as $key => $val){
            
            if(is_int($key) || empty($val))
                continue;
            
            $lang = array(
                'type' => $key,
                'lang' => strtolower(substr($val, 1)), // Lowercase + Remove the first '_'
            );
            
        }
        
        if(empty($lang))
            return false;

        // Get WPML/Polylang Languages List
        $languages = $this->get_languages($lang['type']);
        $languages = array_map('strtolower', $languages);

        // Compare Matches vs WPML/Polylang Languages List
        return in_array($lang['lang'], $languages);
        
    }
    
    function is_options_page($post_id){
    
        // Get Options Pages
        if($this->options_pages === false){
            
            // Get ACF Options Pages
            $options_pages = acf_get_array(acf_get_options_pages());
            $list = wp_list_pluck($options_pages, 'post_id', true);
            
            // Add 'Post Types List' location
            $post_types = acf_get_post_types(array(
                'show_ui'	=> 1,
                'exclude'	=> array('attachment')
            ));
    
            if(!empty($post_types)){
                
                foreach($post_types as $post_type){
    
                    $list[] = $post_type . '_options';
                    
                }
        
            }
            
            // Add 'Taxonomy List' location
            $taxonomies = acf_get_taxonomies();
    
            if(!empty($taxonomies)){
                
                foreach($taxonomies as $taxonomy){
    
                    $list[] = 'tax_' . $taxonomy . '_options';
                    
                }
        
            }
            
            $list = apply_filters('acfe/modules/multilang/options', $list);
            
            $this->options_pages = $list;
            
        }
        
        if(is_array($this->options_pages) && !empty($this->options_pages)){
    
            return in_array($post_id, $this->options_pages);
            
        }
        
        return false;
        
    }
    
    function get_languages($pluck = 'code', $type = 'all', $plugin = false){
        
        // Polylang
        if($this->is_polylang || $plugin === 'polylang'){
            
            return $this->polylang_get_languages($pluck, $type);
            
            // WPML
        }elseif($this->is_wpml || $plugin === 'wpml'){
            
            return $this->wpml_get_languages($pluck, $type);
            
        }
        
        return array();
        
    }
    
}

acf_new_instance('acfe_multilang');

endif;

function acfe_is_multilang(){
    
    return acf_get_instance('acfe_multilang')->is_multilang;
    
}

function acfe_get_multilang(){
    
    $wpml = acf_get_instance('acfe_multilang')->is_wpml;
    $polylang = acf_get_instance('acfe_multilang')->is_polylang;
    
    $data = array(
        'dl'        => acf_get_setting('default_language'),
        'cl'        => acf_get_setting('current_language'),
        'wpml'      => $wpml,
        'polylang'  => $polylang,
    );
    
    return $data;
    
}

function acfe_get_languages($pluck = 'code', $type = 'all', $plugin = false){
    
    return acf_get_instance('acfe_multilang')->get_languages($pluck, $type, $plugin);
    
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