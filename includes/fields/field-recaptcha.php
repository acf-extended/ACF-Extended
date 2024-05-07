<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_recaptcha')):

class acfe_field_recaptcha extends acf_field{
    
    public $hide_logo = false;
    
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
        
        $this->add_action('acf/input/admin_print_footer_scripts', array($this, 'admin_print_footer_scripts'));
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // Version
        acf_render_field_setting($field, array(
            'label'         => __('Version', 'acfe'),
            'instructions'  => __('Select the reCaptcha version', 'acfe'),
            'type'          => 'select',
            'name'          => 'version',
            'choices'       => array(
                'v2' => __('reCaptcha V2', 'acfe'),
                'v3' => __('reCaptcha V3', 'acfe'),
            )
        ));
        
        // V2 Theme
        acf_render_field_setting($field, array(
            'label'         => __('Theme', 'acfe'),
            'instructions'  => __('Select the reCaptcha theme', 'acfe'),
            'type'          => 'select',
            'name'          => 'v2_theme',
            'choices'       => array(
                'light' => __('Light', 'acfe'),
                'dark'  => __('Dark', 'acfe'),
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
            'label'         => __('Size', 'acfe'),
            'instructions'  => __('Select the reCaptcha size', 'acfe'),
            'type'          => 'select',
            'name'          => 'v2_size',
            'choices'       => array(
                'normal'        => __('Normal', 'acfe'),
                'compact'       => __('Compact', 'acfe'),
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
            'label'             => __('Hide logo', 'acfe'),
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
            'label'         => __('Site key', 'acfe'),
            'instructions'  => __('Enter the site key. <a href="https://www.google.com/recaptcha/admin" target="_blank">reCaptcha API Admin</a>', 'acfe'),
            'type'          => 'text',
            'name'          => 'site_key',
        ));
        
        // Site Secret
        acf_render_field_setting($field, array(
            'label'         => __('Secret key', 'acfe'),
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
        $site_key = acf_get_setting('acfe/field/recaptcha/site_key') ? acf_get_setting('acfe/field/recaptcha/site_key') : $field['site_key'];
        $version = acf_get_setting('acfe/field/recaptcha/version') ? acf_get_setting('acfe/field/recaptcha/version') : $field['version'];
        
        // wrapper attributes
        $wrapper = array(
            'class'         => 'acf-input-wrap',
            'data-site-key' => $site_key,
            'data-version'  => $version,
        );
        
        // v2
        if($version === 'v2'){
            
            // vars
            $field['v2_theme'] = acf_get_setting('acfe/field/recaptcha/v2/theme') ? acf_get_setting('acfe/field/recaptcha/v2/theme') : $field['v2_theme'];
            $field['v2_size'] = acf_get_setting('acfe/field/recaptcha/v2/size') ? acf_get_setting('acfe/field/recaptcha/v2/size') : $field['v2_size'];
            
            // wrapper attributes
            $wrapper['data-size'] = $field['v2_size'];
            $wrapper['data-theme'] = $field['v2_theme'];
            
        }
        
        // v3
        if($version === 'v3'){
            
            $field['v3_hide_logo'] = acf_get_setting('acfe/field/recaptcha/v3/hide_logo') ? acf_get_setting('acfe/field/recaptcha/v3/hide_logo') : $field['v3_hide_logo'];
            
            if($field['v3_hide_logo']){
                $this->hide_logo = true;
            }
            
        }
        
        // hidden input
        $hidden_input = array(
            'id'    => $field['id'],
            'name'  => $field['name'],
        );
        
        ?>
        <div <?php echo acf_esc_atts($wrapper); ?>>
        
            <?php if($version === 'v2'){ ?>
                <div></div>
            <?php } ?>
            <?php acf_hidden_input($hidden_input); ?>

        </div>
        <?php

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
        
        // bail early in ajax validation
        // token can only be verified once then becomes invalid
        $should_validate = apply_filters('acfe/field/recpatcha/should_validate_value', !acf_is_ajax(), $value, $field, $input);
        
        if(!$should_validate){
            return $valid;
        }
    
        // vars
        $secret_key = acf_get_setting('acfe/field/recaptcha/secret_key') ? acf_get_setting('acfe/field/recaptcha/secret_key') : $field['secret_key'];
        
        // post request
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret'    => $secret_key,
                'response'  => $value,
            ),
        ));
        
        // validate request response
        if(is_wp_error($response)){
            return __('An error has occured');
        }
        
        // get response as json
        $data = json_decode(wp_remote_retrieve_body($response));
        
        // success is not true|false
        // something went wrong
        if(!isset($data->success)){
            return __('An error has occured');
        }
        
        // error
        if($data->success === false){
            return __('Invalid reCaptcha, please try again');
        }
        
        // success
        return true;
        
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
    
    
    /**
     * admin_print_footer_scripts
     *
     * @return void
     */
    function admin_print_footer_scripts(){
        
        if($this->hide_logo){
            ?>
            <style>
            .grecaptcha-badge{
                display: none;
                visibility: hidden;
            }
            </style>
            <?php
        }
        
    }

}

// initialize
acf_register_field_type('acfe_field_recaptcha');

endif;