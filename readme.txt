=== Advanced Custom Fields: Extended ===
Contributors: hwk-fr
Donate link: https://ko-fi.com/acfextended
Tags: acf, custom fields, meta, admin, fields, form, repeater, content
Requires at least: 4.9
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 0.8.5.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

All-in-one enhancement suite that improves WordPress & Advanced Custom Fields.

== Description ==

🚀 All-in-one enhancement suite that improves WordPress & Advanced Custom Fields. This plugin aims to provide a powerful administration framework with a wide range of improvements & optimizations.

== ⭐ Highlight ==

* New Field Groups Locations
* 14+ New ACF Fields
* 10+ ACF Fields Enhanced
* Bidirectional Fields
* Advanced Fields Validation
* Flexible Content as Page Builder
* Compress ACF values into a single metadata
* ACF Forms Manager
* ACF Options Pages / Block Types Manager
* ACF & WordPress Meta Overview
* WordPress Post Types / Taxonomies Manager
* WordPress Options Manager
* WordPress Admin Enhancements
* ... And many more features

== 🤟 Philosophy ==

* 100% free
* Seamless integration
* No extra menu, ads or notices
* Built by developers, for developers

== 🛠️ Links ==

* Join the [ACF Slack community](https://join.slack.com/t/wpacf/shared_invite/enQtODc2MjczMzM3NzQ1LTQxNmY2ZGY3OTU2NDkxNTliYmNhMmY1NDMwZGM4NmYxNzgwZTI5MDUzMDFlMGQ5YzcwNDc0ZWM0MDVmODI4NmI)
* Check the [ACF Extended Trello board](https://trello.com/b/QEgpU7CL/acf-extended)
* Visit our website [ACF-Extended.com](https://www.acf-extended.com) (WIP)
* Have a question? [Check the FAQ](https://wordpress.org/plugins/acf-extended/#faq)
* Found a bug? [Submit a ticket](https://wordpress.org/support/plugin/acf-extended)
* A feature request? [Submit an idea](https://wordpress.org/support/plugin/acf-extended)
* Want to fork me? [GitHub repository](https://github.com/acf-extended/ACF-Extended)
* Enjoying this plugin? [Submit a review](https://wordpress.org/support/plugin/acf-extended/reviews/#new-post)
* Want to keep me awake? [Buy me a coffee](https://ko-fi.com/acfextended)
* Want to follow me? [Here is my Twitter](https://twitter.com/hwkfr)

== 📺 Flexible Content Showcase ==

[youtube https://www.youtube.com/watch?v=hzkNL0BA3Dk]

== 📝 Requirements ==

**This plugin requires at least ACF Pro 5.7.10**

If you don't already own [ACF Pro](https://www.advancedcustomfields.com/pro/), you should consider it. It's one of the most powerful WordPress plugin, with a life-time licence for unlimited websites.

== 🏷️ Features ==

= ACF: Field Groups Settings =

* **Auto Sync PHP**
Automatically synchronize field groups with local PHP files upon field group updates. This feature will create, include and update a local PHP file for each field group (just like the native Json sync feature).
Default folder: `/wp-content/themes/my-theme/acfe-php/`

* **Auto Sync Json**
Control which field groups you want to synchronize with local Json files. Display warnings if the Json file has been manually deleted. Manually synchronize Json from field group screen.

* **Categories**
Spice up your field groups with a custom taxonomy and filter field groups by terms.

* **Permissions**
Add permission layer to field groups. Choose which roles can view & edit field groups in the post edition screen.

* **Alternative Title**
Display an alternative field group title in post edition screen.

* **Note**
Add a personal note in the field group administration. Only visible to administrators

* **Custom meta data**
Add custom metas (key/value) in the field group administration. Data can be retrieved using `acf_get_field_group()`

* **View raw data**
Display raw field group data in a modal to check your configuration & settings

* **Custom key**
Set custom field group key. ie: `group_custom_name`

* **Hide on Screen: Block Editor**
Choose to disable Block Editor (Gutenberg) on the field group location

* **Advanced settings**
Enable advanced fields settings & validation based on screen (administration/front-end). See fields advanced settings/validation

* **New field group instructions placement: Tooltip**
Display instructions in a tooltip which is displayed when hovering an information icon

* **New field group location: All post types**
Display field group on all post types edition screen

* **New field group location: Post type List**
Display field group on post types admin list screen. Fields are saved in the option: `{post_type}_options`

* **New field group location: Taxonomy List**
Display field group on taxonomies admin list screen. Fields are saved in the option: `tax_{taxonomy}_options`

* **New field group location: Post type Archive**
Display field group on the post type archive (option page). The post type argument `acfe_admin_archive` must be set to `true`. This feature is also available in the Dynamic Post Type UI. Fields are saved in the option: `{post_type}_archive`

= ACF: Field Groups List =

* **Column: Category**
Display and filter field groups categories

* **Column: Locations**
Quick view of field groups locations information using icons & popover

* **Column: Load**
Quick view of field groups data load source (DB, PHP or Json)

* **Column: Sync PHP / Json**
Quick view of field groups synchronization status with warnings

* **Row action: Export PHP / Json**
One-click export for each field groups

* **Row action: Field group key**
Quick view of field groups keys

= ACF: Field Groups - Local =

* **Local**
Display local field groups that are loaded by ACF, but not available in the ACF field group administration. Example: a field group is registered locally in the `functions.php` file, but not in ACF

* **Export**
Export local field groups into PHP or Json

* **Sync**
Sync local field groups back to the database and edit their fields just like any other field group

= ACF: Fields Settings =

* **Bidirectional fields**
An advanced bidirectional setting (also called post-to-post) is available for the following fields: Relationship, Post object, User & Taxonomy terms.
Fields will work bidirectionally and automatically update each others. Works in groups & clones (prefixed field names must be turned off).
[Usage example is available in the FAQ](#faq)

* **Advanced settings**
A more sophisticated field settings based on specified location (administration/front-end). Example: Field is required only in front-end. The field group "Advanced settings" must be turned ON.

* **Advanced validation**
A more sophisticated validation conditions (AND/OR) with custom error messages based on specified location (administration/front-end). The field group "Advanced settings" must be turned ON.

* **Permissions**
Add permission layer to fields. Choose which roles can view & edit fields in the post edition screen. (can be combined with field groups permissions)

* **Save as Meta**
Exclude the field from the "Single Meta Save" compression feature. The global ACF setting "Single Meta Save" must be turned ON.

* **View raw data**
Display raw field data in a modal to check your configuration & settings

= ACF: Fields =

* **Field Clone: Edit in modal**
Allow users to edit clone fields in a modal. Choose the edit button text, display close button and the modal size

* **Field Datepicker/Datetime picker/Timepicker: Improved UI**
Fields interfaces have been improved to display a more modern version

* **Field File: Uploader type**
Choose the uploader type: Basic or native WP uploader

* **Field Group: Edit in modal**
Allow users to edit group fields in a modal Choose the edit button text, display close button and the modal size

* **Field Image: Use as Featured Thumbnail**
Choose if an image field should be considered as post featured thumbnail

* **Field Image: Uploader type**
Choose the uploader type: Basic or native WP uploader

* **Field Post Object: Allow custom values**
Allow user to enter custom value which will be saved as a new post

* **Field Repeater: Stylised button**
Add style to 'Add Row' button

* **Field Repeater: Disable Sortable**
Disable sortable rows using `filter('acfe/repeater/lock/name=my_repeater', true, $field)`

* **Field Repeater: Remove Actions**
Remove the actions buttons using `filter('acfe/repeater/remove_actions/name=my_repeater', true, $field)`

* **Field Select: Placeholder**
Change the default "Select" placeholder text and Search Input placeholder

* **Field Select: Allow custom values**
Allow user to enter custom value which will be saved

* **Field Textarea: Code mode**
Switch font family to monospace and allow tab indent

= ACF: New Fields =

* **New Field: Advanced Link**
Display a modern Link Selection in a modal. Posts, Post Types Archives & terms selection can be filtered in the field administration. Add custom sub fields using `filter('acfe/fields/advanced_link/sub_fields', $sub_fields, $field, $value)`

* **New Field: Button**
Display a custom submit or button. Built-in ajax call setting. Usage example available in the field administration

* **New Field: Code Editor**
Edit code using the native WP Core Codemirror library. Default languages: Text/HTML, Javascript, CSS, PHP mixed/plain.

* **New Field: Columns**
Re-arrange fields administration using columns

* **New Field: Dynamic form**
Select any dynamic form (format: checkbox, radio or select). See ACF: Dynamic Forms section

* **New Field: Dynamic message**
Display custom HTML/PHP content using `acf/render_field/name=my_field`. Example is available in the field administration

* **New Field: Google reCaptcha**
Display a reCaptcha field (compatible v2 & v3)

* **New Field: Hidden input**
Display a hidden input with custom name/value

* **New Field: Post status selection**
Select any post status (format: checkbox, radio or select)

* **New Field: Post type selection**
Select any post type (format: checkbox, radio or select)

* **New Field: Slug**
A slug text input (ie: `my-text-input`)

* **New Field: Taxonomy selection**
Select any taxonomy (format: checkbox, radio or select)

* **New Field: Taxonomy Terms selection**
Select any terms of any taxonomies, allow specific terms, level or childrens (format: checkbox or select). Terms can be loaded & saved for the current post (just like the native ACF Taxonomy field)

* **New Field: User roles selection**
Select any user role (format: checkbox, radio or select)

= WordPress: Dynamic Post Types =

Create and manage post types from your WordPress administration (Tools > Post Types). All WordPress post types arguments can be set and managed. But also:

* Manage Posts per page, order by and order for the post type archive
* Manage Posts per page, order by and order for the post type administration screen
* Set custom single template (ie: `my-single.php`) instead of the native `single-{post_type}.php`
* Set custom archive template (ie: `my-archive.php`) instead of the native `archive-{post_type}.php`
* Add an Archive Option Page under the post type menu and set your field groups
* Manual PHP/Json Import & Export is available in the ACF > Tools page

= WordPress: Dynamic Taxonomies =

Create and manage taxonomies from your WordPress administration (Tools > Taxonomies). All WordPress taxonomies arguments can be set and managed. But also:

* Manage Posts per page, order by and order for the taxonomy term archive
* Manage Posts per page, order by and order for the taxonomy administration screen
* Set custom taxonomy template (ie: `my-taxonomy.php`) instead of the native `taxonomy-{taxonomy}.php`
* Manual PHP/Json Import & Export is available in the ACF > Tools page

= WordPress: Ajax Author Box =

The native WP Author Metabox has been replaced with a dynamic version allowing to manage thousands of users without slowing down the post administration.

= WordPress: Taxonomy List & Edit =

Taxonomies list & edit views have been enhanced for a more consistent administration experience, using CSS/JS only. Views are now similar to post type edition screens.

= WordPress: Options =

Manage WordPress options from Settings > Options.

* View, add, edit and delete options
* Working with strings, serialized & Json values

= ACF: Settings =

* **ACF: Settings page**
Display all ACF settings in one page.

* **ACF Extended: Settings page**
Display all ACF Extended settings in one page.

= ACF: Dev Mode =

View all custom Posts, Terms, Users & Options meta in a readable format

* Print Arrays & Json values
* ACF fields meta are grouped together
* ACF field groups related to fields are displayed when available
* Dev mode also enable `SCRIPT_DEBUG`
* Activate using `acf_update_setting('acfe/dev', true)` or `define('ACFE_dev', true)`

= ACF: Options Pages =

Manage ACF Options Pages from ACF > Options.

* View, add, edit and delete options pages
* All arguments are available
* Manual PHP/Json Import & Export is available in the ACF > Tools page

= ACF: Block Types (Gutenberg) =

Manage ACF Block Types from ACF > Block Types.

* View, add, edit and delete Block Types
* All arguments are available
* Manual PHP/Json Import & Export is available in the ACF > Tools page
* Requires ACF Pro 5.8

= ACF: Flexible Content Enhancement =

* Controls: Inline Layout Title Edition
* Controls: Copy, Paste & Duplicate Layouts on the fly
* Controls: Copy & Paste all layouts on the fly
* Stylised Button: Add style to 'Add Row' button
* Hide Empty Message: Hide the native Flexible Content 'Empty' message
* Empty Message: Change the native Flexible Content 'Click the Add Row button below...' message
* Layouts Thumbnails: Add thumbnails for each layout in the layout selection
* Layouts Settings: Choose a field group to clone and to be used as a layout configuration modal in the administration. Data can be retrieved using `while(have_settings()): the_setting(); get_sub_field('my_setting');`
* Layouts Render: Add `template.php`, `style.css` & `script.js` files settings for each layout. Those settings can be then accessed in the front-end ([More information in the FAQ](#faq))
* Layouts Dynamic Preview: Edit & Preview Layouts on-the-fly from your WordPress administration, just like in Gutenberg (Layouts Render must be turned ON)
* Modal Edition: Edit layouts in a modal
* Modal Selection: Change the layout selection into a modal
* Modal Selection Title: Change the layout modal title
* Modal Selection Columns: Change the layout modal columns grid. 1, 2, 3, 4, 5 or 6 columns available
* Modal Selection Categories: Add category for each layout in the layout modal
* Modal Settings: Clone field groups and display them as a layout settings modal
* Layouts State: Force layouts to be collapsed or opened by default
* Layouts Collapse: Remove collapse action
* Layouts: Hide "Add Layout" buttons
* Layouts: Hide "Remove Layout" buttons. Can also be done using `filter('acfe/flexible/remove_actions/name=my_flexible', true, $field)`
* Lock Layouts: Disable sortable Layouts. Can also be done using `filter('acfe/flexible/lock/name=my_flexible', true, $field)`
* Button Label: Supports Dashicons icons elements `<span>`
* One Click: the 'Add row' button will add a layout without the selection modal if there is only one layout available in the flexible content
* Asynchronous Layouts: Add layouts using Ajax method. This setting increase performance on complex Flexible Content
* Disable Legacy Layout Title Ajax: Disable the native ACF Layout Title Ajax call on `acf/fields/flexible_content/layout_title`.

= ACF: Dynamic Forms =

Manage ACF Forms from your WordPress administration. All ACF Form settings are available. But also:

* Render multiple field groups in one single form with custom HTML between them
* Apply custom CSS class to all fields & wrappers
* Apply custom CSS class to fields errors
* Group errors above the form, display them above fields, below fields or hide them
* Hide form on successful submission
* Map fields and change their settings based on the location (front-end/back-end)
* Add multiple actions on form submission:
* Create or Update Posts
* Create or Update Terms
* Create Update, or Log Users
* Send multiple e-mails
* Trigger custom PHP actions
* Integration, validation & custom submission examples in the administration
* Display forms using `acfe_form('my_form_name')` or `acfe_form(188)` helpers
* Display forms using shortcodes `[acfe_form name="my_form_name"]` or `[acfe_form ID="188"]`
* The function `acf_form_head()` is not needed anymore
* Manual Import & Export is available in the ACF > Tools page

= ACF: Single Meta Save (Beta) =

Compress all fields values from the current post, term or user into one single meta data. This process lighten the database load as values are saved and read from one single row. Once activated and after saving a post/term/user in the administration, all old meta data will be removed and packed together in a meta called `acf`.

To monitor the process, it is possible to enable the "ACF Extended: Dev Mode" which will display all WP & ACF meta data on every Posts, Terms & Users.

This feature also enables a new setting available in every fields: "Save as individual meta". If this setting is turned ON on a specific field, then the value will be saved individually. `WP Queries` and `Meta Queries` can be used just like before.

Single Meta Save is disabled by default. To enable it, add the following code in your `functions.php` file:

`
add_action('acf/init', 'my_acfe_modules');
function my_acfe_modules(){
    
    // Enable Single Meta Save
    acf_update_setting('acfe/modules/single_meta', true);
    
}
`

Note: It is possible to revert back to the native ACF save process. To do so, keep the feature enabled, get in the post administration you want to revert back. Disable the feature in your code, and save the post. All data will be saved back to individual meta data.

== ❤️ Supporters ==

* Thanks to [Brandon A.](https://twitter.com/AsmussenBrandon) for his support & tests
* Thanks to [Damien C.](https://twitter.com/DamChtlv) for his support & tests
* Thanks to [Valentin P.](https://twitter.com/Val_Pellegrin) for his support & tests
* Thanks to Damian P. for his support & tests
* Thanks to [Jaakko S.](https://twitter.com/jsaarenk) for his support & tests
* Thanks to [Renan A.](https://twitter.com/altendorfme) for his support & tests

== Installation ==

= Wordpress Install =

1. Install Advanced Custom Fields: Pro
2. Upload the plugin files to the `/wp-content/plugins/advanced-custom-fields-extended` directory, or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin through the 'Plugins' screen in WordPress.
4. Everything is ready!

== Frequently Asked Questions ==

= How to enable PHP Auto Sync? =

Create a folder `/acfe-php/` in your theme. Go to your field group administration, check to 'Sync PHP' option in the sidebar and save the field group.

= How to disable PHP/Json Auto Sync? =

Once you activated PHP or Json Sync on a field group, you must manually delete the file `group_xxxxxxxxxx` in your theme folder in order disable it. This behavior is applied to avoid any data desynchronization.

= How to get fields set in the Post Type List location? =

Fields are saved in the option: `{post_type}_options`. Frontend usage example: `get_field('my_field', 'page_options')`

= How to get fields set in the Taxonomy List location? =

Fields are saved in the option: `tax_{taxonomy}_options`. Frontend usage example: `get_field('my_field', 'tax_category_options')`

= How to activate the Post Type Archive location? =

The post type archive location is a virtual option page created under the post type menu of your choice. In order to activate this feature, you must set `acfe_admin_archive => true` in the `register_post_type()` declaration. Once activated, a new submenu called "Archive" will appear under the said post type. Then, you'll be able to set this location in any field group.

Note: This feature is available in the Dynamic Post Type UI in the Administration > Tools > Post Types, under the "Admin" tab.

= How to get fields set in the Post Type Archive location? =

Fields are saved in the option: `{post_type}_archive`. Frontend usage example: `get_field('my_field', 'post_archive')`

= How the bidirectional field setting works? =

Usage example:

* Create a field group "Page: Relation" displaying on the post type: page

* Inside it, create a relationship field, allowing the post type: post

* Create an another field group "Post: Relation" displaying on the post type: post

* Inside it, create a relationship field, allowing the post type: page

* Activate the "Bidirectional" setting and select the "Page: Relation" relationship field

* Edit any page, and select any post of the post type post in the relationship field

* The page is now also saved in the said post relationship field

= How to use Flexible Content: Templates, Styles & Scripts render? =

Templates, styles & scripts settings are saved in each layouts. They can be accessed manually via `get_field('my_flexible')` for example.

The settings are saved in the following keys: `acfe_flexible_render_template`, `acfe_flexible_render_style` and `acfe_flexible_render_script`.

ACF Extended has two functions which will automatically include those files: `echo get_flexible($selector, $post_id)` or `the_flexible($selector, $post_id)` (`$post_id` is optional).

Usage example: `the_flexible('my_flexible');`.

When using this function, you have access to the following global variables: `$layout`, `$field` & `$is_preview` (when Dynamic Preview setting is enabled).

[More information are available on the official website](https://www.acf-extended.com/post/flexible-content-dynamic-layout-preview)

= How the Flexible Content: Dynamic Preview works? =

[More information are available on the official website](https://www.acf-extended.com/post/flexible-content-dynamic-layout-preview)

= How to change the Flexible Content: Thumbnails URL in PHP? =

You can use the following filters:

`
// add_filter('acfe/flexible/thumbnail/name=my_flexible', 'acf_flexible_layout_thumbnail', 10, 3);
// add_filter('acfe/flexible/thumbnail/key=field_xxxxxx', 'acf_flexible_layout_thumbnail', 10, 3);

// add_filter('acfe/flexible/layout/thumbnail/name=my_flexible&layout=my_layout', 'acf_flexible_layout_thumbnail', 10, 3);
// add_filter('acfe/flexible/layout/thumbnail/key=field_xxxxxx&layout=my_layout', 'acf_flexible_layout_thumbnail', 10, 3);

add_filter('acfe/flexible/layout/thumbnail/layout=my_layout', 'acf_flexible_layout_thumbnail', 10, 3);
function acf_flexible_layout_thumbnail($thumbnail, $field, $layout){
    
    
    // Must return an URL or Attachment ID
    return 'https://www.example.com/my-image.jpg';
    
}
`

= How to change the Flexible Content: Dynamic Preview content in PHP? =

You can use the following actions:

`
// add_action('acfe/flexible/preview/name=my_flexible', 'acf_flexible_preview', 10, 2);
// add_action('acfe/flexible/preview/key=field_xxxxxx', 'acf_flexible_preview', 10, 2);

add_action('acfe/flexible/preview', 'acf_flexible_preview', 10, 2);
function acf_flexible_preview($field, $layout){
    
    echo 'My Preview';
    
    // It is important to use 'die', as we are in an Ajax request
    die;
    
}

// add_action('acfe/flexible/layout/preview/name=my_flexible&layout=my_layout', 'acf_flexible_layout_preview', 10, 2);
// add_action('acfe/flexible/layout/preview/key=field_xxxxxx&layout=my_layout', 'acf_flexible_layout_preview', 10, 2);

add_action('acfe/flexible/layout/preview/layout=my_layout', 'acf_flexible_layout_preview', 10, 2);
function acf_flexible_layout_preview($field, $layout){
    
    echo 'My Preview';
    
    // It is important to use 'die', as we are in an Ajax request
    die;
    
}
`

= How to enqueue new style/script files in the Flexible Content in PHP? =

You can use the following actions:

`
// add_action('acfe/flexible/enqueue/name=my_flexible', 'acf_flexible_enqueue', 10, 2);
// add_action('acfe/flexible/enqueue/key=field_xxxxxx', 'acf_flexible_enqueue', 10, 2);

add_action('acfe/flexible/enqueue', 'acf_flexible_enqueue', 10, 2);
function acf_flexible_enqueue($field, $is_preview){
    
    // Only in Ajax preview
    if($is_preview){
        
        wp_enqueue_style('my-style-preview', 'https://www.example.com/style-preview.css');
        
    }
    
    wp_enqueue_style('my-style', 'https://www.example.com/style.css');
    
}

// add_action('acfe/flexible/layout/enqueue/name=my_flexible&layout=my_layout', 'acf_flexible_layout_enqueue', 10, 3);
// add_action('acfe/flexible/layout/enqueue/key=field_xxxxxx&layout=my_layout', 'acf_flexible_layout_enqueue', 10, 3);

add_action('acfe/flexible/layout/enqueue/layout=my_layout', 'acf_flexible_layout_enqueue', 10, 3);
function acf_flexible_layout_enqueue($field, $layout, $is_preview){
    
    // Only in Ajax preview
    if($is_preview){
        
        wp_enqueue_style('my-style-preview', 'https://www.example.com/style-preview.css');
        
    }
    
    wp_enqueue_style('my-style', 'https://www.example.com/style.css');
    
}
`

= How to change the Flexible Content: Layout Render Paths in PHP? =

You can use the following actions:

`
// add_filter('acfe/flexible/render/template', 'acf_flexible_layout_render_template', 10, 4);
// add_filter('acfe/flexible/render/template/name=my_flexible', 'acf_flexible_layout_render_template', 10, 4);
// add_filter('acfe/flexible/render/template/key=field_xxxxxx', 'acf_flexible_layout_render_template', 10, 4);

// add_filter('acfe/flexible/layout/render/template/name=my_flexible&layout=my_layout', 'acf_flexible_layout_render_template', 10, 4);
// add_filter('acfe/flexible/layout/render/template/key=field_xxxxxx&layout=my_layout', 'acf_flexible_layout_render_template', 10, 4);

add_filter('acfe/flexible/layout/render/template/layout=my_layout', 'acf_flexible_layout_render_template', 10, 4);
function acf_flexible_layout_render_template($template, $field, $layout, $is_preview){
    
    // Only in Ajax preview
    if($is_preview){
        
        return get_stylesheet_directory() . '/my-template-preview.php';
        
    }
    
    return get_stylesheet_directory() . '/my-template.php';
    
}

// add_filter('acfe/flexible/render/style', 'acf_flexible_layout_render_style', 10, 4);
// add_filter('acfe/flexible/render/style/name=my_flexible', 'acf_flexible_layout_render_style', 10, 4);
// add_filter('acfe/flexible/render/style/key=field_xxxxxx', 'acf_flexible_layout_render_style', 10, 4);

// add_filter('acfe/flexible/layout/render/style/name=my_flexible&layout=my_layout', 'acf_flexible_layout_render_style', 10, 4);
// add_filter('acfe/flexible/layout/render/style/key=field_xxxxxx&layout=my_layout', 'acf_flexible_layout_render_style', 10, 4);

add_filter('acfe/flexible/layout/render/style/layout=my_layout', 'acf_flexible_layout_render_style', 10, 4);
function acf_flexible_layout_render_style($style, $field, $layout, $is_preview){
    
    // Only in Ajax preview
    if($is_preview){
        
        return get_stylesheet_directory_uri() . '/my-style-preview.css';
        
    }
    
    return get_stylesheet_directory_uri() . '/my-style.css';
    
}

// add_filter('acfe/flexible/render/script', 'acf_flexible_layout_render_script', 10, 4);
// add_filter('acfe/flexible/render/script/name=my_flexible', 'acf_flexible_layout_render_script', 10, 4);
// add_filter('acfe/flexible/render/script/key=field_xxxxxx', 'acf_flexible_layout_render_script', 10, 4);

// add_filter('acfe/flexible/layout/render/script/name=my_flexible&layout=my_layout', 'acf_flexible_layout_render_script', 10, 4);
// add_filter('acfe/flexible/layout/render/script/key=field_xxxxxx&layout=my_layout', 'acf_flexible_layout_render_script', 10, 4);

add_filter('acfe/flexible/layout/render/script/layout=my_layout', 'acf_flexible_layout_render_script', 10, 4);
function acf_flexible_layout_render_script($script, $field, $layout, $is_preview){
    
    // Only in Ajax preview
    if($is_preview){
        
        return get_stylesheet_directory_uri() . '/my-script-preview.js';
        
    }
    
    return get_stylesheet_directory_uri() . '/my-script.js';
    
}
`

= How to disable specific ACF Extended modules? (Dynamic Post Types, Taxonomies, Options Pages etc...) =

You can use the following action:

`
add_action('acf/init', 'my_acfe_modules');
function my_acfe_modules(){
    
    // Disable Ajax Author box
    acf_update_setting('acfe/modules/author', false);
    
    // Disable ACF > Block Types
    acf_update_setting('acfe/modules/dynamic_block_types', false);
    
    // Disable Forms
    acf_update_setting('acfe/modules/dynamic_forms', false);
    
    // Disable Tools > Post Types
    acf_update_setting('acfe/modules/dynamic_post_types', false);
    
    // Disable Tools > Taxonomies
    acf_update_setting('acfe/modules/dynamic_taxonomies', false);
    
    // Disable ACF > Options Pages
    acf_update_setting('acfe/modules/dynamic_options_pages', false);
    
    // Disable Settings > Options
    acf_update_setting('acfe/modules/options', false);
    
    // Disable Taxonomies enhancements
    acf_update_setting('acfe/modules/taxonomies', false);
    
}
`

== Screenshots ==

1. Flexible Content Preview
2. Flexible Content Modal
3. Field Groups List
4. Field Group
5. Dynamic Post Type
6. Dynamic Taxonomy
7. Dynamic Options Pages
8. Dynamic Block Types
9. Field
10. ACF Settings

== Changelog ==

= 0.8.5.5 =
* Field: Button - Added nominative JS hooks `action('acfe/fields/button/before/name=my_field', response, $el, data);`
* Field: Button - Added nominative JS hooks `action('acfe/fields/button/success/name=my_field', response, $el, data);`
* Field: Button - Added nominative JS hooks `action('acfe/fields/button/complete/name=my_field', response, $el, data);`
* Field: Button - Deprecated JS hooks `acfe/fields/button/before_ajax` & `acfe/fields/button/ajax_success`. Replaced by `acfe/fields/button/before` & `acfe/fields/button/success`
* Field: Flexible Content - Added generic `acfe/flexible/thumbnail` hook
* Module: Dynamic Forms - Fixed a bug where 2 forms with 2 fields with the same name on the same page, will override `default_value` during render
* Module: Dynamic Forms - Fixed a nasty bug with "Post Action" which could trigger an infinite loop when using Elementor & YOAST. See bug report: https://github.com/elementor/elementor/issues/10998
* Module: Dynamic Forms - Fixed jQuery not recognized on form success in some specific case
* Module: Dynamic Forms - Added "No form element" compatibility allowing validation settings to be applied when form tag isn't printed
* Module: Dynamic Forms - Fixed typo in "Custom Action" code example
* Module: Dynamic Forms - Added to the possibility to use `get_field('my_field')` to retrieve form input value inside `acfe/form/load` hooks
* Module: Single Meta Save - Fixed hook arguments which could trigger a PHP error in some specific cases
* General: Readme - Added Flexible Content Settings Modal example

= 0.8.5 =
* General: Added Settings/Options API
* General: Improved modules performance
* Module: Dynamic Forms - Improved module UI, fields UI & documentation
* Module: Dynamic Forms - Added "Cheatsheet" tab to list all available template tags
* Module: Dynamic Forms - Added "Validation" tab
* Module: Dynamic Forms - Email Action - Added "Reply-to", "Cc", & "Bcc" fields
* Module: Dynamic Forms - Email Action - Added "Delete attachment once sent" setting for each Dynamic attachments
* Module: Dynamic Forms - Email Action - Added "Static attachments"
* Module: Dynamic Forms - User Action - Added "Log user" action type
* Module: Dynamic Forms - Post Action - Terms can now be created on the fly using custom template tags. Example: `My new term|my-taxonomy`
* Module: Dynamic Forms - Post Action - Fixed terms not being set correctly when user wasn't logged
* Module: Dynamic Forms - Template tags are now allowed everywhere
* Module: Dynamic Forms - Added "Apply field groups rules" setting to take field group conditional locations into account
* Module: Dynamic Forms - Added a switch on "Form HTML override" to explicity explain this setting is optional
* Module: Dynamic Forms - Added `{get_field:field_name}` template tags to retrieve field value from DB. More information in the "Cheatsheet" tab
* Module: Dynamic Forms - Added `{current:post/term/user/author/form}` template tags to retrieve current data information. More information in the "Cheatsheet" tab
* Module: Dynamic Forms - Added `{current:***}` template tags to retrieve current post/term/user/post author/form information. More information in the "Cheatsheet" tab
* Module: Dynamic Forms - Added "Hide successful re-validation notice" setting
* Module: Dynamic Forms - Google Map return value is now correctly formatted
* Module: Dynamic Forms - Fixed WPML compatibility problem when using "honeypot" field
* Module: Dynamic Forms - Fields conditional logic are now working when fields are wrapper within custom div
* Module: Dynamic Forms - Added CSS to WP Media modal & cleaned accessibility text
* Module: Dynamic Forms - Added "Default Uploader Type" setting (based on field setting)
* Module: Dynamic Forms - Fixed Loading value not working correctly on repeaters/groups/flexible content
* Module: Dynamic Forms - Added PHP function to easily import form. `acfe_import_dynamic_form($array|$json);`
* Module: Dynamic Forms - Fixed shortcode being interpreted in admin view (Gutenberg/Dynamic Ajax Preview)
* Module: Dynamic Post Type - Fixed capabilities not being correctly mapped
* Module: Dynamic Post Type - Fixed Admin orderby & order not working correctly when `has_archive` was disabled
* Module: Dynamic Taxonomy - Fixed capabilities not being correctly mapped
* Module: Dynamic Taxonomy - Fixed Front posts per page, orderby & order settings
* Module: Dynamic Author - Added better post author ID cast
* Module: AutoSync - New fields groups now have Json/PHP AutoSync setting checked by default if the corresponding folder exists
* Field: Flexible Content - Modal Categories Selection setting is now using Select2 with tags
* Field: Flexible Content - The bottom bar in modal now clear potential floating elements
* Field: Flexible Content - Fixed clone function not working correctly with tabs that have conditional logic
* Field: Select - Added "## Title" markdown to add option group in field's choices
* Field: Select - Added "Allow custom" setting
* Field: Select2 - Tweaked CSS to match WordPress 5.3 UI
* Field: Select2 - Fixed WPML CSS overriding select2 style
* Field: Code Editor - The field now correctly trigger `change()` on input
* Field: Code Editor - Added "Max rows" setting
* Field: Code Editor - Fixed "Default value" javascript code being executed within the Data modal
* Field: Advanced Link - Added Term link types
* Field: Advanced Link - Added Post Type Archive link types
* Field: Advanced Link - Fixed custom sub fields not working correctly when retrieving values
* Field: Advanced Link - Reworked the custom sub fields declaration. It's now easier to add custom fields
* Field: reCaptcha - Fixed v2 theme & size settings not working correctly
* Field: reCaptcha - Fixed v3 JS error on reset
* Field: Datepicker/Datetime picker/Timepicker - Added CSS to match WordPress 5.3 UI
* Field Group: Fixed Instructions "Tooltip" mode not working correclty in groups
* Field Group: Added pre-rendering CSS for field groups with labels on left, removing potential during admin page load
* Field Group: Renamed "Third Party" Field Groups types to "Local"
* General: Gutenberg - Fixed CSS for modal

= 0.8.4.6 =
* Field: Taxonomy Terms - Fixed a problem with value return when "Load Terms" was enabled
* General: Modal - Fixed z-index problem with WP attachment modal

= 0.8.4.5 =
* Field Group: Hide on Screen - Added "Block Editor" (Gutenberg) setting, allowing administrator to disable the block editor on field group location
* Field Group: Third Party - Fixed Export/Sync Clones fields being processed during the action
* Field Group: Postbox Seamless CSS class are now added in PHP, which remove the blink during admin page load caused by the class being added in JS
* Field Group: Raw Data button now also display the WP_Post object
* Field: Button - Before/After HTML settings are now using code editor
* Field: Button - Ajax call - Added nominative hooks allowing to target specific field. Hook is now easier. Instructions have been updated
* Field: Flexible Content - Added "Advanced Flexible Content" setting (ON/OFF). All advanced settings are now hidden by default and depend on that setting to be shown. (Retro compatibility: if any advanced setting has been saved before, this setting will be set to ON)
* Field: Flexible Content - Added "Clone" button as a setting (Not enabled by default in all flexible content anymore)
* Field: Flexible Content - Added "Hide: Add Layout Button" setting
* Field: Flexible Content - Added "Hide: Remove Layout Button" setting
* Field: Flexible Content - Added "Lock Flexible Content" (sortable) setting
* Field: Flexible Content - Flexible Content can now be completely locked and all actions removed from the field settings
* Field: Flexible Content - Layout Settings modal now allow multiple clones
* Field: Flexible Content - Layout Settings modal size can now be selected for each layout (small/medium/large/extra large/full)
* Field: Flexible Content - Fixed "Force State: Open" not working correctly in some specific cases
* Field: Flexible Content - Fixed Modal Selection z-index problem on attachment screen
* Field: Flexible Content - Fixed Modal Edition z-index problem with button group (when already inside a modal)
* Field: Flexible Content - Fixed a bug causing duplicated "Layout Setting" & "Layout Title Edition" fields when export/re-importing a field group with flexible content that had this settings
* Field: Taxonomy Terms - Fixed a PHP notice when "Load Terms" was enabled with "select" as field type
* Field: Group/Clone - Seamless style CSS fixed in term view
* Field: Color Picker - Added position relative property when the field is used inside a modal
* Field: Hidden - Added global CSS style
* Module: Single Meta Save - Improved save process performance (it's now even faster!)
* Module: Dynamic Forms - Added `{query_var:var}` template tag in E-mail action, Post Load source, Post Target, Term Load source, Term Target & User Load source, Redirection & Updated Message fields. This will allow user to retrieve a specific `query_var` and use it dynamically
* Module: Dynamic Forms - `{query_var:var}` template tag also allow to specific key if the value is an array, using the following tag: `{query_var:var:key}`
* Module: Dynamic Forms - New "Query var" settings on Post, Terms, Users & E-mail actions that generate a custom query var based on the "Action name" after completing the action. For example: Post Action with Action name "my-action" will generate a `query_var` named `my-action` and as value the post data (array) that has been created/updated. This `query_var` can be accessed using `get_query_var('my-action');` or the template tag `{query_var:my-action:ID}` (Post ID), `{query_var:my-action:post_title}` (Post Title), `{query_var:my-action:permalink}` (Post Permalink), `{query_var:my-action:admin_url}` (Post Admin URL)
* Module: Dynamic Forms - Fixed shortcode not working correctly when using Form ID instead of Form name
* Module: Dynamic Forms - Actions UI Layouts can now be collapsed
* Module: Dev Mode - Added Meta Overview for Options Page
* Module: Options Page - Child Options page are now correctly displayed as child in the Options Page UI
* General: Modal - Added localization for the "Close" button
* General: Modal - Added Small/Medium/Large/Extra Large/Full sizes

= 0.8.4.1 =
* General: Fixed loading sequence when `get_field` was called directly in the functions file
* Field: Flexible Content - Fixed Dynamic Preview overlay z-index

= 0.8.4 =
* Module: Added Single Meta Save feature - Compress all fields values from the current post, term or user into one single meta data. This feature is disabled by default, to enable it, use `acf_update_setting('acfe/modules/single_meta', true);`. More information available in the readme.
* Module: Dynamic Post Types - Added `while(have_archive()): the_archive();` logic when the "Admin Archive Page" setting is turned on. This template tags can be used in the `archive-post-type.php` template which makes `get_field('my_field')` calls easier
* Module: Dynamic Post Types/Taxonomies - Fixed Posts/Terms column data when object was trashed.
* Module: PHP AutoSync - PHP sync files are now removed when the field group is disabled/trashed.
* Module: Dynamic Forms - In the "E-mail Action" the "From" field is now required.
* Module: Dynamic Forms - The Javascript hook `acfe/form/submit/success` is now correctly fired on form submission
* Module: Dev Mode - Added the WP & ACF Meta Overview on User pages
* Module: Dynamic Post Types/Taxonomies/Options Pages/Block Types - Removed Draft button action
* Field: Flexible Content - Significant Performance Boost (~50/60% faster during the loading). Many settings are now loaded in PHP. This massive rework brings some great performance on complex Flexible Content fields.
* Field: Flexible Content - Added "Layouts: Asynchronous" setting which add layouts using Ajax method (instead of having hidden layouts models in the DOM). This setting increase performance on complex Flexible Content fields.
* Field: Flexible Content - Added "Layouts: Settings" setting which let you choose a field group to clone and to be used as a configuration modal for each layout. Settings can then be used using `while(have_settings()): the_setting(); get_sub_field('my_setting');` in the Layout Template.
* Field: Flexible Content - Initial "Dynamic Preview" are now processed during page administration load, and not Ajax anymore. This tweak also speed-up the loading speed.
* Field: Flexible Content - Fixed a potential duplicated categories bug in the Selection Modal if the category name had spaces.
* Field: Flexible Content - "Remove Actions" now correctly remove Clone & Copy/Paste buttons.
* Field: Flexible Content - Added "Disable Legacy Layout Title Ajax" setting. It disables the native ACF Layout Title Ajax call on `acf/fields/flexible_content/layout_title`.
* Field: Flexible Content - Fixed the `$is_preview` variable not being available in specific situations.
* Field: Flexible Content - Fixed Dynamic Preview repeater fields inside a layout which could send an additional `acfcloneindex` during the preview mode
* Field: Flexible Content - Fixed Dynamic Preview with WP Query in the layout, which could be duplicated due to how WP Admin manage custom queries
* Field: Flexible Content - Fixed "Edit" icon vertical align with the latest WP 5.3 update
* Field: Flexible Content - Added shorter CSS class for preview wrapper `-preview`
* Field: Flexible Content - Fixed the native ACF setting "Select layout" with a wrong height on WP 5.3
* Field: Flexible Content - Fixed Enter key opening duplicated modal with Modal Edition setting
* Field: Flexible Content - Added `filter('acfe/flexible/layouts/div/name=my_flexible', $div, $layout, $field)` to change layout div attributes (with 5 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/layouts/handle/name=my_flexible', $handle, $layout, $field)` to change layout handle attributes (with 5 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/layouts/icons/name=my_flexible', $icons, $layout, $field)` to change layout handle icons (with 5 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/secondary_actions/name=my_flexible', $secondary_actions, $field)` to change Flexible Content secondary actions (copy, paste...) (with 3 variations)
* Field: Advanced Link - Fixed required validation which could fail if a post object was selected.
* Field: Advanced Link - Fixed a `z-index` problem in menu/items
* Field: Code Editor - Added compatibility with WP Code Editor Settings (editor themes).
* Field: Group/Clone - In Seamless Style mode instructions could be truncated in some specific cases
* Field: Group/Clone - Seamless Style mode wasn't working correctly in the Term administration
* Field: Group/Clone - Fixed "Seamless Style" typo
* Field: Group/Clone - Fixed "Edit in modal" which wasn't correctly working in menus
* Field Group: Fixed Category sync which failed to create & set new field group category if not already available in WP
* Field Groups: Fixed empty field groups list colspan
* Fields: Post Statuses/Post Types/Taxonomies/Taxonomies Terms/User Roles can now be used as conditional display field
* General: ACF Extended now correctly detects ACF Pro when included in the WP Theme.
* General: ACF Extended can now be included in WP Themes (following the same logic as ACF)

= 0.8.3.1 =
* Field: Flexible Content - Fixed PHP `Undefined index: acfe_flexible_modal` notice
* Fields: Select2 CSS Enhanced - Fixed forced height when in multiple mode
* Fields: Select2 CSS Enhanced - Global standardization of generic select input & select2 style
* Module: Dynamic Forms - Added missing hook `filter('acfe/form/load/action=my-action-alias',  $args, $post_id);`
* General: Fixed typo in Readme

= 0.8.3 =
* Field: Advanced Link - Added "Allowed Post Types" & "Allowed Taxonomies" setting to filter allowed Post types & Taxonomy terms in the post selection
* Field: Flexible Content - Categories in the Layouts Selection Modal are now sticky, the vertical scrollbar is now applied to layouts (Thanks @Damien C.)
* Field: Flexible Content - Added filter to disable a potentially unnecessary ACF ajax call when closing a layout: `filter('acfe/flexible/remove_ajax_title/name=my_flexible', false, $field);`
* Field: Flexible Content - Fixed Layout Title Edition input which could disappear in some rare cases
* Field: Flexible Content - Fixed `z-index` CSS conflict in modals when the flexible content was inside an accordion field (Thanks @Damian P.)
* Field: Flexible Content - Fixed `border-bottom` CSS on layout handle when edition modal is set to ON
* Field: Flexible Content - Fixed an issue where Categories in the Layouts Modal would still appear when setting was set to ON then to OFF
* Field: Post Object - Added "Allow custom value" setting when "Advanced UI" is ON
* Field: Post Object - Added "Save custom value as post" setting when "Allow custom value" is ON
* Field: reCaptcha - Changed `file_get_contents()` to `curl` method for better compatibility (Thanks @Brandon A.)
* Field: Select - Placeholder setting is now also available if "Advanced UI" is set to ON
* Fields: Select2 - CSS enhancements have been moved to the WP admin and are not enqueued in the front-end anymore (Thanks @jaakkosaarenketo)
* Field: Taxonomy Terms - Added "Load Terms" & "Save Terms" allowing the user the load & add terms to the current post, just like ACF does with the "Taxonomy" field (Feature request: @gptrading)
* Field Settings: Bidirectional - Fixed multiple sub fields check which bail too early during the field relation selection process (Thanks @doublesharp)
* Fields Groups: Fixed Json/PHP Sync warnings that were not properly checking the ACF setting `load_json` paths (Thanks @doublesharp)
* Fields Groups: Added Export Json & PHP in the Field Group single view (sidebar)
* Fields Groups: Categories are now synced with field groups during the export/import process
* Module: Author - Fixed duplicated post revision when updating an ACF value (Thanks: @François B.)
* Module: Author - Fixed an issue where the module would not show up on post types which are registered using a priority higher or equal to 5 (Thanks @yangkennyk)
* Module: Dev Mode - Added fields counter in the metabox title (Feature request: @Damien C.)
* Module: Dynamic Forms - Added the ability to use `{field:field_name}` & `{field:field_key}` values in the "Updated message" setting (Feature request: @alexene22)
* Module: Dynamic Forms - Better handling of select/checkbox/radio values render (Thanks @jabbadu)
* Module: Dynamic Forms - Fixed the ACF form submit button which would be still displayed, even if the setting was set to OFF (Thanks @Damien C.)
* Module: Dynamic Forms - Added "Post field groups" setting in the "Advanced" tab to override displayed field groups by a specific post field groups
* Module: Dynamic Forms - Added fallback when rendering complex fields values (array)
* Module: Dynamic Forms - Added `filter('acfe/form/format_value/name=my_field', $value, $unformatted_value, $post_id, $field)` to format field output used in email/post/term/user actions. (Also works with `/type` & `/key` selectors)
* Module: Dynamic Forms - Added Javascript ACF Action on successful form submission: `acf.doAction('acfe/form/submit/success/name=my-form')`
* Module: Dynamic Forms - Added compatibility fix for the plugin "MC ACF Flexible Template" (Thanks @MarcinKilarski)
* Module: Dynamic Forms - Added Manual Json Export & Import tools
* Module: Dynamic Forms / Post Types / Taxonomies / Block Types / Options - Added Manual Export in the single view
* Module: Dynamic Forms / Post Types / Taxonomies / Block Types / Options - Added compatibility fix for PolyLang, allowing user to translate modules items (Thanks @Drashka)
* Module: Dynamic Post Types / Taxonomies / Block Types / Options - Added Manual PHP Export action (Feature request: @jaakkosaarenketo)
* General: Added CSS styles to match WP 5.3 Update UI

= 0.8.2 =
* Dynamic Forms: Fixed error position 'below' not working on some specific fields (Select)
* Dynamic Forms: `acfe_form_is_front()` & `acfe_form_is_admin()` helpers now also check native ACF Form screen
* Dynamic Forms: Added action alias name setting for each action allowing better targeting when using hooks
* Dynamic Forms: Reworked forms actions hooks and added 'Advanced' tab for each action with code examples
* Field: Groups/Clones - CSS integration tweaks are now optional (Thanks @Brandon A.)
* Field: Groups/Clones - Added "Seamless style" setting which enable better CSS integration (remove borders and padding)
* Field: Code Editor - Added Field (use the WP Core CodeMirror script)
* Field: Taxonomy Terms - Added advanced settings allowing specific taxonomies or specific terms in the field (with level or parent/child dependencies)
* Field: reCaptcha - Fixed a bug where reCaptcha would not work properly
* Field: Flexible Content - Updated JS filter `acf.doAction('acfe/fields/flexible_content/preview', response, flexible.$el, $layout, ajaxData)` & moved it after preview HTML parse
* Field: Flexible Content - Updated JS filters & added variations `acf.doAction('acfe/fields/flexible_content/preview/name=my_flexible&layout=my_layout', response, flexible.$el, $layout, ajaxData)`
* Field: Flexible Content - Fixed "Remove Collapse" setting not working on newly added layout (Moved RemoveCollapse from acf-extended-fc-control.js to acf-extended-fc.js (acfe/flexible/layouts))
* Fields: Fields are now registered using `acf_register_field_type()`. This will allow developers to use `acf_get_field_type('acfe_field')`
* Fields settings: Bidirectional - Added filter which allow to force related field to be updated when migrating from already existing values
* Fields settings: Bidirectional - Added `filter('acfe/bidirectional/force_update/name=my_field', true, $field, $post_id)` (available with /type & /key) (Feature request: @anjanphukan)
* Field Groups: Fixed CSS which wrongly hide instructions when there's no field label (Thanks @Damien C.)
* Field Groups: Third Party - Added PHP filter to change source column output `filter('acfe/field_groups_third_party/source', $source, $post_id, $field_group)`
* General: Added `filter('acfe/field_wrapper_attributes/type=$field_type', $wrapper, $field)` (also with /name & /key variations)
* General: Splitted CSS for front-end/back-end
* General: Fixed ACF Setting Tab "Module: Taxonomies Enhancements" title (thanks @doublesharp)

= 0.8.1 =
* Field: Advanced Link - Added instructions to add custom fields in the field administration
* Field: Advanced Link - Added filters `acfe/fields/advanced_link/fields/name=my_field` & `acfe/fields/advanced_link/fields/key=field_xxxxxxx`
* Field: Advanced Link - Changed values keys to: `type`, `url`, `post`, `title` & `target`
* Field: Button - Updated JS hook `acf.doAction('acfe/fields/button/before_ajax', $el, data)`
* Field: Button - Updated JS hook `acf.doAction('acfe/fields/button/ajax_success', response, $el, data)`
* Field: Column - Fixed endpoint column not correctly closing the row
* Field: Flexible Content - Added JS hook `acf.doAction('acfe/fields/flexible_content/before_preview', $el, data)`
* Field: Flexible Content - Added JS hook `acf.doAction('acfe/fields/flexible_content/preview', response, $el, data)`
* Field: Flexible Content - Clone/Copy/Paste - Fixed a problem where new select option values weren't properly duplicated (thanks @chrisschrijver)
* Module: Dynamic Forms - Advanced settings: Fixed `prepare_field` on form front
* Module: Dynamic Forms - Fixed a compatibility problem if a dynamic form was named `form`
* Module: Dynamic Forms - Rename `field class` to `input class`
* Module: Dynamic Forms - Fixed a problem where native ACF Form would not properly work (thanks @maximelessard)
* Module: Dynamic Forms - Added Javascript to avoid re-submission on page refresh when 'Hide form' is set to ON

= 0.8 =
* Module: Dynamic Forms - Added module. Forms UI available under ACF menu
* Module: Dynamic Forms - Added PHP helper `acfe_form('my_form_name')` & `acfe_form(188)` to display a form
* Module: Dynamic Forms - Added shortcode `[acfe_form name="my_form_name"]` & `[acfe_form ID="188"]` to display a form
* Module: Dynamic Forms - Added setting to disable Dynamic Forms: `acf_update_setting('acfe/modules/dynamic_forms', false)`
* Module: Dynamic Forms - Added `action('acfe/form/validation', $form, $post_id)` & `action('acfe/form/validation/name=form_name', $form, $post_id)` to validate form before submission. `get_field()`, `have_rows()`, `get_sub_field()` functions can be used.
* Module: Dynamic Forms - Added `acfe_add_validation_error('field_name_or_key', 'message')` helper to add error on specific field during the validation
* Module: Dynamic Forms - Added `action('acfe/form/submit', $form, $post_id)` & `action('acfe/form/submit/name=form_name', $form, $post_id)` to add custom action on submission
* Module: Dynamic Forms - Added 8 new hooks per form action allowing developers to customize each action
* Field: Added Dynamic Form Select field
* Field: Added Google reCaptcha field (compatible v2 & v3)
* Field: Google reCaptcha - Added global setting: `acfe/field/recaptcha/site_key` (API site key)
* Field: Google reCaptcha - Added global setting: `acfe/field/recaptcha/secret_key` (API secret key)
* Field: Google reCaptcha - Added global setting: `acfe/field/recaptcha/version` (v2 or v3)
* Field: Google reCaptcha - Added global setting: `acfe/field/recaptcha/v2/theme` (light or dark)
* Field: Google reCaptcha - Added global setting: `acfe/field/recaptcha/v2/size` (normal or compact)
* Field: Google reCaptcha - Added global setting: `acfe/field/recaptcha/v3/hide_logo` (true or false)
* Field: File - Removed the native 'No file selected' text
* Field: File & Image - Added 'Uploader type' setting to choose which uploader to use (Basic or Media)
* Field: Post Type Selection - Code has been reworked. The field is now compatible with all select, checkbox & radio settings
* Field: Taxonomy Selection - Code has been reworked. The field is now compatible with all select, checkbox & radio settings
* Field: Button - Code has been reworked & added Ajax call setting
* Field: Button - Added Ajax action: 'acfe/fields/button', POST parameters: $post_id, $field_key, $field_name
* Field: Button - Added Javascript Ajax action: `('acfe/fields/button/before_ajax', this.$el)`
* Field: Button - Added Javascript Ajax action: `('acfe/fields/button/ajax_success', response, this.$el)`
* Field: Slug - Code has been reworked
* Field: Dynamic Message - Added instructions & code example in the field setting view
* Field: Column - Added Field
* Field: Post Status Selection - Added Field
* Field: User Roles Selection - Added Field
* Field: Hidden Input - Added Field
* Field: Advanced Link - Added Field. Added `filter('acfe/fields/advanced_link/fields', $fields, $field, $link)` allowing developers to add custom field to the modal
* Field: Taxonomy Terms - Added Field
* Field: Group - Added Modal Edition setting allowing users to edit group values in a modal
* Field: Group - Added CSS fixes for better integration
* Field: Clone - Added Modal Edition setting allowing users to edit clone values in a modal (Only in group mode)
* Field: Clone - Added CSS fixes for better integration
* Field: Textarea - Added Code mode setting to switch font family to monospace and allow tab indent
* Field: Select - Added placeholder setting when allow null is activated
* Field: Flexible Content - Added 'Remove Collapse Action' setting
* Field: Flexible Content - Added `filter('acfe/flexible/lock', true, $field)` to lock flexible content layouts (disable sortable)
* Field: Flexible Content - Added `filter('acfe/flexible/lock/name=my_flexible', true, $field)` to lock flexible content layouts (disable sortable)
* Field: Flexible Content - Added `filter('acfe/flexible/remove_actions', true, $field)` to remove the "Add layout" button
* Field: Flexible Content - Added `filter('acfe/flexible/remove_actions/name=my_flexible', true, $field)` to remove the "Add layout" button
* Field: Flexible Content - Clone & Copy/Paste functions are now compatible with min/max settings for each layout
* Field: Flexible Content - Added `action('acfe/flexible/render/before_template', $field, $layout, $is_preview)` to add wrapper around the template render (with 6 variations)
* Field: Flexible Content - Added `action('acfe/flexible/render/after_template', $field, $layout, $is_preview)` to add wrapper around the template render (with 6 variations)
* Field: Flexible Content - Fixed `text-align:center` applied to placeholder css
* Field: Flexible Content - Fixed Style/Script render filters when returning a full URL
* Field: Flexible Content - Fixed a bug where the layout Title Edition input could disappear when clicking on the handle
* Field: Flexible Content - Fixed a bug where the close button would not appear after adding a new layout
* Field: Repeater - Added Stylised button setting
* Field: Repeater - Added CSS fixes when table is empty
* Field: Repeater - Added `filter('acfe/repeater/lock', true, $field)` to lock repeater rows (disable sortable)
* Field: Repeater - Added `filter('acfe/repeater/lock/name=my_repeater', true, $field)` to repeater rows (disable sortable)
* Field: Repeater - Added `filter('acfe/repeater/remove_actions', true, $field)` to remove the "Add row" button
* Field: Repeater - Added `filter('acfe/repeater/remove_actions/name=my_repeater', true, $field)` to remove the "Add row" button
* Field: Fixed fields label CSS when label is empty (top & left placement)
* Fields: "Advanced Validation" settings are now disabled by default. To display them, the "Advanced settings" must be turned ON in the field group
* Fields: Added "Advanced Settings" allowing administrator to set custom field properties based on the current screen (administration/front-end). "Form settings" must be turned ON in the field group
* Fields: Added Hide Label setting (in the Advanced Settings)
* Fields: Added Hide Field setting (in the Advanced Settings)
* Fields: Added `filter('acfe/load_field', $field)` to filter field settings everywhere but not in field group & tools management
* Fields: Added `filter('acfe/load_field_front', $field)` to filter field settings in the front-end (and ajax coming from front-end) but not in field group & tools management
* Fields: Added `filter('acfe/load_field_admin', $field)` to filter field settings in the administration (and ajax coming from administration) but not in field group & tools management
* Field Groups: Instructions Placement - Added "Tooltip" placement for instructions 
* Field Groups: Third Party - Added PHP / Json Export & Sync actions
* Field Groups: Third Party - Added Source column
* Field Groups: Fixed potential undefined index in location column
* Location: Old Location "Post Type Archive" & "Taxonomy Archive" (in the admin list) have been renamed "Post Type List" & "Taxonomy List"
* Location: Post Type List/Taxonomy List - Fixed Image & File Upload fields being forced on basic mode (Thanks @dominikkucharski)
* Location: New Location "Post Type Archive" creates an option page under post types menu when argument `acfe_admin_archive` is set to true (also available in Dynamic Post Type)
* Module: Settings - Added ACF Extended tab to list the plugin's current settings
* Module: Settings - Fixed `save_json` setting being incorrectly displayed in the ACF Setting tab
* Module: PHP Sync - Fixed a problem where field group would not be recognized as loaded in PHP if Json AutoSync is also activated
* Module: PHP Sync - Renamed PHP Sync settings to `acfe/php`, `acfe/php_save`, `acfe/php_load`, `acfe/php_found`
* Module: Dev Mode - Added mode which replace the WP Post Meta box with more details data (in posts & terms). It also enable `SCRIPT_DEBUG`. Can be activated using `acf_update_setting('acfe/dev', true)` or `define('ACFE_dev', true)`
* Module: Dynamic Taxonomy - Added missing `meta_box_cb` setting (thanks @DavidGMiles)
* Module: Options - Fixed potential validation problem
* General: Improved ACF Extended modal CSS style & Added Modal inside modal overlay

= 0.7.9.9.9 =
* Field: Flexible Content - Fixed Copy/Paste function doing incorrect checks on radio, checkboxes and select inputs
* Field Group: Fixed field 'Data' button being displayed on newly created fields

= 0.7.9.9.8 =
* Field: Flexible Content - Fixed Clone & Copy/Paste functions in multi level flexible content (flexible inside flexible inside flexible...) (Thanks @AsmussenBrandon)
* Field: Flexible Content - Fixed CSS border glitch

= 0.7.9.9.6 =
* Field: Flexible Content - Fixed Clone & Copy/Paste functions for accordions fields (Thanks @Damian P.)
* Field: Flexible Content - Fixed Clone & Copy/Paste functions for FontAwesome fields (Thanks @Damian P.)
* Field: Flexible Content - Close Button setting is now always available and is not conditional anymore
* Field: Flexible Content - Render Template/Style/Script path now supports parent/child theme. If a file is found in the child theme, it will be included. Otherwise it will be checked against the parent theme path (Feature Request: @r3dridl3)
* Field: Flexible Content - Fixed Layout Title Edition not working in some rare cases (Thanks @Damian P.)
* Field: Post Types & Taxonomies Select - Fixed two PHP noticed
* General: Added ACF Extended GitHub repository URL in the readme

= 0.7.9.9 =
* Field: Flexible Content - Settings are now dynamic (and not global anymore) (Thanks @Val) 
* Field: Flexible Content - Added CSS class on cloned layouts
* Field: Flexible Content - Removed `esc_attr()` from Layout Title Edition, allowing icons to be displayed correctly
* Field: Flexible Content - Fixed potential duplicated clone buttons in specific cases (Thanks @chrisschrijver)
* Field: Flexible Content - Added "Layout Placeholder" setting, disabled by default (feature request: @Matt H.)
* Field: Flexible Content - Added "Layout Title Edition" setting, disabled by default
* Field: Flexible Content - Fixed Enter key closing modal in textarea inputs (thanks @dominikkucharski)
* Field: Flexible Content - Fixed Clone & Copy/Paste functions on select2 fields (Thanks @AsmussenBrandon)
* Field: Flexible Content - Multiple Layouts Categories are now allowed in the Selection Modal, using pipes "|". ie: Main|Shopping|Interactive (Feature request: @Damian P.)
* Field: Flexible Content - Fixed a problem where "Min/Max Layouts" limitation (setting per layout) weren't working properly when using the Layout Selection Modal (Thanks: @Matt H.)
* Module: Taxonomy - Added Polylang compatibility when translating a term (Thanks @jaakkosaarenketo)
* Module: Taxonomy - Fixed spacing when a meta field has no label
* Field: Bidirectional - Values are now saved as string when Post Object & User "Allow multiple values" setting is disabled (Thanks @screamingdev)
* Fields Groups: Added `word-break` on field description
* Fields Groups: Fixed PHP Notice when group location is an attachment (Thanks @herrschuessler)
* General: Added multiples settings in order to disable specific plugin's modules. See FAQ (Feature request: @Matt H.)
* General: Added `ACFE_VERSION` constant to force cache flush on plugin update
* General: PHP Strict Type checks globally (Thanks @Liam S.)
* General: Added Flexible Content Dynamic Preview Video in readme

= 0.7.9.4 =
* Module: Author Box - Hotfix

= 0.7.9.3 =
* Field: Flexible Content - Added `filter('acfe/flexible/thumbnail/name={flexible:name}', $thumbnail, $field, $layout)` to change all layouts thumbnails (must return `attachment ID` or `URL`) (Thanks @Dam)
* Field: Flexible Content - Fixed `$is_preview` not being available during the Dynamic Layout Preview (thanks @Dam)
* Module: Author Box - Added custom authors roles being able to be selected in the Author Box (Thanks @Andremacola)
* General: Fixed Readme typos

= 0.7.9 =
* Field: Flexible Content - Added Inline Layout Title Edition
* Field: Flexible Content - Added Auto scroll + Modal edit on One Click layout
* Field: Flexible Content - Removed native "Controls Icons" visibility being visible on all sub flexible content fields (better readability)
* Field: Flexible Content - Added WP Unslash on preview values to prevent backlashes on values (thanks @Dam)
* Field: Flexible Content - Added compatibility for layouts that have been synced and not manually created (thanks @T. Dubois)
* Field: Flexible Content - Copy/Paste functionality is now a Flexible Content setting (Default: Disabled) (Feature request: @louiswalch)
* Field: Flexible Content - 'Close Button' (collapse) on layouts is now a Flexible Content setting(Default: Disabled)
* Field: Flexible Content - Layouts Thumbnails aspect ratio are now locked (base ratio: 450px * 200px) (Feature request: @louiswalch)
* Field: Flexible Content - Dynamic Layout Preview refresh has been optimized. The preview content is now kept instead of being reset
* Field: Flexible Content - Dynamic Layout Preview style & script enqueue now use wp_enqueue_style() & wp_enqueue_script()
* Field: Flexible Content - Modal Edition - 'Enter' & 'ESC' keys now close Modals (instead of submitting the form)
* Field: Flexible Content - Added `action('acfe/flexible/enqueue', $field, $is_preview)` to enqueue new style/script (back & front) (with 6 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/layout/thumbnail/layout={layout:name}', $thumbnail, $field, $layout)` to change layout thumbnail (must return `attachment ID` or `URL`) (with 3 variations)
* Field: Flexible Content - Added `action('acfe/flexible/preview', $field, $layout)` to change Dynamic Layout Preview content (with 6 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/render/template', $template, $field, $layout, $is_preview)` to change Layout Render: Template Path (with 6 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/render/style', $style, $field, $layout, $is_preview)` to change Layout Render: Style Path (with 6 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/render/script', $script, $field, $layout, $is_preview)` to change Layout Render: Script Path (with 6 variations)
* Field: Flexible Content - Added `filter('acfe/flexible/placeholder/icon', $class, $field)` to change the Placeholder Button Dashicons class (default: 'dashicons dashicons-edit') (with 3 variations)
* Module: Dynamic Options Page - Fixed 'Undefined $post_id' PHP warning in Dynamic Options Page screen
* Module: Dynamic Options Page - Fixed registration order for child options pages (thanks @Val)
* Module: Dynamic Post Type - Fixed undefined ID php Warning on edit screen when Dynamic Post Type is registered locally (thanks @Val)
* Module: Dynamic Taxonomies - Taxonomy name character limit has been fixed to 32 instead of 20 (thanks @Damian)
* Module: Dynamic Taxonomies - 'Add New' button is now based on Taxonomy capabilities & Taxonomy Label (thanks @absolute_web)
* Module: Author - Field groups 'Hide on screen' is now taken in account (thanks @louiswalch)
* Tools: Dynamic Taxonomies Import - Fixed 'undefined index' PHP warning on taxonomy import (thanks @Val)

= 0.7.8 =
* Field: Flexible Content - Removed 'Layouts Thumbnail as Preview' setting. You should now use 'Layouts: Dynamic Preview'
* Field: Flexible Content - Added 'Layouts: Dynamic Preview' ('Layouts: Render' setting must be turned ON)
* Field: Flexible Content - Reworked layouts settings order (better readability)
* Field: Flexible Content - Modal Edition title now removes eventual extra HTML tags (thanks @Thomas D.)
* Field: Flexible Content - Modal Edition CSS has been fixed on Gutenberg Editor view (thanks @Val)
* Field: Flexible Content - Fixed 'Empty Message' placeholder setting using wrong `__()` function (thanks @illiminal)
* Field: Flexible Content - Removed query vars from `get_flexible()`. Global variables `$layout` & `$field` can be used in the template to retrieve current settings
* Field: Flexible Content - Added global variable `$is_preview` which is true when the template file is called as a layout preview 
* Field: Flexible Content - `get_flexible()` now uses `wp_enqueue_style()` & `wp_enqueue_script()` when rendering on front-end
* Field: Image - 'No image selected' text has been removed
* Module: Dynamic Post Types/Taxonomies - Fixed 'index key not found' PHP warning (thanks @Val)
* Module: Dynamic Post Types/Taxonomies/Options & Block Types - Added `edit_posts` capabilities matching the ACF capability setting
* Tools: Dynamic Post Type Import - Fixed 'capabilities key not found' PHP warning during import process (thanks @Val)
* General: Improved Metaboxes CSS on Gutenberg Editor views
* General: Reworked JS enqueue. Flexible Content JS is now excluded from ACF Field Groups views

= 0.7.5.5 =
* Field: Flexible Content - Completely revamped Flexible Content JavaScript for a more solid & optimized code
* Field: Flexible Content - Automatically scroll to the layout position when adding a new layout
* Field: Flexible Content - Automatically open layout edition modal when adding a new layout
* Field: Flexible Content - Added 'Close' (collapse) button at the bottom of layout when opened
* Field: Flexible Content - Fixed typo error in the 'Paste Layouts' prompt
* Field: Flexible Content - Added Flexbox CSS compatibility
* Field: Flexible Content - Better Multi Modal Handling (modal inside a modal inside a modal...)
* Field: Flexible Content - Better Field Validation Handling inside layouts
* Field: Flexible Content - Added `has_flexible($field_name, $post_id)` front-end function to check if rows exists
* Field: Flexible Content Control - Automatically scroll to the new layout position when using 'Clone Layout'
* Field: Flexible Content Control - Fixed 'Clone Layout' when an already cloned layout had an 'Editor' field
* Field: Flexible Content Control - Fixed 'Clone Layout' unwanted icon when a layout had an 'Accordion' field
* Field: Advanced Validation/Update - The settings are now hidden on non-necessary fields (Clone, Flexible content, Tabs etc...)
* Module: Dynamic Options Pages - Now forces a unique slug to avoid duplication
* Module: Dynamic Post Types/Taxonomies/Options Pages & Block Types - Manual Json export has been removed from possible actions on the trashed status screen
* Module: Options - Fixed a CSS enqueue problem introduced in last patch
* Location: Post Type Archive & Taxonomy Archive options now use ACF multi-languages settings
* General: Removed jQuery UI & jQuery UI Dialog dependency (ACF Extended now uses its own lightweight modal system)

= 0.7.5 =
* Field: Flexible Content - Added 'Control': Copy, Paste & Duplicate Layouts on the fly using icons in the layouts handle
* Field: Flexible Content - Control: Copy & Paste all layouts on the fly using the new icon next to 'Add row' button (can be used to transfer layout data from one page to an another)
* Field: Flexible Content - Added 'Modal: Edition' setting, allowing to edit layouts in a modal
* Field: Flexible Content - Added 'Layouts Previews' setting, allowing to display the layout thumbnail as preview (collapsed state)
* Field: Flexible Content - Added `filter('acfe/flexible/previews/name=$field_name', $thumbnails, $field)` allowing to override the preview image for each layout (usage example is available in the FAQ)
* Field: Flexible Content - Added `filter('acfe/flexible/previews/key=$field_key', $thumbnails, $field)` allowing to override the preview image for each layout (usage example is available in the FAQ)
* Field: Flexible Content - When using `get_flexible()`, `get_query_var('acf_flexible_field')` & `get_query_var('acf_flexible_layout')` can be used in the template file to retrieve current field & layout informations
* Field: Flexible Content - When using `get_flexible()`, an HTML comment has been added for each rendered templates
* Field: Flexible Content - Fixed the possibility to render the same layout multiple times when using `get_flexible()` (thanks to @Val_Pellegrin)
* Field: Flexible Content - `get_flexible()` now enqueue each style.css & script.js only one time on the whole page
* Field: Flexible Content - Added more width spacing for the 'Modal: Category' checkbox (compatibility for small screens)
* Tools: Added Export & Import Tools for Dynamic Post Types, Taxonomies, Block Types & Options Pages using Json files
* Location: Post Type Archive & Taxonomy Archive now use field group location (High, Normal or Side) & field group style (WP Box or seamless) (Feature Request)
* Module: Taxonomy - Added some spacing on the term edition screen (compatibility with YOAST/Rank Math metaboxes)
* Module: Taxonomy - Fixed Edit Screen CSS for Repeaters & Groups (thanks to @Val_Pellegrin)
* Module: Dynamic Taxonomies - Fixed 'Post Type' column when a post type does not exists anymore (thanks to @Val_Pellegrin)
* Module: Dynamic Taxonomies - Fixed Single Posts per page, Orderby & Order
* Module: Dynamic Post Types - Fixed 'Taxonomies' column when a taxonomy does not exists anymore (thanks to @Val_Pellegrin)
* Module: Dynamic Post Types & Taxonomies - Fixed Admin Orderby, Order & Menu position which weren't working properly (thanks to @Val_Pellegrin)
* Module: Dynamic Post Types & Taxonomies - Fixed user Posts per page, Orderby & Order option screen which were forced (thanks to @Val_Pellegrin)
* Field Groups: Hide 'Category' column if there's no term
* Misc: Added 'Advanced Custom Fields' tab in the WP 'Add plugin' page

= 0.7.0.3 =
* Field: Flexible Content - 'Modal: Title' - The custom modal title now works correctly (thanks to Damian P.)
* Field: Flexible Content - 'Layouts State' - Fixed a problem where layouts title were incorrect when forcing layouts state (thanks to Damian P.)
* Compatibility: ACF Pro 5.7.13 - Fixed Archive Location 'All' PHP error (acf/location/rule_match filter)

= 0.7 =
* Field: Flexible Content - Added 'Stylised Button' setting which automatically hide native ACF 'empty' message and add style to 'Add row' button
* Field: Flexible Content - Added 'Hide Empty Message' setting to hide native ACF 'empty' message
* Field: Flexible Content - Added 'Empty Message' text setting to change the native ACF 'click the Add Row button below...' message
* Field: Flexible Content - Added 'Layouts Thumbnails' setting to add image thumbnails for each layout in the admin layout selection
* Field: Flexible Content - Added 'Layouts Render' setting to add template, style & script file for each layout. Those settings can be then accessed on the front-end
* Field: Flexible Content - Added `get_flexible($selector, $post_id)` and `the_flexible($selector, $post_id)` functions to automatically use the 'Layouts Render' settings in front-end
* Field: Flexible Content - Added 'Modal' setting to change the layout selection into a proper modal in the administration
* Field: Flexible Content - Added 'Modal: Title' setting to change the layout modal title
* Field: Flexible Content - Added 'Modal: Columns' setting to change the layout modal columns grid. 1, 2, 3, 4, 5 or 6 columns available
* Field: Flexible Content - Added 'Modal: Categories' setting to add a category for each layout in the layout modal
* Field: Flexible Content - Added 'Layouts State' setting to force layouts to be collapsed or opened by default
* Field: Flexible Content - Added 'Button Label' native compatibility fix to make it work with Dashicons (CSS to fix vertical alignment)
* Field: Flexible Content - Added 'One click' hidden function. In the post administration, the 'Add row' button will add a layout without the selection modal if there is only one layout available in the flexible content
* Field: Flexible Content - Note - The following settings: Layouts Thumbnails, Layouts Render & Modal Categories will be visible after saving field group
* Module: Ajax Author - Fixed a bug where field groups 'Hide on screen' setting wasn't applied on post administration
* Module: Json AutoSync - Added "'/acf-json' folder not found" warning message if Json Sync is set in a field group and the '/acf-json' folder doesn't exists
* Module: Taxonomy - Forced Tabs to be 'Aligned Top' in taxonomies fields (JS Only - ACF Bug) & added better CSS style (thanks to @Val_Pellegrin)
* Module: Dynamic Post Type/Taxonomy/Option Page/Block Type - Hidden 'Minor publishing' panel (Save as draft, visibility...) to avoid confusion (thanks to @Val_Pellegrin)
* Field: Bidirectional - Removed the 'bail early if old values == new values' check. This will let users convert existing fields with saved values into bidirectional without hassle (thanks to @Val_Pellegrin)
* Field: Repeater - Added CSS spacing for block repeaters (better readability)
* Field Group: Location 'Taxonomy All' - Fix native ACF location 'Taxonomy == All' matching all ACF Extended 'Taxonomies Archives' locations
* Compatibility: Added compatibility fix for Rank Math SEO & YOAST Plugin to avoid the plugin's post metabox being above ACF metaboxes

= 0.6.7.2 =
* Field Group: Latest Post Type 'All' location fix was too sensitive. The location now works as expected
* Module: Dynamic Post Types, Taxonomies & Block Types modules now set the 'slug' as disabled once it's saved (to avoid duplication). A more flexible solution will be introduced later (WIP)

= 0.6.7 =
* Module: Added Block Types Module. You can now add, edit and delete Block Types in the ACF > Block Types UI
* Module: Added Options Pages Module. You can now add, edit and delete Options Pages in the ACF > Options UI
* Field Group: Fixed Post Type 'All' location that could render field groups on internal/excluded post types

= 0.6.5 =
* Field: Added 'Featured Thumbnail' setting on image fields. When selected, the field will update the post featured thumbnail
* Field: Fixed bidirectional ON/OFF switch 'width:auto' causing warning with ACF Pro 5.8
* Module: Options - Added support of Json value (introduced by WordPress 5.2 Health Check transients)
* Module: Dynamic Post Type & Taxonomy - Removed 'sanitize_title()' pass on archive & single rewrite settings. Allowing rewrite slugs to be saved as: 'prefix1/prefix2'
* General: Added Gutenberg CSS on post metaboxes. More contrast for better metaboxes integration & visibility

= 0.6.3 =
* Module: Dynamic Post Type & Taxonomy now deregister post types /taxonomies that have been deleted (or trashed) via the Tools > Post Types / Taxonomies
* Module: Dynamic Post Type & Taxonomy now register post types / taxonomies in ASC order
* Module: Dynamic Post Type - Fixed a bug where hierarchical post types had a query error in the admin archive
* General: Improved the ACF Pro dependency style in plugins list when ACF Pro isn't activated
* Plugin: Readme - Reworked structure
* Plugin: Readme - Added Supporters section
* Plugin: Readme - Trying to implement emojis ✌ 

= 0.6.1 =
* Admin: Re-introduced 'Options' admin screen under Settings > Options. Code has been completely refactored using native WP List Table. New features: Searchbox, item per page preference (default: 100), sortable columns, bulk delete and ability to edit serialized values.

= 0.6.0.2 =
* Field Group: Lowered 'Field Group Data' Metabox priority which was too high and was displayed above fields.

= 0.6.0.1 =
* General: Fixed backward compatibility for ACF Pro 5.7.10. The function: acf_add_filter_variations() was causing problems.
* Admin: Temporarily removed the 'Options Beta' admin screen. Still needs some works. (thanks to @DamChtlv)

= 0.6 =
* Field Group: New location available - Post type archive (under Post type). Field group will be displayed on post type list view, as a sidebar. Fields will be saved in the option: `{post_type}_options`. Frontend usage example: `get_field('my_field', 'page_options')`.
* Field Group: New location available - Taxonomy archive (under Taxonomy). Field group will be displayed on taxonomy list view, as a sidebar. Fields will be saved in the option: `tax_{taxonomy}_options`. Frontend usage example: `get_field('my_field', 'tax_category_options')`.
* Taxonomies: Taxonomies list & edit views have been tweaked for a more consistent administration experience, using CSS/JS only. Views are now similar to post type edition screens.
* Field Groups: Added a 'Third party' status (just like 'Sync available') in order to display local field groups thats are loaded by ACF, but not available in the ACF field group administration. Example: a field group is registered locally in the `functions.php` file.
* Dynamic Post Type: Added a configuration button next to the post type title, if the post type was generated by the Dynamic Post Type tool.
* Dynamic Taxonomy: Added a configuration button next to the taxonomy title, if the taxonomy was generated by the Dynamic Taxonomy tool.
* Field Groups: Better 'Load' column data source. Now display: DB, Json or PHP.
* Field Groups: Now forcing Json / PHP Sync if local files are loaded by ACF. In order to disable it, and if the setting is already enabled, you must manually delete the `group_xxxxxxxxx` file in your theme folder. This behavior is applied to avoid any data desynchonization.
* Field: Fixed a PHP notice in the Advanced Validation setting update.
* Field Groups: Taxonomy acf-field-group-category - Better exclusion from ACF taxonomy selection (location & fields)

= 0.5.8.1 =
* Plugin: Less aggressive ACF Pro check on activation. Now displaying a notice (allowing pre-activation of ACF Extended)
* Plugin: Readme text fix

= 0.5.8 =
* Field: Added Bidirectional setting for the following fields: relationship, post object, user & taxonomy terms
* Module: Added 'Ajax Author' field to replace the native WP Author Meta Box
* Module: Dynamic Post Type & Taxonomy - Better exclusion from ACF post types selection (location & fields)
* General: Fixed ACF Select2 CSS to fit ACF input styles (border-radius, border-color & line-height)
* General: Renamed ACF-Extended assets for better readability in the browser console resources tab
* Compatibility: Removed the Taxonomy Order submenu created under ACF for the taxonomy 'Field Group Category' by the plugin 'Category Order and Taxonomy Terms Order'

= 0.5.5.1 =
* Module: Dynamic Taxonomy - Fixed Terms PHP warning
* General: Plugin readme

= 0.5.5 =
* Module: Added Dynamic Post Type module
* Module: Added Dynamic Taxonomy module
* Admin: Added WP Options page
* Field: Added Post Type Selection field
* Field: Added Taxonomy Selection field
* Field: Added Slug field
* Field Groups: Fixed 'no field groups found' wrong colspan
* General: Reworked plugin folders and files hierarchy

= 0.5.2.3 =
* Field Groups: Fixed unused category column on Field Groups Sync page
* Fields: Fixed subfields 'ghost' acfcloneindex saved when duplicating flexible content (thanks to @AsmussenBrandon)

= 0.5.2.1 =
* Field Group: Fixed Left Label Placement overwriting existing field groups (thanks to @AsmussenBrandon)

= 0.5.2 =
* Fields: Added new dynamic message field
* Fields: Added new button field
* General: Added compatibility filters for 'Post Types Order' plugin
* Plugin: Updated assets
* Plugin: Reworked readme
* Plugin: Fixed typos

= 0.5.1 =
* Plugin: Added screenshots
* Field Group: Moved Auto Sync Warnings below Auto Sync instructions
* Field: Added filters variation to `acfe/validate` & `acfe/update`

= 0.5 =
* Initial release

== Upgrade Notice ==

None