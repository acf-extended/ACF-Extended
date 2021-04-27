<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content')):

class acfe_field_flexible_content{
    
    var $instance;
    
    function __construct(){
        
        // Flexible Content Instance
        $this->instance = acf_get_field_type('flexible_content');
        
        // Flexible Settings
        add_action('acf/render_field_settings/type=flexible_content',       array($this, 'render_field_settings'), 0);
        add_action('acf/render_field',                                      array($this, 'render_field_layouts_settings_label'), 0);
        add_action('acf/render_field',                                      array($this, 'render_field_layouts_settings'), 10);
        
        add_filter('acf/validate_field/type=flexible_content',              array($this, 'validate_field'));
        add_filter('acf/prepare_field/type=flexible_content',               array($this, 'prepare_field'));
        add_filter('acfe/load_fields/type=flexible_content',                array($this, 'load_fields'), 10, 2);
        add_filter('acfe/field_wrapper_attributes/type=flexible_content',   array($this, 'wrapper_attributes'), 10, 2);
        
        // Render Flexible
        remove_action('acf/render_field/type=flexible_content',             array($this->instance, 'render_field'), 9);
        add_action('acf/render_field/type=flexible_content',                array($this, 'render_field'), 9);
        add_filter('acf/fields/flexible_content/layout_title',              array($this, 'prepare_layout_title'), 0, 4);
        
    }
    
    /*
     *  Field Settings
     */
    function render_field_settings($field){
        
        // Action
        do_action("acfe/flexible/render_field_settings", $field);
        
    }
    
