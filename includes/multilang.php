<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_multilang')):
    
class acfe_multilang{
    
    // vars
    var $is_wpml       = false;
    var $is_polylang   = false;
    var $is_multilang  = false;
    var $options_pages = array();
    
    /**
     * construct
     */
    function __construct(){
        
        // wpml
        if(defined('ICL_SITEPRESS_VERSION')){
            
            $this->is_wpml = true;
            $this->is_multilang = true;
            
        }
        
        // polyLang
        if(defined('POLYLANG_VERSION') && function_exists('pll_default_language')){
            
            $this->is_polylang = true;
            $this->is_multilang = true;
            
        }
        
        if($this->is_multilang){
            add_action('acf/init', array($this, 'init'), 99);
        }
        
    }
    
    
    /**
     * init
     */
    function init(){
    
        // check setting
        if(!acf_get_setting('acfe/modules/multilang')){
            return;
        }
        
        // polylang specific
        if($this->is_polylang){
    
            // default/Current Language
            $dl = pll_default_language('locale');
            $cl = pll_current_language('locale');
    
            // update settings
            acf_update_setting('default_language', $dl);
            acf_update_setting('current_language', $cl);
    
            add_filter('acf/pre_load_reference',    array($this, 'polylang_preload_reference'), 10, 3);
            add_filter('acf/pre_load_value',        array($this, 'polylang_preload_value'), 10, 3);
        
        }
    
        // options page Message
        add_action('acf/options_page/submitbox_before_major_actions', array($this, 'options_page_message'));
    
        // acf options post id
        add_filter('acf/validate_post_id', array($this, 'set_options_post_id'), 99, 2);
        
    }
    
    
    /**
     * polylang_preload_reference
     *
     * @param $null
     * @param $field_name
     * @param $post_id
     *
     * @return mixed|null
     */
    function polylang_preload_reference($null, $field_name, $post_id){
    
        // validate post id
        $original_post_id = $this->polylang_validate_preload_post_id($post_id);
    
        if(!$original_post_id){
            return $null;
        }
    
        $reference = acf_get_metadata($post_id, $field_name, true);
    
        if($reference !== null){
            return $null;
        }
        
        return acf_get_metadata($original_post_id, $field_name, true);
    
    }
    
    
    /**
     * polylang_preload_value
     *
     * @param $null
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    function polylang_preload_value($null, $post_id, $field){
        
        // validate post id
        $original_post_id = $this->polylang_validate_preload_post_id($post_id);
        
        if(!$original_post_id){
            return $null;
        }
    
        // get field name
        $field_name = $field['name'];
    
        // check store
        $store = acf_get_store('values');
        
        if($store->has("$post_id:$field_name")){
            return $null;
        }
    
        // load value from database
        $value = acf_get_metadata($post_id, $field_name);
    
        // use field's default_value if no meta was found
        if($value !== null){
            return $null;
        }

        return acf_get_value($original_post_id, $field);
        
    }
    
    
    /**
     * polylang_validate_preload_post_id
     *
     * @param $post_id
     *
     * @return array|false|string|string[]|null
     */
    function polylang_validate_preload_post_id($post_id){
    
        // bail early if admin screen
        if(is_admin() || !is_string($post_id)){
            return false;
        }
    
        // get post id info
        $data = acf_get_post_id_info($post_id);
    
        // bail early if post id isn't an option type
        if($data['type'] !== 'option'){
            return false;
        }
    
        // bail early if not localized
        if(!$this->is_localized($post_id)){
            return false;
        }
    
        $original_post_id = preg_replace( '/([_\-][A-Za-z]{2}_[A-Za-z]{2})$/', '', $post_id);
    
        // check the regex
        if($original_post_id === $post_id){
            return false;
        }
    
        // bail early if no Options Page found with that post id
        if(!$this->is_options_page($original_post_id)){
            return false;
        }
        
        return $original_post_id;
        
    }
    
    
    /**
     * wpml_get_languages
     *
     * https://wpml.org/documentation/support/wpml-coding-api/wpml-hooks-reference/
     *
     * @param $pluck
     * @param $type
     *
     * @return array|int[]|mixed|string[]|null
     */
    function wpml_get_languages($pluck = '', $type = 'all'){
        
        // vars
        $languages = array();
        $pluck = $pluck === 'locale' ? 'default_locale' : $pluck;
    
        switch($type){
        
            // active
            case 'active': {
                
                // https://wpml.org/wpml-hook/wpml_active_languages/
                $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    
                // Set locale as key
                $_languages = $languages;
                $languages = array();
    
                foreach($_languages as $lang){
                    $languages[ $lang['default_locale'] ] = $lang;
                }
    
                if($pluck){
                    $languages = wp_list_pluck($languages, $pluck, true);
                }
    
                return $languages;
                
            }
            
            // all
            case '':
            case 'all': {
    
                // https://wpml.org/wpml-hook/wpml_active_languages/
                $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
                $languages = wp_list_pluck($languages, 'code', 'default_locale');
        
                // Default Languages
                $_languages = icl_get_languages_locales();
                $_languages = array_flip($_languages);
        
                if(!empty($_languages)){
            
                    $languages = array_merge($languages, $_languages);
                    $languages = array_unique($languages);
            
                }
        
                if($pluck){
                    $languages = $pluck === 'code' ? array_values($_languages) : array_keys($_languages);
                }
        
                return $languages;
                
            }
                
        }
    
        return $languages;
        
    }
    
    
    /**
     * polylang_get_languages
     *
     * https://polylang.pro/doc/filter-reference/
     * https://polylang.pro/doc/developpers-how-to/
     * https://polylang.pro/doc-category/developers/
     * https://polylang.wordpress.com/documentation/documentation-for-developers/general/
     * https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
     *
     * @param $pluck
     * @param $type
     *
     * @return array|string[]
     */
    function polylang_get_languages($pluck = '', $type = 'all'){
        
        // vars
        $languages = array();
        
        switch($type){
            
            // active
            case 'active': {
    
                // convert pluck
                $pluck = $pluck === 'code' ? 'slug' : $pluck;
    
                // https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/
                $languages = pll_languages_list(array(
                    'hide_empty'    => false,
                    'fields'        => $pluck
                ));
                
                return $languages;
                
            }
    
            // all
            case '':
            case 'all': {
    
                $languages = PLL_Settings::get_predefined_languages();
        
                if($pluck){
                    $languages = wp_list_pluck($languages, $pluck, true);
                }
        
                return $languages;
                
            }
                
        }
        
        return $languages;
        
    }
    
    
    /**
     * set_options_post_id
     *
     * @param $post_id
     * @param $original_post_id
     *
     * @return mixed|string
     */
    function set_options_post_id($post_id, $original_post_id){
        
        // bail early if original post id is 'options' ||'option'
        if(!is_string($post_id)){
            return $post_id;
        }
        
        $data = acf_get_post_id_info($post_id);
        
        // bail early if post id isn't an option type
        if($data['type'] !== 'option'){
            return $post_id;
        }
        
        // options Exception
        // $post_id already translated during the native acf/validate_post_id
        if(in_array($original_post_id, array('options', 'option'))){
            
            // exclude filter
            $exclude = apply_filters('acfe/modules/multilang/exclude_options', array());
            
            if(in_array('options', $exclude)){
                return 'options';
            }
    
            return $post_id;
            
        }

        // bail early if no Options Page found with that post id
        if(!$this->is_options_page($post_id)){
            return $post_id;
        }
        
        // bail early if already localized: 'my-options_en_US'
        if($this->is_localized($post_id)){
            return $post_id;
        }

        // append current language to post id
        $dl = acf_get_setting('default_language');
        $cl = acf_get_setting('current_language');

        // add Language
        if($cl && $cl !== $dl){
            $post_id .= '_' . $cl;
        }

        return $post_id;
        
    }
    
    
    /**
     * is_localized
     *
     * @param $post_id
     *
     * @return bool
     */
    function is_localized($post_id){
        
        // check if post id ends with '-en_US' || '_en_US' || '-en' || '_en'
        // https://regex101.com/r/oMsyeL/4
        preg_match('/(?P<locale>[_\-][A-Za-z]{2}_[A-Za-z]{2})$|(?P<code>[_\-][A-Za-z]{2})$/', $post_id, $matches);
        
        if(empty($matches)){
            return false;
        }

        // cleanup matches
        $lang = array();
        
        foreach($matches as $key => $val){
            
            if(is_int($key) || empty($val)){
                continue;
            }
            
            $lang = array(
                'type' => $key,
                'lang' => strtolower(substr($val, 1)), // Lowercase + Remove the first '_'
            );
            
        }
        
        if(empty($lang)){
            return false;
        }

        // get wpml/polylang languages list
        $languages = $this->get_languages($lang['type']);
        $languages = array_map('strtolower', $languages);

        // compare matches vs wpml/polylang languages list
        return in_array($lang['lang'], $languages);
        
    }
    
    
    /**
     * is_options_page
     *
     * @param $post_id
     *
     * @return bool
     */
    function is_options_page($post_id){
    
        // check if post id already in options pages
        if(in_array($post_id, $this->options_pages)){
            return true;
        }
    
        // get acf options pages
        $options_pages = acf_get_array(acf_get_options_pages());
        $list = wp_list_pluck($options_pages, 'post_id', true);
    
        // add 'post type list' location
        $post_types = acf_get_post_types(array(
            'show_ui' => 1,
            'exclude' => array('attachment')
        ));
    
        foreach($post_types as $post_type){
            $list[] = "{$post_type}_options";
        }
    
        // add 'taxonomy list' location
        $taxonomies = acf_get_taxonomies();
    
        foreach($taxonomies as $taxonomy){
            $list[] = "tax_{$taxonomy}_options";
        }
    
        // deprecated filter
        $list = apply_filters_deprecated('acfe/modules/multilang/options', array($list), '0.8.8.2', 'acfe/modules/multilang/exclude_options');
    
        // include filter
        $list = apply_filters('acfe/modules/multilang/include_options', $list);
    
        // exclude filter
        $exclude = apply_filters('acfe/modules/multilang/exclude_options', array());
    
        if(is_array($exclude) && !empty($exclude)){
        
            foreach($list as $i => $option){
                if(in_array($option, $exclude)){
                    unset($list[ $i ]);
                }
            }
        
            $list = array_values($list);
        
        }
    
        $this->options_pages = $list;
    
        return in_array($post_id, $this->options_pages);
        
    }
    
    
    /**
     * get_languages
     *
     * @param $pluck
     * @param $type
     * @param $plugin
     *
     * @return array|int[]|mixed|string[]|null
     */
    function get_languages($pluck = '', $type = '', $plugin = ''){
        
        // polylang
        if($this->is_polylang || $plugin === 'polylang'){
            return $this->polylang_get_languages($pluck, $type);
            
        // wpml
        }elseif($this->is_wpml || $plugin === 'wpml'){
            return $this->wpml_get_languages($pluck, $type);
        }
        
        return array();
        
    }
    
    
    /**
     * options_page_message
     */
    function options_page_message(){
        
        $default_language = acf_get_setting('default_language');
        $current_language = acf_get_setting('current_language');
        
        $message = false;
        
        // polylang
        if($this->is_polylang){
    
            if(!$current_language){
                $current_language = $default_language;
            }
    
            $message = "Language: {$current_language}";
    
            $nice_language = false;
            $nice_flag = false;
            
            $languages = pll_languages_list(array(
                'hide_empty'    => false,
                'fields'        => false
            ));
            
            if($languages){
    
                foreach($languages as $language){
        
                    if($language->locale !== $current_language){
                        continue;
                    }
    
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
        
        // wpml
        elseif($this->is_wpml){
            
            if($current_language === 'all'){
                $current_language = 'All';
            }
    
            $message = "Language: {$current_language}";
            
            if($current_language !== 'All'){
                
                $nice_language = false;
                $nice_flag = false;
                
                $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    
                if($languages){
                    
                    foreach($languages as $language){
            
                        if($language['language_code'] !== $current_language){
                            continue;
                        }
        
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
        
        if(empty($message)){
            return;
        }
        
        echo "<div class='misc-pub-section' style='padding-top:15px; padding-bottom:15px;'>{$message}</div>";
        
        
    }
    
}

acf_new_instance('acfe_multilang');

endif;


/**
 * acfe_is_multilang
 *
 * @return mixed
 */
function acfe_is_multilang(){
    return acf_get_instance('acfe_multilang')->is_multilang;
}


/**
 * acfe_get_multilang
 *
 * @return array
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


/**
 * acfe_get_multilang_languages
 *
 * @param $pluck
 * @param $type
 * @param $plugin
 *
 * @return mixed
 */
function acfe_get_multilang_languages($pluck = '', $type = '', $plugin = ''){
    return acf_get_instance('acfe_multilang')->get_languages($pluck, $type, $plugin);
}


/**
 * acfe_is_polylang
 *
 * @return mixed
 */
function acfe_is_polylang(){
    return acf_get_instance('acfe_multilang')->is_polylang;
}


/**
 * acfe_is_wpml
 *
 * @return mixed
 */
function acfe_is_wpml(){
    return acf_get_instance('acfe_multilang')->is_wpml;
}


/**
 * acfe_get_post_lang
 *
 * @param $post_id
 * @param $field
 *
 * @return false|mixed|string
 */
function acfe_get_post_lang($post_id, $field = false){
    
    // bail early if not multilang
    if(!acfe_is_multilang()){
        return false;
    }
    
    // polylang
    if(acfe_is_polylang()){
        
        // default field
        if(!$field){
            $field = 'locale';
        }
        
        return pll_get_post_language($post_id, $field);
        
    // wpml
    }elseif(acfe_is_wpml()){
        
        $post_lang = apply_filters('wpml_post_language_details', NULL, $post_id);
        
        // default field
        if(!$field){
            $field = 'slug';
        }
        
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


/**
 * acfe_get_post_translated
 *
 * @param $post_id
 * @param $lang
 *
 * @return false|int|mixed|null
 */
function acfe_get_post_translated($post_id, $lang = false){
    
    // bail early if not multilang
    if(!acfe_is_multilang()){
        return $post_id;
    }
    
    // default
    $translated_post_id = $post_id;
    
    // polylang
    if(acfe_is_polylang()){
        $translated_post_id = pll_get_post($post_id, $lang);
        
    // wpml
    }elseif(acfe_is_wpml()){
        $translated_post_id = apply_filters('wpml_object_id', $post_id, 'post', false, $lang);
    }
    
    /*
    if(empty($translated_post_id))
        return $post_id;
    */
    
    return $translated_post_id;
    
}


/**
 * acfe_get_post_translated_default
 *
 * @param $post_id
 *
 * @return int|mixed
 */
function acfe_get_post_translated_default($post_id){
    
    // get translated post id
    $translated_post_id = acfe_get_post_translated($post_id, acf_get_setting('default_language'));
    
    // fallback to current
    if(empty($translated_post_id)){
        return $post_id;
    }
    
    return $translated_post_id;
    
}


/**
 * acfe_translate
 *
 * @param $string
 * @param $name
 * @param $textdomain
 *
 * @return mixed|string|null
 */
function acfe_translate($string, $name = '', $textdomain = ''){
    
    // no polylang/wpml
    if(!acfe_is_multilang()){
        return acf_translate($string);
    }
    
    // acf translate
    if(empty($name) && empty($textdomain)){
        return acf_translate($string);
    }
    
    // is array
    if(is_array($string)){
        
        foreach($string as $k => $v){
            $name = !is_numeric($k) ? ucfirst($k) : $name;
            $string[ $k ] = acfe_translate($v, $name, $textdomain);
        }
        
    }
    
    // bail early if not string
    if(!is_string($string)){
        return $string;
    }
    
    // bail early if empty
    if($string === ''){
        return $string;
    }
    
    // wpml
    if(acfe_is_wpml()){
        return apply_filters('wpml_translate_single_string', $string, $textdomain, $name);
    }
    
    // polylang
    if(acfe_is_polylang()){
        
        pll_register_string($name, $string, $textdomain);
        return pll__($string);
        
    }
    
    // default translate
    return acf_translate($string);
    
}


/**
 * acfe_register_translate
 *
 * @param $string
 * @param $name
 * @param $textdomain
 */
function acfe_register_translate($string, $name = '', $textdomain = ''){
    
    // wpml only
    if(acfe_is_wpml()){
    
        // is array
        if(is_array($string)){
            
            foreach($string as $k => $v){
                $name = !is_numeric($k) ? ucfirst($k) : $name;
                acfe_register_translate($v, $name, $textdomain);
            }
            
        // string
        }else{
            do_action('wpml_register_single_string', $textdomain, $name, $string);
        }
    
    }
    
    
}


/**
 * acfe__
 *
 * @param $string
 * @param $name
 * @param $textdomain
 *
 * @deprecated
 *
 * @return mixed|string|null
 */
function acfe__($string, $name = false, $textdomain = 'acfe'){
    return acfe_translate($string, $name, $textdomain);
}


/**
 * acfe__e
 *
 * @param $string
 * @param $name
 * @param $textdomain
 *
 * @deprecated
 */
function acfe__e($string, $name = false, $textdomain = 'acfe'){
    echo acfe_translate($string, $name, $textdomain);
}