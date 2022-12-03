<?php

if(!defined('ABSPATH')){
    exit;
}

/**
 * acfe_get_post_id_field_groups
 *
 * Get all field groups for a specific Post ID
 *
 * @param int $post_id
 *
 * @return array
 */
function acfe_get_post_id_field_groups($post_id = 0){
    
    /**
     * @string  $post_id  12   | term_46 | user_22 | my-option | comment_89 | widget_56 | menu_74 | menu_item_96 | block_my-block | blog_55 | site_36 | attachment_24
     * @string  $id       12   | 46      | 22      | my-option | 89         | widget_56 | 74      | 96           | block_my-block | 55      | 36      | 24
     * @string  $type     post | term    | user    | option    | comment    | option    | term    | post         | block          | blog    | blog    | post
     */
    $post_id = acf_get_valid_post_id($post_id);
    
    // extract
    extract(acf_decode_post_id($post_id));
    
    /** @var $type */
    /** @var $id */
    
    // vars
    $field_groups = array();
    $post_type = '';
    $taxonomy = '';
    
    // check post id is attachment
    if($type === 'post' && get_post_type($id) === 'attachment'){
        $post_id = "attachment_{$id}";
    }
    
    // override attachment
    if($type === 'post' && acfe_starts_with($post_id, 'attachment_')){
        
        $type = 'attachment';
        
    // override menu
    }elseif($type === 'term' && acfe_starts_with($post_id, 'menu_')){
        
        $type = 'menu';
        
    // override user list
    }elseif($post_id === 'user_options'){
        
        $type = 'user_list';
        
    // override attachment list
    }elseif($post_id === 'attachment_options'){
        
        $type = 'attachment_list';
        
    // override post type list
    }elseif($type === 'option' && strpos($post_id, 'tax_') === false && strpos($post_id, '_options') !== false){
        
        // check post types for post_type_list
        $post_types = acf_get_post_types();
        $found = false;
        
        // loop
        foreach($post_types as $_post_type){
            
            if($post_id !== "{$_post_type}_options") continue;
    
            $found = $_post_type;
            break;
            
        }
        
        if($found){
            
            $post_type = $found;
            $type = 'post_type_list';
            
        }
        
    // override taxonomy list
    }elseif($type === 'option' && strpos($post_id, 'tax_') === 0 && strpos($post_id, '_options') !== false){
        
        // check taxonomies for taxonomy_list
        $taxonomies = acf_get_taxonomies();
        $found = false;
        
        // loop
        foreach($taxonomies as $_taxonomy){
            
            if($post_id !== "tax_{$_taxonomy}_options") continue;
    
            $found = $_taxonomy;
            break;
            
        }
        
        if($found){
            
            $taxonomy = $found;
            $type = 'taxonomy_list';
            
        }
    
    // override settings page
    }elseif($type === 'option' && in_array($post_id, array('options-general', 'options-writing', 'options-reading', 'options-discussion', 'options-media', 'options-permalink'))){
        
        $type = 'settings_page';
        
    }
    
    // user
    if($type === 'user'){
        
        $keys = array();
        
        foreach(array('edit', 'new') as $user_form){
            
            $_field_groups = acf_get_field_groups(array(
                'user_id'   => $id,
                'user_form' => $user_form,
            ));
            
            foreach($_field_groups as $_field_group){
                
                if(in_array($_field_group['key'], $keys)) continue;
                
                $keys[] = $_field_group['key'];
                $field_groups[] = $_field_group;
                
            }
            
        }
        
    // attachment
    }elseif($type === 'attachment'){
        
        $field_groups = acf_get_field_groups(array(
            'attachment_id' => $id,
            'attachment'    => $id,
        ));
        
    // taxonomy
    }elseif($type === 'term'){
        
        $term = get_term($id);
        
        if($term && !is_wp_error($term)){
            
            $taxonomy = $term->taxonomy;
            
            $field_groups = acf_get_field_groups(array(
                'taxonomy' => $taxonomy,
                'term_id' => $id,
            ));
            
        }
        
    // post type
    }elseif($type === 'post'){
        
        $post_type = get_post_type($post_id);
        
        $field_groups = acf_get_field_groups(array(
            'post_id'   => $post_id,
            'post_type' => $post_type,
        ));
        
    // options page
    }elseif($type === 'option'){
        
        // vars
        $keys = array();
        $options_pages = array();
        
        // get all options pages using the post id
        foreach(acf_get_options_pages() as $page){
            
            if($page['post_id'] !== $id) continue;
            
            $options_pages[] = $page;
            
        }
        
        foreach($options_pages as $page){
            
            $_field_groups = acf_get_field_groups(array(
                'options_page' => $page['menu_slug'],
            ));
            
            foreach($_field_groups as $_field_group){
                
                if(in_array($_field_group['key'], $keys)) continue;
                
                $keys[] = $_field_group['key'];
                $field_groups[] = $_field_group;
                
            }
            
        }
        
    // nav menu
    }elseif($type === 'menu'){
        
        $field_groups = acf_get_field_groups(array(
            'screen'  => 'nav_menu',
            'post_id' => $id,
        ));
        
    // user list
    }elseif($type === 'user_list'){
    
        $field_groups = acf_get_field_groups(array(
            'user_list' => 1,
        ));
        
    // attachment list
    }elseif($type === 'attachment_list'){
    
        $field_groups = acf_get_field_groups(array(
            'attachment_list' => 1,
        ));
        
    // post type list
    }elseif($type === 'post_type_list'){
    
        $field_groups = acf_get_field_groups(array(
            'post_type_list' => $post_type,
        ));
        
    // taxonomy list
    }elseif($type === 'taxonomy_list'){
    
        $field_groups = acf_get_field_groups(array(
            'taxonomy_list' => $taxonomy,
        ));
        
    // settings page
    }elseif($type === 'settings_page'){
    
        $field_groups = acf_get_field_groups(array(
            'wp_settings' => $post_id
        ));
        
    }
    
    return $field_groups;
    
}

