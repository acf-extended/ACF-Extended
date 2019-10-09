<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_recaptcha extends acf_field{
    
    public function __construct(){
        
        $this->name = 'acfe_recaptcha';
        $this->label = __('reCAPTCHA', 'acf');
        $this->category = 'jquery';
        $this->defaults = array(
            'required'      => 0,
            'disabled'      => 0,
            'readonly'      => 0,
            'version'       => 'v2',
            'theme'         => 'light',
            'size'          => 'normal',
        );
        
        parent::__construct();
        
    }
    
    public function render_field_settings($field){
        
        // Version
        acf_render_field_setting($field, array(
            'label'			=> __('Version', 'acf'),
            'instructions'	=> __('Select the reCaptcha version', 'acf'),
            'type'			=> 'select',
            'name'			=> 'version',
            'optgroup'		=> false,
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
            'optgroup'		=> false,
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
            'optgroup'		=> false,
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
    
    public function prepare_field($field){
        
        if($field['version'] === 'v3')
            $field['wrapper']['class'] = 'acf-hidden';
        
        return $field;
        
    }
    
    public function render_field($field){

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
            ?>
        
            <script type="text/javascript">
            acf.addAction('validation_failure', function($form){
                
                if($form.find('.acfe-recaptcha').length)
                    grecaptcha.reset(acfe_recaptcha_<?php echo $field['key']; ?>);
                
            });
            
            var acfe_recaptcha_<?php echo $field['key']; ?>;
            
            var acfe_recaptcha_error_<?php echo $field['key']; ?> = function(){
                
                jQuery('input#<?php echo $field['key']; ?>').val('error').change();
                
            };
            
            var acfe_recaptcha_callback_<?php echo $field['key']; ?> = function(response){
                
                jQuery('.g-recaptcha-response').change();
                jQuery('input#<?php echo $field['key']; ?>').val(response).change();
                jQuery('input#<?php echo $field['key']; ?>').closest('.acf-input').find('> .acf-notice.-error').hide();
                
            };
            
            var acfe_recaptcha_expired_<?php echo $field['key']; ?> = function(){
                
                jQuery('input#<?php echo $field['key']; ?>').val('expired').change();
                
            };

            var acfe_recaptcha_onload_<?php echo $field['key']; ?> = function(){
                
                acfe_recaptcha_<?php echo $field['key']; ?> = grecaptcha.render('acfe_recaptcha_<?php echo $field['key']; ?>', {
                    'sitekey':          '<?php echo $site_key; ?>',
                    'error-callback':   'acfe_recaptcha_error_<?php echo $field['key']; ?>',
                    'callback':         'acfe_recaptcha_callback_<?php echo $field['key']; ?>',
                    'expired-callback': 'acfe_recaptcha_expired_<?php echo $field['key']; ?>',
                    'theme':            '<?php echo $field['v2_theme']; ?>',
                    'size':             '<?php echo $field['v2_size']; ?>'
                });
                
            };
            </script>
            
            <script src="https://www.google.com/recaptcha/api.js?onload=acfe_recaptcha_onload_<?php echo $field['key']; ?>&render=explicit" async defer></script>

            <div id="acfe_recaptcha_<?php echo $field['key']; ?>" class="acfe-recaptcha"></div>
            
            <div class="acf-input-wrap">
                <?php
                acf_hidden_input(array(
                    'id'	=> $field['key'],
                    'name'	=> $field['name']
                ));
                ?>
            </div>
            
            <?php
            return;

        }
        
        // V3
        elseif($field['version'] === 'v3'){
            
            // Hide logo
            $field['v3_hide_logo'] = acf_get_setting('acfe/field/recaptcha/v3/hide_logo', $field['v3_hide_logo']);
            
            if($field['v3_hide_logo']){ ?>
                <style>
                .grecaptcha-badge{
                    display: none;
                    visibility: hidden;
                }
                </style>
            <?php } ?>
            
            <script type="text/javascript">
            var acfe_recaptcha_onload_<?php echo $field['key']; ?> = function(){
                
                grecaptcha.ready(function() {
                    grecaptcha.execute('<?php echo $site_key; ?>', {action: 'homepage'}).then(function(response){
                        
                        jQuery('.g-recaptcha-response').change();
                        jQuery('input#<?php echo $field['key']; ?>').val(response).change();
                        jQuery('input#<?php echo $field['key']; ?>').closest('.acf-input').find('> .acf-notice.-error').hide();
                        
                    });
                });
                
            };
            </script>
            
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $site_key; ?>&onload=acfe_recaptcha_onload_<?php echo $field['key']; ?>" async defer></script>

            <div id="acfe_recaptcha_<?php echo $field['key']; ?>" class="acfe-recaptcha"></div>
            
            <div class="acf-input-wrap">
                <?php
                acf_hidden_input(array(
                    'id'	=> $field['key'],
                    'name'	=> $field['name']
                ));
                ?>
            </div>
            
            <?php
            return;
            
        }

    }
    
    public function validate_value($valid, $value, $field, $input){
        
        // No value
        if(empty($value)){

            return __('This field is required.');
            
        }
        
        // Expired
        elseif($value === 'expired'){
            
            return __('reCaptcha has expired.');

        }
        
        // Error
        elseif($value === 'error'){
            
            return __('An error has occured.');

        }
        
        // Success
        else{
            
            // Secret key
            $secret_key = acf_get_setting('acfe/field/recaptcha/secret_key', $field['secret_key']);

            // API Call
            $api = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$value}");
            
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

        return $valid;
        
    }

}

new acfe_field_recaptcha();