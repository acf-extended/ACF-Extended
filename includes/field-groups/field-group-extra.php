<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_group_extra')):

class acfe_field_group_extra{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acf/field_group/admin_head', array($this, 'admin_head'));
        
    }
    
    
    /**
     * admin_head
     */
    function admin_head(){

        // extra metabox
        add_action('acfe/field_group/render_extra_metabox', array($this, 'render_extra_metabox'));

        // acfe data
        add_action('acf/render_field/name=acfe.data', array($this, 'render_data'));

    }
    
    
    /**
     * render_extra_metabox
     */
    function render_extra_metabox(){
        
        global $field_group;
        
        // meta
        acfe_render_group_setting($field_group, array(
            'label'         => __('Custom meta data', 'acfe'),
            'name'          => 'acfe.meta',
            'instructions'  => __('Add custom meta data to the field group.', 'acfe'),
            'type'          => 'repeater',
            'button_label'  => __('+ Meta'),
            'required'      => false,
            'layout'        => 'table',
            'wrapper'       => array(
                'data-enable-switch' => true
            ),
            'sub_fields'    => array(
                array(
                    'ID'            => false,
                    'label'         => __('Key', 'acfe'),
                    'name'          => 'acfe.meta.key',
                    'key'           => 'key',
                    'prefix'        => '',
                    '_name'         => '',
                    '_prepare'      => '',
                    'type'          => 'text',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                ),
                array(
                    'ID'            => false,
                    'label'         => __('Value', 'acfe'),
                    'name'          => 'acfe.meta.value',
                    'key'           => 'value',
                    'prefix'        => '',
                    '_name'         => '',
                    '_prepare'      => '',
                    'type'          => 'text',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                ),
            )
        ), 'div', 'label');

        // note
        acfe_render_group_setting($field_group, array(
            'label'         => __('Note', 'acfe'),
            'name'          => 'acfe.note',
            'type'          => 'textarea',
            'instructions'  => __('Add personal note. Only visible to administrators', 'acfe'),
            'required'      => false,
            'wrapper'       => array(
                'data-enable-switch' => true
            ),
        ), 'div', 'label');
        
        // data
        acfe_render_group_setting($field_group, array(
            'label'         => __('Field group data', 'acfe'),
            'instructions'  => __('View raw field group data, for development use', 'acfe'),
            'name'          => 'acfe.data',
            'type'          => 'acfe_dynamic_render',
            'value'         => $field_group['key'],
        ), 'div', 'label');
        
        ?>
        <script type="text/javascript">
            if(typeof acf !== 'undefined'){
                acf.postbox.render({
                    'id':       'acf-field-group-acfe-extra',
                    'label':    'left'
                });
            }
        </script>
        <?php
    }
    
    
    /**
     * render_data
     *
     * @param $field
     */
    function render_data($field){
        
        // get field group
        $field_group = acf_get_field_group($field['value']);
        
        if(!$field_group){
            echo '<a href="#" class="button disabled" disabled>' . __('Data') . '</a>';
            return;
        }
        
        // esc field group
        $field_group = @map_deep($field_group, 'esc_html');
        
        // get raw field group
        $raw_field_group = get_post($field_group['ID']);
        $raw_field_group = @map_deep($raw_field_group, 'esc_html');
        
        ?>
        <a href="#" class="acf-button button" data-modal><?php _e('Data', 'acfe'); ?></a>
        <div class="acfe-modal" data-title="<?php echo $field_group['title']; ?>" data-footer="<?php _e('Close', 'acfe'); ?>">
            <div class="acfe-modal-spacer">
                <pre style="margin-bottom:15px;"><?php print_r($field_group); ?></pre>
                <pre><?php print_r($raw_field_group); ?></pre>
            </div>
        </div>
        <?php
        
    }
    
}

// initialize
acf_new_instance('acfe_field_group_extra');

endif;