/**
 * acfe_get_locations_array
 *
 * Legacy way to retrieve Field Groups Locations data in ACF 5.8
 *
 * @param $locations
 *
 * @return array
 */
function acfe_get_locations_array($locations){
    
    $return = array();
    $types = acf_get_location_rule_types();
    
    if(!$locations || !$types){
        return $return;
    }
    
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
        'admin-appearance' => array(
            'acfe_template'
        )
    );
    
    $rules = array();
    
    foreach($types as $key => $type){
        
        foreach($type as $slug => $name){
            
            $icon = $icon_default;
            
            foreach($icons as $_icon => $icon_slugs){
                
                if(!in_array($slug, $icon_slugs)){
                    continue;
                }
                
                $icon = $_icon;
                break;
                
            }
            
            $rules[ $slug ] = array(
                'name'  => $slug,
                'label' => $name,
                'icon'  => $icon
            );
            
        }
        
    }
    
    foreach($locations as $group){
        
        if(!acf_maybe_get($rules, $group['param']) || !acf_maybe_get($group, 'value')){
            continue;
        }
        
        // init
        $rule = $rules[ $group['param'] ];
        
        // vars
        $icon = $rule['icon'];
        $name = $rule['name'];
        $label = $rule['label'];
        $operator = $group['operator'] === '==' ? '=' : $group['operator'];
        $value = $group['value'];
        
        // Exception for Post/Page/page Parent ID
        if(in_array($group['param'], array('post', 'page', 'page_parent'))){
            
            $value = get_the_title((int) $value);
            
        }else{
            
            // Validate value
            $values = acf_get_location_rule_values($group);
            
            if(!empty($values) && is_array($values)){
                
                foreach($values as $value_slug => $value_name){
                    
                    if($value != $value_slug){
                        continue;
                    }
                    
                    $value = $value_name;
                    
                    if(is_array($value_name) && isset($value_name[$value_slug])){
                        $value = $value_name[$value_slug];
                    }
                    
                    break;
                    
                }
                
            }
            
        }
        
        // html
        $title = $label . ' ' . $operator . ' ' . $value;
        
        $atts = array(
            'class' => 'acf-js-tooltip dashicons dashicons-' . $icon,
            'title' => $title
        );
        
        if($operator === '!='){
            
            $atts['style'] = 'color: #ccc;';
            
        }
        
        $html = '<span ' . acf_esc_attrs($atts) . '></span>';
        
        $return[] = array(
            'html'      => $html,
            'icon'      => $icon,
            'title'     => $title,
            'name'      => $name,
            'label'     => $label,
            'operator'  => $operator,
            'value'     => $value,
        );
        
    }
    
    return $return;
    
}

