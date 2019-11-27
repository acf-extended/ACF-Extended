<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_recaptcha extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_recaptcha';
        $this->label = __('reCAPTCHA', 'acf');
        $this->category = 'jquery';
        $this->defaults = array(
            'required'      => 0,
            'disabled'      => 0,
            'readonly'      => 0,
            'version'       => 'v2',
            'v2_theme'      => 'light',
            'v2_size'       => 'normal',
            'v3_hide_logo'  => false,
            'site_key'      => '',
            'secret_key'    => '',
        );
        
        parent::__construct();
        
    }
    
    function render_field_settings($field){
        
        // Version
        acf_render_field_setting($field, array(
            'label'			=> __('Version', 'acf'),
            'instructions'	=> __('Select the reCaptcha version', 'acf'),
            'type'			=> 'select',
            'name'			=> 'version',
            'choices'		=> array(
                'v2'    => __('reCaptcha V2', 'acf'),
                'v3'    => __('reCaptcha V3', 'acf'),
            )
        ));
        
        // V2 Theme
        acf_render_field_setting($field, array(
            'label'			=> __('Theme', 'acf'),
            'instructions'	=> __('Select the reCaptcha theme', 'acf'),
            'type'			=> 'select',
            'name'			=> 'v2_theme',
            'choices'		=> array(
                'light' => __('Light', 'acf'),
                'dark'  => __('Dark', 'acf'),
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'version',
                        'operator'  => '==',
                        'value'     => 'v2',
                    )
                )
            )
        ));
        
        // V2 Size
        acf_render_field_setting($field, array(
            'label'			=> __('Size', 'acf'),
            'instructions'	=> __('Select the reCaptcha size', 'acf'),
            'type'			=> 'select',
            'name'			=> 'v2_size',
            'choices'		=> array(
                'normal'    => __('Normal', 'acf'),
                'compact'   => __('Compact', 'acf'),
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'version',
                        'operator'  => '==',
                        'value'     => 'v2',
                    )
                )
            )
        ));
        
        // V3 Hide Logo
        acf_render_field_setting($field, array(
            'label'			=> __('Hide logo', 'acf'),
            'instructions'	=> __('Hide the reCaptcha logo', 'acf'),
            'type'			=> 'true_false',
            'name'			=> 'v3_hide_logo',
            'ui'            => true,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'version',
                        'operator'  => '==',
                        'value'     => 'v3',
                    )
                )
            )
        ));
        
        // Site Key
        acf_render_field_setting($field, array(
            'label'			=> __('Site key', 'acf'),
            'instructions'	=> __('Enter the site key. <a href="https://www.google.com/recaptcha/admin" target="_blank">reCaptcha API Admin</a>', 'acf'),
            'type'			=> 'text',
            'name'			=> 'site_key',
        ));
        
        // Site Secret
        acf_render_field_setting($field, array(
            'label'			=> __('Secret key', 'acf'),
            'instructions'	=> __('Enter the secret key. <a href="https://www.google.com/recaptcha/admin" target="_blank">reCaptcha API Admin</a>', 'acf'),
            'type'			=> 'text',
            'name'			=> 'secret_key',
        ));

    }
    
    function prepare_field($field){
        
        if($field['version'] === 'v3'){
            
            $field['wrapper']['class'] = 'acf-hidden';
            
        }
        
        return $field;
        
    }
    
    function render_field($field){

        // Site key
        $site_key = acf_get_setting('acfe/field/recaptcha/site_key', $field['site_key']);
        
        // Version
        $field['version'] = acf_get_setting('acfe/field/recaptcha/version', $field['version']);

        // V2
        if($field['version'] === 'v2'){ ?>
        
            <?php
            // Theme & Size
            $field['v2_theme'] = acf_get_setting('acfe/field/recaptcha/v2/theme', $field['v2_theme']);
            $field['v2_size'] = acf_get_setting('acfe/field/recaptcha/v2/size', $field['v2_size']);
            
            $wrapper = array(
                'class'         => 'acf-input-wrap acfe-field-recaptcha',
                'data-site-key' => $site_key,
                'data-version'  => 'v2',
                'data-size'     => $field['v2_size'],
                'data-theme'    => $field['v2_theme'],
            );
            
            $hidden_input = array(
                'id'    => $field['id'],
                'name'  => $field['name'],
            );
            
            ?>
            <div <?php acf_esc_attr_e($wrapper); ?>>
                
                <div></div>
                <?php acf_hidden_input($hidden_input); ?>
                
            </div>
            
            <script src="https://www.google.com/recaptcha/api.js?onload=acfe_recaptcha&render=explicit" async defer></script>
            
            <?php
            return;

        }
        
        // V3
        elseif($field['version'] === 'v3'){
            
            // Hide logo
            $field['v3_hide_logo'] = acf_get_setting('acfe/field/recaptcha/v3/hide_logo', $field['v3_hide_logo']);
            
            $wrapper = array(
                'class'         => 'acf-input-wrap acfe-field-recaptcha',
                'data-site-key' => $site_key,
                'data-version'  => 'v3',
            );
            
            $hidden_input = array(
                'id'    => $field['id'],
                'name'  => $field['name'],
            );
            
            ?>
            <div <?php acf_esc_attr_e($wrapper); ?>>
                
                <div></div>
                <?php acf_hidden_input($hidden_input); ?>
                
            </div>
            
            <?php if($field['v3_hide_logo']){ ?>
                <style>
                .grecaptcha-badge{
                    display: none;
                    visibility: hidden;
                }
                </style>
            <?php } ?>
            
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $site_key; ?>&onload=acfe_recaptcha" async defer></script>
            
            <?php
            return;
            
        }

    }
    
    function validate_value($valid, $value, $field, $input){
        
        // Expired
        if($value === 'expired'){
            
            return __('reCaptcha has expired.');

        }
        
        // Error
        elseif($value === 'error'){
            
            return __('An error has occured.');

        }
        
        // Only true submission
        elseif(!wp_doing_ajax()){
            
            // Empty & Required
            if(empty($value) && $field['required']){
                
                return $valid;
                
            }
            
            // Success
            else{
                
                // Secret key
                $secret_key = acf_get_setting('acfe/field/recaptcha/secret_key', $field['secret_key']);

                // API Call
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$value}");
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                $api = curl_exec($curl);

                curl_close($curl);
                
                // No response
                if(empty($api))
                    return false;
                
                $response = json_decode($api);
                
                if($response->success === false){
                    
                    $valid = false;
                    
                }
                
                elseif($response->success === true){
                    
                    $valid = true;
                    
                }
                
            }
            
        }
        
        return $valid;
        
    }
    
    function update_value($value, $post_id, $field){
        
        // Do not save field value
        return null;
        
    }

}

// initialize
acf_register_field_type('acfe_field_recaptcha');