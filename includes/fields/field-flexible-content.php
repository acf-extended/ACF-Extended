<?php

if(!defined('ABSPATH'))
    exit;

add_action('wp_ajax_acfe/flexible/layout_preview', 'acfe_flexible_layout_preview');
function acfe_flexible_layout_preview($options = array()){
    
    if(empty($options)){
    
        // Options
        $options = acf_parse_args($_POST, array(
            'post_id'		=> 0,
            'i'				=> 0,
            'field_key'		=> '',
            'nonce'			=> '',
            'layout'		=> '',
            'value'			=> array()
        ));
    
    }
    
    // Load field
    $field = acf_get_field($options['field_key']);
    if(!$field)
        die;
    
    // Flexible
    $flexible = acf_get_field_type('flexible_content');
    
    // Layout
    $layout = $flexible->get_layout($options['layout'], $field);
    if(!$layout)
        die;
    
    // Global
    global $is_preview;
    
    // Vars
    $is_preview = true;
    $name = $field['_name'];
    $key = $field['key'];
    $l_name = $layout['name'];
    
    // Thumbnail
    $flexible->prepare_layout_thumbnail($field, $layout);
    
    // Prepare values
    $meta = array($options['field_key'] => array(
        wp_unslash($options['value'])
    ));
    
    acf_setup_meta($meta, $options['field_key'] . '_' . $options['i'], true);
    
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
    
    acf_reset_meta($options['field_key'] . '_' . $options['i']);
    
    if(wp_doing_ajax()){
        die;
    }
    
}

if(!class_exists('acfe_field_flexible_content')):

class acfe_field_flexible_content extends acf_field_flexible_content{
    
    public $flexible = '';
    
    function __construct(){
        
        parent::initialize();
        
        // Retrieve Flexible Content
        $this->flexible = acf_get_field_type('flexible_content');
        
        // Remove Inherit Actions
        remove_action('acf/render_field/type=flexible_content',                     array($this->flexible, 'render_field'), 9);
        
        // Field Action
        $this->add_field_action('acf/render_field_settings',                        array($this, 'render_field_settings'), 0);
        $this->add_action('acf/render_field',                                       array($this, 'render_layouts_settings_label'), 0);
        $this->add_action('acf/render_field',                                       array($this, 'render_layouts_settings'), 10);
        
        $this->add_field_filter('acf/validate_field',                               array($this, 'validate_field'));
        $this->add_field_filter('acf/prepare_field',                                array($this, 'prepare_field'));
        $this->add_field_filter('acfe/load_fields',                                 array($this, 'load_fields'), 10, 2);
        $this->add_field_filter('acfe/field_wrapper_attributes',                    array($this, 'wrapper_attributes'), 10, 2);
        $this->add_field_action('acf/render_field',                                 array($this, 'render_field'), 9);
        
        $this->add_field_filter('acf/load_value',                                   array($this, 'load_value_toggle'), 10, 3);
        
        // General Filters
        $this->add_filter('acfe/flexible/layouts/icons',                            array($this, 'add_layout_icons'), 10, 3);
        $this->add_filter('acfe/flexible/layouts/div',                              array($this, 'add_layout_div'), 10, 3);
        $this->add_filter('acfe/flexible/layouts/handle',                           array($this, 'add_layout_handle'), 10, 3);
        
        $this->add_filter('acf/fields/flexible_content/no_value_message',           array($this, 'add_empty_message'), 10, 2);
        $this->add_filter('acf/fields/flexible_content/layout_title',               array($this, 'add_layout_title'), 0, 4);
        
        // General Actions
        $this->add_action('wp_ajax_acfe/flexible/models',                           array($this, 'ajax_layout_model'));
        $this->add_action('wp_ajax_nopriv_acfe/flexible/models',                    array($this, 'ajax_layout_model'));
        
    }
    