/**
 * acfe_render_field_group_locations_html
 *
 * Legacy way to display Field Groups Locations in ACF 5.8
 *
 * @param $field_group
 */
function acfe_render_field_group_locations_html($field_group){
    
    foreach($field_group['location'] as $groups){
        
        $html = acfe_get_locations_array($groups);
        
        if($html){
            
            $array = array();
            
            foreach($html as $location){
                $array[] = $location['html'];
            }
            
            echo implode(' ', $array);
            
        }
        
    }
    
}

/**
 * acfe_add_field_groups_metabox
 *
 * @param array $args
 */
function acfe_add_field_groups_metabox($args = array()){
    
    $args = wp_parse_args($args, array(
        'id'            => 'acfe-field-groups',
        'title'         => __('Field Groups', 'acfe'),
        'screen'        => '',
        'context'       => 'normal',
        'priority'      => 'default',
        'field_groups'  => array(),
    ));
    
    add_meta_box($args['id'], $args['title'], function($object, $data) use($args){
        
        $data = $data['args'];
    
        foreach($data as $field_group){
            
            $fields = acf_get_fields($field_group);
            $url = $field_group['ID'] ? admin_url("post.php?post={$field_group['ID']}&action=edit") : false;
            $edit = $url ? '(<a href="' . $url . '">' .  __('edit'). '</a>)' : '';
            ?>
        
            <div class="acf-field">
            
                <div class="acf-label">
                    <label><?php echo $field_group['title']; ?> <?php echo $edit; ?></label>
                    <p class="description"><code style="font-size:12px;"><?php echo $field_group['key']; ?></code></p>
                </div>
            
                <div class="acf-input">
                    <?php if(!empty($fields)){ ?>
                        
                        <?php $details = acfe_get_fields_details_recursive($fields); ?>
                        
                        <table class="acf-table">
                            <thead>
                                <th class="acf-th" width="25%"><strong>Label</strong></th>
                                <th class="acf-th" width="25%"><strong>Name</strong></th>
                                <th class="acf-th" width="25%"><strong>Key</strong></th>
                                <th class="acf-th" width="25%"><strong>Type</strong></th>
                            </thead>
                        
                            <tbody>
                            <?php foreach($details as $field){ ?>
                                
                                <?php
                                $field_name = $field['name'] ? '<code style="font-size:12px;">' . $field['name'] . '</code>' : '';
                                $field_key = $field['key'] ? '<code style="font-size:12px;">' . $field['key'] . '</code>' : '';
                                ?>
                                
                                <tr class="acf-row">
                                    <td width="25%"><?php echo $field['label']; ?></td>
                                    <td width="25%"><?php echo $field_name; ?></td>
                                    <td width="25%"><?php echo $field_key; ?></td>
                                    <td width="25%"><?php echo $field['type']; ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                
                    <?php } ?>
                </div>
        
            </div>
    
        <?php } ?>
    
        <script type="text/javascript">
        (function($){

            if(typeof acf === 'undefined'){
                return;
            }
    
            acf.newPostbox(<?php echo wp_json_encode(array(
                'id'    => $args['id'],
                'key'   => '',
                'style' => 'default',
                'label' => 'left',
                'edit'  => false
            )); ?>);

        })(jQuery);
        </script>
        <?php
        
    }, $args['screen'], $args['context'], $args['priority'], $args['field_groups']);
    
}