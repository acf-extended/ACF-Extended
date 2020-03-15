<?php

if(!defined('ABSPATH'))
    exit;

add_filter('views_edit-acf-field-group', 'acfe_field_groups_third_party_views', 99);
function acfe_field_groups_third_party_views($views){
    
    // Total
    $total = count(acfe_get_third_party_field_groups());
    
    // Bail early if empty
    if($total === 0)
        return $views;
    
    // Class
    $class = '';
    
    // active
    if(acf_maybe_get_GET('post_status') === 'acfe-local'){
        
        // actions
        add_action('admin_footer', 'acfe_field_groups_third_party_footer', 5);
        
        // set active class
        $class = ' class="current"';
        
        // global
        global $wp_list_table;
        
        // update pagination
        $wp_list_table->set_pagination_args(array(
            'total_items' => $total,
            'total_pages' => 1,
            'per_page' => $total
        ));
        
    }
    
    // add view
    $views['acfe-local'] = '<a' . $class . ' href="' . admin_url('edit.php?post_type=acf-field-group&post_status=acfe-local') . '">' . __('Local', 'acfe') . ' <span class="count">(' . $total . ')</span></a>';
    
    // return
    return $views;
    
}

function acfe_field_groups_third_party_footer(){
		
    // vars
    $i = -1;
    $columns = array(
        'acfe-source',
        'acfe-count',
        'acfe-locations',
        'acfe-local'
    );
		
    ?>
    <script type="text/html" id="tmpl-acfe-local-tbody">
    <?php
    
    foreach(acfe_get_third_party_field_groups() as $field_group ): 
        
        // vars
        $i++; 
        $key = $field_group['key'];
        $title = $field_group['title'];
        $local = $field_group['local'];
        
        ?>
        <tr <?php if($i%2 == 0): ?>class="alternate"<?php endif; ?>>
            <td class="post-title page-title column-title">
                <strong>
                    <span class="row-title"><?php echo esc_html($title); ?></span>
                </strong>
                <div class="row-actions">
                
                    <span>
                        <a href="<?php echo add_query_arg(array('action' => 'php', 'keys' => $key), acf_get_admin_tool_url('acfe-fg-local')); ?>">PHP</a> | 
                    </span>
                    
                    <span>
                        <a href="<?php echo add_query_arg(array('action' => 'json', 'keys' => $key), acf_get_admin_tool_url('acfe-fg-local')); ?>">Json</a> | 
                    </span>
                    
                    <span>
                        <a href="<?php echo add_query_arg(array('action' => 'sync', 'keys' => $key), acf_get_admin_tool_url('acfe-fg-local')); ?>">Sync</a> | 
                    </span>
                    
                    <span class="acfe-key">
                        <span style="color:#555;">
                            <code style="font-size: 12px;"><?php echo esc_html($key); ?></code>
                        </span>
                    </span>
                    
                </div>
            </td>
            <?php foreach($columns as $column): ?>
                <td class="column-<?php echo esc_attr($column); ?>">
                    <?php echo acfe_field_groups_column_html($column, $key); ?>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>
    </script>
    
    <script type="text/javascript">
    (function($){
        
        // update table HTML
        $('#the-list').html($('#tmpl-acfe-local-tbody').html());
            
    })(jQuery);
    </script>
    <?php
		
}

function acfe_get_third_party_field_groups(){
    
    $get_local_field_groups = acf_get_local_field_groups();
    if(empty($get_local_field_groups))
        return array();
    
    $locals = array();
    
    foreach($get_local_field_groups as $field_group){
        
        // Exclude ACFE Field Groups
        
        if(!acfe_is_super_dev()){
         
	        if(stripos($field_group['key'], 'group_acfe_') === 0)
		        continue;
          
        }
        
        
        $locals[] = $field_group;
        
    }
    
    // Bail early if no local fields
    if(empty($locals))
        return $locals;
    
    // Get DB field groups
    $get_db_field_groups = acfe_get_db_field_groups();
    
    // Bail early if no DB field groups
    if(empty($get_db_field_groups))
        return $locals;
    
    foreach($get_db_field_groups as $field_group){
        
        foreach($locals as $k => $local){
            
            if($local['key'] === $field_group['key'])
                unset($locals[$k]);
            
        }
        
    }
    
    return $locals;
    
}

function acfe_get_db_field_groups(){
    
    acf_disable_filters();
    
    $get_db_field_groups = acf_get_field_groups();
    
    acf_enable_filters();
    
    return $get_db_field_groups;
    
}

add_filter('bulk_actions-edit-acf-field-group', function($actions){
    
    if(acf_maybe_get_GET('post_status') === 'acfe-local')
        return array();
    
    return $actions;
    
}, 99);

add_filter('manage_edit-acf-field-group_sortable_columns', function($sortable_columns){
    
    if(acf_maybe_get_GET('post_status') === 'acfe-local')
        return array();
    
    return $sortable_columns;
    
}, 99);