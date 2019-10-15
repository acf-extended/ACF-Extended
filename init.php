<?php

if(!defined('ABSPATH'))
    exit;

/**
 * ACFE: Include
 */
function acfe_include($filename = ''){
    
	$file_path = ACFE_PATH . ltrim($filename, '/');
    
	if(file_exists($file_path)){
        
		include_once($file_path);
        
	}
    
}

/**
 * ACFE: ACF Pro Check
 */
add_action('after_plugin_row_' . ACFE_BASENAME, 'acfe_plugin_row', 5, 3);
function acfe_plugin_row($plugin_file, $plugin_data, $status){
    
    if(acfe()->has_acf())
        return;
    
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
        <td colspan="3" class="plugin-update colspanchange">
            <div class="update-message notice inline notice-error notice-alt">
                <p><?php _e('ACF Extended requires Advanced Custom Fields PRO (minimum: 5.7.10).', 'acfe'); ?></p>
            </div>
        </td>
    </tr>
    
    <?php
    
}