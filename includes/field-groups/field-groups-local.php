<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_Field_Groups_Local')):

class ACFE_Field_Groups_Local{
    
    // vars
    var $view = '';
    var $local_field_groups = array();
    var $autosync_field_groups = array();
    var $old_version = false;
    var $acfe_admin_field_groups = '';
    
    /**
     * construct
     */
    function __construct(){
        
        // Actions
        add_action('current_screen', array($this, 'current_screen'));
        
    }
    
    
    /**
     * current_screen
     */
    function current_screen(){
        
        // bail early if not field groups admin page.
        if(!acf_is_screen('edit-acf-field-group')){
            return;
        }
    
        // old Compatibility
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
            $this->old_version = true;
        }
        
        // get acf instance
        $this->acfe_admin_field_groups = acf_get_instance('ACFE_Field_Groups');
        $this->view = $this->acfe_admin_field_groups->view;
        
        // hooks
        add_filter('views_edit-acf-field-group', array($this, 'views'), 20);
        
        if($this->view === 'acfe-local'){
            
            add_filter('admin_footer',                             array($this, 'admin_footer'));
            add_filter('bulk_actions-edit-acf-field-group',        array($this, 'bulk_actions'));
            add_filter('handle_bulk_actions-edit-acf-field-group', array($this, 'handle_bulk_actions'), 10, 3);
            
        }
        
    }
    
    
    /**
     * views
     *
     * @param $views
     *
     * @return mixed
     */
    function views($views){
        
        // total
        $count = count($this->get_local_field_groups());
        
        // bail early
        if($count === 0){
            return $views;
        }
            
        $views['acfe-local'] = sprintf(
            '<a %s href="%s">%s <span class="count">(%s)</span></a>',
            ($this->view === 'acfe-local' ? 'class="current"' : ''),
            esc_url(admin_url('edit.php?post_type=acf-field-group&post_status=acfe-local')),
            esc_html(__('Local', 'acf')),
            $count
        );
        
        if($this->view === 'acfe-local'){
            
            global $wp_list_table;
            
            $wp_list_table->set_pagination_args(array(
                'total_items' => $count,
                'total_pages' => 1,
                'per_page' => $count
            ));
            
        }
        
        return $views;
        
    }
    
    
    /**
     * admin_footer
     */
    function admin_footer(){
    
        // vars
        $i = -1;
    
        $columns = array(
            'acfe-source',
            'acf-count',
            'acf-location',
            'acfe-load',
        );
        
        if($this->old_version){
    
            $columns = array(
                'acfe-source',
                'acfe-count',
                'acfe-location',
                'acfe-load'
            );
            
        }
    
        if(acf_get_setting('acfe/php')){
            $columns[] = 'acfe-autosync-php';
        }
    
        if(acf_get_setting('json')){
            $columns[] = 'acfe-autosync-json';
        }
    
        ?>
        <script type="text/html" id="tmpl-acfe-local-tbody">
            <?php
        
            foreach($this->local_field_groups as $field_group):
            
                // vars
                $i++;
                $field_group['ID'] = 0;
                $key = $field_group['key'];
                $title = $field_group['title'];
                
                if(isset($this->autosync_field_groups[$field_group['key']])){
                    
                    $field_group['acfe_local_source'] = $this->autosync_field_groups[$field_group['key']];
                    
                }
            
                ?>
                <tr <?php if($i%2 == 0): ?>class="alternate"<?php endif; ?>>

                    <th class="check-column" data-colname="">
                        <label for="cb-select-<?php echo $field_group['key']; ?>" class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Select %s', 'acf' ), $field_group['title'] ) ); ?></label>
                        <input id="cb-select-<?php echo $field_group['key']; ?>" type="checkbox" value="<?php echo $field_group['key']; ?>" name="post[]">
                    </th>

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
                                <a href="<?php echo add_query_arg(array('action' => 'sync', 'keys' => $key), acf_get_admin_tool_url('acfe-fg-local')); ?>">Sync to database</a> |
                            </span>
                            
                            <span class="acfe-key">
                                <code><?php echo esc_html($key); ?></code>
                            </span>

                        </div>
                    </td>
                
                    <?php foreach($columns as $column): ?>
                        <td class="column-<?php echo esc_attr($column); ?>">
                        
                            <?php
                            
                            if($this->old_version){
    
                                $this->acfe_admin_field_groups->render_table_column($column, $field_group);
                                
                            }else{
    
                                if(strpos($column, 'acfe') === 0){
        
                                    $this->acfe_admin_field_groups->render_table_column($column, $field_group);
        
                                }else{
    
                                    acf_get_instance('ACF_Admin_Field_Groups')->render_admin_table_column($column, $field_group);
        
                                }
                                
                            }
                            
                            ?>

                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </script>

        <script type="text/javascript">
            (function($){
                
                $('#the-list').html($('#tmpl-acfe-local-tbody').html());

            })(jQuery);
        </script>
        <?php
        
    }
    
    
    /**
     * bulk_actions
     *
     * @param $actions
     *
     * @return array
     */
    function bulk_actions($actions){
        
        $actions = array();
        
        $actions['acfe_local_php'] = __( 'Export PHP', 'acf' );
        $actions['acfe_local_json'] = __( 'Export Json', 'acf' );
        $actions['acfe_local_sync'] = __( 'Sync to database', 'acf' );
        
        return $actions;
        
    }
    
    
    /**
     * handle_bulk_actions
     *
     * @param $redirect
     * @param $action
     * @param $post_ids
     *
     * @return mixed|void
     */
    function handle_bulk_actions($redirect, $action, $post_ids){
    
        if(!isset($_REQUEST['post']) || empty($_REQUEST['post'])){
            return $redirect;
        }
        
        // PHP
        if($action === 'acfe_local_php'){
    
            $post_ids = $_REQUEST['post'];
    
            $url = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe-fg-local&action=php&keys=' . implode('+', $post_ids));
            wp_redirect($url);
            exit;
        
        }
        
        // Json
        elseif($action === 'acfe_local_json'){
    
            $post_ids = $_REQUEST['post'];
    
            $url = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe-fg-local&action=json&keys=' . implode('+', $post_ids));
            wp_redirect($url);
            exit;
            
        }
        
        // Sync DB
        elseif($action === 'acfe_local_sync'){
            
            $post_ids = $_REQUEST['post'];
            
            $url = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=acfe-fg-local&action=sync&keys=' . implode('+', $post_ids));
            wp_redirect($url);
            exit;
        
        }
        
        return $redirect;
        
    }
    
    
    /**
     * get_local_field_groups
     *
     * @return array
     */
    function get_local_field_groups(){
        
        $local_field_groups = acf_get_local_field_groups();
        
        $locals = array();
        
        foreach($local_field_groups as $field_group){
            
            // local PHP
            if(acf_maybe_get($field_group, 'local') !== 'php'){
                continue;
            }
            
            // exclude acfe field groups
            if(!acfe_is_super_dev() && in_array($field_group['key'], acfe_get_setting('reserved_field_groups', array()))){
                continue;
            }
            
            $locals[] = $field_group;
            
        }
        
        // get desync php field groups
        $desync_php_field_groups = acfe_get_desync_php_field_groups();
        
        foreach($desync_php_field_groups as $file_key => $file_path){
            
            require_once($file_path);
            
            $this->autosync_field_groups[$file_key] = $file_path;
            $locals[] = acf_get_field_group($file_key);
            
        }
        
        $order = 'ASC';
        if(isset($_REQUEST['orderby']) && $_REQUEST['orderby'] === 'title' && isset($_REQUEST['order']) && $_REQUEST['order'] === 'desc'){
            $order = 'DESC';
        }
        
        // Sort Title ASC
        if($order === 'ASC'){
            
            usort($locals, function($a, $b){
                return strcmp($a['title'], $b['title']);
            });
            
        }else{
            
            usort($locals, function($a, $b){
                return strcmp($b['title'], $a['title']);
            });
            
        }
        
        $this->local_field_groups = $locals;
        
        return $locals;
        
    }
    
}

acf_new_instance('ACFE_Field_Groups_Local');

endif;


/**
 * acfe_get_desync_php_field_groups
 *
 * @return array
 */
function acfe_get_desync_php_field_groups(){
    
    $file_field_groups = acfe_get_local_php_files();
    $db_field_groups = acf_get_raw_field_groups();
    
    foreach($file_field_groups as $file_key => $file_path){
        
        foreach($db_field_groups as $db){
            
            if($db['key'] === $file_key){
                
                unset($file_field_groups[$file_key]);
                break;
                
            }
            
        }
        
    }
    
    return (array) $file_field_groups;
    
}