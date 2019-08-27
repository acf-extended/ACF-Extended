<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Field Group Options: Note
 */
add_action('acf/render_field_group_settings', 'acfe_render_field_group_options');
function acfe_render_field_group_options($field_group){
    
    acf_render_field_wrap(array(
        'label'         => __('Note'),
        'name'          => 'acfe_note',
        'prefix'        => 'acf_field_group',
        'type'          => 'textarea',
        'instructions'	=> __('Personal note. Only visible to administrators'),
        'value'         => (isset($field_group['acfe_note'])) ? $field_group['acfe_note'] : '',
        'required'      => false,
    ));
    
}

/**
 * Field Group Options: Data
 */
add_action('acf/field_group/admin_head', 'acfe_render_field_group_settings');
function acfe_render_field_group_settings(){
    
    add_meta_box('acf-field-group-acfe', __('Data', 'acfe'), function(){
        
        global $field_group;
        
        acf_render_field_wrap(array(
            'label'         => __('Custom meta data'),
            'name'          => 'acfe_meta',
            'key'           => 'acfe_meta',
            'instructions'  => '',
            'prefix'        => 'acf_field_group',
            'type'          => 'repeater',
            'button_label'  => __('+ Add row'),
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
        
        acf_render_field_wrap(array(
            'label'         => __('Field group data'),
            'instructions'  => __('View raw data'),
            'type'          => 'acfe_dynamic_message',
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
add_action('post_submitbox_misc_actions', 'acfe_render_field_group_submitbox');
function acfe_render_field_group_submitbox(){
    
    if(get_post_type(get_the_ID()) !== 'acf-field-group')
        return;
    
    $field_group = acf_get_field_group(get_the_ID());
    ?>
    <div class="misc-pub-section misc-pub-acfe-field-group-key">
        <span style="font-size:16px;color: #82878c;" class="dashicons dashicons-tag"></span> <code style="font-size: 12px;"><?php echo $field_group['key']; ?></code>
    </div>
    <script type="text/javascript">
    (function($) {
        $('.misc-pub-acfe-field-group-key').insertBefore('.misc-pub-post-status');
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
    
    if($field['_name'] != 'acfe_autosync')
        return;
    
    global $field_group;
    
    // PHP
    
    // Fix to load local fiel groups
    acf_enable_filters();
    
        if(acfe_has_field_group_autosync($field_group, 'php') && !acf_get_setting('acfe_php_found')){
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
        
        if(!acfe_folder_exists('acf-json')){
            
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
    echo '<div class="acfe-modal" data-modal-key="' . $field_group['key'] . '"><div style="padding:15px;"><pre>' . print_r($field_group, true) . '</pre></div>';
    
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

/**
 * Hooks: Default label placement - Left
 */
add_filter('acf/validate_field_group', 'acfc_field_group_default_options');
function acfc_field_group_default_options($field_group){
    
    if(!isset($field_group['location']) || empty($field_group['location']))
        $field_group['label_placement'] = 'left';
    
    return $field_group;
    
}