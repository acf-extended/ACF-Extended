# Advanced Custom Fields: Extended

All-in-one enhancement suite to improve WordPress & Advanced Custom Fields. This plugin aims to provide a powerful administration toolset with a wide range of improvements & optimizations.

[ACF-Extended.com](acf-extended.com) (in development)

**Requires at least ACF Pro 5.7.10**

*If you don't already own [ACF Pro](advancedcustomfields.com/pro), you should consider it. It's one of the most powerful WordPress plugin, with a life-time licence for unlimited websites.*

## 🏷️ Features

### ACF: Field Groups Settings

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
Add custom metas (key/value) in the field group administration

* **View raw data**
Display raw field group data in a modal to check your configuration & settings

* **Custom key**
Set custom field group key. ie: `group_custom_name`

* **New field group location: All post types**
Display field group on all post types edition screen

* **New field group location: Post type Archive**
Display field group on post types archive screen. Fields are saved in the option: `{post_type}_options`

* **New field group location: Taxonomy Archive**
Display field group on taxonomies archive screen. Fields are saved in the option: `tax_{taxonomy}_options`

### ACF: Field Groups List

* **Column: Category**
Display and filter field groups categories

* **Column: Locations**
Quick view of field groups locations informations using icons & popover

* **Column: Load**
Quick view of field groups data load source (DB, PHP or Json)

* **Column: Sync PHP / Json**
Quick view of field groups synchronization status with warnings

* **Row action: Export PHP / Json**
One-click export for each field groups

* **Row action: Field group key**
Quick view of field groups keys

* **Status: Third party**
Display local field groups thats are loaded by ACF, but not available in the ACF field group administration. Example: a field group is registered locally in the `functions.php` file, but not in ACF

### ACF: Fields Settings 

* **Bidirectional fields**
An advanced bidirectional setting (also called post-to-post) is available for the following fields: Relationship, Post object, User & Taxonomy terms.
Fields will work bidirectionally and automatically update each others. Works in groups & clones (prefixed field names must be turned off).
[Usage example is available in the FAQ](#faq)

* **Advanced validation**
A more sophisticated validation conditions (AND/OR) with custom error messages in the post edition screen.

* **Advanced update filter**
Add specific PHP filters right before the value is saved in the database.

* **Permissions**
Add permission layer to fields. Choose which roles can view & edit fields in the post edition screen. (can be combinated with field groups permissions)

* **Image as Featured Thumbnail**
Choose if an image field should be considered as post featured thumbnail

* **View raw data**
Display raw field data in a modal to check your configuration & settings

### ACF: Fields

* **New Field: Dynamic message**
Display dynamic PHP content using `acf/render_field`

* **New Field: Button**
Display a submit button

* **New Field: Post type selection**
Select any post type (format: checkbox, radio or select)

* **New Field: Taxonomy selection**
Select any taxonomy (format: checkbox, radio or select)

* **New Field: Slug**
A slug text input (ie: `my-text-input`)

### ACF: Settings

* **Settings page**
Display all ACF settings in one page.

### WordPress: Dynamic Post Types

Create and manage post types from your WordPress administration (Tools > Post Types). All WordPress post types arguments can be set and managed. But also:

* Manage Posts per page, order by and order for the post type archive
* Manage Posts per page, order by and order for the post type administration screen
* Set custom single template (ie: `my-single.php`) instead of the native `single-{post_type}.php`
* Set custom archive template (ie: `my-archive.php`) instead of the native `archive-{post_type}.php`
* Manual Import & Export is available in the ACF > Tools page

### WordPress: Dynamic Taxonomies

Create and manage taxonomies from your WordPress administration (Tools > Taxonomies). All WordPress taxonomies arguments can be set and managed. But also:

* Manage Posts per page, order by and order for the taxonomy term archive
* Manage Posts per page, order by and order for the taxonomy administration screen
* Set custom taxonomy template (ie: `my-taxonomy.php`) instead of the native `taxonomy-{taxonomy}.php`
* Manual Import & Export is available in the ACF > Tools page

### WordPress: Ajax Author Box

The native WP Author Metabox has been replaced with a dynamic version allowing to manage thousands of users without slowing down the post administration.

### WordPress: Taxonomy List & Edit

Taxonomies list & edit views have been enhanced for a more consistent administration experience, using CSS/JS only. Views are now similar to post type edition screens.

### WordPress: Options

Manage WordPress options from Settings > Options.

* View, add, edit and delete options
* Working with strings, serialized & Json values

### ACF: Options Pages

Manage ACF Options Pages from ACF > Options.

* View, add, edit and delete options pages
* All arguments are available
* Manual Import & Export is available in the ACF > Tools page

### ACF: Block Types (Gutenberg)

Manage ACF Block Types from ACF > Block Types.

* View, add, edit and delete Block Types
* All arguments are available
* Manual Import & Export is available in the ACF > Tools page
* Requires ACF Pro 5.8

### ACF: Flexible Content Enhancement

* Controls: Inline Layout Title Edition
* Controls: Copy, Paste & Duplicate Layouts on the fly
* Controls: Copy & Paste all layouts on the fly
* Stylised Button: Add style to 'Add Row'
* Hide Empty Message: Hide the native Flexible Content 'Empty' message
* Empty Message: Change the native Flexible Content 'Click the Add Row button below...' message
* Layouts Thumbnails: Add thumbnails for each layout in the layout selection
* Layouts Render: Add `template.php`, `style.css` & `script.js` files settings for each layout. Those settings can be then accessed in the front-end ([More informations in the FAQ](#faq))
* Layouts Dynamic Preview: Edit & Preview Layouts on-the-fly from your WordPress administration, just like in Gutenberg (Layouts Render must be turned ON)
* Modal Edition: Edit layouts in a modal
* Modal Selection: Change the layout selection into a modal
* Modal Selection Title: Change the layout modal title
* Modal Selection Columns: Change the layout modal columns grid. 1, 2, 3, 4, 5 or 6 columns available
* Modal Selection Categories: Add category for each layout in the layout modal
* Layouts State: Force layouts to be collapsed or opened by default
* Button Label: Supports Dashicons icons elments `<span>`
* One Click: the 'Add row' button will add a layout without the selection modal if there is only one layout available in the flexible content

## 🛠️ Links

* Found a bug? [Submit an issue](acf-extended/ACF-Extended/issues/new)
* Want to fork me? [GitHub repository](acf-extended/ACF-Extended)
* Enjoying this plugin? [Submit a review](wordpress.org/support/plugin/acf-extended/reviews/#new-post)
* Want to keep me awake? [Buy me a coffee](ko-fi.com/acfextended)
* Want to check upcoming features? [Here is my Twitter](twitter.com/hwkfr)
