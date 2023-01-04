<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_options_page')):

class acfe_module_options_page extends acfe_module{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name         = 'options_page';
        $this->plural       = 'options_pages';
        $this->setting      = 'modules/options_pages';
        $this->settings     = 'modules.options_pages';
        $this->view         = 'admin.php?page=%s';
        $this->register     = 'acfe/init';
        
        $this->post_type    = 'acfe-dop';
        $this->args         = array(
            'label'             => __('Options Pages', 'acfe'),
            'show_in_menu'      => 'edit.php?post_type=acf-field-group',
            'hierarchical'      => true,
            'labels'            => array(
                'name'          => __('Options Pages', 'acfe'),
                'singular_name' => __('Options Page', 'acfe'),
                'menu_name'     => __('Options Pages', 'acfe'),
                'edit_item'     => __('Edit Options Page', 'acfe'),
                'add_new_item'  => __('New Options Page', 'acfe'),
                'enter_title'   => __('Options Page Title', 'acfe'),
            ),
        );
        
        $this->messages     = array(
            'export_title'              => __('Export Options Pages', 'acfe'),
            'export_description'        => __('Export Options Pages', 'acfe'),
            'export_select'             => __('Select Options Pages', 'acfe'),
            'export_not_found'          => __('No options page available.', 'acfe'),
            'export_not_selected'       => __('No options pages selected', 'acfe'),
            'export_success_single'     => __('1 options page exported', 'acfe'),
            'export_success_multiple'   => __('%s options pages exported', 'acfe'),
            'export_instructions'       => sprintf(__('It is recommended to include this code within the <code>acf/init</code> hook (<a href="%s" target="blank">see documentation</a>).', 'acfe'), esc_url('https://www.advancedcustomfields.com/resources/acf_add_options_page/')),
            'import_title'              => __('Import Options Pages', 'acfe'),
            'import_description'        => __('Import Options Pages', 'acfe'),
            'import_success_single'     => __('1 options page imported', 'acfe'),
            'import_success_multiple'   => __('%s options pages imported', 'acfe'),
        );
    
        $this->export_files = array(
            'single'    => 'options-page',
            'multiple'  => 'options-pages',
        );
    
        $this->validate = array('name');
    
        $this->columns  = array(
            'acfe-name'      => __('Menu slug', 'acfe'),
            'acfe-post-id'   => __('Post ID', 'acfe'),
            'acfe-autoload'  => __('Autoload', 'acfe'),
            'acfe-position'  => __('Position', 'acfe'),
        );
    
        $this->item     = array(
            'menu_slug'       => '',
            'page_title'      => '',
            'active'          => true,
            'menu_title'      => '',
            'capability'      => 'edit_posts',
            'parent_slug'     => '',
            'position'        => null,
            'icon_url'        => false,
            'redirect'        => true,
            'post_id'         => 'options',
            'autoload'        => false,
            'update_button'   => __('Update', 'acf'),
            'updated_message' => __('Options Updated', 'acf'),
        );
    
        $this->alias    = array(
            'menu_slug'  => 'name',
            'page_title' => 'label',
        );
    
