<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Display Title (post states)
 */
add_filter('display_post_states', 'acfe_field_groups_states', 10, 2);
function acfe_field_groups_states($states, $post){
    
    if(!acf_is_screen('edit-acf-field-group'))
        return $states;
    
    if(get_post_type($post->ID) != 'acf-field-group')
        return $states;
    
    $field_group = acf_get_field_group($post->ID);
    
    if(!$field_group || !isset($field_group['acfe_display_title']) || empty($field_group['acfe_display_title']))
        return $states;
    
    $states[] = $field_group['acfe_display_title'];
    
    return $states;
    
}

/**
 * Table Columns
 */
add_filter('manage_edit-acf-field-group_columns', 'acfe_field_groups_column', 999);
function acfe_field_groups_column($columns){
    
    // Locations
    $columns['acfe-locations'] = __('Locations');
    
    // Load
    $columns['acfe-local'] = __('Load');
    
    // PHP sync
    if(acf_get_setting('acfe/php'))
        $columns['acfe-autosync-php'] = __('PHP sync');
    
    // Json sync
    if(acf_get_setting('json'))
        $columns['acfe-autosync-json'] = __('Json sync');
    
    // Fix 'Sync' screen columns
    if(acf_maybe_get_GET('post_status') === 'sync'){
        
        unset($columns['acf-field-group-category']);
        
        unset($columns['acfe-locations']);
        unset($columns['acfe-local']);
        
        if(isset($columns['acfe-autosync-php']))
            unset($columns['acfe-autosync-php']);
        
        if(isset($columns['acfe-autosync-json']))
            unset($columns['acfe-autosync-json']);
        
    }
    
    // Fix 'Third party' screen columns
    elseif(acf_maybe_get_GET('post_status') === 'acfe-third-party'){
        
        $columns = array(
            'title'             => __('Title', 'acf'),
            'acfe-source'       => __('Source', 'acf'),
            'acf-fg-count'      => __('Fields', 'acf'),
            'acfe-locations'    => __('Locations', 'acf'),
            'acfe-local'        => __('Load', 'acf'),
        );
        
    }
    
    // Remove 'Field Group Category' column if there is no terms
    $categories = get_terms(array(
        'taxonomy'      => 'acf-field-group-category',
        'hide_empty'    => false,
    ));
    
    if(empty($categories) && isset($columns['acf-field-group-category']))
        unset($columns['acf-field-group-category']);
    
    return $columns;
    
}

/**
 * Table Columns HTML
 */
