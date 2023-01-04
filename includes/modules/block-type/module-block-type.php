<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_block_type')):

class acfe_module_block_type extends acfe_module{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name         = 'block_type';
        $this->plural       = 'block_types';
        $this->setting      = 'modules/block_types';
        $this->settings     = 'modules.block_types';
        $this->register     = 'acfe/init';
    
        $this->post_type    = 'acfe-dbt';
        $this->args         = array(
            'label'             => __('Block Types', 'acfe'),
            'show_in_menu'      => 'edit.php?post_type=acf-field-group',
            'labels'            => array(
                'name'          => __('Block Types', 'acfe'),
                'singular_name' => __('Block Type', 'acfe'),
                'menu_name'     => __('Block Types', 'acfe'),
                'edit_item'     => __('Edit Block Type', 'acfe'),
                'add_new_item'  => __('New Block Type', 'acfe'),
                'enter_title'   => __('Block Type Title', 'acfe'),
            ),
        );
    
        $this->messages     = array(
            'export_title'              => __('Export Block Types', 'acfe'),
            'export_description'        => __('Export Block Types', 'acfe'),
            'export_select'             => __('Select Block Types', 'acfe'),
            'export_not_found'          => __('No block type available.', 'acfe'),
            'export_not_selected'       => __('No block types selected', 'acfe'),
            'export_success_single'     => __('1 block type exported', 'acfe'),
            'export_success_multiple'   => __('%s block types exported', 'acfe'),
            'export_instructions'       => sprintf(__('It is recommended to include this code within the <code>acf/init</code> hook (<a href="%s" target="blank">see documentation</a>).', 'acfe'), esc_url('https://www.advancedcustomfields.com/resources/acf_register_block_type/')),
            'import_title'              => __('Import Block Types', 'acfe'),
            'import_description'        => __('Import Block Types', 'acfe'),
            'import_success_single'     => __('1 block type imported', 'acfe'),
            'import_success_multiple'   => __('%s block types imported', 'acfe'),
        );
    
        $this->export_files = array(
            'single'    => 'block-type',
            'multiple'  => 'block-types',
        );
    
        $this->validate = array('name');
    
        $this->columns  = array(
            'acfe-name'         => __('Name', 'acfe'),
            'acfe-category'     => __('Category', 'acfe'),
            'acfe-post-types'   => __('Posts', 'acfe'),
        );
    
        $this->item     = array(
            'name'              => '',
            'title'             => '',
            'active'            => true,
            'description'       => '',
            'category'          => 'common',
            'icon'              => '',
            'keywords'          => array(),
            'post_types'        => array(),
            'mode'              => 'preview',
            'align'             => '',
            'align_text'        => '',
            'align_content'     => 'top',
            'render_template'   => '',
            'render_callback'   => '',
            'enqueue_style'     => '',
            'enqueue_script'    => '',
            'enqueue_assets'    => '',
            'supports'          => array(
                'anchor'            => false,
                'align'             => true,
                'align_text'        => false,
                'align_content'     => false,
                'full_height'       => false,
                'mode'              => true,
                'multiple'          => true,
                'example'           => array(),
                'jsx'               => false,
            ),
        );
    
        $this->alias    = array(
            'title' => 'label',
        );
    
        $this->l10n = array('title', 'description');
        
