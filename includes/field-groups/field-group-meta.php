<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(acfe_get_setting('modules/field_group_ui'))
    return;

if(!class_exists('acfe_field_group_meta')):

class acfe_field_group_meta{
 
    function __construct(){
        
        add_action('acf/field_group/admin_head', array($this, 'admin_head'));
        add_action('acf/field_group/admin_head', array($this, 'prepare_meta'));
        
    }
    
    /**
     * Admin Head
     */
    function admin_head(){
        
        add_action('acf/render_field/name=acfe_data', array($this, 'render_data'));
        
        add_meta_box('acf-field-group-acfe', __('Field group', 'acf'), array($this, 'render_metabox'), 'acf-field-group', 'normal');
        
    }
    
    /*
     * Render Metabox
     */
    function render_metabox(){
        
        global $field_group;
        
        // Meta
        acf_render_field_wrap(array(
            'label'         => __('Custom meta data'),
            'name'          => 'acfe_meta',
            'key'           => 'acfe_meta',
            'instructions'  => __('Add custom meta data to the field group.'),
            'prefix'        => 'acf_field_group',
            'type'          => 'repeater',
            'button_label'  => __('+ Meta'),
            'required'      => false,
            'layout'        => 'table',
            'value'         => (isset($field_group['acfe_meta'])) ? $field_group['acfe_meta'] : array(),
            'wrapper'       => array(
                'data-enable-switch' => true
            ),
            'sub_fields'    => array(
                array(
                    'ID'            => false,
                    'label'         => __('Key'),
                    'name'          => 'acfe_meta_key',
                    'key'           => 'acfe_meta_key',
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
                    'label'         => __('Value'),
                    'name'          => 'acfe_meta_value',
                    'key'           => 'acfe_meta_value',
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
        ));
    
        // Note
        acf_render_field_wrap(array(
            'label'         => __('Note'),
            'name'          => 'acfe_note',
            'prefix'        => 'acf_field_group',
            'type'          => 'textarea',
            'instructions'  => __('Add personal note. Only visible to administrators'),
            'value'         => (isset($field_group['acfe_note'])) ? $field_group['acfe_note'] : '',
            'required'      => false,
            'wrapper'       => array(
                'data-enable-switch' => true
            ),
        ));
        
        // Data
        acf_render_field_wrap(array(
            'label'         => __('Field group data'),
            'instructions'  => __('View raw field group data, for development use'),
            'type'          => 'acfe_dynamic_render',
            'name'          => 'acfe_data',
            'prefix'        => 'acf_field_group',
            'value'         => $field_group['key'],
        ));
        
        ?>
        <script type="text/javascript">
            if(typeof acf !== 'undefined'){
                acf.postbox.render({
                    'id':       'acf-field-group-acfe',
                    'label':    'left'
                });
            }
        </script>
        <?php
    }
    
    /*
     * Render: Data button
     */
    function render_data($field){
        
        $field_group = acf_get_field_group($field['value']);
        
        if(!$field_group){
            
            echo '<a href="#" class="button disabled" disabled>' . __('Data') . '</a>';
            return;
            
        }
        
        $raw_field_group = get_post($field_group['ID']);
        
        ?>
        <a href="#" class="acf-button button" data-acfe-modal data-acfe-modal-title="<?php echo $field_group['title']; ?>" data-acfe-modal-footer="<?php _e('Close', 'acfe'); ?>"><?php _e('Data', 'acfe'); ?></a>
        <div class="acfe-modal">
            <div class="acfe-modal-spacer">
                <pre style="margin-bottom:15px;"><?php print_r($field_group); ?></pre>
                <pre><?php print_r($raw_field_group); ?></pre>
            </div>
        </div>
        <?php
        
    }
    
    /*
     * Prepare Meta
     */
    function prepare_meta(){
        
        $names = array('acfe_meta', 'acfe_meta_key', 'acfe_meta_value');
        
        foreach($names as $name){
            
            add_filter("acf/prepare_field/name={$name}", function($field){
                
                $field['prefix'] = str_replace('row-', '', $field['prefix']);
                $field['name'] = str_replace('row-', '', $field['name']);
                
                return $field;
                
            });
            
        }
        
    }
    
}

// initialize
new acfe_field_group_meta();

endif;