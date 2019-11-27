<?php

if(!defined('ABSPATH'))
    exit;

add_action('acf/update_field_group', 'acfe_field_group_update', 0);
function acfe_field_group_update($field_group){
    
    // Get Fields
    $fields = acf_get_fields($field_group);
    if(empty($fields))
        return;
    
    // Add acfe_form
    if(acf_maybe_get($field_group, 'acfe_form')){

        // Update Fields
        acfe_field_group_fields_add_fields_form($fields);
    
    }
    
    // Remove acfe_form
    else{
        
        // Update Fields
        acfe_field_group_fields_add_fields_form($fields, false);
        
    }
    
}

function acfe_field_group_fields_add_fields_form($fields, $add = true){
    
    if(empty($fields))
        return;
    
    foreach($fields as $field){
        
        // bypass clone
        if($field['type'] === 'clone')
            continue;
        
        // Group / Clone
        if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
            
            acfe_field_group_fields_add_fields_form($field['sub_fields'], $add);
            
        }
        
        // Flexible Content
        elseif(isset($field['layouts']) && !empty($field['layouts'])){
            
            foreach($field['layouts'] as $layout){
                
                if(isset($layout['sub_fields']) && !empty($layout['sub_fields'])){
                    
                    acfe_field_group_fields_add_fields_form($layout['sub_fields'], $add);
                    
                } 
            }
            
        }
        
        // Add
        if($add){
            
            if(acf_maybe_get($field, 'acfe_form'))
                continue;
            
            $field['acfe_form'] = true;
            
        }
        
        // Remove
        else{
            
            if(!acf_maybe_get($field, 'acfe_form'))
                continue;
            
            unset($field['acfe_form']);
            
            if(isset($field['acfe_settings']))
                unset($field['acfe_settings']);
            
            if(isset($field['acfe_validate']))
                unset($field['acfe_validate']);
            
        }
        
        acf_update_field($field);
        
    }
    
}

add_filter('acf/prepare_field/name=acfe_meta', 'acfe_field_group_meta_fix_repeater');
add_filter('acf/prepare_field/name=acfe_meta_key', 'acfe_field_group_meta_fix_repeater');
add_filter('acf/prepare_field/name=acfe_meta_value', 'acfe_field_group_meta_fix_repeater');
function acfe_field_group_meta_fix_repeater($field){
    
    $field['prefix'] = str_replace('row-', '', $field['prefix']);
    $field['name'] = str_replace('row-', '', $field['name']);
    
    return $field;
    
}

/**
 * Field Group Options: Data
 */