    /*
     *  Layout Settings Label
     */
    function render_field_layouts_settings_label($field){
        
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
    
    /*
     *  Layout Settings
     */
    function render_field_layouts_settings($field){
        
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
        
        if(!acf_maybe_get($flexible, 'layouts'))
            return;
        
        $layout = $flexible['layouts'][$_layout_key];
        
        // Vars
        $name = $flexible['name'];
        $key = $flexible['key'];
        $l_name = $layout['name'];
        
        // Do Actions
        do_action("acfe/flexible/render_layout_settings",                               $flexible, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/name={$name}",                  $flexible, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/key={$key}",                    $flexible, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/layout={$l_name}",              $flexible, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/name={$name}&layout={$l_name}", $flexible, $layout, $prefix);
        do_action("acfe/flexible/render_layout_settings/key={$key}&layout={$l_name}",   $flexible, $layout, $prefix);
        
    }
    
    /*
     *  Validate Field
     */
    function validate_field($field){
        
        // Defaults
        $_field = array();
        $_layout = array();
    
        // Filters
        $_field = apply_filters("acfe/flexible/defaults_field", $_field);
        $_layout = apply_filters("acfe/flexible/defaults_layout", $_layout);
    
        foreach($_field as $k => $v){
        
            if(!isset($field[$k])){
                $field[$k] = $v;
            }
            
            if(is_array($v)){
                foreach($v as $ak => $av){
    
                    if(!isset($field[$k][$ak])){
                        $field[$k][$ak] = $av;
                    }
                    
                }
            }
        
        }
        
        foreach($field['layouts'] as &$layout){
            foreach($_layout as $k => $v){
        
                if(!isset($layout[$k])){
                    $layout[$k] = $v;
                }
        
                if(is_array($v)){
                    foreach($v as $ak => $av){
                        
                        if(!isset($layout[$k][$ak])){
                            $layout[$k][$ak] = $av;
                        }
                        
                    }
                }
        
            }
        }
        
        $field = apply_filters('acfe/flexible/validate_field', $field);
        
        return $field;
        
    }
    
    /*
     *  Prepare Field
     */
    function prepare_field($field){
        
        // Vars
        $name = $field['_name'];
        $key = $field['key'];
        
        foreach($field['layouts'] as &$layout){
            
            // Vars
            $l_name = $layout['name'];
            
            // Prepend
            $prepend = '';
            $prepend = apply_filters("acfe/flexible/layouts/label_prepend",                                 $prepend, $layout, $field);
            $prepend = apply_filters("acfe/flexible/layouts/label_prepend/name={$name}",                    $prepend, $layout, $field);
            $prepend = apply_filters("acfe/flexible/layouts/label_prepend/key={$key}",                      $prepend, $layout, $field);
            $prepend = apply_filters("acfe/flexible/layouts/label_prepend/layout={$l_name}",                $prepend, $layout, $field);
            $prepend = apply_filters("acfe/flexible/layouts/label_prepend/name={$name}&layout={$l_name}",   $prepend, $layout, $field);
            $prepend = apply_filters("acfe/flexible/layouts/label_prepend/key={$key}&layout={$l_name}",     $prepend, $layout, $field);
            
            // Atts
            $atts = array('class' => 'no-thumbnail');
            $atts = apply_filters("acfe/flexible/layouts/label_atts",                               $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/label_atts/name={$name}",                  $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/label_atts/key={$key}",                    $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/label_atts/layout={$l_name}",              $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/label_atts/name={$name}&layout={$l_name}", $atts, $layout, $field);
            $atts = apply_filters("acfe/flexible/layouts/label_atts/key={$key}&layout={$l_name}",   $atts, $layout, $field);
            
            // Label
            $layout['label'] = $prepend . '<span ' . acf_esc_atts($atts) . '>' . $layout['label'] . '</span>';
            
        }
        
        return $field;
        
    }
    
    /*
     *  Load Fields
     */
    function load_fields($fields, $field){
        
        if(acfe_is_admin_screen())
            return $fields;
        
        // check layouts
        if(empty($field['layouts']))
            return $fields;
        
        // vars
        $name = $field['name'];
        $key = $field['key'];
    
        $fields = apply_filters("acfe/flexible/load_fields",                $fields, $field);
        $fields = apply_filters("acfe/flexible/load_fields/name={$name}",   $fields, $field);
        $fields = apply_filters("acfe/flexible/load_fields/key={$key}",     $fields, $field);
        
        return $fields;
        
    }
    
    /*
     *  Wrapper Attributes
     */
    function wrapper_attributes($wrapper, $field){
    
        $wrapper = apply_filters('acfe/flexible/wrapper_attributes', $wrapper, $field);
        
        return $wrapper;
        
    }
    
    /*
     *  Render Field
     */
    function render_field($field){
        
        // Vars
        $name = $field['_name'];
        $key = $field['key'];
        
        // defaults
        if(empty($field['button_label'])){
            $field['button_label'] = __("Add Row", 'acf');
        }
        
        // sort layouts into names
        $layouts = array();
        
        foreach($field['layouts'] as $k => $layout){
            $layouts[$layout['name']] = $layout;
        }
        
        // vars
        $div = array(
            'class'     => 'acf-flexible-content',
            'data-min'  => $field['min'],
            'data-max'  => $field['max']
        );
        
        // empty
        if(empty($field['value'])){
            $div['class'] .= ' -empty';
        }
        
        // no value message
        $no_value_message = __('Click the "%s" button below to start creating your layout', 'acf');
        $no_value_message = apply_filters('acf/fields/flexible_content/no_value_message', $no_value_message, $field);
        
        $values = array(
            'class' => 'values'
        );
    
        $values = apply_filters("acfe/flexible/div_values",                 $values, $field);
        $values = apply_filters("acfe/flexible/div_values/name={$name}",    $values, $field);
        $values = apply_filters("acfe/flexible/div_values/key={$key}",      $values, $field);

    ?>
    <div <?php echo acf_esc_attrs($div); ?>>

        <?php acf_hidden_input(array('name' => $field['name'])); ?>

        <div class="no-value-message">
            <?php printf($no_value_message, $field['button_label']); ?>
        </div>

        <div class="clones">
            <?php foreach($layouts as $layout):
                
                // Vars
                $l_name = $layout['name'];
                
                // Models
                $model = false;
                $model = apply_filters("acfe/flexible/layouts/model",                               $model, $field, $layout);
                $model = apply_filters("acfe/flexible/layouts/model/name={$name}",                  $model, $field, $layout);
                $model = apply_filters("acfe/flexible/layouts/model/key={$key}",                    $model, $field, $layout);
                $model = apply_filters("acfe/flexible/layouts/model/layout={$l_name}",              $model, $field, $layout);
                $model = apply_filters("acfe/flexible/layouts/model/name={$name}&layout={$l_name}", $model, $field, $layout);
                $model = apply_filters("acfe/flexible/layouts/model/key={$key}&layout={$l_name}",   $model, $field, $layout);
                
                if(!$model){
                    $this->render_layout($field, $layout, 'acfcloneindex', array());
                }
                
            endforeach; ?>
        </div>

        <div <?php echo acf_esc_attrs($values); ?>>
            <?php if(!empty($field['value'])): 
                
                foreach($field['value'] as $i => $value):
                    
                    // validate
                    if(empty($layouts[$value['acf_fc_layout']]))
                        continue;
                    
                    // render
                    $this->render_layout($field, $layouts[$value['acf_fc_layout']], $i, $value);
                    
                endforeach;
                
            endif; ?>
        </div>

        <?php
        
        // Remove actions
        $remove_actions = false;
        $remove_actions = apply_filters("acfe/flexible/remove_actions",                 $remove_actions, $field);
        $remove_actions = apply_filters("acfe/flexible/remove_actions/name={$name}",    $remove_actions, $field);
        $remove_actions = apply_filters("acfe/flexible/remove_actions/key={$key}",      $remove_actions, $field);
        
        if(!$remove_actions){
            
            // Wrapper
            $wrapper = array();
            $wrapper = apply_filters('acfe/flexible/action_wrapper', $wrapper, $field);
            
            // Button
            $button = array(
                'class'     => 'acf-button button',
                'href'      => '#',
                'data-name' => 'add-layout',
            );
            
            $button = apply_filters('acfe/flexible/action_button', $button, $field);
            
            if(!empty($wrapper)){
                echo '<div ' . acf_esc_attrs($wrapper) . '>';
            }
            
            ?>
            
            <div class="acf-actions">
                <a <?php echo acf_esc_attrs($button); ?>><?php echo $field['button_label']; ?></a>
                
                <?php
                
                $secondary_actions = array();
                $secondary_actions = apply_filters("acfe/flexible/secondary_actions",               $secondary_actions, $field);
                $secondary_actions = apply_filters("acfe/flexible/secondary_actions/name={$name}",  $secondary_actions, $field);
                $secondary_actions = apply_filters("acfe/flexible/secondary_actions/key={$key}",    $secondary_actions, $field);
                
                if(!empty($secondary_actions)){
                    
                    $button_secondary = array(
                        'class'     => 'button',
                        'style'     => 'padding-left:5px;padding-right:5px; margin-left:3px;',
                        'href'      => '#',
                        'data-name' => 'acfe-flexible-control-button',
                    );
    
                    $button_secondary = apply_filters('acfe/flexible/action_button_secondary', $button_secondary, $field);
                    ?>
                
                    <a <?php echo acf_esc_attrs($button_secondary); ?>>
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
            
            <?php
            if(!empty($wrapper)){
                echo '</div>';
            }
            ?>

            <script type="text-html" class="tmpl-popup">
                <ul>
                <?php foreach($layouts as $layout):
                    
                    $atts = array(
                        'href'          => '#',
                        'data-layout'   => $layout['name'],
                        'data-min'      => $layout['min'],
                        'data-max'      => $layout['max'],
                    );
                    
                    ?><li><a <?php echo acf_esc_attrs($atts); ?>><?php echo $layout['label']; ?></a></li><?php
                
                endforeach; ?>
                </ul>
            </script>
        
        <?php } ?>

    </div>
    <?php
    
    }
    
    /*
     *  Render Layout
     */
    function render_layout($field, $layout, $i, $value){
        
        // vars
        $id = ($i === 'acfcloneindex') ? 'acfcloneindex' : "row-$i";
        $prefix = $field['name'] . '[' . $id .  ']';
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // div
        $div = array(
            'class'         => 'layout',
            'data-id'       => $id,
            'data-layout'   => $layout['name']
        );
        
        // is clone?
        if(!is_numeric($i)){
            $div['class'] .= ' acf-clone';
        }
        
        $div = apply_filters("acfe/flexible/layouts/div",                               $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/name={$name}",                  $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/key={$key}",                    $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/layout={$l_name}",              $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/name={$name}&layout={$l_name}", $div, $layout, $field, $i, $value, $prefix);
        $div = apply_filters("acfe/flexible/layouts/div/key={$key}&layout={$l_name}",   $div, $layout, $field, $i, $value, $prefix);
        
        // handle
        $handle = array(
            'class'     => 'acf-fc-layout-handle',
            'title'     => __('Drag to reorder','acf'),
            'data-name' => 'collapse-layout',
        );
        
        $handle = apply_filters("acfe/flexible/layouts/handle",                                 $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/name={$name}",                    $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/key={$key}",                      $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/layout={$l_name}",                $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/name={$name}&layout={$l_name}",   $handle, $layout, $field, $i, $value, $prefix);
        $handle = apply_filters("acfe/flexible/layouts/handle/key={$key}&layout={$l_name}",     $handle, $layout, $field, $i, $value, $prefix);
        
        // remove row
        // This makes Flexible Content in ACFE Form buggy in a Flexible Content Preview
        //reset_rows();
        
        ?>
        <div <?php echo acf_esc_attrs($div); ?>>
                    
            <?php acf_hidden_input(array('name' => $prefix.'[acf_fc_layout]', 'value' => $layout['name'])); ?>
            
            <div <?php echo acf_esc_attrs($handle); ?>>
                <?php echo $this->instance->get_layout_title($field, $layout, $i, $value); ?>
            </div>
            
            <?php
            
            $layout = apply_filters("acfe/flexible/prepare_layout",                                 $layout, $field, $i, $value, $prefix);
            $layout = apply_filters("acfe/flexible/prepare_layout/name={$name}",                    $layout, $field, $i, $value, $prefix);
            $layout = apply_filters("acfe/flexible/prepare_layout/key={$key}",                      $layout, $field, $i, $value, $prefix);
            $layout = apply_filters("acfe/flexible/prepare_layout/layout={$l_name}",                $layout, $field, $i, $value, $prefix);
            $layout = apply_filters("acfe/flexible/prepare_layout/name={$name}&layout={$l_name}",   $layout, $field, $i, $value, $prefix);
            $layout = apply_filters("acfe/flexible/prepare_layout/key={$key}&layout={$l_name}",     $layout, $field, $i, $value, $prefix);
            
            do_action("acfe/flexible/pre_render_layout",                                            $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/pre_render_layout/name={$name}",                               $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/pre_render_layout/key={$key}",                                 $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/pre_render_layout/layout={$l_name}",                           $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/pre_render_layout/name={$name}&layout={$l_name}",              $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/pre_render_layout/key={$key}&layout={$l_name}",                $layout, $field, $i, $value, $prefix);
            
            // Prepare Editor
            add_filter('acf/prepare_field/type=wysiwyg',                                            array($this, 'prepare_layout_editor'));

            // Render Layout Fields
            $this->render_layout_fields($layout, $field, $i, $value, $prefix);
            
            // Unprepare Editor
            remove_filter('acf/prepare_field/type=wysiwyg',                                         array($this, 'prepare_layout_editor'));
            
            do_action("acfe/flexible/render_layout",                                                $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/render_layout/name={$name}",                                   $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/render_layout/key={$key}",                                     $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/render_layout/layout={$l_name}",                               $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/render_layout/name={$name}&layout={$l_name}",                  $layout, $field, $i, $value, $prefix);
            do_action("acfe/flexible/render_layout/key={$key}&layout={$l_name}",                    $layout, $field, $i, $value, $prefix);
            
            ?>

        </div>
        <?php
        
    }
    
    /*
     *  Render Layout Fields
     */
    function render_layout_fields($layout, $field, $i, $value, $prefix){
        
        // vars
        $sub_fields = $layout['sub_fields'];
        $el = $layout['display'] === 'table' ? 'td' : 'div';
        
        if(empty($sub_fields))
            return;
        
        if($layout['display'] == 'table'): ?>
            <table class="acf-table">
            <thead>
                <tr>
                    <?php foreach($sub_fields as $sub_field):
                        
                        // prepare field (allow sub fields to be removed)
                        $sub_field = acf_prepare_field($sub_field);
                    
                        // bail ealry if no field
                        if(!$sub_field)
                            continue;
                        
                        // vars
                        $atts = array();
                        $atts['class'] = 'acf-th';
                        $atts['data-name'] = $sub_field['_name'];
                        $atts['data-type'] = $sub_field['type'];
                        $atts['data-key'] = $sub_field['key'];
                        
                        // Add custom width
                        if($sub_field['wrapper']['width']){
                            
                            $atts['data-width'] = $sub_field['wrapper']['width'];
                            $atts['style'] = 'width: ' . $sub_field['wrapper']['width'] . '%;';
                            
                        }
                        
                        ?>
                        <th <?php echo acf_esc_attrs($atts); ?>>
                            <?php echo acf_get_field_label($sub_field); ?>
                            <?php if($sub_field['instructions']): ?>
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
        foreach($sub_fields as $sub_field){
            
            // add value
            if(isset($value[$sub_field['key']])){
                
                $sub_field['value'] = $value[$sub_field['key']];
                
            }elseif(isset($sub_field['default_value'])){
                
                $sub_field['value'] = $sub_field['default_value'];
                
            }
            
            // update prefix to allow for nested values
            $sub_field['prefix'] = $prefix;
            
            // render input
            acf_render_field_wrap($sub_field, $el);
            
        }
        
        ?>
        
        <?php if($layout['display'] == 'table'): ?>
            </tr>
            </tbody>
            </table>
    
            <?php if(!$field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'] && in_array('close', $field['acfe_flexible_add_actions'])){ ?>
                <div class="acfe-flexible-opened-actions"><a href="javascript:void(0);" class="button"><?php _e('Close', 'acf'); ?></button></a></div>
            <?php } ?>
        
        <?php else: ?>
    
            <?php if(!$field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'] && in_array('close', $field['acfe_flexible_add_actions'])){ ?>
                <div class="acfe-flexible-opened-actions"><a href="javascript:void(0);" class="button"><?php _e('Close', 'acf'); ?></button></a></div>
            <?php } ?>
        
            </div>
        <?php endif;
        
    }
    
    /*
     *  Prepare Layout Editor
     */
    function prepare_layout_editor($field){
        
        $field['delay'] = 1;
        return $field;
        
    }
    
    /*
     * Prepare Layout Title
     */
    function prepare_layout_title($title, $field, $layout, $i){
        
        return '<span class="acfe-layout-title-text">' . $title . '</span>';
        
    }
    
}

acf_new_instance('acfe_field_flexible_content');

endif;

/*
 * Includes
 */
acfe_include('includes/fields/field-flexible-content-actions.php');
acfe_include('includes/fields/field-flexible-content-async.php');
acfe_include('includes/fields/field-flexible-content-controls.php');
acfe_include('includes/fields/field-flexible-content-edit.php');
acfe_include('includes/fields/field-flexible-content-hide.php');
acfe_include('includes/fields/field-flexible-content-preview.php');
acfe_include('includes/fields/field-flexible-content-select.php');
acfe_include('includes/fields/field-flexible-content-settings.php');
acfe_include('includes/fields/field-flexible-content-state.php');
acfe_include('includes/fields/field-flexible-content-thumbnail.php');
