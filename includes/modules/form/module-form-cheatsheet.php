<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_dynamic_forms_cheatsheet')):

class acfe_dynamic_forms_cheatsheet{
    
    /*
     * Construct
     */
    function __construct(){
    
        add_action('acf/render_field/name=acfe_form_cheatsheet_field',              array($this, 'field'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_fields',             array($this, 'fields'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_get_field',          array($this, 'get_field'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_get_option',         array($this, 'get_option'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_query_var',          array($this, 'query_var'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_form',       array($this, 'current_form'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_actions_post',       array($this, 'actions_post'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_actions_term',       array($this, 'actions_term'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_actions_user',       array($this, 'actions_user'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_actions_email',      array($this, 'actions_email'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_request',            array($this, 'request'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_post',       array($this, 'current_post'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_term',       array($this, 'current_term'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_user',       array($this, 'current_user'));
        add_action('acf/render_field/name=acfe_form_cheatsheet_current_author',     array($this, 'current_author'));
        
    }
    
    function field($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{field:field_5e5c07b6dfae9}</code></td>
                <td>User input</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{field:my_field}</code></td>
                <td>User input</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{field:my_field:false}</code></td>
                <td>User input (unformatted)</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function fields($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{fields}</code></td>
                <td>My text: User input<br /><br />My textarea: User input<br /><br />My date: 2020-03-01</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function get_field($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{get_field:my_field}</code></td>
                <td>DB value (current post)</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{get_field:my_field:current}</code></td>
                <td>DB value (current post)</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{get_field:my_field:128}</code></td>
                <td>DB value (post:128)</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{get_field:my_field:128:false}</code></td>
                <td>DB value (post:128 - unformatted)</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function get_option($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{get_option:my_option}</code></td>
                <td>DB value</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{get_option:my_option_array:key}</code></td>
                <td>DB value</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function query_var($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{query_var:name}</code></td>
                <td>value</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{query_var:name:key}</code></td>
                <td>Array value</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function actions_post($field){
        ?>
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Last Post Action</strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:ID}</code></td>
                <td>128</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:post_title}</code></td>
                <td>Title</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:admin_url}</code></td>
                <td><?php echo admin_url('post.php?post=128&action=edit'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:permalink}</code></td>
                <td><?php echo home_url('my-post'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:post_author}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:post_author_data:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:post:post_author_data:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td colspan="2"><em>See <code>{post}</code> for all available tags</em></td>
            </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Post Action Named <code>my-post</code></strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:ID}</code></td>
                <td>128</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:post_title}</code></td>
                <td>Title</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:admin_url}</code></td>
                <td><?php echo admin_url('post.php?post=128&action=edit'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:permalink}</code></td>
                <td><?php echo home_url('my-post'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:post_author}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:post_author_data:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-post:post_author_data:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td colspan="2"><em>See <code>{post}</code> for all available tags</em></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function actions_term($field){
        ?>
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Last Term Action</strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:term:ID}</code></td>
                <td>23</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:term:post_title}</code></td>
                <td>Term</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:term:admin_url}</code></td>
                <td><?php echo admin_url('term.php?tag_ID=23'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:term:permalink}</code></td>
                <td><?php echo home_url('taxonomy/term'); ?></td>
            </tr>
            <tr class="acf-row">
                <td colspan="2"><em>See <code>{term}</code> for all available tags</em></td>
            </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Term Action Named <code>my-term</code></strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-term:ID}</code></td>
                <td>23</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-term:post_title}</code></td>
                <td>Term</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-term:admin_url}</code></td>
                <td><?php echo admin_url('term.php?tag_ID=23'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-term:permalink}</code></td>
                <td><?php echo home_url('taxonomy/term'); ?></td>
            </tr>
            <tr class="acf-row">
                <td colspan="2"><em>See <code>{term}</code> for all available tags</em></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function actions_user($field){
        ?>
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Last User Action</strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:user:ID}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:user:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:user:user_email}</code></td>
                <td>user@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:user:user_url}</code></td>
                <td>https://www.website.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:user:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td colspan="2"><em>See <code>{user}</code> for all available tags</em></td>
            </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>User Action Named <code>my-user</code></strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-user:ID}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-user:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-user:user_email}</code></td>
                <td>user@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-user:user_url}</code></td>
                <td>https://www.website.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-user:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td colspan="2"><em>See <code>{user}</code> for all available tags</em></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function actions_email($field){
        ?>
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Last Email Action</strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:from}</code></td>
                <td>Contact <contact@website.com></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:to}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:reply_to}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:cc}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:bcc}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:subject}</code></td>
                <td>Subject</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:email:content}</code></td>
                <td>Content</td>
            </tr>
            </tbody>
        </table>
        
        <br />
        
        <table class="acf-table">
            <thead>
            <tr>
                <th colspan="2"><strong>Email Action Named <code>my-email</code></strong></td>
            </tr>
            </thead>
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:from}</code></td>
                <td>Contact <contact@website.com></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:to}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:reply_to}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:cc}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:bcc}</code></td>
                <td>email@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:subject}</code></td>
                <td>Subject</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{action:my-email:content}</code></td>
                <td>Content</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function request($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{request:name}</code></td>
                <td><code>$_REQUEST['name']</code></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{request:name:key}</code></td>
                <td><code>$_REQUEST['name']['key']</code></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function current_post($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{post}</code></td>
                <td>128</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:ID}</code></td>
                <td>128</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_date}</code></td>
                <td>2020-03-01 20:07:48</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_date_gmt}</code></td>
                <td>2020-03-01 19:07:48</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_content}</code></td>
                <td>Content</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_title}</code></td>
                <td>Title</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_excerpt}</code></td>
                <td>Excerpt</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:permalink}</code></td>
                <td><?php echo home_url('my-post'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:admin_url}</code></td>
                <td><?php echo admin_url('post.php?post=128&action=edit'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_status}</code></td>
                <td>publish</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:comment_status}</code></td>
                <td>closed</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:ping_status}</code></td>
                <td>closed</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_password}</code></td>
                <td>password</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_name}</code></td>
                <td>name</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:to_ping}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:pinged}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_modified}</code></td>
                <td>2020-03-01 20:07:48</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_modified_gmt}</code></td>
                <td>2020-03-01 19:07:48</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_content_filtered}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_parent}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:guid}</code></td>
                <td><?php echo home_url('?page_id=128'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:menu_order}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_type}</code></td>
                <td>page</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_mime_type}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:comment_count}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:filter}</code></td>
                <td>raw</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author}</code></td>
                <td>1</td>
            </tr>
            
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:ID}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_pass}</code></td>
                <td>password_hash</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_nicename}</code></td>
                <td>nicename</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_email}</code></td>
                <td>user@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_url}</code></td>
                <td>https://www.website.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:admin_url}</code></td>
                <td><?php echo admin_url('user-edit.php?user_id=1'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_registered}</code></td>
                <td>2020-02-22 22:10:02</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_activation_key}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:user_status}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:display_name}</code></td>
                <td>John Doe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:nickname}</code></td>
                <td>JohnDoe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:first_name}</code></td>
                <td>John</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:last_name}</code></td>
                <td>Doe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:description}</code></td>
                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:rich_editing}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:syntax_highlighting}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:comment_shortcuts}</code></td>
                <td>false</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:admin_color}</code></td>
                <td>fresh</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:use_ssl}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:show_admin_bar_front}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:locale}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:wp_capabilities}</code></td>
                <td>a:1:{s:13:"administrator";b:1;}</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:wp_user_level}</code></td>
                <td>10</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:dismissed_wp_pointers}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{post:post_author_data:show_welcome_panel}</code></td>
                <td>1</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function current_term($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{term}</code></td>
                <td>23</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:ID}</code></td>
                <td>23</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:term_id}</code></td>
                <td>23</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:name}</code></td>
                <td>Term</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:slug}</code></td>
                <td>term</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:permalink}</code></td>
                <td><?php echo home_url('taxonomy/term'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:admin_url}</code></td>
                <td><?php echo admin_url('term.php?tag_ID=23'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:term_group}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:term_taxonomy_id}</code></td>
                <td>23</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:taxonomy}</code></td>
                <td>taxonomy</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:description}</code></td>
                <td>Content</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:parent}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:count}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{term:filter}</code></td>
                <td>raw</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function current_user($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{user}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:ID}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_pass}</code></td>
                <td>password_hash</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_nicename}</code></td>
                <td>nicename</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_email}</code></td>
                <td>user@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_url}</code></td>
                <td>https://www.website.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:admin_url}</code></td>
                <td><?php echo admin_url('user-edit.php?user_id=1'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_registered}</code></td>
                <td>2020-02-22 22:10:02</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_activation_key}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:user_status}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:display_name}</code></td>
                <td>John Doe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:nickname}</code></td>
                <td>JohnDoe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:first_name}</code></td>
                <td>John</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:last_name}</code></td>
                <td>Doe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:description}</code></td>
                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:rich_editing}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:syntax_highlighting}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:comment_shortcuts}</code></td>
                <td>false</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:admin_color}</code></td>
                <td>fresh</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:use_ssl}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:show_admin_bar_front}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:locale}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:wp_capabilities}</code></td>
                <td>a:1:{s:13:"administrator";b:1;}</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:wp_user_level}</code></td>
                <td>10</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:dismissed_wp_pointers}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{user:show_welcome_panel}</code></td>
                <td>1</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function current_author($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{author}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:ID}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_login}</code></td>
                <td>login</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_pass}</code></td>
                <td>password_hash</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_nicename}</code></td>
                <td>nicename</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_email}</code></td>
                <td>user@domain.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_url}</code></td>
                <td>https://www.website.com</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:permalink}</code></td>
                <td><?php echo home_url('author/johndoe'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:admin_url}</code></td>
                <td><?php echo admin_url('user-edit.php?user_id=1'); ?></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_registered}</code></td>
                <td>2020-02-22 22:10:02</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_activation_key}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:user_status}</code></td>
                <td>0</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:display_name}</code></td>
                <td>John Doe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:nickname}</code></td>
                <td>JohnDoe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:first_name}</code></td>
                <td>John</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:last_name}</code></td>
                <td>Doe</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:description}</code></td>
                <td>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:rich_editing}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:syntax_highlighting}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:comment_shortcuts}</code></td>
                <td>false</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:admin_color}</code></td>
                <td>fresh</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:use_ssl}</code></td>
                <td>1</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:show_admin_bar_front}</code></td>
                <td>true</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:locale}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:wp_capabilities}</code></td>
                <td>a:1:{s:13:"administrator";b:1;}</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:wp_user_level}</code></td>
                <td>10</td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:dismissed_wp_pointers}</code></td>
                <td></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{author:show_welcome_panel}</code></td>
                <td>1</td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
    function current_form($field){
        ?>
        <table class="acf-table">
            <tbody>
            <tr class="acf-row">
                <td width="35%"><code>{form}</code></td>
                <td>11<br/></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{form:ID}</code></td>
                <td>11<br/></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{form:title}</code></td>
                <td>Form<br/></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{form:name}</code></td>
                <td>form<br/></td>
            </tr>
            <tr class="acf-row">
                <td width="35%"><code>{form:custom_key}</code></td>
                <td>Custom key value<br/></td>
            </tr>
            </tbody>
        </table>
        <?php
    }
    
}

acf_new_instance('acfe_dynamic_forms_cheatsheet');

endif;