add_action('acf/field_group/admin_head', 'acfe_render_field_group_settings');
function acfe_render_field_group_settings(){
    
    add_meta_box('acf-field-group-acfe', __('Field group', 'acf'), function(){
        
        global $field_group;
        
        // Form settings
        acf_render_field_wrap(array(
            'label'         => __('Advanced settings'),
            'name'          => 'acfe_form',
            'prefix'        => 'acf_field_group',
            'type'			=> 'true_false',
			'ui'			=> 1,
            'instructions'	=> __('Enable advanced fields settings & validation'),
            'value'         => (isset($field_group['acfe_form'])) ? $field_group['acfe_form'] : '',
            'required'      => false,
        ));
        
        // Meta
        acf_render_field_wrap(array(
            'label'         => __('Custom meta data'),
            'name'          => 'acfe_meta',
            'key'           => 'acfe_meta',
            'instructions'  => __('Add custom meta data to the field group. Can be retrived using <code>acf_get_field_group()</code>'),
            'prefix'        => 'acf_field_group',
            'type'          => 'repeater',
            'button_label'  => __('+ Meta'),
            'required'      => false,
            'layout'        => 'table',
            'value'         => (isset($field_group['acfe_meta'])) ? $field_group['acfe_meta'] : array(),
            'sub_fields'    => array(
                array(
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
        
        // Data
        
        acf_render_field_wrap(array(
            'label'         => __('Field group data'),
            'instructions'  => __('View raw field group data, for development use'),
            'type'          => 'acfe_dynamic_message',
            'name'          => 'acfe_data',
            'prefix'        => 'acf_field_group',
            'value'         => $field_group['key'],
        ));
        
        // Note
        acf_render_field_wrap(array(
            'label'         => __('Note'),
            'name'          => 'acfe_note',
            'prefix'        => 'acf_field_group',
            'type'          => 'textarea',
            'instructions'	=> __('Add personal note. Only visible to administrators'),
            'value'         => (isset($field_group['acfe_note'])) ? $field_group['acfe_note'] : '',
            'required'      => false,
        ));

        ?>
        <script type="text/javascript">
        if(typeof acf !== 'undefined'){
            acf.postbox.render({
                'id':       'acf-field-group-acfe',
                'label':    'left'
            });	
        }
        
        jQuery(document).ready(function($){
            $('#post_name').on('keyup', function(){
                var val = $(this).val();
                if(!val.startsWith('group_')){
                    var val = 'group_' + val;
                    $(this).val(val);
                }
                
                $('[name="acf_field_group[key]"]').val(val);
                $('.misc-pub-acfe-field-group-key code').html(val);
            });
        });
        </script>
        <?php
    }, 'acf-field-group', 'normal');
    
}

/**
 * Field Group Options: Sidebar - Submit Div
 */
add_action('post_submitbox_misc_actions', 'acfe_render_field_group_submitbox', 11);
function acfe_render_field_group_submitbox($post){
    
    if($post->post_type !== 'acf-field-group')
        return;
    
    global $field_group;
    ?>
    <div class="misc-pub-section misc-pub-acfe-field-group-key" style="padding-top:2px;">
        <span style="font-size:16px;color: #82878c;width: 20px;margin-right: 2px;" class="dashicons dashicons-tag"></span> <code style="font-size: 12px;"><?php echo $field_group['key']; ?></code>
    </div>
    <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
        <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=php&keys=' . $field_group['key']); ?>">PHP</a> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=json&keys=' . $field_group['key']); ?>">Json</a>
    </div>
    <script type="text/javascript">
    (function($) {
        $('.misc-pub-acfe-field-group-key').insertAfter('.misc-pub-post-status');
        $('.misc-pub-acfe-field-group-export').insertAfter('.misc-pub-post-status');
    })(jQuery);	
    </script>
    <?php
    
}

/**
 * Field Group Options: Sidebar
 */
add_action('acf/field_group/admin_head', 'acfe_render_field_group_settings_side');
function acfe_render_field_group_settings_side(){
    
    add_meta_box('acf-field-group-acfe-side', __('Advanced Settings', 'acfe'), function(){
        
        // Global
        global $field_group;
        
        // Proxy
        $_field_group = $field_group;
        
        acf_render_field_wrap(array(
            'label'         => __('Display title', 'acfe'),
            'instructions'  => __('Render this title on edit post screen', 'acfe'),
            'type'          => 'text',
            'name'          => 'acfe_display_title',
            'prefix'        => 'acf_field_group',
            'value'         => (isset($field_group['acfe_display_title'])) ? $field_group['acfe_display_title'] : '',
            'placeholder'   => '',
            'prepend'       => '',
            'append'        => ''
        ));
        
        if(acfe_is_field_group_json_desync($field_group)){
            acf_render_field_wrap(array(
                'label'         => __('Json Desync'),
                'instructions'  => __('Local json file is different from this version. If you manually synchronize it, you will lose your current field group settings'),
                'type'          => 'acfe_dynamic_message',
                'name'          => 'acfe_sync_available',
                'prefix'        => 'acf_field_group',
                'value'         => $field_group['key'],
            ));
        }
        
        
        $force_json_sync = false;
        $json_text = 'Json';
        
        if(acfe_has_field_group_autosync_file($_field_group, 'json')){
            
            if(isset($_field_group['acfe_autosync']) && is_array($_field_group['acfe_autosync']))
                $_field_group['acfe_autosync'][] = 'json';
            else
                $_field_group['acfe_autosync'] = array('json');
            
            $json_text = '<span class="acf-js-tooltip" title="To disable the Json Sync you must manually delete the file: '.$_field_group['key'].'.json">Json</span>';
            
            $force_json_sync = true;
            
        }
        
        $force_php_sync = false;
        $php_text = 'PHP';
        acf_enable_filter('local');
        
        if(acfe_has_field_group_autosync_file($_field_group, 'php')){
            
            acf_disable_filter('local');
            
            if(isset($_field_group['acfe_autosync']) && is_array($_field_group['acfe_autosync']))
                $_field_group['acfe_autosync'][] = 'php';
            else
                $_field_group['acfe_autosync'] = array('php');
            
            $php_text = '<span class="acf-js-tooltip" title="To disable the PHP Sync you must manually delete the file: '.$_field_group['key'].'.php">PHP</span>';
            
            $force_php_sync = true;
            
        }
        
        acf_render_field_wrap(array(
            'label'         => __('Auto Sync'),
            'instructions'  => '',
            'type'          => 'checkbox',
            'name'          => 'acfe_autosync',
            'prefix'        => 'acf_field_group',
            'value'         => (isset($_field_group['acfe_autosync']) && !empty($_field_group['acfe_autosync'])) ? $_field_group['acfe_autosync'] : array(),
            'choices'       => array(
				'php'   => $php_text,
				'json'  => $json_text,
			)
        ));
        
        acf_render_field_wrap(array(
            'label'         => __('Permissions'),
            'name'          => 'acfe_permissions',
            'prefix'        => 'acf_field_group',
            'type'          => 'checkbox',
            'instructions'	=> __('Select user roles that are allowed to view and edit this field group in post edition'),
            'required'      => false,
            'default_value' => false,
            'choices'       => acfe_get_roles(),
            'value'         => (isset($field_group['acfe_permissions'])) ? $field_group['acfe_permissions'] : array(),
            'layout'        => 'vertical'
        ));
        
        ?>
        <script type="text/javascript">
        if(typeof acf !== 'undefined'){
            acf.postbox.render({
                'id':       'acf-field-group-acfe-side',
                'label':    'top'
            });
        }
        
        (function($){
            
            <?php if($force_json_sync){ ?>
                
                $('#acf_field_group-acfe_autosync-json').prop('readonly', true).addClass('disabled').click(function(){
                    return false;
                });
                
                $('#acf_field_group-acfe_autosync-json').closest('label').css('color', '#999');
                
            <?php } ?>
            
            <?php if($force_php_sync){ ?>
                
                $('#acf_field_group-acfe_autosync-php').prop('readonly', true).addClass('disabled').click(function(){
                    return false;
                });
                
                $('#acf_field_group-acfe_autosync-php').closest('label').css('color', '#999');
                
            <?php } ?>
            
            if($('[data-name=acfe_sync_available]').length){
                
                if($('[data-name=acfe_sync_available]').find('[data-acfe-autosync-json-active]').attr('data-acfe-autosync-json-active') === '0'){
                    $('#acf_field_group-acfe_autosync-json').change(function(e){
                        if($(this).prop('checked')){
                            if(!confirm('Local json file was found and is different from this version.' + "\n" + 'Enabling json auto sync will erase the local file with the current field group settings')){
                                $(this).prop('checked', false);
                                return false;
                            }
                        }
                    });
                }
                
                else{
                
                    $('#publish').click(function(e){
                        if(!confirm('Local json file was found and is different from this version.' + "\n" + 'Proceed to erase the local file with the current field group settings'))
                            e.preventDefault();
                    });
                
                }
            }
        })(jQuery);
        </script>
        <?php
    }, 'acf-field-group', 'side');
    
}

/**
 * Render: Sync Available
 */
add_action('acf/render_field/name=acfe_sync_available', 'acfe_render_field_sync_available');
function acfe_render_field_sync_available($field){
    
    $field_group = acf_get_field_group($field['value']);
    
    $acfe_autosync_active = 0;
    if(isset($field_group['acfe_autosync']) && is_array($field_group['acfe_autosync']) && in_array('json', $field_group['acfe_autosync']))
        $acfe_autosync_active = 1;
    
    $nonce = wp_create_nonce('bulk-posts');
    echo '<a data-acfe-autosync-json-active="'.$acfe_autosync_active.'" class="button" href="'.admin_url('edit.php?post_type=acf-field-group&post_status=sync&acfsync=' . $field['value'] . '&_wpnonce=' . $nonce).'">Synchronize</a>';
    
}

/**
 * Render: Sync Warnings
 */
add_action('acf/render_field', 'acfe_render_field_acfe_sync_warnings', 5);
function acfe_render_field_acfe_sync_warnings($field){
    
    if($field['_name'] !== 'acfe_autosync')
        return;
    
    global $field_group;
    
    // PHP
    
    // Fix to load local fiel groups
    acf_enable_filters();
    
        if(acfe_has_field_group_autosync($field_group, 'php') && !acf_get_setting('acfe/php_found')){
            echo '<p class="description"><span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="dashicons dashicons-warning"></span> Folder <code style="font-size:11px;">/acfe-php</code> was not found in your theme. You must create it to activate PHP Sync</p>';
        }
        
        elseif(!acfe_has_field_group_autosync($field_group, 'php') && acfe_has_field_group_autosync_file($field_group, 'php')){
            echo '<p class="description"><span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="dashicons dashicons-warning"></span> This field group is registered via a third-party PHP code</p>';
        }
        
        elseif(acfe_has_field_group_autosync($field_group, 'php') && !acfe_has_field_group_autosync_file($field_group, 'php')){
            echo '<p class="description"><span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="dashicons dashicons-warning"></span> <code style="font-size:11px;">' . $field_group['key'] . '.php</code> will be created upon update</p>';
        }
    
    // Re-disable filters, as natively
    acf_disable_filters();
    
    // Json
    if(acfe_has_field_group_autosync($field_group, 'json') && !acfe_has_field_group_autosync_file($field_group, 'json')){
        
        if(!acf_get_setting('acfe/json_found')){
            
            echo '<p class="description"><span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="dashicons dashicons-warning"></span> Folder <code style="font-size:11px;">/acf-json</code> was not found in your theme. You must create it to activate Json Sync.</p>';
            
        }
        
        else{
        
            echo '<p class="description"><span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="dashicons dashicons-warning"></span> <code style="font-size:11px;">' . $field_group['key'] . '.json</code> will be created upon update.</p>';
            
        }
    }
    
}

/**
 * Render: Data button
 */
add_action('acf/render_field/name=acfe_data', 'acfe_render_field_group_data');
function acfe_render_field_group_data($field){
    
    $field_group = acf_get_field_group($field['value']);
    if(!$field_group){
        echo '<a href="#" class="button disabled" disabled>' . __('Data') . '</a>';
        return;
    }
    
    echo '<a href="#" class="button acfe_modal_open" data-modal-key="' . $field_group['key'] . '">' . __('Data') . '</a>';
    echo '<div class="acfe-modal" data-modal-key="' . $field_group['key'] . '"><div style="padding:15px;"><pre>' . print_r($field_group, true) . '</pre></div></div>';
    
}

/**
 * Hooks: Display title (post edit)
 */
add_filter('acf/get_field_groups', 'acfe_render_field_groups', 999);
function acfe_render_field_groups($field_groups){
    
    if(!is_admin())
        return $field_groups;
    
    $check_current_screen = acf_is_screen(array(
        'edit-acf-field-group',
        'acf-field-group',
        'acf_page_acf-tools'
    ));
    
    if($check_current_screen)
        return $field_groups;
    
    foreach($field_groups as &$field_group){
        if(!isset($field_group['acfe_display_title']) || empty($field_group['acfe_display_title']))
            continue;
        
        $field_group['title'] = $field_group['acfe_display_title'];
    }
    
    return $field_groups;
    
}

/**
 * Hooks: Permissions (post edit)
 */
add_filter('acf/get_field_groups', 'acfe_permissions_field_groups', 999);
function acfe_permissions_field_groups($field_groups){
    
    if(!is_admin())
        return $field_groups;
    
    $check_current_screen = acf_is_screen(array(
        'edit-acf-field-group',
        'acf-field-group',
        'acf_page_acf-tools'
    ));
    
    if($check_current_screen)
        return $field_groups;
    
    $current_user_roles = acfe_get_current_user_roles();
    
    foreach($field_groups as $key => $field_group){
        if(!isset($field_group['acfe_permissions']) || empty($field_group['acfe_permissions']))
            continue;
        
        $render_field_group = false;
        
        foreach($current_user_roles as $current_user_role){
            foreach($field_group['acfe_permissions'] as $field_group_role){
                if($current_user_role !== $field_group_role)
                    continue;
                
                $render_field_group = true;
                break;
            }
            
            if($render_field_group)
                break;
        }
        
        if(!$render_field_group)
            unset($field_groups[$key]);
    }
    
    return $field_groups;
    
}

add_filter('acf/prepare_field/name=instruction_placement', 'acfe_field_group_instruction_placement');
function acfe_field_group_instruction_placement($field){
    
    $field['choices'] = array_merge($field['choices'], array('acfe_instructions_tooltip' => 'Tooltip'));
    
    return $field;
    
}

/**
 * Hooks: Default label placement - Left
 */
add_filter('acf/validate_field_group', 'acfc_field_group_default_options');
function acfc_field_group_default_options($field_group){
    
    if(!isset($field_group['location']) || empty($field_group['location']))
        $field_group['label_placement'] = 'left';
    
    return $field_group;
    
}

add_filter('acf/prepare_field_group_for_export', 'acfc_field_group_export_categories');
function acfc_field_group_export_categories($field_group){
    
    $_field_group = acf_get_field_group($field_group['key']);
    if(empty($_field_group))
        return $field_group;
    
    if(!acf_maybe_get($_field_group, 'ID'))
        return $field_group;
    
    $categories = get_the_terms($_field_group['ID'], 'acf-field-group-category');
    
    if(empty($categories) || is_wp_error($categories))
        return $field_group;
    
    $field_group['acfe_categories'] = array();
    
    foreach($categories as $term){
        
        $field_group['acfe_categories'][$term->slug] = $term->name;
        
    }
    
    return $field_group;
    
}

add_action('acf/import_field_group', 'acfc_field_group_import_categories');
function acfc_field_group_import_categories($field_group){
    
    if(!$categories = acf_maybe_get($field_group, 'acfe_categories'))
        return;
    
    foreach($categories as $term_slug => $term_name){
        
        $new_term_id = false;
        $get_term = get_term_by('slug', $term_slug, 'acf-field-group-category');
        
        // Term doesn't exists
        if(empty($get_term)){
            
            $new_term = wp_insert_term($term_name, 'acf-field-group-category', array(
                'slug' => $term_slug
            ));
            
            if(!is_wp_error($new_term)){
                
                $new_term_id = $new_term->term_id;
                
            }
            
        }
        
        // Term already exists
        else{
            
            $new_term_id = $get_term->term_id;
            
        }
        
        if($new_term_id){
            
            wp_set_post_terms($field_group['ID'], array($new_term_id), 'acf-field-group-category', true);
            
        }
        
    }
    
}