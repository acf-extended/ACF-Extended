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
    
            add_filter('acf/pre_load_reference',    array($this, 'polylang_preload_reference'), 10, 3);
            add_filter('acf/pre_load_value',        array($this, 'polylang_preload_value'), 10, 3);
        
        }
    
        // Options Page Message
        add_filter('acf/options_page/submitbox_before_major_actions', array($this, 'options_page_message'));
    
        // ACF Options Post ID
        add_filter('acf/validate_post_id', array($this, 'set_options_post_id'), 99, 2);
        
    }
    
    function polylang_preload_reference($null, $field_name, $post_id){
    
        // Validate post id
        $original_post_id = $this->polylang_validate_preload_post_id($post_id);
    
        if(!$original_post_id)
            return $null;
    
        $reference = acf_get_metadata($post_id, $field_name, true);
    
        if($reference !== null)
            return $null;
        
        return acf_get_metadata($original_post_id, $field_name, true);
    
    }
    
    function polylang_preload_value($null, $post_id, $field){
        
        // Validate post id
        $original_post_id = $this->polylang_validate_preload_post_id($post_id);
        
        if(!$original_post_id)
            return $null;
    
        // Get field name.
        $field_name = $field['name'];
    
        // Check store.
        $store = acf_get_store('values');
        
        if($store->has("$post_id:$field_name"))
            return $null;
    
        // Load value from database.
        $value = acf_get_metadata($post_id, $field_name);
    
        // Use field's default_value if no meta was found.
        if($value !== null)
            return $null;

        return acf_get_value($original_post_id, $field);
        
    }
    
    function polylang_validate_preload_post_id($post_id){
    
        // Bail early if admin screen
        if(is_admin() || !is_string($post_id))
            return false;
    
        // Get post id info
        $data = acf_get_post_id_info($post_id);
    
        // Bail early if post id isn't an option type
        if($data['type'] !== 'option')
            return false;
    
        // Bail early if not localized
        if(!$this->is_localized($post_id))
            return false;
    
        $original_post_id = preg_replace( '/([_\-][A-Za-z]{2}_[A-Za-z]{2})$/', '', $post_id);
    
        // Check the regex
        if($original_post_id === $post_id)
            return false;
    
        // Bail early if no Options Page found with that post id
        if(!$this->is_options_page($original_post_id))
            return false;
        
        return $original_post_id;
        
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
     * https://polylang.pro/doc-category/developers/
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
        if(!is_string($post_id))
            return $post_id;
        
        $data = acf_get_post_id_info($post_id);
        
        // Bail early if post id isn't an option type
        if($data['type'] !== 'option')
            return $post_id;
        
        // Options Exception
        // $post_id already translated during the native acf/validate_post_id
        if(in_array($original_post_id, array('options', 'option'))){
            
            // Exclude filter
            $exclude = apply_filters('acfe/modules/multilang/exclude_options', array());
            
            if(in_array('options', $exclude)){
                return 'options';
            }
    
            return $post_id;
            
        }

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
                'show_ui' => 1,
                'exclude' => array('attachment')
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
            
            // Depreacted filter
            $list = apply_filters_deprecated('acfe/modules/multilang/options', array($list), '0.8.8.2', 'acfe/modules/multilang/exclude_options');
            
            // Include filter
            $list = apply_filters('acfe/modules/multilang/include_options', $list);
            
            // Exclude filter
            $exclude = apply_filters('acfe/modules/multilang/exclude_options', array());
            
            if(is_array($exclude) && !empty($exclude)){
                
                foreach($list as $i => $option){
                    
                    if(!in_array($option, $exclude))
                        continue;
                    
                    unset($list[$i]);
                    
                }
                
                $list = array_values($list);
                
            }
            
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
    
    function options_page_message(){
        
        $default_language = acf_get_setting('default_language');
        $current_language = acf_get_setting('current_language');
        
        $message = false;
        
        // Polylang
        if($this->is_polylang){
    
            if(!$current_language)
                $current_language = $default_language;
    
            $message = "Language: {$current_language}";
    
            $nice_language = false;
            $nice_flag = false;
            
            $languages = pll_languages_list(array(
                'hide_empty'    => false,
                'fields'        => false
            ));
            
            if($languages){
    
                foreach($languages as $language){
        
                    if($language->locale !== $current_language)
                        continue;
    
                    $nice_language = $language->name;
                    $nice_flag = $language->flag_url;
                    break;
        
                }
                
            }
    
            if($nice_language){
        
                $message = "<img src='{$nice_flag}' style='margin-right:5px;vertical-align:-1px;' /> Language: {$nice_language}";
        
            }
            
            if($default_language === $current_language){
                
                $message .= ' (Default)';
                
            }
            
        }
        
        // WPML
        elseif($this->is_wpml){
            
            if($current_language === 'all')
                $current_language = 'All';
    
            $message = "Language: {$current_language}";
            
            if($current_language !== 'All'){
                
                $nice_language = false;
                $nice_flag = false;
                
                $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    
                if($languages){
                    
                    foreach($languages as $language){
            
                        if($language['language_code'] !== $current_language)
                            continue;
        
                        $nice_language = $language['native_name'];
                        $nice_flag = $language['country_flag_url'];
                        break;
            
                    }
                    
                }
    
                if($nice_language){
    
                    $message = "<img src='{$nice_flag}' style='margin-right:5px;vertical-align:-1px; width:16px; height:11px;' /> Language: {$nice_language}";
        
                }
                
            }
            
        }
        
        if(empty($message))
            return;
        
        echo "<div class='misc-pub-section' style='padding-top:15px; padding-bottom:15px;'>{$message}</div>";
        
        
    }
    
}