        $this->l10n = array('page_title', 'menu_title', 'update_button', 'updated_message');
        
    }
    
    
    /**
     * register_items
     *
     * acfe/module/register_items
     *
     * @param $items
     *
     * @return array
     */
    function register_items($items){
        
        // vars
        $top_pages = $sub_pages = array();
        
        foreach($items as $item){
            
            // top pages
            if(!$item['parent_slug']){
                $top_pages[] = $item;
                
            // sub pages
            }else{
                $sub_pages[] = $item;
                
            }
            
        }
        
        // sort sub pages
        if(!empty($sub_pages)){
            
            uasort($sub_pages, function($a, $b){
                return (int) $a['position'] - (int) $b['position'];
            });
            
        }
        
        // register parent before childs so ACF correctly assign sub options pages
        $items = array_merge($top_pages, $sub_pages);
        
        return $items;
        
    }
    
    
    /**
     * register_item
     *
     * acfe/module/register_item
     *
     * @param $item
     */
    function register_item($item){
        
        // validate
        if(!empty($item['menu_slug']) && !acf_get_options_page($item['menu_slug'])){
            acf_add_options_page($item);
        }
        
    }
    
    
    /**
     * updated_item
     *
     * acfe/module/updated_item
     *
     * @param $item
     */
    function updated_item($item){
        
        $this->update_item_hierarchy($item);
        
    }
    
    
    /**
     * imported_item
     *
     * acfe/module/imported_item
     *
     * @param $item
     */
    function imported_item($item){
        
        $this->update_item_hierarchy($item);
        
    }
    
    
    /**
     * update_item_hierarchy
     *
     * @param $item
     */
    function update_item_hierarchy($item){
        
        // get raw items
        $raw_items = $this->get_raw_items();
        
        // loop
        foreach($raw_items as $raw_item){
            
            // item is child
            if($raw_item['menu_slug'] === $item['parent_slug']){
                
                wp_update_post(array(
                    'ID'            => $item['ID'],
                    'post_parent'   => $raw_item['ID'],
                ));
                
            }
            
            // item is parent
            if($item['menu_slug'] === $raw_item['parent_slug']){
                
                wp_update_post(array(
                    'ID'            => $raw_item['ID'],
                    'post_parent'   => $item['ID'],
                ));
                
            }
            
        }
        
    }
    
    
    /**
     * load_post
     *
     * acfe/module/load_post
     */
    function load_post(){
        
        global $item;
        
        $field_groups = acf_get_field_groups(array(
            'options_page' => $item['name']
        ));
        
        if($field_groups){
            
            acfe_add_field_groups_metabox(array(
                'id'            => 'acfe-field-groups',
                'title'         => __('Field Groups', 'acf'),
                'screen'        => $this->post_type,
                'field_groups'  => $field_groups,
            ));
            
        }
        
    }
    
    
    /**
     * validate_name
     *
     * @param $value
     * @param $item
     *
     * @return false|string
     */
    function validate_name($value, $item){
        
        // editing current options page
        if($item['name'] === $value){
            return false;
        }
        
        // check sibiling options pages (could be disabled)
        $sibiling_item = $this->get_item($value);
        
        if($sibiling_item && $sibiling_item['ID'] !== $item['ID']){
            return __('This options page slug already exists', 'acfe');
        }
        
        // check existing options pages
        $options_pages = acf_get_array(acf_get_options_pages());
        
        foreach($options_pages as $slug => $options_page){
            
            // options page already exists
            if($value === $slug){
                return __('This options page slug already exists', 'acfe');
            }
            
        }
        
        return false;
        
    }
    
    
    /**
     * edit_column_acfe_name
     *
     * @param $item
     */
    function edit_column_acfe_name($item){
        echo '<code style="font-size: 12px;">' . $item['name'] . '</code>';
    }
    
    
    /**
     * edit_column_acfe_post_id
     *
     * @param $item
     */
    function edit_column_acfe_post_id($item){
        echo '<code style="font-size: 12px;">' . $item['post_id'] . '</code>';
    }
    
    
    /**
     * edit_column_acfe_autoload
     *
     * @param $item
     */
    function edit_column_acfe_autoload($item){
        echo $item['autoload'] ? __('Yes') : __('No');
    }
    
    
    /**
     * edit_column_acfe_position
     *
     * @param $item
     */
    function edit_column_acfe_position($item){
        echo !acf_is_empty($item['position']) ? $item['position'] : '—';
    }
    
    
    /**
     * export_code
     *
     * @param $return
     * @param $code
     * @param $args
     *
     * @return string
     */
    function export_code($code, $args){
        return "acf_add_options_page({$code});";
    }
    
    
    /**
     * export_local_code
     *
     * @param $return
     * @param $code
     * @param $args
     *
     * @return string
     */
    function export_local_code($code, $args){
        return "acfe_register_options_page({$code});";
    }
    
}

acfe_register_module('acfe_module_options_page');

endif;

function acfe_register_options_page($item){
    acfe_get_module('options_page')->add_local_item($item);
}