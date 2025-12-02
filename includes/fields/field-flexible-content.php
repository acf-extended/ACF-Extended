<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_flexible_content')):

class acfe_field_flexible_content extends acfe_field_extend{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'flexible_content';
        $this->replace = array(
            'render_field',
        );
    
        $this->add_field_action('acf/render_field_settings',                             array($this, '_render_field_settings'), 0);
        $this->add_action('acf/render_field',                                            array($this, 'render_layout_label'), 0);
        $this->add_action('acf/render_field',                                            array($this, 'render_layout_settings'));
        
        $this->replace_action('wp_ajax_acf/fields/flexible_content/layout_title',        array($this, 'ajax_layout_title'));
        $this->replace_action('wp_ajax_nopriv_acf/fields/flexible_content/layout_title', array($this, 'ajax_layout_title'));
        
    }
    
    
    /**
     * input_admin_enqueue_scripts
     */
    function input_admin_enqueue_scripts(){
        
        // localize
        acf_localize_text(array(
            'Layout data has been copied to your clipboard.'                          => __('Layout data has been copied to your clipboard.', 'acfe'),
            'Layouts data have been copied to your clipboard.'                        => __('Layouts data have been copied to your clipboard.', 'acfe'),
            'Please copy the following data to your clipboard.'                       => __('Please copy the following data to your clipboard.', 'acfe'),
            'Please paste previously copied layout data in the following field:'      => __('Please paste previously copied layout data in the following field:', 'acfe'),
            'You can now paste it on another page, using the "Paste" button action.'  => __('You can now paste it on another page, using the "Paste" button action.', 'acfe'),
            'You can then paste it on another page, using the "Paste" button action.' => __('You can then paste it on another page, using the "Paste" button action.', 'acfe'),
        ));
        
    }
    
    
    /**
     * field_group_admin_head
     */
    function field_group_admin_head(){
        
        // clear fields cache
        // this fix an issue where plugins could query acf fields using acf_get_fields() very early
        // and push unwanted settings such as "inline title" on the Field Group UI
        acf_get_store('fields')->reset();
        
    }
    
    
    /**
     * _render_field_settings
     *
     * acf/render_field_settings/type=flexible_content:0
     *
     * @param $field
     */
    function _render_field_settings($field){
        
        // action
        do_action('acfe/flexible/render_field_settings', $field);
        
    }
    
    
    /**
     * render_layout_label
     *
     * @param $field
     */
    function render_layout_label($field){
        
        // validate setting
        if($field['_name'] !== 'label' || stripos($field['name'], '[layouts]') === false){
            return;
        }
        
        echo '</li>';
        
        acf_render_field_wrap(array(
            'label' => __('Settings', 'acfe'),
            'type'  => 'hidden',
            'name'  => 'acfe_flexible_settings_label'
        ), 'ul');
        
        echo '<li>';
        
    }
    
    
    /**
     * render_layout_settings
     *
     * @param $field
     */
    function render_layout_settings($field){
        
        // validate setting
        if($field['_name'] !== 'max' || stripos($field['name'], '[layouts]') === false){
            return;
        }
        
        // Prefix
        $prefix = $field['prefix'];
        
        // black magic
        parse_str($prefix, $output);
        $keys = acfe_array_keys_r($output);
        
        // ...
        $_field_id = $keys[1];
        $_layout_key = $keys[3];
        
        // profit!
        $flexible = acf_get_field($_field_id);
        
        // bail early
        if(!acf_maybe_get($flexible, 'layouts')){
            return;
        }
        
        // get layout
        $layout = $flexible['layouts'][ $_layout_key ];
        
        // actions (with variations)
        do_action('acfe/flexible/render_layout_settings', $flexible, $layout, $prefix);
        
    }
    
    
    /**
     * validate_field
     *
     * @param $field
     *
     * @return mixed|null
     */
    function validate_field($field){
    
        // default filter
        $defaults_field = apply_filters('acfe/flexible/defaults_field', array());
        
        // loop field keys
        foreach($defaults_field as $default_k => $default_v){
        
            if(!isset($field[ $default_k ])){
                $field[ $default_k ] = $default_v;
            }
            
            if(is_array($default_v)){
                foreach($default_v as $default_ak => $default_av){
    
                    if(!isset($field[ $default_k ][ $default_ak ])){
                        $field[ $default_k ][ $default_ak ] = $default_av;
                    }
                    
                }
            }
        
        }
        
        // loop layouts
        if(!empty($field['layouts'])){
            foreach(array_keys($field['layouts']) as $l_key){
                $field['layouts'][ $l_key ] = $this->validate_layout($field['layouts'][ $l_key ], $field);
            }
        }
        
        // validate
        $field = apply_filters('acfe/flexible/validate_field', $field);
        
        // return
        return $field;
        
    }
    
    
    /**
     * validate_layout
     *
     * @param $layout
     * @param $field
     *
     * @return mixed|null
     */
    function validate_layout($layout, $field){
        
        // defaults filter
        $defaults_layout = apply_filters('acfe/flexible/defaults_layout', array(), $field);
        
        // loop layout keys
        foreach($defaults_layout as $default_k => $default_v){
            
            if(!isset($layout[ $default_k ])){
                $layout[ $default_k ] = $default_v;
            }
            
            if(is_array($default_v)){
                foreach($default_v as $default_ak => $default_av){
                    
                    if(!isset($layout[ $default_k ][$default_ak])){
                        $layout[ $default_k ][$default_ak] = $default_av;
                    }
                    
                }
            }
            
        }
        
        // validate
        $layout = apply_filters('acfe/flexible/validate_layout', $layout, $field);
        
        // return
        return $layout;
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field($field){
        
        // bail early
        if(empty($field['layouts'])){
            return $field;
        }
        
        // loop layouts
        foreach($field['layouts'] as &$layout){
        
            // filters (with variations)
            $prepend = apply_filters('acfe/flexible/layouts/label_prepend', '', $layout, $field);
            $atts    = apply_filters('acfe/flexible/layouts/label_atts', array(), $layout, $field);
        
            // save label
            $label = $layout['label'];
            
            // new label
            $layout['label'] = '';
            $layout['label'] .= $prepend;
            
            if(!empty($atts)){$layout['label'] .= '<span ' . acf_esc_atts($atts) . '>';}
            $layout['label'] .= $label;
            if(!empty($atts)){$layout['label'] .= '</span>';}
        
        }
        
        return $field;
        
    }
    
    
    /**
     * load_fields
     *
     * @param $fields
     * @param $field
     *
     * @return mixed|null
     */
    function load_fields($fields, $field){
        
        if(acfe_is_admin_screen()){
            return $fields;
        }
        
        // check layouts
        if(empty($field['layouts'])){
            return $fields;
        }
        
        // filter (with variations)
        $fields = apply_filters('acfe/flexible/load_fields', $fields, $field);
        
        return $fields;
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed|null
     */
    function field_wrapper_attributes($wrapper, $field){
    
        $wrapper = apply_filters('acfe/flexible/wrapper_attributes', $wrapper, $field);
        
        return $wrapper;
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // defaults
        if(empty($field['button_label'])){
            $field['button_label'] = $this->instance->defaults['button_label'];
        }
        
        // vars
        $div = array(
            'class'             => 'acf-flexible-content',
            'data-min'          => $field['min'],
            'data-max'          => $field['max'],
            'data-button-label' => $field['button_label'],
        );
        
        // empty
        if(empty($field['value'])){
            $div['class'] .= ' -empty';
        }
        
        echo '<div ' . acf_esc_atts($div) . '>';
        
        acf_hidden_input(array(
            'name' => $field['name']
        ));
        
        $this->render_actions($field, 'top');
        $this->render_no_value_message($field);
        $this->render_clones($field);
        $this->render_layouts($field);
        $this->render_actions($field);
        $this->render_popup($field);
        
        echo '</div>';
    
    }
    
    
    /**
     * render_no_value_message
     *
     * @param $field
     *
     * @return void
     */
    function render_no_value_message($field){
        
        // no value message
        $no_value_message = __('Click the "%s" button below to start creating your layout', 'acf');
        $no_value_message = apply_filters('acf/fields/flexible_content/no_value_message', $no_value_message, $field);
        $no_value_message = sprintf($no_value_message, $field['button_label']);
        
        echo '<div class="no-value-message">' . acf_esc_html($no_value_message) . '</div>';
        
    }
    
    
    /**
     * render_clones
     *
     * @param $field
     *
     * @return void
     */
    function render_clones($field){
        
        echo '<div class="clones">';
        
        foreach($field['layouts'] as $layout){
            
            // filter (with variations)
            $model = apply_filters('acfe/flexible/layouts/model', false, $field, $layout);
            
            // allow bypass
            if(!$model){
                $this->render_layout($field, $layout, 'acfcloneindex', array());
            }
        
        }
        
        echo '</div>';
        
    }
    
    
    /**
     * render_layouts
     *
     * @param $field
     *
     * @return void
     */
    function render_layouts($field){
        
        // filter (with variations)
        $values = array('class' => 'values');
        $values = apply_filters('acfe/flexible/div_values', $values, $field);
        
        // wrapper
        echo '<div '. acf_esc_atts($values) .'>';
        
        if(!empty($field['value'])){
            foreach($field['value'] as $i => $value){
                
                if(!empty($this->instance->get_layout($value['acf_fc_layout'], $field))){
                    $this->render_layout($field, $this->instance->get_layout($value['acf_fc_layout'], $field), $i, $value);
                }
                
            }
        }
        
        echo '</div>';
        
    }
    
    
    /**
     * render_actions
     *
     * @param $field
     * @param $position
     *
     * @return void
     */
    function render_actions($field, $position = 'bottom'){
        
        // filter (with variations)
        $should_hide = apply_filters('acfe/flexible/remove_actions', false, $field, $position);
        if($should_hide){
            return;
        }
        
        // get buttons
        $buttons = $this->get_actions_buttons($field, $position);
        if(empty($buttons)){
            return;
        }
        
        // filter (with variations)
        $wrapper = array('class' => "acf-actions acf-fc-{$position}-actions");
        $wrapper = apply_filters('acfe/flexible/action_wrapper', $wrapper, $field, $position);
        
        // wrapper
        echo '<div ' . acf_esc_atts($wrapper) . '>';
        
        // loop buttons
        foreach($buttons as $button){
            echo $button;
        }
            
        echo '</div>';
    
    }
    
    
    /**
     * render_popup
     *
     * @param $field
     *
     * @return void
     */
    function render_popup($field){
        
        // action (with variations)
        do_action("acfe/flexible/render_popup", $field);
    
    }
    
    
    /**
     * render_layout
     *
     * @param $field
     * @param $layout
     * @param $i
     * @param $value
     */
    function render_layout($field, $layout, $i, $value){
        
        // attributes
        $id    = "row-$i";
        $class = 'layout';
        
        // layout clone
        if($i === 'acfcloneindex'){
            $id     = 'acfcloneindex';
            $class .= ' acf-clone';
        }
        
        // vars
        $prefix = $field['name'] . '[' . $id .  ']';
        
        // div
        $div = array(
            'class'        => $class,
            'data-id'      => $id,
            'data-layout'  => $layout['name'],
            'data-label'   => $layout['label'],
            'data-min'     => $layout['min'],
            'data-max'     => $layout['max'],
            'data-enabled' => $this->get_layout_disabled($field, $i) ? 0 : 1,
            'data-renamed' => empty($this->get_layout_renamed($field, $i)) ? 0 : 1,
        );
        
        // filter (with variations)
        $div = apply_filters('acfe/flexible/layouts/div', $div, $layout, $field, $i, $value, $prefix);
        
        // wrapper
        echo '<div ' . acf_esc_atts($div) . '>';
            
            acf_hidden_input(array(
                'name'  => $prefix . '[acf_fc_layout]',
                'value' => $layout['name'],
            ));
            
            acf_hidden_input(array(
                'class' => 'acf-fc-layout-disabled',
                'name'  => $prefix . '[acf_fc_layout_disabled]',
                'value' => $this->get_layout_disabled($field, $i) ? 1 : 0,
            ));
            
            acf_hidden_input(array(
                'class' => 'acf-fc-layout-custom-label',
                'name'  => $prefix . '[acf_fc_layout_custom_label]',
                'value' => $this->get_layout_renamed($field, $i),
            ));
            
            echo '<div class="acf-fc-layout-actions-wrap">';
                $this->render_layout_handle($field, $layout, $i, $value, $prefix);
                $this->render_layout_controls($field, $layout, $i, $value, $prefix);
            echo '</div>';
            
            
            // filter (with variations)
            $layout = apply_filters('acfe/flexible/prepare_layout', $layout, $field, $i, $value, $prefix);
            
            // action (with variations)
            do_action('acfe/flexible/pre_render_layout', $layout, $field, $i, $value, $prefix);
            
            // render fields: table
            if($layout['display'] == 'table'){
                $this->render_layout_table($layout, $field, $i, $value, $prefix);
                
            // render fields: div
            }else{
                $this->render_layout_div($layout, $field, $i, $value, $prefix);
            }
            
            // action (with variations)
            do_action('acfe/flexible/render_layout', $layout, $field, $i, $value, $prefix);
        
        echo '</div>';
        
    }
    
    
    /**
     * render_layout_handle
     *
     * @param $field
     * @param $layout
     * @param $i
     * @param $value
     *
     * @return void
     */
    function render_layout_handle($field, $layout, $i, $value, $prefix){
        
        // get elements
        $elements = $this->get_layout_handle_elements($layout, $field, $i, $value, $prefix);
        
        // handle
        $handle = array(
            'class'     => 'acf-fc-layout-handle',
            'title'     => __('Drag to reorder','acf'),
            'data-name' => 'collapse-layout',
        );
        
        // filter (with variations)
        $handle = apply_filters('acfe/flexible/layouts/handle', $handle, $layout, $field, $i, $value, $prefix);
        
        // wrapper
        echo '<div ' . acf_esc_atts($handle) . '>';
        
        // loop elements
        if(!empty($elements)){
            foreach($elements as $element){
                echo $element;
            }
        }
        
        echo '</div>';
        
    }
    
    
    /**
     * render_layout_controls
     *
     * @param $field
     * @param $layout
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return void
     */
    function render_layout_controls($field, $layout, $i, $value, $prefix){
        
        // actions (with variations)
        do_action('acfe/flexible/layouts/controls', $layout, $field, $i, $value, $prefix);
        
        // get buttons
        $buttons = $this->get_layout_controls_buttons($field, $layout);
        if(empty($buttons)){
            return;
        }
        
        // controls
        echo '<div class="acf-fc-layout-controls">';
        
        // loop buttons
        foreach($buttons as $button){
            echo $button;
        }
        
        echo '</div>';
        
    }
    
    
    /**
     * render_layout_table
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     */
    function render_layout_table($layout, $field, $i, $value, $prefix){
        
        // bail early
        if(empty($layout['sub_fields'])){
            return;
        }
        
        ?>
        <table class="acf-table">
            <thead>
                <tr>
                <?php foreach($layout['sub_fields'] as $sub_field):
                    
                    // prepare field (allow subfields to be removed)
                    $sub_field = acf_prepare_field($sub_field);
                    
                    // bail ealry if no field
                    if(!$sub_field){
                        continue;
                    }
                    
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
                    <th <?php echo acf_esc_atts($atts); ?>>
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
                    <?php $this->render_sub_fields($layout, $field, $i, $value, $prefix); ?>
                </tr>
            </tbody>
            
            <?php if(!$field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'] && in_array('close', $field['acfe_flexible_add_actions'])){ ?>
                <?php $close_label = !empty($field['acfe_flexible_close_button_label']) ? $field['acfe_flexible_close_button_label'] : __('Close', 'acfe'); ?>
                <tfoot>
                <tr class="acfe-tfoot-row">
                    <td colspan="<?php echo count($layout['sub_fields']); ?>">
                        <div class="acfe-flexible-opened-actions"><a href="#" class="button"><?php echo $close_label; ?></button></a></div>
                    </td>
                </tr>
                </tfoot>
            <?php } ?>
        </table>
        <?php
        
    }
    
    
    /**
     * render_layout_div
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     */
    function render_layout_div($layout, $field, $i, $value, $prefix){
        
        // bail early
        if(empty($layout['sub_fields'])){
            return;
        }
        
        // wrapper
        $div = array(
            'class' => 'acf-fields'
        );
        
        if($layout['display'] === 'row'){
            $div['class'] .= ' -left';
        }
        
        // wrapper
        echo '<div ' . acf_esc_atts($div) . '>';
        
        // render fields
        $this->render_sub_fields($layout, $field, $i, $value, $prefix);
        
        // close button
        if(!$field['acfe_flexible_modal_edit']['acfe_flexible_modal_edit_enabled'] && in_array('close', $field['acfe_flexible_add_actions'])){
            
            $close_label = !empty($field['acfe_flexible_close_button_label']) ? $field['acfe_flexible_close_button_label'] : __('Close', 'acfe');
            echo '<div class="acfe-flexible-opened-actions"><a href="#" class="button">' . $close_label . '</button></a></div>';
            
        }
        
        echo '</div>';
        
    }
    
    
    /**
     * render_sub_fields
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return void
     */
    function render_sub_fields($layout, $field, $i, $value, $prefix){
        
        // loop though sub fields
        foreach($layout['sub_fields'] as $sub_field){
            
            // add value
            if(isset($value[$sub_field['key']])){
                $sub_field['value'] = $value[$sub_field['key']];
                
            }elseif(isset($sub_field['default_value'])){
                $sub_field['value'] = $sub_field['default_value'];
            }
            
            // update prefix to allow for nested values
            $sub_field['prefix'] = $prefix;
            
            // render input
            $el = $layout['display'] === 'table' ? 'td' : 'div';
            acf_render_field_wrap($sub_field, $el);
            
        }
        
    }
    
    
    /**
     * get_actions_buttons
     *
     * @param $field
     * @param $position
     *
     * @return mixed|null
     */
    function get_actions_buttons($field, $position = 'bottom'){
        
        // prepare buttons
        $buttons = array();
        
        // top actions buttons
        if($position === 'top'){
            $buttons['expand'] = '<button class="acf-btn acf-btn-clear acf-fc-expand-all">' . esc_html__('Expand All', 'acf') . '</button>';
            $buttons['collapse'] = '<button class="acf-btn acf-btn-clear acf-fc-collapse-all">' . esc_html__('Collapse All', 'acf') . '</button>';
            $buttons['separator'] = '<span class="acf-separator"></span>';
        }
        
        // button add
        $button_add = array(
            'href'         => '#',
            'class'        => 'acf-button button',
            'data-name'    => 'add-layout',
            'data-context' => "{$position}-actions",
        );
        
        // filter (with variations)
        $button_add = apply_filters('acfe/flexible/action_button', $button_add, $field, $position);
        
        // button add
        $buttons['add'] = '<a ' . acf_esc_atts($button_add) . '>' . acf_esc_html($field['button_label']) . '</a>';
        
        // filter (with variations)
        $buttons = apply_filters('acfe/flexible/action_buttons', $buttons, $field, $position);
        
        return $buttons;
        
    }
    
    
    /**
     * get_layout_handle_elements
     *
     * @param $layout
     * @param $field
     * @param $i
     * @param $value
     * @param $prefix
     *
     * @return mixed|null
     */
    function get_layout_handle_elements($layout, $field, $i, $value, $prefix){
        
        // filter (with variations)
        $attrs = array('class' => 'acf-fc-layout-title');
        $attrs = apply_filters('acf/fields/flexible_content/layout_attrs', $attrs, $field, $layout, $i);
        
        // vars
        $title = $this->get_layout_title($field, $layout, $i, $value);
        $order = is_numeric($i) ? $i + 1 : 0;
        $order = (int) $order;
        $renamed = $this->get_layout_renamed($field, $i);
        
        // handle elements
        $elements = array(
            'order'          => '<span class="acf-fc-layout-order">' . $order . '</span>',
            'drag'           => '<span class="acf-fc-layout-draggable-icon"></span>',
            'title'          => '<span ' . acf_esc_atts($attrs) . '>' . (!empty($renamed) ? esc_html($renamed) : $title) . '</span>',
            'original_title' => '<span class="acf-fc-layout-original-title">(' . $title . ')</span>',
            'disabled'       => '<span class="acf-layout-disabled">' . esc_html__('Disabled', 'acf') . '</span>',
        );
        
        // filters
        $elements = apply_filters('acfe/flexible/layouts/handle_elements', $elements, $layout, $field, $i, $value, $prefix);
        
        // return
        return $elements;
        
    }
    
    
    /**
     * get_layout_controls_buttons
     *
     * @param $field
     * @param $layout
     *
     * @return mixed|null
     */
    function get_layout_controls_buttons($field, $layout){
        
        // default icons
        $icons = array(
            'add'       => '<a class="acf-js-tooltip" href="#" data-name="add-layout" data-context="layout" title="' . esc_attr__('Add layout','acf') . '"><span class="acf-icon -plus-alt "></span></a>',
            'duplicate' => '<a class="acf-js-tooltip" href="#" data-name="duplicate-layout" title="' . esc_attr__('Duplicate','acf') . '"><span class="acf-icon -duplicate-alt"></span></a>',
            'delete'    => '<a class="acf-js-tooltip" href="#" data-name="remove-layout" title="' . esc_attr__('Delete','acf') . '"><span class="acf-icon -trash-alt"></span></a>',
            'more'      => '<a class="acf-js-tooltip" aria-haspopup="menu" href="#" data-name="more-layout-actions" title="' . esc_attr__('More layout actions...','acf') . '"><span class="acf-icon -more-actions"></span></a>',
            'collapse'  => '<div class="acf-layout-collapse"><a class="acf-icon -collapse -clear" href="#" data-name="collapse-layout" aria-label="' . esc_attr__('Toggle layout','acf') . '"></a></div>'
        );
        
        // filters (with variations)
        $icons = apply_filters('acfe/flexible/layouts/icons', $icons, $layout, $field);
        
        // return
        return $icons;
        
    }
    
    
    /**
     * ajax_layout_title
     *
     * wp_ajax_acf/fields/flexible_content/layout_title
     */
    function ajax_layout_title(){
        
        // options
        $options = acf_parse_args($_POST, array(
            'post_id'   => 0,
            'i'         => 0,
            'field_key' => '',
            'nonce'     => '',
            'layout'    => '',
            'value'     => array(),
        ));
        
        // load field
        $field = acf_get_field($options['field_key']);
        if(!$field){
            die();
        }
        
        // vars
        $layout = $this->instance->get_layout($options['layout'], $field);
        if(!$layout){
            die();
        }
        
        // title
        $title = $this->get_layout_title($field, $layout, $options['i'], $options['value']);
        
        // echo
        echo $title;
        die();
        
    }
    
    
    /**
     * get_layout_title
     *
     * @param $field
     * @param $layout
     * @param $i
     * @param $value
     *
     * @return string
     */
    function get_layout_title($field, $layout, $i, $value){
        
        // vars
        $rows       = array();
        $rows[ $i ] = $value;
        
        // add loop
        acf_add_loop(array(
            'selector' => $field['name'],
            'name'     => $field['name'],
            'value'    => $rows,
            'field'    => $field,
            'i'        => $i,
            'post_id'  => 0,
        ));
        
        // vars
        $title = $layout['label'];
        $name = $field['_name'];
        $key = $field['key'];
        
        // filters (default ACF filters)
        $title = apply_filters("acf/fields/flexible_content/layout_title",              $title, $field, $layout, $i);
        $title = apply_filters("acf/fields/flexible_content/layout_title/name={$name}", $title, $field, $layout, $i);
        $title = apply_filters("acf/fields/flexible_content/layout_title/key={$key}",   $title, $field, $layout, $i);
        
        // remove loop
        acf_remove_loop();
        
        // return
        return acf_esc_html($title);
        
    }
    
    
    /**
     * get_layout_disabled
     *
     * Proxy function for ACF 6.5 disable layout feature
     *
     * @param $field
     * @param $i
     *
     * @return bool
     */
    function get_layout_disabled($field, $i){
        
        // default (all acf versions)
        $disabled_layouts = array();
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            $disabled_layouts = $this->instance->get_disabled_layouts($this->instance->post_id, $field);
        }
        
        // get disabled
        $disabled = in_array($i, $disabled_layouts, true);
        $disabled = apply_filters('acfe/flexible/layout_disabled', $disabled, $field, $i); // (with variations)
        
        return $disabled;
        
    }
    
    
    /**
     * get_layout_renamed
     *
     * Proxy function for ACF 6.5 rename layout feature
     *
     * @param $field
     * @param $i
     *
     * @return mixed|string
     */
    function get_layout_renamed($field, $i){
        
        // default (all acf versions)
        $renamed_layouts = array();
        
        // ACF 6.5+
        if(acfe_is_acf_65()){
            $renamed_layouts  = $this->instance->get_renamed_layouts($this->instance->post_id, $field);
        }
        
        // get renamed
        $renamed = !empty($renamed_layouts[ $i ]) ? $renamed_layouts[ $i ] : '';
        $renamed = apply_filters('acfe/flexible/layout_renamed', $renamed, $field, $i); // (with variations)
        
        return $renamed;
        
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     */
    function translate_field($field){
        
        if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_title'])){
            $field['acfe_flexible_modal']['acfe_flexible_modal_title'] = acf_translate($field['acfe_flexible_modal']['acfe_flexible_modal_title']);
        }
        
        if(isset($field['acfe_flexible_close_button_label'])){
            $field['acfe_flexible_close_button_label'] = acf_translate($field['acfe_flexible_close_button_label']);
        }
        
        if(isset($field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_close_label'])){
            $field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_close_label'] = acf_translate($field['acfe_flexible_modal_settings']['acfe_flexible_modal_settings_close_label']);
        }
    
        // loop
        if(!empty($field['layouts'])){
            foreach($field['layouts'] as &$layout){
            
                if(isset($layout['acfe_flexible_category'])){
                    $layout['acfe_flexible_category'] = acf_translate($layout['acfe_flexible_category']);
                }
            
            }
        }
    
        // return
        return $field;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content');

endif;

// includes
acfe_include('includes/fields/field-flexible-content-actions.php');
acfe_include('includes/fields/field-flexible-content-actions-title.php');
acfe_include('includes/fields/field-flexible-content-actions-toggle.php');
acfe_include('includes/fields/field-flexible-content-async.php');
acfe_include('includes/fields/field-flexible-content-controls.php');
acfe_include('includes/fields/field-flexible-content-compatibility.php');
acfe_include('includes/fields/field-flexible-content-hide.php');
acfe_include('includes/fields/field-flexible-content-hooks.php');
acfe_include('includes/fields/field-flexible-content-popup.php');
acfe_include('includes/fields/field-flexible-content-preview.php');
acfe_include('includes/fields/field-flexible-content-modal-edit.php');
acfe_include('includes/fields/field-flexible-content-modal-select.php');
acfe_include('includes/fields/field-flexible-content-modal-settings.php');
acfe_include('includes/fields/field-flexible-content-state.php');
acfe_include('includes/fields/field-flexible-content-thumbnail.php');
acfe_include('includes/fields/field-flexible-content-wysiwyg.php');
