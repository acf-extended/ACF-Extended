<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_select')):

class acfe_field_flexible_content_select{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',          array($this, 'defaults_field'), 10);
        add_filter('acfe/flexible/defaults_layout',         array($this, 'defaults_layout'), 10);
        
        add_action('acfe/flexible/render_field_settings',   array($this, 'render_field_settings'), 10);
        add_action('acfe/flexible/render_layout_settings',  array($this, 'render_layout_settings'), 10, 3);
        
        add_filter('acfe/flexible/wrapper_attributes',      array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/layouts/select_atts',     array($this, 'select_atts'), 10, 3);
        add_filter('acfe/flexible/layouts/select_label',    array($this, 'select_label'), 10, 3);
        
        add_action('acfe/flexible/render_popup_select',     array($this, 'render_popup_select_open_wrapper'),   5);
        add_action('acfe/flexible/render_popup_select',     array($this, 'render_popup_select_categories'),     7);
        add_action('acfe/flexible/render_popup_select',     array($this, 'render_popup_select_before_layouts'), 9);
        add_action('acfe/flexible/render_popup_select',     array($this, 'render_popup_select_close_wrapper'),  15);
        
    }
    
    
    /**
     * defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_modal'] = array(
            'acfe_flexible_modal_enabled'    => false,
            'acfe_flexible_modal_title'      => false,
            'acfe_flexible_modal_size'       => 'xlarge',
            'acfe_flexible_modal_col'        => '4',
            'acfe_flexible_modal_categories' => false,
        );
        
        return $field;
        
    }
    
    
    /**
     * defaults_layout
     *
     * @param $layout
     *
     * @return mixed
     */
    function defaults_layout($layout){
        
        $layout['acfe_flexible_category'] = false;
        
        return $layout;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        acf_render_field_setting($field, array(
            'label'         => __('Select Modal', 'acfe'),
            'name'          => 'acfe_flexible_modal',
            'key'           => 'acfe_flexible_modal',
            'instructions'  => __('Select layouts in a modal', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/modal-settings#selection-modal" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'          => 'group',
            'layout'        => 'block',
            'sub_fields'    => array(
                array(
                    'label'             => '',
                    'name'              => 'acfe_flexible_modal_enabled',
                    'key'               => 'acfe_flexible_modal_enabled',
                    'type'              => 'true_false',
                    'instructions'      => '',
                    'required'          => false,
                    'wrapper'           => array(
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
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_title',
                    'key'           => 'acfe_flexible_modal_title',
                    'type'          => 'text',
                    'prepend'       => __('Modal Title', 'acfe'),
                    'placeholder'   => $field['button_label'],
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => '',
                        'id'    => '',
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_size',
                    'key'           => 'acfe_flexible_modal_size',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        'small'     => __('Small', 'acfe'),
                        'medium'    => __('Medium', 'acfe'),
                        'large'     => __('Large', 'acfe'),
                        'xlarge'    => __('Extra Large', 'acfe'),
                        'full'      => __('Full', 'acfe'),
                    ),
                    'default_value' => 'large',
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => '',
                        'id'    => '',
                        'data-acfe-prepend' => __('Size', 'acfe'),
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_col',
                    'key'           => 'acfe_flexible_modal_col',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        '1' => '1',
                        '2' => '2',
                        '3' => '3',
                        '4' => '4',
                        '5' => '5',
                        '6' => '6',
                    ),
                    'default_value' => '4',
                    'wrapper'       => array(
                        'width' => '15',
                        'class' => '',
                        'id'    => '',
                        'data-acfe-prepend' => __('Cols', 'acfe'),
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
                array(
                    'label'         => '',
                    'name'          => 'acfe_flexible_modal_categories',
                    'key'           => 'acfe_flexible_modal_categories',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        '1'   => __('Enabled', 'acfe'),
                    ),
                    'default_value' => '',
                    'allow_null'    => true,
                    'placeholder'   => __('Disabled', 'acfe'),
                    'wrapper'       => array(
                        'width' => '25',
                        'class' => '',
                        'id'    => '',
                        'data-acfe-prepend' => __('Categories', 'acfe'),
                    ),
                    'conditional_logic' => array(
                        array(
                            array(
                                'field'     => 'acfe_flexible_modal_enabled',
                                'operator'  => '==',
                                'value'     => '1',
                            )
                        )
                    )
                ),
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            ),
            'wrapper' => array(
                'class' => 'acfe-field-setting-flex'
            )
        ));
        
    }
    
    
    /**
     * render_layout_settings
     *
     * @param $field
     * @param $layout
     * @param $prefix
     */
    function render_layout_settings($field, $layout, $prefix){
        
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_categories']){
            return;
        }
        
        // Title
        echo '</li>';
        acf_render_field_wrap(array(
            'label' => __('Select Modal', 'acfe'),
            'type'  => 'hidden',
            'name'  => 'acfe_flexible_grid_label',
            'wrapper' => array(
                'class' => 'acfe-flexible-field-setting',
            )
        ), 'ul');
        echo '<li>';
        
        acf_render_field_wrap(array(
            'prepend'       => __('Category', 'acfe'),
            'name'          => 'acfe_flexible_category',
            'type'          => 'select',
            'ui'            => 1,
            'multiple'      => 1,
            'allow_custom'  => 1,
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_category'],
            'placeholder'   => __('Enter category', 'acfe'),
            'wrapper'       => array(
                'data-acfe-prepend' => 'Categories',
            ),
        ), 'ul');
        
    }
    
    
    /**
     * wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function wrapper_attributes($wrapper, $field){
        
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
            return $wrapper;
        }
        
        // modal settings
        $wrapper['data-acfe-flexible-modal'] = 1;
        $wrapper['data-acfe-flexible-modal-col'] = $field['acfe_flexible_modal']['acfe_flexible_modal_col'];
        $wrapper['data-acfe-flexible-modal-size'] = $field['acfe_flexible_modal']['acfe_flexible_modal_size'];
        $wrapper['data-acfe-flexible-modal-categories'] = $field['acfe_flexible_modal']['acfe_flexible_modal_categories'];
        $wrapper['data-acfe-flexible-modal-title'] = $field['button_label'];
    
        // modal title
        if(!empty($field['acfe_flexible_modal']['acfe_flexible_modal_title'])){
            $wrapper['data-acfe-flexible-modal-title'] = $field['acfe_flexible_modal']['acfe_flexible_modal_title'];
        }
        
        return $wrapper;
        
    }
    
    
    /**
     * select_atts
     *
     * @param $atts
     * @param $layout
     * @param $field
     *
     * @return mixed
     */
    function select_atts($atts, $layout, $field){
        
        // check setting
        if($field['acfe_flexible_modal']['acfe_flexible_modal_categories']){
            $atts['data-category'] = $this->get_layout_categories_names($layout);
        }
        
        // return
        return $atts;
        
    }
    
    
    /**
     * select_label
     *
     * @param $label
     * @param $layout
     * @param $field
     *
     * @return mixed|string
     */
    function select_label($label, $layout, $field){
        
        // check modal select + thumbnail
        if($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
            $label = "<div class='acfe-fc-layout-label'>{$label}</div>";
        }
        
        return $label;
        
    }
    
    
    /**
     * render_popup_select_open_wrapper
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_select_open_wrapper($field){
        
        // open wrapper
        if($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
            echo '<div>';
        }
        
    }
    
    
    /**
     * render_popup_select_categories
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_select_categories($field){
        
        // categories
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_categories']){
            return;
        }
        
        // wrapper filters
        $wrapper = array('class' => 'acfe-nav-tabs acfe-fc-categories');
        $wrapper = apply_filters("acfe/flexible/modal_select_categories_wrapper",                        $wrapper, $field);
        $wrapper = apply_filters("acfe/flexible/modal_select_categories_wrapper/name={$field['_name']}", $wrapper, $field);
        $wrapper = apply_filters("acfe/flexible/modal_select_categories_wrapper/key={$field['key']}",    $wrapper, $field);
        
        // wrapper
        echo '<div ' . acf_esc_attrs($wrapper) . '>';
        
        // loop categories
        foreach($this->get_categories($field) as $name => $category){
            
            $atts = array(
                'href'          => '#',
                'class'         => 'acfe-nav-tab',
                'data-category' => $name,
            );
            
            if($name === 'acfe-all'){
                $atts['class'] .= ' -active';
            }
            
            echo '<a ' . acf_esc_attrs($atts) . '>';
            echo acf_esc_html($category);
            echo '</a>';
            
        }
        
        echo '</div>';
        
    }
    
    
    
    /**
     * render_popup_select_before_layouts
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_select_before_layouts($field){
        
        // check setting
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
            return;
        }
        
        $atts = array(
            'class' => 'acfe-fc-layouts',
        );
        
        // get col size
        $col = (int) $field['acfe_flexible_modal']['acfe_flexible_modal_col'];
        $atts['class'] .= " -col-{$col}";
        
        if(!$field['acfe_flexible_layouts_thumbnails']){
            $atts['class'] .= ' -no-thumbnails';
        }
        
        // get col size
        echo '<div ' . acf_esc_attrs($atts) . '>';
        
    }
    
    
    /**
     * render_popup_select_close_wrapper
     *
     * @param $field
     *
     * @return void
     */
    function render_popup_select_close_wrapper($field){
        
        // close wrapper
        if($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
            echo '</div></div>';
        }
        
    }
    
    
    /**
     * get_categories
     *
     * @param $field
     *
     * @return array
     */
    function get_categories($field){
        
        // initial array
        $storage = array();
        
        // check settings
        if(!$field['acfe_flexible_modal']['acfe_flexible_modal_categories']){
            return $storage;
        }
        
        // loop layouts
        foreach($field['layouts'] as $layout){
            
            // loop categories
            foreach($this->get_layout_categories($layout) as $name => $category){
                
                // make sure category is unique
                if(!isset($storage[ $name ])){
                    $storage[ $name ] = $category;
                }
                
            }
            
        }
        
        // sort by keys alphabetically
        ksort($storage);
        
        // prepare categories array
        $categories = array(
            'acfe-all' => '<span class="dashicons dashicons-screenoptions"></span><span class="label">' . __('All Layouts', 'acfe') . '</span>',
        );
        
        // loop storage
        foreach($storage as $name => $category){
            $categories[ $name ] = $category;
        }
        
        // filters
        $categories = apply_filters("acfe/flexible/modal_select_categories",                        $categories, $field);
        $categories = apply_filters("acfe/flexible/modal_select_categories/name={$field['_name']}", $categories, $field);
        $categories = apply_filters("acfe/flexible/modal_select_categories/key={$field['key']}",    $categories, $field);
        
        // return
        return $categories;
        
    }
    
    
    /**
     * get_layout_categories
     *
     * @param $layout
     *
     * @return array
     */
    function get_layout_categories($layout){
        
        // get layout categories
        $categories = $layout['acfe_flexible_category'];
        
        // back-compatibility
        if(is_string($categories) && !empty($categories)){
            $categories = explode('|', $categories);
            $categories = array_map('trim', $categories);
        }
        
        // sanitize
        $categories = acf_get_array($categories);
        $categories = array_filter($categories);
        
        $storage = array();
        
        // loop categories
        foreach($categories as $category){
            
            //slugify
            $name = sanitize_title($category);
            
            // make sure category is unique
            if(!isset($storage[ $name ])){
                $storage[ $name ] = $category;
            }
            
        }
        
        // return
        return $storage;
        
    }
    
    
    /**
     * get_layout_categories_names
     *
     * @param $layout
     *
     * @return array
     */
    function get_layout_categories_names($layout){
        
        // get layout categories
        $layout_categories = $this->get_layout_categories($layout);
        $layout_categories = array_keys($layout_categories);
        
        // return
        return $layout_categories;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_select');

endif;