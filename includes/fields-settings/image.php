<?php

if(!defined('ABSPATH'))
    exit;

add_filter('gettext', 'acfe_field_image_text', 99, 3);
function acfe_field_image_text($translated_text, $text, $domain){
    
    if($domain != 'acf')
        return $translated_text;
    
    if($text === 'No image selected')
        return '';

    return $translated_text;
    
}