        // register variations
        acf_add_filter_variations('acfe/block_type/prepend/template', array('name'), 1);
        acf_add_filter_variations('acfe/block_type/prepend/style',    array('name'), 1);
        acf_add_filter_variations('acfe/block_type/prepend/script',   array('name'), 1);
        
    }
    
    
    /**
     * register_item_args
     *
     * acfe/module/register_item_args
     *
     * @param $item
     *
     * @return mixed
     */
    function register_item_args($item){
        
        // template
        if($item['render_template']){
            $template = acfe_locate_file_path($item['render_template']);
            
            if(!empty($template)){
                $item['render_template'] = $template;
            }
        }
        
        // style
        if($item['enqueue_style']){
            $style = acfe_locate_file_url($item['enqueue_style']);
            
            if(!empty($style)){
                $item['enqueue_style'] = $style;
            }
        }
        
        // script
        if($item['enqueue_script']){
            $script = acfe_locate_file_url($item['enqueue_script']);
            
            if(!empty($script)){
                $item['enqueue_script'] = $script;
            }
        }
        
        return $item;
        
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
        if(!empty($item['name']) && !acf_has_block_type("acf/{$item['name']}")){
            acf_register_block_type($item);
        }
        
    }
    
    
    /**
     * load_post
     *
     * acfe/module/load_post
     */
    function load_post(){
        
        global $item;
        
        $prepend = acfe_get_setting('theme_folder') ? trailingslashit(acfe_get_setting('theme_folder')) : '';
    
        $template = apply_filters('acfe/block_type/prepend/template', $prepend, $item);
        $style    = apply_filters('acfe/block_type/prepend/style',    $prepend, $item);
        $script   = apply_filters('acfe/block_type/prepend/script',   $prepend, $item);
    
        add_filter('acf/prepare_field/name=render_template', function($field) use($template){
            $field['prepend'] = $template;
            return $field;
        });
    
        add_filter('acf/prepare_field/name=enqueue_style', function($field) use($style){
            $field['prepend'] = $style;
            return $field;
        });
    
        add_filter('acf/prepare_field/name=enqueue_script', function($field) use($script){
            $field['prepend'] = $script;
            return $field;
        });
        
        $field_groups = acf_get_field_groups(array(
            'block' => "acf/{$item['name']}"
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
        
        // editing current block type
        if($item['name'] === $value){
            return false;
        }
        
        // check sibiling post types (could be disabled)
        $sibiling_item = $this->get_item($value);
        
        if($sibiling_item && $sibiling_item['ID'] !== $item['ID']){
            return __('This block type already exists', 'acfe');
        }
        
        // existing block types
        if(acf_has_block_type("acf/{$value}")){
            return __('This block type already exists', 'acfe');
        }
        
        // return
        return false;
        
    }
    
    
    /**
     * prepare_load_item
     *
     * acfe/module/prepare_load_item
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_load_item($item){
        
        // supports
        $supports = array('anchor', 'jsx', 'align', 'align_text', 'align_content', 'full_height', 'mode', 'multiple');
        
        // loop supports
        foreach($supports as $key){
            $item["supports_$key"] = $item['supports'][ $key ];
        }
        
        $item['supports_align_content'] = acfe_unparse_types($item['supports_align_content']);
        
        // supports: align arguments
        if($item['supports']['align'] && is_array($item['supports']['align'])){
            $item['supports_align_args'] = acf_encode_choices($item['supports']['align'], false);
            $item['supports_align'] = true;
        }
        
        // icon
        if($item['icon'] && is_string($item['icon'])){
            
            $item['icon_type'] = 'simple';
            $item['icon_text'] = $item['icon'];
            
        }elseif($item['icon'] && is_array($item['icon'])){
            
            $item['icon_type'] = 'colors';
            $item['icon_background'] = acf_maybe_get($item['icon'], 'background');
            $item['icon_foreground'] = acf_maybe_get($item['icon'], 'foreground');
            $item['icon_src'] = acf_maybe_get($item['icon'], 'src');
            
        }
        
        return $item;
        
    }
    
    
    /**
     * prepare_save_item
     *
     * acfe/module/prepare_save_item
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_item($item){
        
        // general: post types
        $item['post_types'] = acf_get_array($item['post_types']);
        
        // supports
        $supports = array('anchor', 'jsx', 'align', 'align_text', 'align_content', 'full_height', 'mode', 'multiple');
        
        // loop supports
        foreach($supports as $key){
            $item['supports'][ $key ] = $item["supports_$key"];
        }
        
        // supports: align arguments
        if(!empty($item['supports_align_args'])){
            $item['supports']['align'] = $item['supports_align_args'];
        }
        
        // icon
        if($item['icon_type'] === 'simple'){
            
            $item['icon'] = $item['icon_text'];
            
        }elseif($item['icon_type'] === 'colors'){
            
            $item['icon'] = array(
                'background' => $item['icon_background'],
                'foreground' => $item['icon_foreground'],
                'src'        => $item['icon_src'],
            );
            
        }
        
        // return
        return $item;
        
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
     * edit_column_acfe_category
     *
     * @param $item
     */
    function edit_column_acfe_category($item){
        
        if(empty($item['category'])){
            echo '—';
            return;
        }
        
        echo ucfirst($item['category']);
        
    }
    
    
    /**
     * edit_column_acfe_post_types
     *
     * @param $item
     */
    function edit_column_acfe_post_types($item){
        
        $text = '—';
        
        if(empty($item['post_types'])){
            echo $text;
            return;
        }
        
        $post_types = array();
        
        foreach($item['post_types'] as $post_type){
            if(post_type_exists($post_type)){
                $post_types[] = $post_type;
            }
        }
        
        if($post_types){
            
            $labels = acf_get_pretty_post_types($post_types);
            
            if(!empty($labels)){
                
                $output = array();
                
                foreach($labels as $post_type => $label){
                    $output[] = '<a href="' . admin_url("edit.php?post_type={$post_type}") . '">' . $label . '</a>';
                }
                
                $text = implode(', ', $output);
                
            }
            
        }
        
        echo $text;
        
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
        return "acf_register_block_type({$code});";
    }
    
    
    /**
     * export_item_local_code
     *
     * acfe/module/export_item_local_code
     *
     * @param $return
     * @param $code
     * @param $args
     *
     * @return string
     */
    function export_local_code($code, $args){
        return "acfe_register_block_type({$code});";
    }
    
}

acfe_register_module('acfe_module_block_type');

endif;

function acfe_register_block_type($item){
    acfe_get_module('block_type')->add_local_item($item);
}