acf_new_instance('acfe_multilang');

endif;

/*
 * Is Multilang Enabled
 */
function acfe_is_multilang(){
    
    return acf_get_instance('acfe_multilang')->is_multilang;
    
}

/*
 * Get Multilang Data
 */
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

/*
 * Get Languages
 */
function acfe_get_languages($pluck = 'code', $type = 'all', $plugin = false){
    
    return acf_get_instance('acfe_multilang')->get_languages($pluck, $type, $plugin);
    
}

/*
 * Is Polylang
 */
function acfe_is_polylang(){
    
    return acf_get_instance('acfe_multilang')->is_polylang;
    
}

/*
 * Is WPML
 */
function acfe_is_wpml(){
    
    return acf_get_instance('acfe_multilang')->is_wpml;
    
}

/*
 * Get Post Language
 */
function acfe_get_post_lang($post_id, $field = false){
    
    // Bail early if not multilang
    if(!acfe_is_multilang())
        return false;
    
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

/*
 * Get Post Translated
 */
function acfe_get_post_translated($post_id, $lang = false){
    
    // Bail early if not multilang
    if(!acfe_is_multilang())
        return $post_id;
    
    // Default
    $translated_post_id = $post_id;
    
    // Polylang
    if(acfe_is_polylang()){
    
        $translated_post_id = pll_get_post($post_id, $lang);
        
    // WPML
    }elseif(acfe_is_wpml()){
    
        $translated_post_id = apply_filters('wpml_object_id', $post_id, 'post', false, $lang);
        
    }
    
    /*
    if(empty($translated_post_id))
        return $post_id;
    */
    
    return $translated_post_id;
    
}

/*
 * Get Default Post Translated
 */
function acfe_get_post_translated_default($post_id){
    
    // Get translated post id
    $translated_post_id = acfe_get_post_translated($post_id, acf_get_setting('default_language'));
    
    // Fallback to current
    if(empty($translated_post_id))
        return $post_id;
    
    return $translated_post_id;
    
}

/*
 * Translate String
 */
function acfe_translate($string, $name = false, $textdomain = 'acfe'){
    
    // Bail early
    if(!acfe_is_multilang() || empty($string))
        return __($string, $textdomain);
    
    // Name compatibility
    if(empty($name))
        $name = $string;
    
    // WPML
    if(acfe_is_wpml()){
        
        // Translate (Register string during save)
        return apply_filters('wpml_translate_single_string', $string, $textdomain, $name);
        
    }
    
    // PolyLang
    if(acfe_is_polylang()){
        
        // Register string
        pll_register_string($name, $string, $textdomain);
        
        // Translate
        return pll__($string);
        
    }
    
    // Default Translate
    return __($string, $textdomain);
    
}

/*
 * Deprecated Translate String
 */
function acfe__($string, $name = false, $textdomain = 'acfe'){
    
    return acfe_translate($string, $name, $textdomain);
    
}

/*
 * Deprecated Translate String (echo)
 */
function acfe__e($string, $name = false, $textdomain = 'acfe'){
    
    echo acfe_translate($string, $name, $textdomain);
    
}