add_action('manage_acf-field-group_posts_custom_column', 'acfe_field_groups_column_html', 10, 2);
function acfe_field_groups_column_html($column, $post_id){
    
    /**
     * Count
     */
    if($column === 'acfe-count'){
        
        $field_group = acf_get_field_group($post_id);
        echo esc_html(acf_get_field_count($field_group));
    
    }
    
    /**
     * Count
     */
    elseif($column === 'acfe-source'){
        
        $field_group = acf_get_field_group($post_id);
        
        $source = false;
        
        // ACF Extended
        if(strpos($post_id, 'group_acfe_') === 0){
            
            $source = 'ACF Extended';
            
        }
        
        // Advanced Forms
        elseif($post_id === 'group_form_settings' || $post_id === 'group_entry_data'){
            
            $source = 'Advanced Forms';
            
        }
        
        else{
            
            $source = '<span style="color:#aaa;">' . __('Unknown', 'acf') . '</span>';
            
        }
        
        $source = apply_filters('acfe/field_groups_third_party/source', $source, $post_id, $field_group);
        
        echo $source;
    
    }
    
    /**
     * Locations
     */
    elseif($column === 'acfe-locations'){
        
        $field_group = acf_get_field_group($post_id);
        $choices = acf_get_location_rule_types();
        
        if(!isset($field_group['location']) || empty($field_group['location']) ||empty($choices))
            return;
        
        $final = array();
        
        $icon_default = 'admin-generic';
            
        $icons = array(
            'edit' => array(
                'post_type',
                'post_template',
                'post_status',
                'post_format',
                'post',
            ),
            'media-default' => array(
                'page_template',
                'page_type',
                'page_parent',
                'page',
            ),
            'admin-users' => array(
                'current_user',
                'user_form',
            ),
            'welcome-widgets-menus' => array(
                'widget',
                'nav_menu',
                'nav_menu_item',
            ),
            'category' => array(
                'taxonomy',
                'post_category',
                'post_taxonomy',
            ),
            'admin-comments' => array(
                'comment',
            ),
            'paperclip' => array(
                'attachment',
            ),
            'admin-settings' => array(
                'options_page',
            ),
            'businessman' => array(
                'current_user_role',
                'user_role',
            ),
        );
        
        foreach($choices as $key => $sub_choices){
            
            foreach($sub_choices as $choice_slug => $choice_name){
                
                $final_icon = $icon_default;
                foreach($icons as $icon => $icon_slugs){
                    foreach($icon_slugs as $icon_slug){
                        if($choice_slug != $icon_slug)
                            continue;

                        $final_icon = $icon;
                        break(2);
                    }
                }
                
                $final[$choice_slug] = array(
                    'name' => $choice_name,
                    'icon' => $final_icon
                );
                
            }
            
        }
        
        
        
        $html = array();
        foreach($field_group['location'] as $or){
            
            foreach($or as $and){
                
                if(!isset($final[$and['param']])|| !isset($and['value']))
                    continue;
                
                $final_name = $and['value'];
                $values = acf_get_location_rule_values($and);
                
                if(!empty($values) && is_array($values)){
                    
                    foreach($values as $value_slug => $value_name){
                        
                        if($and['value'] != $value_slug)
                            continue;
                        
                        if(is_array($value_name) && isset($value_name[$and['value']])){
                            
                            $final_name = $value_name[$and['value']];
                            
                        }else{
                            
                            $final_name = $value_name;
                            
                        }
                        
                        break;
                        
                    }
                    
                }
                
                $name = '<span class="acf-js-tooltip dashicons dashicons-' . $final[$and['param']]['icon'] . '" title="' . $final[$and['param']]['name'] . ' = ' . $final_name . '"></span>';
                if($and['operator'] === '!=')
                    $name = '<span class="acf-js-tooltip dashicons dashicons-' . $final[$and['param']]['icon'] . '" title="' . $final[$and['param']]['name'] . ' != ' . $final_name . '" style="color:#ccc;"></span>';
                
                $html[] = $name;
                
            }
            
        }
        
        echo implode(' ', $html);
        
    }
    
    /**
     * Load
     */
    elseif($column === 'acfe-local'){
        
        if(!$field_group = acf_get_field_group($post_id))
            return;
        
        $local_field_group = acf_get_local_field_group($field_group['key']);
        $local_field_group_type = acf_maybe_get($local_field_group, 'local', false);
        
        if($local_field_group_type === 'php'){
            
            echo '<span class="acf-js-tooltip" title="' . $field_group['key'] . ' is registered locally">php</span>';
            
            return;
            
        }
        
        elseif($local_field_group_type === 'json'){
            
            echo '<span class="acf-js-tooltip" title="' . $field_group['key'] . ' is registered locally">json</span>';
            
            return;
            
        }
        
        else{
        
            echo '<span class="acf-js-tooltip" title="' . $field_group['key'] . ' is not registered locally">DB</span>';
            
            return;
            
        }
        
    }
    
    /**
     * PHP sync
     */
    elseif($column === 'acfe-autosync-php'){
        
        if(!$field_group = acf_get_field_group($post_id))
            return;
        
        if(!acfe_has_field_group_autosync($field_group, 'php')){
            
            echo '<span style="color:#ccc" class="dashicons dashicons-no-alt"></span>';
            
            if(acfe_has_field_group_autosync_file($field_group, 'php')){
                echo '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Field group: ' . $field_group['key'] . ' is registered via a third-party PHP code"></span>';
            }
                
            return;
            
        }
        
        if(!acf_get_setting('acfe/php_found')){
            
            echo '<span style="color:#ccc" class="dashicons dashicons-yes"></span>';
            
            echo '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Folder \'/acfe-php\' was not found in your theme.<br />You must create it to activate this setting"></span>';
            
        }
        
        elseif(!acfe_has_field_group_autosync_file($field_group, 'php')){
            
            echo '<span style="color:#ccc" class="dashicons dashicons-yes"></span>';
            echo '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Local file ' . $field_group['key'] . '.php will be created upon update"></span>';
            
        }
        
        else{
            
            echo '<span class="dashicons dashicons-yes"></span>';
            
        }
        
    }
    
    /**
     * Json sync
     */
    elseif($column === 'acfe-autosync-json'){

        if(!$field_group = acf_get_field_group($post_id))
            return;
        
        if(acfe_has_field_group_autosync_file($field_group, 'json')){
            
            echo '<span class="dashicons dashicons-yes"></span>';
            
        }
        
        else{
        
            if(!acfe_has_field_group_autosync($field_group, 'json')){
                
                echo '<span style="color:#ccc" class="dashicons dashicons-no-alt"></span>';
                
            }else{
                
                echo '<span style="color:#ccc" class="dashicons dashicons-yes"></span>';
                
                if(!acf_get_setting('acfe/json_found')){
                    
                    echo '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Folder \'/acf-json\' was not found in your theme.<br />You must create it to activate this setting"></span>';
                    
                }
                
                else{
                    
                    echo '<span style="color:#ccc;font-size:16px;vertical-align:text-top;" class="acf-js-tooltip dashicons dashicons-warning" title="Local file ' . $field_group['key'] . '.json will be created upon update."></span>';
                    
                }
                
                
                
            }
        
        }
        
    }
}

