<?php

if(!defined('ABSPATH')){
    exit;
}

// check version
if(!acfe_is_acf('5.8', '5.9')){
    return;
}

if(!class_exists('acfe_compatibility_acf_58')):

class acfe_compatibility_acf_58{
    
    /**
     * construct
     */
    function __construct(){

        add_action('current_screen',                                 array($this, 'current_screen'));
        add_action('after_plugin_row_' . plugin_basename(ACFE_FILE), array($this, 'after_plugin_row'), 5, 3);
        
    }


    /**
     * current_screen
     *
     * @param $screen
     *
     * @return void
     */
    function current_screen($screen){

        // global
        global $pagenow;

        // check screen
        if($pagenow === 'plugins.php' || $pagenow === 'plugin-install.php'){
            acf_log('[ACF Extended] Support of ACF Pro 5.8 (2019) will be dropped on September 2026. Please upgrade to ACF Pro 5.9 or higher to maintain compatibility with future versions of ACF Extended.');
        }

    }
    
    
    /**
     * after_plugin_row
     *
     * @param $plugin_file
     * @param $plugin_data
     * @param $status
     *
     * @return void
     */
    function after_plugin_row($plugin_file, $plugin_data, $status){
        
        // vars
        $colspan = version_compare($GLOBALS['wp_version'], '5.5', '<') ? 3 : 4;
        
        // message
        $message = __('Support of ACF Pro 5.8 (2019) will be dropped on September 2026. Please upgrade to ACF Pro 5.9 or higher to maintain compatibility with future versions of ACF Extended.', 'acfe');
        
        // class
        $class = 'acfe-plugin-tr';
        if(isset($plugin_data['update']) && !empty($plugin_data['update'])){
            $class .= ' acfe-plugin-tr-update';
        }
        
        ?>
        <style>
            .plugins tr[data-plugin='<?php echo $plugin_file; ?>'] th,
            .plugins tr[data-plugin='<?php echo $plugin_file; ?>'] td{
                box-shadow:none;
            }
        </style>
        
        <tr class="plugin-update-tr active <?php echo $class; ?>">
            <td colspan="<?php echo $colspan; ?>" class="plugin-update colspanchange">
                <div class="update-message notice inline notice-error notice-alt">
                    <p><?php echo $message; ?></p>
                </div>
            </td>
        </tr>
        <?php
        
    }
    
}

acf_new_instance('acfe_compatibility_acf_58');

endif;

if(!function_exists('acf_get_location_type')){
    function acf_get_location_type(){
        return array();
    }
}

if(!function_exists('acf_enqueue_script')){
    function acf_enqueue_script(){
        acf_enqueue_scripts();
    }
}

if(!function_exists('acf_esc_attrs')){
    function acf_esc_attrs($atts = array()){
        return acf_esc_atts($atts);
    }
}

if(!function_exists('acf_get_user_result')){
    function acf_get_user_result($user){

        // Vars.
        $id = $user->ID;
        $text = $user->user_login;

        // Add name.
        if($user->first_name && $user->last_name){
            $text .= " ({$user->first_name} {$user->last_name})";
        }elseif($user->first_name){
            $text .= " ({$user->first_name})";
        }
        return compact('id', 'text');
    }
}

if(!function_exists('acf_not_empty')){
    function acf_not_empty($var){
        return ($var || is_numeric($var));
    }
}