    /**
     *  Field Settings
     */
    function render_field_settings($field){
        
        // Advanced settings
        acf_render_field_setting($field, array(
            'label'         => __('Advanced Flexible Content'),
            'name'          => 'acfe_flexible_advanced',
            'key'           => 'acfe_flexible_advanced',
            'instructions'  => __('Show advanced Flexible Content settings'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
        ));
        
        // Stylised button
        acf_render_field_setting($field, array(
            'label'         => __('Stylised Button'),
            'name'          => 'acfe_flexible_stylised_button',
            'key'           => 'acfe_flexible_stylised_button',
            'instructions'  => __('Better actions buttons integration'),
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
                        'field'     => 'acfe_flexible_remove_add_button',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        // Hide Empty Message
        acf_render_field_setting($field, array(
            'label'         => __('Hide Empty Message'),
            'name'          => 'acfe_flexible_hide_empty_message',
            'key'           => 'acfe_flexible_hide_empty_message',
            'instructions'  => __('Hide the empty message box'),
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
                        'field'     => 'acfe_flexible_stylised_button',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        // Empty Message
        acf_render_field_setting($field, array(
            'label'         => __('Empty Message'),
            'name'          => 'acfe_flexible_empty_message',
            'key'           => 'acfe_flexible_empty_message',
            'instructions'  => __('Text displayed when the flexible field is empty'),
            'type'          => 'text',
            'placeholder'   => __('Click the "Add Row" button below to start creating your layout'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_stylised_button',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_hide_empty_message',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
        ));
        
        // Disable Legacy Title Ajax
        acf_render_field_setting($field, array(
            'label'         => __('Disable Legacy Layout Title Ajax'),
            'name'          => 'acfe_flexible_disable_ajax_title',
            'key'           => 'acfe_flexible_disable_ajax_title',
            'instructions'  => __('Disable the native ACF Layout Title Ajax call. More informations: <a href="https://www.advancedcustomfields.com/resources/acf-fields-flexible_content-layout_title/" target="_blank">ACF documentation</a>.'),
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
        
        // Layouts thumbnails
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Thumbnails'),
            'name'          => 'acfe_flexible_layouts_thumbnails',
            'key'           => 'acfe_flexible_layouts_thumbnails',
            'instructions'  => __('Set a thumbnail for each layouts. You must save the field group to apply this setting'),
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
        
        // Layouts settings
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Settings'),
            'name'          => 'acfe_flexible_layouts_settings',
            'key'           => 'acfe_flexible_layouts_settings',
            'instructions'  => __('Choose a field group to clone and to be used as a configuration modal'),
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
        
        // Layouts ajax
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Asynchronous'),
            'name'          => 'acfe_flexible_layouts_ajax',
            'key'           => 'acfe_flexible_layouts_ajax',
            'instructions'  => __('Add layouts using Ajax method. This setting increase performance on complex Flexible Content'),
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
        
        // Layouts: Render
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Render'),
            'name'          => 'acfe_flexible_layouts_templates',
            'key'           => 'acfe_flexible_layouts_templates',
            'instructions'  => __('Set template, style & javascript files for each layouts. This setting is mandatory in order to use <code style="font-size:11px;">get_flexible()</code> function. You must save the field group to apply this setting'),
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
        
        // Layouts: Preview
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Dynamic Preview'),
            'name'          => 'acfe_flexible_layouts_previews',
            'key'           => 'acfe_flexible_layouts_previews',
            'instructions'  => __('Use layouts render settings to display a dynamic preview in the post administration'),
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
                    array(
                        'field'     => 'acfe_flexible_layouts_remove_collapse',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        // Layouts: Placholder
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Placeholder'),
            'name'          => 'acfe_flexible_layouts_placeholder',
            'key'           => 'acfe_flexible_layouts_placeholder',
            'instructions'  => __('Display a placeholder with a pencil icon, making edition easier'),
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
                        'operator'  => '==',
                        'value'     => '',
                    ),
                    array(
                        'field'     => 'acfe_flexible_layouts_remove_collapse',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        // Layouts: Title Edition
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Title Edition'),
            'name'          => 'acfe_flexible_title_edition',
            'key'           => 'acfe_flexible_title_edition',
            'instructions'  => __('Allow layout title edition'),
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
    
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
    
            // Layouts: Clone
            acf_render_field_setting($field, array(
                'label'         => __('Layouts: Clone'),
                'name'          => 'acfe_flexible_clone',
                'key'           => 'acfe_flexible_clone',
                'instructions'  => __('Allow clone layouts function'),
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
        
        }
        
        // Layouts: Copy/Paste
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Copy/Paste'),
            'name'          => 'acfe_flexible_copy_paste',
            'key'           => 'acfe_flexible_copy_paste',
            'instructions'  => __('Allow copy/paste layouts functions'),
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
    
        // Layouts: Toggle
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Toggle'),
            'name'          => 'acfe_flexible_toggle',
            'key'           => 'acfe_flexible_toggle',
            'instructions'  => __('Allow toggle layouts function'),
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
        
        // Layouts: Close Button
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Close Button'),
            'name'          => 'acfe_flexible_close_button',
            'key'           => 'acfe_flexible_close_button',
            'instructions'  => __('Display a close button to collapse the layout'),
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
                        'field'     => 'acfe_flexible_layouts_remove_collapse',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
        ));
        
        // Hide: Add Button
        acf_render_field_setting($field, array(
            'label'         => __('Hide: Add layout button'),
            'name'          => 'acfe_flexible_remove_add_button',
            'key'           => 'acfe_flexible_remove_add_button',
            'instructions'  => __('Hide all "Add layout" buttons'),
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
    
        if(acf_version_compare(acf_get_setting('version'),  '>=', '5.9')){
    
            // Hide: Duplicate Button
            acf_render_field_setting($field, array(
                'label'         => __('Hide: Duplicate layout button'),
                'name'          => 'acfe_flexible_remove_duplicate_button',
                'key'           => 'acfe_flexible_remove_duplicate_button',
                'instructions'  => __('Hide the "Duplicate layout" button'),
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
        
        }
        
        // Hide: Delete Button
        acf_render_field_setting($field, array(
            'label'         => __('Hide: Delete layout button'),
            'name'          => 'acfe_flexible_remove_delete_button',
            'key'           => 'acfe_flexible_remove_delete_button',
            'instructions'  => __('Hide the "Delete layout" button'),
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
        
        // Lock
        acf_render_field_setting($field, array(
            'label'         => __('Lock Flexible Content'),
            'name'          => 'acfe_flexible_lock',
            'key'           => 'acfe_flexible_lock',
            'instructions'  => __('Disable the drag & drop function and lock the flexible content'),
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
        
        // Modal: Edition
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Modal: Edition'),
            'name'          => 'acfe_flexible_modal_edition',
            'key'           => 'acfe_flexible_modal_edition',
            'instructions'  => __('Edit layout content in a modal'),
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
        
        // Modal: Selection
        acf_render_field_setting($field, array(
            'label'         => __('Layouts Modal: Selection'),
            'name'          => 'acfe_flexible_modal',
            'key'           => 'acfe_flexible_modal',
            'instructions'  => __('Select layouts in a modal'),
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
                        'width' => '10',
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
                    'prepend'       => __('Modal Title'),
                    'placeholder'   => 'Add Row',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '35',
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
                    'name'          => 'acfe_flexible_modal_col',
                    'key'           => 'acfe_flexible_modal_col',
                    'type'          => 'select',
                    'prepend'       => '',
                    'instructions'  => false,
                    'required'      => false,
                    'choices'       => array(
                        '1' => '1 column',
                        '2' => '2 columns',
                        '3' => '3 columns',
                        '4' => '4 columns',
                        '5' => '5 columns',
                        '6' => '6 columns',
                    ),
                    'default_value' => '4',
                    'wrapper'       => array(
                        'width' => '15',
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
                    'name'          => 'acfe_flexible_modal_categories',
                    'key'           => 'acfe_flexible_modal_categories',
                    'type'          => 'true_false',
                    'message'       => __('Categories'),
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
            ),
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
        
        // Layouts: Force State
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Force State'),
            'name'          => 'acfe_flexible_layouts_state',
            'key'           => 'acfe_flexible_layouts_state',
            'instructions'  => __('Force layouts to be collapsed or opened'),
            'type'          => 'select',
            'placeholder'   => __('Default', 'acfe'),
            'allow_null'    => true,
            'choices'       => array(
                'collapse'  => 'Collapsed',
                'open'      => 'Opened',
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_modal_edition',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
        ));
        
        // Hide: Collapse Button
        acf_render_field_setting($field, array(
            'label'         => __('Layouts: Remove Collapse'),
            'name'          => 'acfe_flexible_layouts_remove_collapse',
            'key'           => 'acfe_flexible_layouts_remove_collapse',
            'instructions'  => __('Remove collapse action'),
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
                        'field'     => 'acfe_flexible_modal_edition',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_layouts_state',
                        'operator'  => '==',
                        'value'     => 'open',
                    )
                )
            )
        ));
        
    }
    
    /**
	 *  Layout Settings Label
	 */
    function render_layouts_settings_label($field){
        
        if($field['_name'] !== 'label' || stripos($field['name'], '[layouts]') === false)
            return;
        
        echo '</li>';
        
        acf_render_field_wrap(array(
            'label' => __('Settings'),
            'type'  => 'hidden',
            'name'  => 'acfe_flexible_settings_label'
        ), 'ul');
        
        echo '<li>';
        
    }
    
    /**
	 *  Layout Settings
	 */
    function render_layouts_settings($field){
        
        if($field['_name'] !== 'max' || stripos($field['name'], '[layouts]') === false)
            return;
        
        // Prefix
        $prefix = $field['prefix'];
        
        // Black magic
        parse_str($prefix, $output);
        $keys = acfe_array_keys_r($output);
        
        // ...
        $_field_id = $keys[1];
        $_layout_key = $keys[3];
        
        // Profit!
        $flexible = acf_get_field($_field_id);
        $layout = $flexible['layouts'][$_layout_key];
        
        // Vars
        $name = $flexible['name'];
        $key = $flexible['key'];
        $l_name = $layout['name'];
        
        // Category
        if($flexible['acfe_flexible_modal'] && acf_maybe_get($flexible['acfe_flexible_modal'], 'acfe_flexible_modal_categories')){
         
	        acf_render_field_wrap(array(
		        'prepend'       => __('Category'),
		        'name'          => 'acfe_flexible_category',
		        'type'          => 'select',
		        'ui'            => 1,
		        'multiple'      => 1,
		        'allow_custom'  => 1,
		        'class'         => 'acf-fc-meta-name',
		        'prefix'        => $prefix,
		        'value'         => $layout['acfe_flexible_category'],
		        'placeholder'   => __('Layouts categories')
	        ), 'ul');
        
        }
        
        // Template
        if($flexible['acfe_flexible_layouts_templates']){
            
            // Prepend
            $prepend = acfe_get_setting('theme_folder') ? trailingslashit(acfe_get_setting('theme_folder')) : '';

            // Template
            $prepend = apply_filters("acfe/flexible/prepend/template",                                  $prepend, $flexible, $layout);
            $prepend = apply_filters("acfe/flexible/prepend/template/name={$name}",                     $prepend, $flexible, $layout);
            $prepend = apply_filters("acfe/flexible/prepend/template/key={$key}",                       $prepend, $flexible, $layout);
            $prepend = apply_filters("acfe/flexible/prepend/template/layout={$l_name}",                 $prepend, $flexible, $layout);
            $prepend = apply_filters("acfe/flexible/prepend/template/name={$name}&layout={$l_name}",    $prepend, $flexible, $layout);
            $prepend = apply_filters("acfe/flexible/prepend/template/key={$key}&layout={$l_name}",      $prepend, $flexible, $layout);
            
            acf_render_field_wrap(array(
                'label'         => __('Render'),
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
        
        // Thumbnail
        if($flexible['acfe_flexible_layouts_thumbnails']){
            
            acf_render_field_wrap(array(
                'label'         => __('Thumbnail'),
                'name'          => 'acfe_flexible_thumbnail',
                'type'          => 'image',
                'class'         => '',
                'prefix'        => $prefix,
                'value'         => $layout['acfe_flexible_thumbnail'],
                'return_format' => 'array',
                'preview_size'  => 'thumbnail',
                'library'       => 'all',
            ), 'ul');
        
        }
        
        // Settings
        if($flexible['acfe_flexible_layouts_settings']){
            
            acf_disable_filters();
            
            $choices = array();
            
            $field_groups = acf_get_field_groups();
            if(!empty($field_groups)){
                
                foreach($field_groups as $field_group){
                    
                    $choices[$field_group['key']] = $field_group['title'];
                    
                }
                
            }
            
            acf_enable_filters();
            
            echo '</li>';
            
            acf_render_field_wrap(array(
                'label' => __('Settings modal'),
                'type'  => 'hidden',
                'name'  => 'acfe_flexible_settings_label'
            ), 'ul');
            
            echo '<li>';
            
            acf_render_field_wrap(array(
                'label'         => '',
                'name'          => 'acfe_flexible_settings',
                'type'          => 'select',
                'class'         => '',
                'prefix'        => $prefix,
                'value'         => $layout['acfe_flexible_settings'],
                'choices'       => $choices,
                'wrapper'       => array(
                    'width' => '33'
                ),
                'allow_null'    => 1,
                'multiple'      => 1,
                'ui'            => 1,
                'ajax'          => 0,
                'return_format' => 0,
            ), 'ul');
            
            acf_render_field_wrap(array(
                'label'         => '',
                'name'          => 'acfe_flexible_settings_size',
                'type'          => 'select',
                'class'         => '',
                'prefix'        => $prefix,
                'value'         => $layout['acfe_flexible_settings_size'],
                'choices'       => array(
                    'small'     => 'Small',
                    'medium'    => 'Medium',
                    'large'     => 'Large',
                    'xlarge'    => 'Extra Large',
                    'full'      => 'Full',
                ),
                'wrapper'       => array(
                    'width' => '33'
                ),
                'default_value' => 'medium',
                'allow_null'    => 0,
                'multiple'      => 0,
                'ui'            => 0,
                'ajax'          => 0,
                'return_format' => 0,
            ), 'ul');
            
        }
        
    }
    
    /**
	 *  Ajax Layout Model
	 */
    function ajax_layout_model(){
        
        // options
        $options = acf_parse_args($_POST, array(
            'field_key' => '',
            'layout'    => '',
        ));
        
        $field = acf_get_field($options['field_key']);
        if(!$field)
            die;
        
        $field = acf_prepare_field($field);
        
        foreach($field['layouts'] as $k => $layout){
            
            if($layout['name'] !== $options['layout'])
                continue;
            
            $this->render_layout($field, $layout, 'acfcloneindex', array());
            
            die;
            
        }
        
        die;
        
    }
    
    /**
	 *  Validate Field
	 */
    function validate_field($field){
        
        $defaults = array(
            'acfe_flexible_advanced'                => 0,
            'acfe_flexible_stylised_button'         => 0,
            'acfe_flexible_hide_empty_message'      => 0,
            'acfe_flexible_empty_message'           => '',
            'acfe_flexible_disable_ajax_title'      => 0,
            
            'acfe_flexible_layouts_thumbnails'      => 0,
            'acfe_flexible_layouts_settings'        => 0,
            'acfe_flexible_layouts_ajax'            => 0,
            
            'acfe_flexible_layouts_templates'       => 0,
            'acfe_flexible_layouts_previews'        => 0,
            'acfe_flexible_layouts_placeholder'     => 0,
            
            'acfe_flexible_title_edition'           => 0,
            'acfe_flexible_clone'                   => 0,
            'acfe_flexible_copy_paste'              => 0,
            'acfe_flexible_toggle'                  => 0,
            'acfe_flexible_close_button'            => 0,
            'acfe_flexible_remove_add_button'       => 0,
            'acfe_flexible_remove_duplicate_button' => 0,
            'acfe_flexible_remove_delete_button'    => 0,
            'acfe_flexible_lock'                    => 0,
            
            'acfe_flexible_modal_edition'           => 0,
            'acfe_flexible_modal'                   => array(),

            'acfe_flexible_layouts_state'           => '',
            'acfe_flexible_layouts_remove_collapse' => 0,
        );
        
        $defaults['acfe_flexible_modal'] = wp_parse_args($defaults['acfe_flexible_modal'], array(
            'acfe_flexible_modal_enabled'   => false,
            'acfe_flexible_modal_title'     => false,
            'acfe_flexible_modal_col'       => '4',
            'acfe_flexible_modal_categories'=> false,
        ));
        
        $field = wp_parse_args($field, $defaults);
        
        foreach($field['layouts'] as &$layout){
            
            $layout = wp_parse_args($layout, array(
                'acfe_flexible_thumbnail'       => false,
                'acfe_flexible_category'        => false,
                'acfe_flexible_render_template' => false,
                'acfe_flexible_render_style'    => false,
                'acfe_flexible_render_script'   => false,
                'acfe_flexible_settings'        => false,
                'acfe_flexible_settings_size'   => 'medium',
            ));
            
        }
        
        if(!$field['acfe_flexible_advanced']){
            
            foreach($defaults as $default => $val){
                
                if(($default === 'acfe_flexible_modal' && empty($field['acfe_flexible_modal']['acfe_flexible_modal_enabled'])) || empty($field[$default]))
                    continue;
                
                $field['acfe_flexible_advanced'] = 1;
                break;
                
            }
            
        }
        
        return $field;
        
    }
    
    /**
	 *  Prepare Field
	 */
    function prepare_field($field){
        
        foreach($field['layouts'] as &$layout){
            
            // vars
            $div = '';
            
            $span = array(
                'class' => 'no-thumbnail'
            );
            
            // Category
            if($layout['acfe_flexible_category'] && $field['acfe_flexible_modal']['acfe_flexible_modal_categories']){

                $categories = $layout['acfe_flexible_category'];
                
                // Compatibility
                if(is_string($categories)){
                 
	                $categories = explode('|', $categories);
	                $categories = array_map('trim', $categories);
	                
                }

                $span['data-acfe-flexible-category'] = $categories;
                
            }
            
            // Thumbnail
            if($field['acfe_flexible_layouts_thumbnails']){
                
                // unset span class
                unset($span['class']);
                
                $div = array(
                    'class' => 'acfe-flexible-layout-thumbnail',
                );
                
                // Modal disabled
                if(!$field['acfe_flexible_modal']['acfe_flexible_modal_enabled']){
                    
                    $div['class'] .= ' acfe-flexible-layout-thumbnail-no-modal';
                    
                }
                
                // Thumbnail
                $thumbnail = $this->prepare_layout_thumbnail($field, $layout);
                
                $has_thumbnail = false;
                
                if(!empty($thumbnail)){
                    
                    $has_thumbnail = true;
                    $div['style'] = 'background-image:url(' . $thumbnail . ');';
                
                    // Attachment ID
                    if(is_numeric($thumbnail)){
                        
                        $has_thumbnail = false;
                        
                        if($thumbnail_src = wp_get_attachment_url($thumbnail)){
                            
                            $has_thumbnail = true;
                            $div['style'] = 'background-image:url(' . $thumbnail_src . ');';
                            
                        }
                    
                    }
                
                }
                
                // Thumbnail not found
                if(!$has_thumbnail){
                    
                    $div['class'] .= ' acfe-flexible-layout-thumbnail-not-found';
                    
                }
                
                $div = '<div ' . acf_esc_atts($div) . '></div>';
                
            }
            
            $layout['label'] = $div . '<span ' . acf_esc_atts($span) . '>' . $layout['label'] . '</span>';
            
        }
        
        return $field;
        
    }
    
    function prepare_layout_thumbnail($field, $layout){
    
        // Vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        $thumbnail = &$layout['acfe_flexible_thumbnail'];
    
        // Flexible Thumbnails
        $thumbnail = apply_filters("acfe/flexible/thumbnail",                                       $thumbnail, $field, $layout);
        $thumbnail = apply_filters("acfe/flexible/thumbnail/name={$name}",                          $thumbnail, $field, $layout);
        $thumbnail = apply_filters("acfe/flexible/thumbnail/key={$key}",                            $thumbnail, $field, $layout);
        $thumbnail = apply_filters("acfe/flexible/thumbnail/layout={$l_name}",                      $thumbnail, $field, $layout);
        $thumbnail = apply_filters("acfe/flexible/thumbnail/name={$name}&layout={$l_name}",         $thumbnail, $field, $layout);
        $thumbnail = apply_filters("acfe/flexible/thumbnail/key={$key}&layout={$l_name}",           $thumbnail, $field, $layout);
    
        // Deprecated
        $thumbnail = apply_filters_deprecated("acfe/flexible/layout/thumbnail/layout={$l_name}",               array($thumbnail, $field, $layout), '0.8.6.7', "acfe/flexible/thumbnail/layout={$l_name}");
        $thumbnail = apply_filters_deprecated("acfe/flexible/layout/thumbnail/name={$name}&layout={$l_name}",  array($thumbnail, $field, $layout), '0.8.6.7', "acfe/flexible/thumbnail/name={$name}&layout={$l_name}");
        $thumbnail = apply_filters_deprecated("acfe/flexible/layout/thumbnail/key={$key}&layout={$l_name}",    array($thumbnail, $field, $layout), '0.8.6.7', "acfe/flexible/thumbnail/key={$key}&layout={$l_name}");
        
        return $thumbnail;
        
    }
    
    /**
	 *  Load Fields
	 */
    function load_fields($fields, $field){
        
        if(acfe_is_admin_screen())
            return $fields;
        
        // check layouts
        if(empty($field['layouts']))
            return $fields;
        
        // settings
        $has_settings       = acf_maybe_get($field, 'acfe_flexible_layouts_settings');
        $has_title_edition  = acf_maybe_get($field, 'acfe_flexible_title_edition');
        $has_toggle         = acf_maybe_get($field, 'acfe_flexible_toggle');
        
        // check settings
        if(!$has_settings && !$has_title_edition && !$has_toggle)
            return $fields;
        
        foreach($field['layouts'] as $layout_key => $layout){
            
            // Settings
            if($has_settings){
                
                if($settings_keys = acf_maybe_get($layout, 'acfe_flexible_settings')){
                    
                    // force array
                    $settings_keys = acf_get_array($settings_keys);
                    
                    // style
                    $field_group_style = 'row';
                    $field_group = acf_get_field_group($settings_keys[0]);
                    
                    if(!empty($field_group)){
                        
                        $field_group_style = $field_group['label_placement'] === 'left' ? 'row' : 'block';
                        
                    }
                    
                    acf_add_local_field(array(
                        'label'                 => false,
                        'key'                   => 'field_' . $layout['key'] . '_settings',
                        'name'                  => 'layout_settings',
                        'type'                  => 'clone',
                        'clone'                 => $settings_keys,
                        'display'               => 'group',
                        'acfe_seamless_style'   => true,
                        'layout'                => $field_group_style,
                        'prefix_label'          => 0,
                        'prefix_name'           => 1,
                        'parent_layout'         => $layout['key'],
                        'parent'                => $field['key']
                    ));
                    
                    $clone = acf_get_field('field_' . $layout['key'] . '_settings');
                    
                    array_unshift($fields, $clone);
                
                }
                
            }
            
            // Title Edition
            if($has_title_edition){
                
                acf_add_local_field(array(
                    'label'                 => false,
                    'key'                   => 'field_' . $layout['key'] . '_title',
                    'name'                  => 'acfe_flexible_layout_title',
                    'type'                  => 'text',
                    'required'              => false,
                    'maxlength'             => false,
                    'default_value'         => $layout['label'],
                    'placeholder'           => $layout['label'],
                    'parent_layout'         => $layout['key'],
                    'parent'                => $field['key']
                ));
                
                $title = acf_get_field('field_' . $layout['key'] . '_title');
                
                array_unshift($fields, $title);
            
            }
            
            // Toggle
            if($has_toggle){
                
                acf_add_local_field(array(
                    'label'                 => false,
                    'key'                   => 'field_' . $layout['key'] . '_toggle',
                    'name'                  => 'acfe_flexible_toggle',
                    'type'                  => 'acfe_hidden',
                    'required'              => false,
                    'default_value'         => false,
                    'parent_layout'         => $layout['key'],
                    'parent'                => $field['key']
                ));
                
                $toggle = acf_get_field('field_' . $layout['key'] . '_toggle');
                
                array_unshift($fields, $toggle);
            
            }
            
        }
        
        return $fields;
        
    }
    
    /**
	 *  Wrapper Attributes
	 */
    function wrapper_attributes($wrapper, $field){
        
        // Stylised button
        if($field['acfe_flexible_stylised_button']){
            
            $wrapper['data-acfe-flexible-stylised-button'] = 1;
            
        }
        
        // Hide Empty Message
        if($field['acfe_flexible_hide_empty_message'] || $field['acfe_flexible_stylised_button']){
            
            $wrapper['data-acfe-flexible-hide-empty-message'] = 1;
            
        }
        
        // Ajax
        if($field['acfe_flexible_layouts_ajax']){
            
            $wrapper['data-acfe-flexible-ajax'] = 1;
            
        }
        
        // Modal: Edition
        if($field['acfe_flexible_modal_edition']){
            
            $wrapper['data-acfe-flexible-modal-edition'] = 1;
        
        }
        
        // Modal: Selection
        if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_enabled'])){
            
            $wrapper['data-acfe-flexible-modal'] = 1;
            
            // Columns
            if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_col']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_col']))
                $wrapper['data-acfe-flexible-modal-col'] = $field['acfe_flexible_modal']['acfe_flexible_modal_col'];
            
            // Title
            if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_title']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_title']))
                $wrapper['data-acfe-flexible-modal-title'] = $field['acfe_flexible_modal']['acfe_flexible_modal_title'];
        
        }
        
        // Layouts: Title Edition
        if($field['acfe_flexible_title_edition']){
            
            $wrapper['data-acfe-flexible-title-edition'] = 1;
            
        }
        
        // Layouts: Close Button
        if($field['acfe_flexible_close_button']){
            
            $wrapper['data-acfe-flexible-close-button'] = 1;
            
        }
        
        // Layouts: Copy/paste
        if($field['acfe_flexible_copy_paste']){
            
            $wrapper['data-acfe-flexible-copy-paste'] = 1;
            
        }
        
        // Layouts: Toggle
        if($field['acfe_flexible_toggle']){
            
            $wrapper['data-acfe-flexible-toggle'] = 1;
            
        }
        
        // Layouts: State
        if(!$field['acfe_flexible_modal_edition']){
            
            // Open
            if($field['acfe_flexible_layouts_state'] === 'open'){
                
                $wrapper['data-acfe-flexible-open'] = 1;
                
            }
        
        }
        
        // Layouts Placeholder
        if($field['acfe_flexible_layouts_placeholder']){
            
            $wrapper['data-acfe-flexible-placeholder'] = 1;
            
        }
        
        // Layouts Previews
        if($field['acfe_flexible_layouts_templates'] && $field['acfe_flexible_layouts_previews']){
            
            $wrapper['data-acfe-flexible-preview'] = 1;
            
        }
        
        // Placeholder Icon
        $placeholder_icon = false;
        $placeholder_icon = apply_filters('acfe/flexible/placeholder/icon',                          $placeholder_icon, $field);
        $placeholder_icon = apply_filters('acfe/flexible/placeholder/icon/name=' . $field['_name'],  $placeholder_icon, $field);
        $placeholder_icon = apply_filters('acfe/flexible/placeholder/icon/key=' . $field['key'],     $placeholder_icon, $field);
        
        if(!empty($placeholder_icon)){
            
            $wrapper['data-acfe-flexible-placeholder-icon'] = $placeholder_icon;
            
        }
        
        // Lock sortable
        $lock_sortable = $field['acfe_flexible_lock'];
        
        $lock_sortable = apply_filters('acfe/flexible/lock',                          $lock_sortable, $field);
        $lock_sortable = apply_filters('acfe/flexible/lock/name=' . $field['_name'],  $lock_sortable, $field);
        $lock_sortable = apply_filters('acfe/flexible/lock/key=' . $field['key'],     $lock_sortable, $field);
        
        if($lock_sortable){
            
            $wrapper['data-acfe-flexible-lock'] = 1;
            
        }
        
        // Remove ajax 'layout_title' call
        $remove_ajax_title = $field['acfe_flexible_disable_ajax_title'];
        
        $remove_ajax_title = apply_filters('acfe/flexible/remove_ajax_title',                           $remove_ajax_title, $field);
        $remove_ajax_title = apply_filters('acfe/flexible/remove_ajax_title/name=' . $field['_name'],   $remove_ajax_title, $field);
        $remove_ajax_title = apply_filters('acfe/flexible/remove_ajax_title/key=' . $field['key'],      $remove_ajax_title, $field);
        
        if($remove_ajax_title){
            
            $wrapper['data-acfe-flexible-remove-ajax-title'] = 1;
            
        }
        
        return $wrapper;
        
    }
    
    /**
	 *  Render Field
	 */
    function render_field($field){
        
        // Preview: Enqueue
        if($field['acfe_flexible_layouts_templates'] && $field['acfe_flexible_layouts_previews']){
        
            // Vars
            global $is_preview;
            $is_preview = true;
            
            // Actions
            do_action('acfe/flexible/enqueue',                          $field, $is_preview);
            do_action('acfe/flexible/enqueue/name=' . $field['_name'],  $field, $is_preview);
            do_action('acfe/flexible/enqueue/key=' . $field['key'],     $field, $is_preview);
            
            // Layouts Previews
            foreach($field['layouts'] as $layout_key => $layout){
                
                // Render: Enqueue
                acfe_flexible_render_layout_enqueue($layout, $field);
                
            }
        
        }
        
        // Remove actions
        $remove_actions = false;
        $remove_actions = apply_filters('acfe/flexible/remove_actions',                           $remove_actions, $field);
        $remove_actions = apply_filters('acfe/flexible/remove_actions/name=' . $field['_name'],   $remove_actions, $field);
        $remove_actions = apply_filters('acfe/flexible/remove_actions/key=' . $field['key'],      $remove_actions, $field);
        
        // defaults
        if(empty($field['button_label'])){
        
            $field['button_label'] = $this->defaults['button_label'];
            
        }
        
        // sort layouts into names
        $layouts = array();
        
        foreach($field['layouts'] as $k => $layout){
        
            $layouts[$layout['name']] = $layout;
            
        }
        
        // vars
        $div = array(
            'class'		=> 'acf-flexible-content',
            'data-min'	=> $field['min'],
            'data-max'	=> $field['max']
        );
        
        // empty
        if(empty($field['value'])){
            $div['class'] .= ' -empty';
        }
        
        // no value message
        $no_value_message = __('Click the "%s" button below to start creating your layout','acf');
        $no_value_message = apply_filters('acf/fields/flexible_content/no_value_message', $no_value_message, $field);

    ?>
    <div <?php acf_esc_attr_e( $div ); ?>>

        <?php acf_hidden_input(array('name' => $field['name'])); ?>

        <div class="no-value-message">
            <?php printf($no_value_message, $field['button_label']); ?>
        </div>

        <div class="clones">
            <?php foreach($layouts as $layout):
            
                // Ajax
                if($field['acfe_flexible_layouts_ajax']){
                
                    $div = array(
                        'class'			=> 'layout acf-clone',
                        'data-id'		=> 'acfcloneindex',
                        'data-layout'	=> $layout['name']
                    );
                    
                    echo '<div ' . acf_esc_attr($div) . '></div>';
                
                // No ajax
                }else{
                    
                    $this->render_layout($field, $layout, 'acfcloneindex', array());
                    
                }
                
            endforeach; ?>
        </div>

        <div class="values">
            <?php if(!empty($field['value'])): 
                
                foreach($field['value'] as $i => $value):
                    
                    // validate
                    if(empty($layouts[$value['acf_fc_layout']]))
                        continue;
                    
                    
                    // render
                    $this->render_layout($field, $layouts[ $value['acf_fc_layout'] ], $i, $value);
                    
                endforeach;
                
            endif; ?>
        </div>

        <?php 
        
        if(!$remove_actions && !$field['acfe_flexible_remove_add_button']){
            
            $button = array(
                'class'     => 'acf-button button',
                'href'      => '#',
                'data-name' => 'add-layout',
            );
            
            if(!$field['acfe_flexible_stylised_button']){
                
                $button['class'] .= ' button-primary';
                
            }
            
            if($field['acfe_flexible_stylised_button']){ ?>
                <div class="acfe-flexible-stylised-button">
            <?php } ?>
            
            <div class="acf-actions">
                <a <?php echo acf_esc_attr($button); ?>><?php echo $field['button_label']; ?></a>
                
                <?php
                
                $secondary_actions = array();
                
                if($field['acfe_flexible_copy_paste']){
                    
                    $secondary_actions['copy'] = '<a href="#" data-acfe-flexible-control-action="copy">' . __('Copy layouts', 'acfe') . '</a>';
                    $secondary_actions['paste'] = '<a href="#" data-acfe-flexible-control-action="paste">' . __('Paste layouts', 'acfe') . '</a>';
                    
                }
                
                $secondary_actions = apply_filters('acfe/flexible/secondary_actions',                           $secondary_actions, $field);
                $secondary_actions = apply_filters('acfe/flexible/secondary_actions/name=' . $field['_name'],   $secondary_actions, $field);
                $secondary_actions = apply_filters('acfe/flexible/secondary_actions/key=' . $field['key'],      $secondary_actions, $field);
                
                ?>
                
                <?php if(!empty($secondary_actions)){ ?>
                
                    <?php
                    
                    $button_secondary = array(
                        'class'     => 'button',
                        'style'     => 'padding-left:5px;padding-right:5px; margin-left:3px;',
                        'href'      => '#',
                        'data-name' => 'acfe-flexible-control-button',
                    );
                    
                    if(!$field['acfe_flexible_stylised_button']){
                        
                        $button_secondary['class'] .= ' button-primary';
                        
                    }
                    ?>
                
                    <a <?php echo acf_esc_attr($button_secondary); ?>>
                       <span class="dashicons dashicons-arrow-down-alt2" style="vertical-align:text-top;width:auto;height:auto;font-size:13px;line-height:20px;"></span>
                    </a>
                    
                    <script type="text-html" class="tmpl-acfe-flexible-control-popup">
                        <ul>
                            <?php foreach($secondary_actions as $secondary_action){ ?>
                                <li><?php echo $secondary_action; ?></li>
                            <?php } ?>
                        </ul>
                    </script>
                
                <?php } ?>
                
            </div>
            
            <?php if($field['acfe_flexible_stylised_button']){ ?>
                </div>
            <?php } ?>

            <script type="text-html" class="tmpl-popup">
                <ul>
                <?php foreach( $layouts as $layout ): 
                    
                    $atts = array(
                        'href'			=> '#',
                        'data-layout'	=> $layout['name'],
                        'data-min' 		=> $layout['min'],
                        'data-max' 		=> $layout['max'],
                    );
                    
                    ?><li><a <?php acf_esc_attr_e($atts); ?>><?php echo $layout['label']; ?></a></li><?php 
                
                endforeach; ?>
                </ul>
            </script>
        
        <?php } ?>

    </div>
    <?php
    
    }
    
    /**
	 *  Render Layout
	 */
	function render_layout($field, $layout, $i, $value){
        
		// vars
		$sub_fields = $layout['sub_fields'];
		$id = ($i === 'acfcloneindex') ? 'acfcloneindex' : "row-$i";
		$prefix = $field['name'] . '[' . $id .  ']';
		
		// div
		$div = array(
			'class'			=> 'layout',
			'data-id'		=> $id,
			'data-layout'	=> $layout['name']
		);
		
		// is clone?
		if(!is_numeric($i)){
			
			$div['class'] .= ' acf-clone';
			
		}
		
        $div = apply_filters('acfe/flexible/layouts/div',                                                         $div, $layout, $field);
        $div = apply_filters('acfe/flexible/layouts/div/name=' . $field['_name'],                                 $div, $layout, $field);
        $div = apply_filters('acfe/flexible/layouts/div/key=' . $field['key'],                                    $div, $layout, $field);
        $div = apply_filters('acfe/flexible/layouts/div/name=' . $field['_name'] . '&layout=' . $layout['name'],  $div, $layout, $field);
        $div = apply_filters('acfe/flexible/layouts/div/key=' . $field['key'] . '&layout=' . $layout['name'],     $div, $layout, $field);
        
        // handle
        $handle = array(
            'class'     => 'acf-fc-layout-handle',
            'title'     => __('Drag to reorder','acf'),
            'data-name' => 'collapse-layout',
        );
        
        if($field['acfe_flexible_layouts_remove_collapse']){
            
            unset($handle['data-name']);
            
        }
        
        $handle = apply_filters('acfe/flexible/layouts/handle',                                                         $handle, $layout, $field);
        $handle = apply_filters('acfe/flexible/layouts/handle/name=' . $field['_name'],                                 $handle, $layout, $field);
        $handle = apply_filters('acfe/flexible/layouts/handle/key=' . $field['key'],                                    $handle, $layout, $field);
        $handle = apply_filters('acfe/flexible/layouts/handle/name=' . $field['_name'] . '&layout=' . $layout['name'],  $handle, $layout, $field);
        $handle = apply_filters('acfe/flexible/layouts/handle/key=' . $field['key'] . '&layout=' . $layout['name'],     $handle, $layout, $field);
		
        // title
		$title = $this->get_layout_title($field, $layout, $i, $value);
        
		// remove row
		reset_rows();
        
        ?>
        <div <?php echo acf_esc_attr($div); ?>>
                    
            <?php acf_hidden_input(array( 'name' => $prefix.'[acf_fc_layout]', 'value' => $layout['name'] )); ?>
            
            <div <?php echo acf_esc_attr($handle); ?>><?php echo $title; ?></div>
            
            <?php 
            
            // Title Edition
            $this->render_layout_title_edition($layout, $sub_fields, $value, $field, $prefix);

            // Toggle
            $this->render_layout_toggle($layout, $sub_fields, $value, $field, $prefix);
            
            // Icons
            $this->render_layout_icons($layout, $field);
            
            // Placeholder
            $this->render_layout_placeholder($value, $layout, $field, $i);
            
            
            add_filter('acf/prepare_field/type=wysiwyg', array($this, 'field_editor_delay'));
            
                // Layouts settings
                $this->render_layout_settings($layout, $sub_fields, $value, $field, $prefix);
            
                // Layouts fields
                $this->render_layout_fields($layout, $sub_fields, $value, $field, $prefix);
            
            remove_filter('acf/prepare_field/type=wysiwyg', array($this, 'field_editor_delay'));
            
            ?>

        </div>
        <?php
		
	}
    
    /**
	 *  Render Title Edition
	 */
    function render_layout_title_edition($layout, &$sub_fields, $value, $field, $prefix){
        
        if(!$field['acfe_flexible_title_edition'])
            return false;
        
        if(empty($sub_fields))
            return false;
        
        $title_key = false;
        
        foreach($sub_fields as $sub_key => $sub_field){
            
            if($sub_field['name'] !== 'acfe_flexible_layout_title')
                continue;
            
            // Remove other potential duplicate
            if($title_key !== false){
                
                unset($sub_fields[$sub_key]);
                
                continue;
                
            }
            
            $title_key = $sub_key;
            
        }
        
        if($title_key === false)
            return false;
        
        // Extract
        $title = acf_extract_var($sub_fields, $title_key);
        
        // Reset keys
        $sub_fields = array_values($sub_fields);
        
        // add value
        if( isset($value[ $title['key'] ]) ) {
            
            // this is a normal value
            $title['value'] = $value[ $title['key'] ];
            
        } elseif( isset($title['default_value']) ) {
            
            // no value, but this sub field has a default value
            $title['value'] = $title['default_value'];
            
        }
        
        // update prefix to allow for nested values
        $title['prefix'] = $prefix;
        
        $title['class'] = 'acfe-flexible-control-title';
        $title['data-acfe-flexible-control-title-input'] = 1;
        
        $title = acf_validate_field($title);
        $title = acf_prepare_field($title);
        
        $input_attrs = array();
        foreach( array( 'type', 'id', 'class', 'name', 'value', 'placeholder', 'maxlength', 'pattern', 'readonly', 'disabled', 'required', 'data-acfe-flexible-control-title-input' ) as $k ) {
            
            if( isset($title[ $k ]) ) {
                $input_attrs[ $k ] = $title[ $k ];
            }
            
        }
        
        // render input
        echo acf_get_text_input(acf_filter_attrs($input_attrs));
        
    }
    
    /**
     *  Render Toggle
     */
    function render_layout_toggle($layout, &$sub_fields, $value, $field, $prefix){
        
        if(!$field['acfe_flexible_toggle'])
            return false;
        
        if(empty($sub_fields))
            return false;
        
        $toggle_key = false;
        
        foreach($sub_fields as $sub_key => $sub_field){
            
            if($sub_field['name'] !== 'acfe_flexible_toggle')
                continue;
            
            // Remove other potential duplicate
            if($toggle_key !== false){
                
                unset($sub_fields[$sub_key]);
                
                continue;
                
            }
    
            $toggle_key = $sub_key;
            
        }
        
        if($toggle_key === false)
            return false;
        
        // Extract
        $toggle = acf_extract_var($sub_fields, $toggle_key);
        
        // Reset keys
        $sub_fields = array_values($sub_fields);
        
        // add value
        if( isset($value[ $toggle['key'] ]) ) {
            
            // this is a normal value
            $toggle['value'] = $value[ $toggle['key'] ];
            
        }
        
        // update prefix to allow for nested values
        $toggle['prefix'] = $prefix;
    
        $toggle['class'] = 'acfe-flexible-layout-toggle';
    
        $toggle = acf_validate_field($toggle);
        $toggle = acf_prepare_field($toggle);
        
        $input_attrs = array();
        foreach(array('type', 'id', 'class', 'name', 'value') as $k){
            
            if(isset($toggle[ $k ])){
                $input_attrs[ $k ] = $toggle[ $k ];
            }
            
        }
        
        // render input
        echo acf_get_hidden_input(acf_filter_attrs($input_attrs));
        
    }
    
    /**
	 *  Render Layout Icons
	 */
    function render_layout_icons($layout, $field){
    
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
    
            // icons
            $icons = array(
                'add'       => '<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" title="' . __('Add layout','acf') . '"></a>',
                'delete'    => '<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="' . __('Remove layout','acf') . '"></a>',
                'collapse'  => '<a class="acf-icon -collapse small acf-js-tooltip" href="#" data-name="collapse-layout" title="' . __('Click to toggle','acf') . '"></a>'
            );
        
        }else{
    
            // icons
            $icons = array(
                'add'       => '<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" title="' . __('Add layout','acf') . '"></a>',
                'duplicate' => '<a class="acf-icon -duplicate small light acf-js-tooltip" href="#" data-name="duplicate-layout" title="' . __('Duplicate layout','acf') . '"></a>',
                'delete'    => '<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="' . __('Remove layout','acf') . '"></a>',
                'collapse'  => '<a class="acf-icon -collapse small acf-js-tooltip" href="#" data-name="collapse-layout" title="' . __('Click to toggle','acf') . '"></a>'
            );
            
        }
        
        $icons = apply_filters('acfe/flexible/layouts/icons',                                                         $icons, $layout, $field);
        $icons = apply_filters('acfe/flexible/layouts/icons/name=' . $field['_name'],                                 $icons, $layout, $field);
        $icons = apply_filters('acfe/flexible/layouts/icons/key=' . $field['key'],                                    $icons, $layout, $field);
        $icons = apply_filters('acfe/flexible/layouts/icons/name=' . $field['_name'] . '&layout=' . $layout['name'],  $icons, $layout, $field);
        $icons = apply_filters('acfe/flexible/layouts/icons/key=' . $field['key'] . '&layout=' . $layout['name'],     $icons, $layout, $field);
        
        if(!empty($icons)){ ?>
        
            <div class="acf-fc-layout-controls">
            
                <?php foreach($icons as $icon){ ?>
                
                    <?php echo $icon; ?>
                    
                <?php } ?>
                
            </div>
            
        <?php }
        
    }
    
    /**
	 *  Render Layout Placeholder
	 */
    function render_layout_placeholder($value, $layout, $field, $i){
        
        if(!$field['acfe_flexible_layouts_placeholder'] && !$field['acfe_flexible_layouts_previews'])
            return false;
        
        $placeholder = array(
            'class' => 'acfe-fc-placeholder',
            'title' => __('Edit layout', 'acfe'),
        );

        if(!$field['acfe_flexible_modal_edition'] && $field['acfe_flexible_layouts_state'] !== 'collapse'){
            
            $placeholder['class'] .= ' acf-hidden';
            
        }
        
        $preview_html = false;
        
        if($field['acfe_flexible_layouts_previews'] && !empty($value)){
            
            ob_start();
            
            acfe_flexible_layout_preview(array(
                'post_id'   => acf_get_valid_post_id(),
                'i'         => $i,
                'field_key' => $field['key'],
                'layout'    => $layout['name'],
                'value'     => $value,
            ));
            
            $preview_html = ob_get_clean();
            
            if(strlen($preview_html) > 0){
                
                $placeholder['class'] .= ' acfe-fc-preview';
                
            }
            
        }
        
        if($field['acfe_flexible_modal_edition']){
            
            $placeholder['data-action'] = 'acfe-flexible-modal-edit';
            
        }
        
        ?>
        
        <div <?php echo acf_esc_attr($placeholder); ?>>
        
            <a href="#" class="button">
                <span class="dashicons dashicons-edit"></span>
            </a>
            
            <div class="acfe-fc-overlay"></div>
            
            <div class="acfe-flexible-placeholder -preview">
                <?php echo $preview_html; ?>
            </div>
            
        </div>
        
        <?php
        
    }
    
    /**
	 *  Render Layout Settings
	 */
    function render_layout_settings($layout, &$sub_fields, $value, $field, $prefix){
        
        if(!$field['acfe_flexible_layouts_settings'])
            return false;
        
        if(empty($sub_fields))
            return false;
        
        $setting_key = false;
        
        foreach($sub_fields as $sub_key => $sub_field){
            
            if($sub_field['name'] !== 'layout_settings')
                continue;
            
            // Remove other potential duplicate
            if($setting_key !== false){
                
                unset($sub_fields[$sub_key]);
                
                continue;
                
            }
            
            $setting_key = $sub_key;
            
        }
        
        // Not found
        if($setting_key === false)
            return false;
        
        // Extract
        $sub_field = acf_extract_var($sub_fields, $setting_key);
        
        // Reset keys
        $sub_fields = array_values($sub_fields);
        
        $size = 'medium';
        if($layout['acfe_flexible_settings_size']){
            
            $size = $layout['acfe_flexible_settings_size'];
            
        }
        
        ?>
        
        <div class="acfe-modal -settings -<?php echo $size; ?>">
        <div class="acfe-modal-wrapper">
        <div class="acfe-modal-content">
        
            <div class="acf-fields -top">
            
                <?php
                    
                // add value
                if( isset($value[ $sub_field['key'] ]) ) {
                    
                    // this is a normal value
                    $sub_field['value'] = $value[ $sub_field['key'] ];
                    
                } elseif( isset($sub_field['default_value']) ) {
                    
                    // no value, but this sub field has a default value
                    $sub_field['value'] = $sub_field['default_value'];
                    
                }
                
                // update prefix to allow for nested values
                $sub_field['prefix'] = $prefix;
                
                // render
                acf_render_field_wrap($sub_field, 'div');
                
                ?>
            
            </div>
        
        </div>
        </div>
        </div>
        
        <?php
        
    }
    
    /**
	 *  Render Layout Fields
	 */
    function render_layout_fields($layout, $sub_fields, $value, $field, $prefix){
        
        if(empty($sub_fields))
            return false;
        
        // el
        $el = 'div';
        
		if($layout['display'] == 'table'){
			
			$el = 'td';
			
		}
    
        if($field['acfe_flexible_modal_edition']){ ?>
        
            <div class="acfe-modal -fields">
            <div class="acfe-modal-wrapper">
            <div class="acfe-modal-content">
        
        <?php } ?>
    
        <?php if( $layout['display'] == 'table' ): ?>
        <table class="acf-table">
            
            <thead>
                <tr>
                    <?php foreach( $sub_fields as $sub_field ): 
                        
                        // prepare field (allow sub fields to be removed)
                        $sub_field = acf_prepare_field($sub_field);
                        
                        
                        // bail ealry if no field
                        if( !$sub_field ) continue;
                        
                        
                        // vars
                        $atts = array();
                        $atts['class'] = 'acf-th';
                        $atts['data-name'] = $sub_field['_name'];
                        $atts['data-type'] = $sub_field['type'];
                        $atts['data-key'] = $sub_field['key'];
                        
                        
                        // Add custom width
                        if( $sub_field['wrapper']['width'] ) {
                        
                            $atts['data-width'] = $sub_field['wrapper']['width'];
                            $atts['style'] = 'width: ' . $sub_field['wrapper']['width'] . '%;';
                            
                        }
                        
                        ?>
                        <th <?php echo acf_esc_attr( $atts ); ?>>
                            <?php echo acf_get_field_label( $sub_field ); ?>
                            <?php if( $sub_field['instructions'] ): ?>
                                <p class="description"><?php echo $sub_field['instructions']; ?></p>
                            <?php endif; ?>
                        </th>
                        
                    <?php endforeach; ?> 
                </tr>
            </thead>
            
            <tbody>
                <tr class="acf-row">
        <?php else: ?>
        <div class="acf-fields <?php if($layout['display'] == 'row'): ?>-left<?php endif; ?>">
        <?php endif; ?>
        
            <?php
                
            // loop though sub fields
            foreach( $sub_fields as $sub_field ) {
                
                // add value
                if( isset($value[ $sub_field['key'] ]) ) {
                    
                    // this is a normal value
                    $sub_field['value'] = $value[ $sub_field['key'] ];
                    
                } elseif( isset($sub_field['default_value']) ) {
                    
                    // no value, but this sub field has a default value
                    $sub_field['value'] = $sub_field['default_value'];
                    
                }
                
                
                // update prefix to allow for nested values
                $sub_field['prefix'] = $prefix;
                
                
                // render input
                acf_render_field_wrap( $sub_field, $el );
            
            }
            
            ?>
            
            <?php if(!$field['acfe_flexible_modal_edition'] && $field['acfe_flexible_close_button']){ ?>
            
                <div class="acfe-flexible-opened-actions"><a href="javascript:void(0);" class="button"><?php _e('Close', 'acf'); ?></button></a></div>
            
            <?php } ?>
                
        <?php if( $layout['display'] == 'table' ): ?>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        </div>
        <?php endif; ?>
        
        <?php if($field['acfe_flexible_modal_edition']){ ?>
        
            </div>
            </div>
            </div>
        
        <?php }
        
    }
    
    /**
	 *  Add Layout Div
	 */
    function add_layout_div($div, $layout, $field){
        
        // Class
        if($field['acfe_flexible_layouts_state'] === 'collapse' || $field['acfe_flexible_modal_edition']){
            
            $div['class'] .= ' -collapsed';
            
        }
        
        return $div;
        
    }
    
    /**
	 *  Add Layout Handle
	 */
    function add_layout_handle($handle, $layout, $field){
        
        // Data
        if($field['acfe_flexible_modal_edition']){
        
            $handle['data-action'] = 'acfe-flexible-modal-edit';
            
        }
        
        return $handle;
        
    }
    
    /**
	 *  Add Layout Icons
	 */
    function add_layout_icons($icons, $layout, $field){
        
        // Settings
        if($field['acfe_flexible_layouts_settings'] && acf_maybe_get($layout, 'acfe_flexible_settings')){
            
            $new_icons = array(
                'settings' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-generic" href="#" title="Settings" data-acfe-flexible-settings="' . $layout['name'] . '"></a>'
            );
            
            $icons = array_merge($icons, $new_icons);
            
        }
        
        // Copy
        if($field['acfe_flexible_copy_paste']){
            
            $new_icons = array(
                'copy' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-category" href="#" title="Copy layout" data-acfe-flexible-control-copy="' . $layout['name'] . '"></a>'
            );
            
            $icons = array_merge($new_icons, $icons);
            
        }
    
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
    
            // Clone
            if($field['acfe_flexible_clone']){
        
                // Clone
                $new_icons = array(
                    'clone' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-page" href="#" title="Clone layout" data-acfe-flexible-control-clone="' . $layout['name'] . '"></a>'
                );
        
                $icons = array_merge($new_icons, $icons);
        
            }
        
        }
    
        // Toggle
        if($field['acfe_flexible_toggle']){
        
            // Clone
            $new_icons = array(
                'toggle' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-hidden" href="#" title="Toggle layout" data-acfe-flexible-control-toggle="' . $layout['name'] . '"></a>'
            );
        
            $icons = array_merge($new_icons, $icons);
        
        }
        
        // Remove: Action filter
        $remove_actions = false;
        $remove_actions = apply_filters('acfe/flexible/remove_actions',                           $remove_actions, $field);
        $remove_actions = apply_filters('acfe/flexible/remove_actions/name=' . $field['_name'],   $remove_actions, $field);
        $remove_actions = apply_filters('acfe/flexible/remove_actions/key=' . $field['key'],      $remove_actions, $field);
        
        if($remove_actions){
            
            // Add
            acfe_unset($icons, 'add');
            acfe_unset($icons, 'duplicate');
            acfe_unset($icons, 'delete');
            acfe_unset($icons, 'clone');
            acfe_unset($icons, 'copy');
            
        }
        
        // Hide: Add button
        if($field['acfe_flexible_remove_add_button'] && isset($icons['add'])){
            
            unset($icons['add']);
            
        }
    
        // Hide: Duplicate button
        if($field['acfe_flexible_remove_duplicate_button'] && isset($icons['duplicate'])){
        
            unset($icons['duplicate']);
        
        }
        
        // Hide: Delete button
        if($field['acfe_flexible_remove_delete_button'] && isset($icons['delete'])){
            
            unset($icons['delete']);
            
        }
        
        // Hide: Collapse
        if(($field['acfe_flexible_modal_edition'] || $field['acfe_flexible_layouts_remove_collapse']) && isset($icons['collapse'])){
            
            unset($icons['collapse']);
            
        }
        
        return $icons;
        
    }
    
    /**
	 *  Add Empty Message
	 */
    function add_empty_message($message, $field){
        
        if(!$field['acfe_flexible_empty_message'])
            return $message;
        
        return $field['acfe_flexible_empty_message'];
        
    }
    
    /**
	 *  Add Layout Title
	 */
    function add_layout_title($title, $field, $layout, $i){
        
        // Remove thumbnail
        $title = preg_replace('#<div class="acfe-flexible-layout-thumbnail(.*?)</div>#', '', $title);
        
        // Title Edition
        if($field['acfe_flexible_title_edition']){
            
            // Get Layout Title
            $acfe_flexible_layout_title = get_sub_field('acfe_flexible_layout_title');
            
            if(!empty($acfe_flexible_layout_title)){
                
                $title = wp_unslash($acfe_flexible_layout_title);
                
            }
            
            // Return
            return '<span class="acfe-layout-title acf-js-tooltip" title="' . __('Layout', 'acfe') . ': ' . esc_attr(strip_tags($layout['label'])) . '"><span class="acfe-layout-title-text">' . $title . '</span></span>';
            
        }
        
        // Return
        return '<span class="acfe-layout-title-text">' . $title . '</span></span>';
        
    }
    
    /**
	 *  Wysiwyg Editor Delay
	 */
    function field_editor_delay($field){
        
        $field['delay'] = 1;
        
        return $field;
        
    }
    
    /**
     *  Load Value Toggle
     */
    function load_value_toggle($value, $post_id, $field){
        
        // Bail early if admin
        if(is_admin() && !wp_doing_ajax())
            return $value;
        
        if(!acf_maybe_get($field, 'acfe_flexible_toggle'))
            return $value;
        
        if(empty($field['layouts']))
            return $value;
        
        $models = array();
        
        foreach($field['layouts'] as $layout_key => $layout){
    
            $models[$layout['name']] = array(
                'key' => $layout['key'],
                'name' => $layout['name'],
                'toggle' => 'field_' . $layout['key'] . '_toggle'
            );
            
        }
        
        $value = acf_get_array($value);
        
        foreach($value as $k => $layout){
            
            if(!isset($models[$layout['acf_fc_layout']]))
                continue;
            
            if(!acf_maybe_get($layout, $models[$layout['acf_fc_layout']]['toggle']))
                continue;
            
            unset($value[$k]);
            
        }
    
        return $value;
        
    }
    
}

acf_register_field_type('acfe_field_flexible_content');

endif;