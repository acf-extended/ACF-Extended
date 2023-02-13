<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content_preview')):

class acfe_field_flexible_content_preview{
    
    /**
     * construct
     */
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',              array($this, 'defaults_field'), 2);
        add_filter('acfe/flexible/defaults_layout',             array($this, 'defaults_layout'), 2);
    
        add_action('acfe/flexible/render_field_settings',       array($this, 'render_field_settings'), 2);
        add_action('acfe/flexible/render_layout_settings',      array($this, 'render_layout_settings'), 15, 3);
        
        add_action('acf/render_field/type=flexible_content',    array($this, 'render_field'), 8);
        add_filter('acfe/flexible/wrapper_attributes',          array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/prepare_layout',              array($this, 'prepare_layout'), 25, 5);
        
        // Ajax
        add_action('wp_ajax_acfe/flexible/layout_preview',      array($this, 'layout_preview'));
        
    }
    
    
    /**
     * defaults_field
     *
     * @param $field
     *
     * @return mixed
     */
    function defaults_field($field){
        
        $field['acfe_flexible_layouts_templates'] = false;
        $field['acfe_flexible_layouts_previews'] = false;
        $field['acfe_flexible_layouts_placeholder'] = false;
        
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
        
        $layout['acfe_flexible_render_template'] = false;
        $layout['acfe_flexible_render_style'] = false;
        $layout['acfe_flexible_render_script'] = false;
        
        return $layout;
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        // Render
        acf_render_field_setting($field, array(
            'label'         => __('Dynamic Render', 'acfe'),
            'name'          => 'acfe_flexible_layouts_templates',
            'key'           => 'acfe_flexible_layouts_templates',
            'instructions'  => __('Render the layout using custom template, style & javascript files', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/dynamic-render" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    
        // Preview
        acf_render_field_setting($field, array(
            'label'         => __('Dynamic Preview', 'acfe'),
            'name'          => 'acfe_flexible_layouts_previews',
            'key'           => 'acfe_flexible_layouts_previews',
            'instructions'  => __('Use layouts render settings to display a dynamic preview in the administration', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/dynamic-render#dynamic-preview" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_layouts_templates',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    
        // Placholder
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Placeholder', 'acfe'),
            'name'          => 'acfe_flexible_layouts_placeholder',
            'key'           => 'acfe_flexible_layouts_placeholder',
            'instructions'  => __('Display a placeholder with an icon', 'acfe') . '. ' . '<a href="https://www.acf-extended.com/features/fields/flexible-content/advanced-settings#layouts-placeholder" target="_blank">' . __('See documentation', 'acfe') . '</a>',
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_layouts_previews',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
    }
    
    
    /**
     * render_layout_settings
     *
     * @param $flexible
     * @param $layout
     * @param $prefix
     */
    function render_layout_settings($flexible, $layout, $prefix){
        
        if(!acf_maybe_get($flexible, 'acfe_flexible_layouts_templates')){
            return;
        }
        
        // vars
        $name = $flexible['name'];
        $key = $flexible['key'];
        $l_name = $layout['name'];
        $prepend = acfe_get_setting('theme_folder') ? trailingslashit(acfe_get_setting('theme_folder')) : '';
    
        // Title
        echo '</li>';
        acf_render_field_wrap(array(
            'label' => __('Render', 'acfe'),
            'type'  => 'hidden',
            'name'  => 'acfe_flexible_render_label',
            'wrapper' => array(
                'class' => 'acfe-flexible-field-setting acfe-flexible-field-setting-row',
            )
        ), 'ul');
        echo '<li>';
        
        // Template
        $prepend = apply_filters("acfe/flexible/prepend/template",                                  $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/template/name={$name}",                     $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/template/key={$key}",                       $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/template/layout={$l_name}",                 $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/template/name={$name}&layout={$l_name}",    $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/template/key={$key}&layout={$l_name}",      $prepend, $flexible, $layout);
        
        acf_render_field_wrap(array(
            'prepend'       => $prepend,
            'name'          => 'acfe_flexible_render_template',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_render_template'],
            'placeholder'   => 'template.php',
        ), 'ul');
        
        
        // Style
        $prepend = apply_filters("acfe/flexible/prepend/style",                                     $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/style/name={$name}",                        $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/style/key={$key}",                          $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/style/layout={$l_name}",                    $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/style/name={$name}&layout={$l_name}",       $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/style/key={$key}&layout={$l_name}",         $prepend, $flexible, $layout);
        
        acf_render_field_wrap(array(
            'prepend'       => $prepend,
            'name'          => 'acfe_flexible_render_style',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_render_style'],
            'placeholder'   => 'style.css',
        ), 'ul');
        
        
        // Script
        $prepend = apply_filters("acfe/flexible/prepend/script",                                    $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/script/name={$name}",                       $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/script/key={$key}",                         $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/script/layout={$l_name}",                   $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/script/name={$name}&layout={$l_name}",      $prepend, $flexible, $layout);
        $prepend = apply_filters("acfe/flexible/prepend/script/key={$key}&layout={$l_name}",        $prepend, $flexible, $layout);
        
        acf_render_field_wrap(array(
            'prepend'       => $prepend,
            'name'          => 'acfe_flexible_render_script',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $prefix,
            'value'         => $layout['acfe_flexible_render_script'],
            'placeholder'   => 'script.js',
        ), 'ul');
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // check setting
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_templates') || !acf_maybe_get($field, 'acfe_flexible_layouts_previews')){
            return;
        }
    
        // vars
        $name = $field['_name'];
        $key = $field['key'];
    
        // vars
        global $is_preview;
        $is_preview = true;
    
        // actions
        do_action("acfe/flexible/enqueue",              $field, $is_preview);
        do_action("acfe/flexible/enqueue/name={$name}", $field, $is_preview);
        do_action("acfe/flexible/enqueue/key={$key}",   $field, $is_preview);
    
        // loop
        foreach($field['layouts'] as $layout){
        
            // Enqueue
            acfe_flexible_render_layout_enqueue($layout, $field);
        
        }
        
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
        
        if(acf_maybe_get($field, 'acfe_flexible_layouts_placeholder')){
            $wrapper['data-acfe-flexible-placeholder'] = 1;
        }
        
        if(acf_maybe_get($field, 'acfe_flexible_layouts_templates') && acf_maybe_get($field, 'acfe_flexible_layouts_previews')){
            $wrapper['data-acfe-flexible-placeholder'] = 1;
            $wrapper['data-acfe-flexible-preview'] = 1;
        }
        
        return $wrapper;
        
    }
    
    
    /**
     * prepare_layout
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed
     */
    function prepare_layout($layout, $field, $i, $value, $prefix){
        
        if(!acf_maybe_get($field, 'acfe_flexible_layouts_placeholder') && !acf_maybe_get($field, 'acfe_flexible_layouts_previews')){
            return $layout;
        }
        
        // Vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        $placeholder = array(
            'class' => 'acfe-fc-placeholder',
            'title' => __('Edit layout', 'acfe'),
        );
        
        $placeholder = apply_filters("acfe/flexible/layouts/placeholder",                               $placeholder, $layout, $field, $i, $value, $prefix);
        $placeholder = apply_filters("acfe/flexible/layouts/placeholder/name={$name}",                  $placeholder, $layout, $field, $i, $value, $prefix);
        $placeholder = apply_filters("acfe/flexible/layouts/placeholder/key={$key}",                    $placeholder, $layout, $field, $i, $value, $prefix);
        $placeholder = apply_filters("acfe/flexible/layouts/placeholder/layout={$l_name}",              $placeholder, $layout, $field, $i, $value, $prefix);
        $placeholder = apply_filters("acfe/flexible/layouts/placeholder/name={$name}&layout={$l_name}", $placeholder, $layout, $field, $i, $value, $prefix);
        $placeholder = apply_filters("acfe/flexible/layouts/placeholder/key={$key}&layout={$l_name}",   $placeholder, $layout, $field, $i, $value, $prefix);
        
        $html = false;
        
        if(!empty($value) && acf_maybe_get($field, 'acfe_flexible_layouts_previews')){
            
            ob_start();
            
            $this->layout_preview(array(
                'post_id'   => acf_get_valid_post_id(),
                'i'         => $i,
                'field_key' => $key,
                'layout'    => $l_name,
                'value'     => $value,
            ));
    
            $html = ob_get_clean();
            
            if(strlen($html) > 0){
                $placeholder['class'] .= ' acfe-fc-preview';
            }
            
        }
        
        ?>
        
        <div <?php echo acf_esc_atts($placeholder); ?>>
            <a href="#" class="button">
                <span class="dashicons dashicons-edit"></span>
            </a>
            
            <div class="acfe-fc-overlay"></div>
            <div class="acfe-flexible-placeholder -preview"><?php echo $html; ?></div>
        </div>
        
        <?php
        
        return $layout;
        
    }
    
    
    /**
     * layout_preview
     *
     * @param $options
     *
     * @return bool|null
     */
    function layout_preview($options = array()){
        
        if(empty($options)){
            
            // Options
            $options = acf_parse_args($_POST, array(
                'post_id'   => 0,
                'i'         => 0,
                'field_key' => '',
                'nonce'     => '',
                'layout'    => '',
                'value'     => array()
            ));
            
        }
        
        // Load field
        $field = acf_get_field($options['field_key']);
        if(!$field){
            return $this->return_or_die();
        }
        
        // Layout
        $instance = acf_get_field_type('flexible_content');
        $layout = $instance->get_layout($options['layout'], $field);
        
        if(!$layout){
            return $this->return_or_die();
        }
        
        // Global
        global $is_preview;
        
        // Vars
        $is_preview = true;
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        $post_id = "{$options['field_key']}_{$options['i']}";
        
        // Prepare values
        $meta = array($options['field_key'] => array(
            wp_unslash($options['value'])
        ));
        
        acfe_setup_meta($meta, $post_id, true);
        
        if(have_rows($options['field_key'])):
            while(have_rows($options['field_key'])): the_row();
                
                global $post;
                $_post = $post;
                
                // Deprecated
                do_action_deprecated("acfe/flexible/preview/name={$name}",                         array($field, $layout), '0.8.6.7');
                do_action_deprecated("acfe/flexible/preview/key={$key}",                           array($field, $layout), '0.8.6.7');
                do_action_deprecated("acfe/flexible/layout/preview/layout={$l_name}",              array($field, $layout), '0.8.6.7');
                do_action_deprecated("acfe/flexible/layout/preview/name={$name}&layout={$l_name}", array($field, $layout), '0.8.6.7');
                do_action_deprecated("acfe/flexible/layout/preview/key={$key}&layout={$l_name}",   array($field, $layout), '0.8.6.7');
                do_action_deprecated("acfe/flexible/preview",                                      array($field, $layout), '0.8.6.7');
                
                // Template
                acfe_flexible_render_layout_template($layout, $field);
                
                $post = $_post;
            
            endwhile;
        endif;
        
        acfe_reset_meta();
        
        return $this->return_or_die();
        
    }
    
    
    /**
     * return_or_die
     *
     * @return bool|void
     */
    function return_or_die(){
        
        // check ajax & make sure the action is correct
        if(wp_doing_ajax() && acf_maybe_get_POST('action') === 'acfe/flexible/layout_preview'){
            die;
        }
        
        return true;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_preview');

endif;