<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_recaptcha')):

class acfe_field_recaptcha extends acf_field{
    
    /**
     * initialize
     */
    function initialize(){
    
        $category = acfe_is_acf_61() ? 'advanced' : 'jquery';
        
        $this->name = 'acfe_recaptcha';
        $this->label = __('Google reCaptcha', 'acfe');
        $this->category = $category;
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
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // Version
        acf_render_field_setting($field, array(
            'label'         => __('Version', 'acf'),
            'instructions'  => __('Select the reCaptcha version', 'acfe'),
            'type'          => 'select',
            'name'          => 'version',
            'choices'       => array(
                'v2' => __('reCaptcha V2', 'acf'),
                'v3' => __('reCaptcha V3', 'acf'),
            )
        ));
        
        // V2 Theme
        acf_render_field_setting($field, array(
            'label'         => __('Theme', 'acf'),
            'instructions'  => __('Select the reCaptcha theme', 'acfe'),
            'type'          => 'select',
            'name'          => 'v2_theme',
            'choices'       => array(
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
            'label'         => __('Size', 'acf'),
            'instructions'  => __('Select the reCaptcha size', 'acfe'),
            'type'          => 'select',
            'name'          => 'v2_size',
            'choices'       => array(
                'normal'        => __('Normal', 'acf'),
                'compact'       => __('Compact', 'acf'),
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
            'label'             => __('Hide logo', 'acf'),
            'instructions'      => __('Hide the reCaptcha logo', 'acfe'),
            'type'              => 'true_false',
            'name'              => 'v3_hide_logo',
            'ui'                => true,
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
            'label'         => __('Site key', 'acf'),
            'instructions'  => __('Enter the site key. <a href="https://www.google.com/recaptcha/admin" target="_blank">reCaptcha API Admin</a>', 'acfe'),
            'type'          => 'text',
            'name'          => 'site_key',
        ));
        
        // Site Secret
        acf_render_field_setting($field, array(
            'label'         => __('Secret key', 'acf'),
            'instructions'  => __('Enter the secret key. <a href="https://www.google.com/recaptcha/admin" target="_blank">reCaptcha API Admin</a>', 'acfe'),
            'type'          => 'text',
            'name'          => 'secret_key',
        ));

    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return array
     */
    function prepare_field($field){
        
        if($field['version'] === 'v3'){
            $field['wrapper']['class'] = 'acf-hidden';
        }
        
        return $field;
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){

        // vars
        $site_key = acf_get_setting('acfe/field/recaptcha/site_key', $field['site_key']);
        $version = acf_get_setting('acfe/field/recaptcha/version', $field['version']);

        // V2
        if($version === 'v2'){
            
            // Theme & Size
            $field['v2_theme'] = acf_get_setting('acfe/field/recaptcha/v2/theme', $field['v2_theme']);
            $field['v2_size'] = acf_get_setting('acfe/field/recaptcha/v2/size', $field['v2_size']);
            
            $wrapper = array(
                'class'         => 'acf-input-wrap acfe-field-recaptcha',
                'data-site-key' => $site_key,
                'data-version'  => $version,
                'data-size'     => $field['v2_size'],
                'data-theme'    => $field['v2_theme'],
            );
            
            $hidden_input = array(
                'id'    => $field['id'],
                'name'  => $field['name'],
            );
            
            ?>
            <div <?php echo acf_esc_atts($wrapper); ?>>
                
                <div></div>
                <?php acf_hidden_input($hidden_input); ?>
                
            </div>
            
            <script src="https://www.google.com/recaptcha/api.js?render=explicit" async defer></script>
            
            <?php
            
        // V3
        }elseif($version === 'v3'){
            
            // Hide logo
            $field['v3_hide_logo'] = acf_get_setting('acfe/field/recaptcha/v3/hide_logo', $field['v3_hide_logo']);
            
            $wrapper = array(
                'class'         => 'acf-input-wrap acfe-field-recaptcha',
                'data-site-key' => $site_key,
                'data-version'  => $version,
            );
            
            $hidden_input = array(
                'id'    => $field['id'],
                'name'  => $field['name'],
            );
            
            ?>
            <div <?php echo acf_esc_atts($wrapper); ?>>
                
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
            
            <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $site_key; ?>" async defer></script>
            
            <?php
            
        }

    }
    
    
    /**
     * validate_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     *
     * @return bool|mixed|string|null
     */
    function validate_value($valid, $value, $field, $input){
        
        // bail early if not required
        if(!$field['required']){
            return $valid;
        }
    
        // Avoid duplicate token: Do not process during Ajax validation
        if(wp_doing_ajax()){
            return $valid;
        }
    
        // Secret key
        $secret_key = acf_get_setting('acfe/field/recaptcha/secret_key', $field['secret_key']);
    
        // API Call
        $curl = curl_init();
    
        curl_setopt($curl, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify?secret={$secret_key}&response={$value}");
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $api = curl_exec($curl);
    
        curl_close($curl);
    
        // No API response
        if(empty($api)){
            return __('An error has occured');
        }
    
        // Decode
        $response = json_decode($api);
    
        // No success
        if(!isset($response->success)){
            return __('An error has occured');
        }
        
        // fail
        if($response->success === false){
            $valid = false;
        
        // success
        }elseif($response->success === true){
            $valid = true;
        }
        
        // return
        return $valid;
        
    }
    
    
    /**
     * update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return null
     */
    function update_value($value, $post_id, $field){
        
        // do not save value
        return null;
        
    }

}

// initialize
acf_register_field_type('acfe_field_recaptcha');

endif;