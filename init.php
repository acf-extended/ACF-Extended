<?php

if(!defined('ABSPATH'))
    exit;

/**
 * ACFE: Include
 *
 * @param string $filename
 */
function acfe_include($filename = ''){
    
    $file_path = ACFE_PATH . ltrim($filename, '/');
    
    if(file_exists($file_path)){
        include_once($file_path);
    }
    
}

/**
 * ACFE: Get URL
 *
 * @param string $filename
 *
 * @return string
 */
function acfe_get_url($filename = ''){
    
    if(!defined('ACFE_URL')){
        define('ACFE_URL', acf_get_setting('acfe/url'));
    }
    
    return ACFE_URL . ltrim($filename, '/');
}

/**
 * ACFE: ACF Pro Check
 *
 * @param $plugin_file
 * @param $plugin_data
 * @param $status
 */
add_action('after_plugin_row_' . ACFE_BASENAME, 'acfe_plugin_row', 5, 3);
function acfe_plugin_row($plugin_file, $plugin_data, $status){
    
    if(acfe()->has_acf())
        return;
    
    // >= WP 5.5
    $colspan = 4;
    
    // < WP 5.5
    if(version_compare($GLOBALS['wp_version'], '5.5', '<'))
        $colspan = 3;
    
    ?>
    
    <style>
        .plugins tr[data-plugin='<?php echo ACFE_BASENAME; ?>'] th,
        .plugins tr[data-plugin='<?php echo ACFE_BASENAME; ?>'] td{
            box-shadow:none;
        }
        
        <?php if(isset($plugin_data['update']) && !empty($plugin_data['update'])){ ?>
            
            .plugins tr.acfe-plugin-tr td{
                box-shadow:none !important;
            }
            
            .plugins tr.acfe-plugin-tr .update-message{
                margin-bottom:0;
            }
            
        <?php } ?>
    </style>
    
    <tr class="plugin-update-tr active acfe-plugin-tr">
        <td colspan="<?php echo $colspan; ?>" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p><?php _e('ACF Extended requires <a href="https://www.advancedcustomfields.com/pro/" target="_blank">Advanced Custom Fields PRO</a> (minimum: 5.8).', 'acfe'); ?></p>
            </div>
        </td>
    </tr>
    
    <?php
    
}