/**
 * Table Row Actions
 */
add_filter('page_row_actions', 'hwk_post_type_exemple_row_actions', 10, 2);
function hwk_post_type_exemple_row_actions($actions, $post){
    
    if(!isset($post->post_type) || $post->post_type != 'acf-field-group')
        return $actions;
    
    $field_group = acf_get_field_group($post->ID);
    
    $actions['acfe-export-php'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=php&keys=' . $field_group['key']) . '">PHP</a>';
    $actions['acfe-export-json'] = '<a href="' . admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=json&keys=' . $field_group['key']) . '">Json</a>';
    
    $actions['acfe-key'] = '<span style="color:#555;"><code style="-webkit-user-select: all;-moz-user-select: all;-ms-user-select: all;user-select: all;font-size: 12px;">' . $field_group['key'] . '</code></span>';
    
    //$actions['acfe-id'] = '<span style="color:#555;">ID: ' . $field_group['ID'] . '</span>';
    
    return $actions;
    
}

/**
 * Sidebar
 */
add_action('current_screen', function(){
    
    if(!acf_is_screen('edit-acf-field-group'))
        return;
    
    add_action('admin_footer', function(){
        ?>
        
        <!-- ACFE: Label -->
        <script type="text/html" id="tmpl-acfe-label">
            <span style="word-wrap: break-word;padding: 2px 6px;margin-left:1px;border-radius:2px;background:#ca4a1f;color: #fff; font-size: 14px;vertical-align: text-bottom;font-style: italic;">Extended</span>
        </script>
        
        <!-- ACFE: Debug -->
        <script type="text/html" id="tmpl-acfe-debug">
            <div class="acf-box">
            </div>
        </script>
        
        <script type="text/javascript">
        (function($){
            
            // ACFE: Label
            $('.acf-column-2 > .acf-box > .inner > h2').append($('#tmpl-acfe-label').html());
            
            // ACFE: Debug
            //$('#posts-filter').append($('#tmpl-acfe-debug').html());
            
            // Fix no field groups found
            $('#the-list tr.no-items td').attr('colspan', 9);
            
        })(jQuery);
        </script>
        <?php
    });
    
});

/**
 * Hooks: Posts per page
 */
add_filter('edit_acf-field-group_per_page', 'acfe_field_groups_posts_per_page');
function acfe_field_groups_posts_per_page(){
    
    return 999;
    
}