(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Advanced Link
     */
    var ACFE_Advanced_Link = acf.Field.extend({

        type: 'acfe_advanced_link',

        events: {
            'click a[data-name="add"]': 'onClickEdit',
            'click a[data-name="edit"]': 'onClickEdit',
            'click a[data-name="remove"]': 'onClickRemove',
        },

        $control: function() {
            return this.$('.acf-link');
        },

        initialize: function() {
            // ...
        },

        getValue: function() {

            // return
            var data = {
                type: this.$('.input-type :checked').val(),
                title: this.$('.input-title').val(),
                url: this.$('.input-url').val(),
                post: this.$('.input-post :selected').text(),
                term: this.$('.input-term :selected').text(),
                target: this.$('.input-target').is(':checked')
            };

            if (data.type === 'post') {

                data.url = data.post;

            } else if (data.type === 'term') {

                data.url = data.term;

            }

            return data;

        },

        setValue: function(val) {

            // default
            val = acf.parseArgs(val, {
                remove: false,
                title: '',
                url: '',
                target: false
            });

            // vars
            var $div = this.$control();

            // remove class
            $div.removeClass('-value -external');

            // add class
            if (val.url)
                $div.addClass('-value');

            if (val.target)
                $div.addClass('-external');

            // update text
            this.$('.link-title').html(val.title);
            this.$('.link-url').attr('href', val.url).html(val.url);

            // remove inputs data
            if (val.remove) {

                this.$('.input-type :checked').prop('checked', false);
                this.$('.input-type [value="url"]').prop('checked', true).trigger('change');
                this.$('.input-title').val('');
                this.$('.input-target').prop('checked', false);
                this.$('.input-url').val('').trigger('change');
                this.$('.input-post').val('').trigger('change');
                this.$('.input-term').val('').trigger('change');

            }

        },

        onClickEdit: function(e, $el) {

            var $modal = $el.closest('.acf-input').find('.acfe-modal');

            var title = $modal.attr('data-modal-title');

            var model = this;

            new acfe.Popup($modal, {
                title: title,
                size: 'medium',
                footer: acf.__('Close'),
                onClose: function() {
                    model.onChange();
                }
            });

        },

        onClickRemove: function(e, $el) {

            this.setValue({
                remove: true
            });

        },

        onChange: function(e, $el) {

            // get the changed value
            var val = this.getValue();

            // update inputs
            this.setValue(val);

        },

    });

    acf.registerFieldType(ACFE_Advanced_Link);

    /*
     * Field: Advanced Link Manager
     */
    new acf.Model({

        actions: {
            'invalid_field': 'invalidField',
        },

        filters: {
            'select2_ajax_data/type=post_object': 'ajaxField',
        },

        invalidField: function(field) {

            var $advanced_link = field.$el.closest('.acf-field-acfe-advanced-link').not('.acf-error');

            if ($advanced_link.length) {

                var advanced_link_field = acf.getInstance($advanced_link);

                advanced_link_field.showError(field.notice.get('text'));

            }

        },

        ajaxField: function(ajaxData, data, $el, field, select) {

            if (field.get('key') !== 'post')
                return ajaxData;

            var advanced_link = acf.getInstance($el.closest('.acf-field-acfe-advanced-link'));

            if (advanced_link) {

                ajaxData.field_key = advanced_link.get('key');

            }

            return ajaxData;

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Button
     */
    var ACFE_Button = acf.Field.extend({

        type: 'acfe_button',

        events: {
            'click input': 'onClick',
            'click button': 'onClick',
        },

        $input: function() {

            if (this.$('input').length) {

                return this.$('input');

            } else if (this.$('button').length) {

                return this.$('button');

            }

        },

        initialize: function() {

            // vars
            var $button = this.$input();

            // inherit data
            this.inherit($button);

        },

        onClick: function(e, $el) {

            if (!this.get('ajax')) return;

            e.preventDefault();

            // serialize form data
            var data = {
                action: 'acfe/fields/button',
                field_key: this.get('key'),
                acf: acf.serialize(this.$el.closest('form'), 'acf')
            };

            data = acf.applyFilters('acfe/fields/button/data', data, this.$el);
            data = acf.applyFilters('acfe/fields/button/data/name=' + this.get('name'), data, this.$el);
            data = acf.applyFilters('acfe/fields/button/data/key=' + this.get('key'), data, this.$el);

            // Deprecated
            acf.doAction('acfe/fields/button/before_ajax', this.$el, data);

            // Actions
            acf.doAction('acfe/fields/button/before', this.$el, data);
            acf.doAction('acfe/fields/button/before/name=' + this.get('name'), this.$el, data);
            acf.doAction('acfe/fields/button/before/key=' + this.get('key'), this.$el, data);

            // ajax
            $.ajax({
                url: acf.get('ajaxurl'),
                data: acf.prepareForAjax(data),
                type: 'post',
                dataType: 'json',
                context: this,

                // Success
                success: function(response) {

                    // Deprecated
                    acf.doAction('acfe/fields/button/ajax_success', response, this.$el, data);

                    // Actions
                    acf.doAction('acfe/fields/button/success', response, this.$el, data);
                    acf.doAction('acfe/fields/button/success/name=' + this.get('name'), response, this.$el, data);
                    acf.doAction('acfe/fields/button/success/key=' + this.get('key'), response, this.$el, data);

                },

                // Complete
                complete: function(xhr) {

                    var response = xhr.responseText;

                    // Actions
                    acf.doAction('acfe/fields/button/complete', response, this.$el, data);
                    acf.doAction('acfe/fields/button/complete/name=' + this.get('name'), response, this.$el, data);
                    acf.doAction('acfe/fields/button/complete/key=' + this.get('key'), response, this.$el, data);

                }


            });

        }

    });

    acf.registerFieldType(ACFE_Button);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Checkbox
     */
    new acf.Model({

        actions: {
            'new_field/type=checkbox': 'newField',
        },

        newField: function(field) {

            // bail early
            if (!field.has('acfeLabels')) return;

            // allow options group
            $.each(field.get('acfeLabels'), function(group, key) {

                field.$control().find('input[type=checkbox][value="' + key + '"]').closest('ul').before('<strong>' + group + '</strong>');

            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Clone
     */
    var Clone = acf.Field.extend({

        wait: false,

        type: 'clone',

        events: {
            'click [data-name="edit"]': 'onClick',
            'duplicateField': 'onDuplicate'
        },

        initialize: function() {

            if (this.has('acfeCloneModal')) {

                var edit = this.get('acfeCloneModalButton');

                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a data-name="edit" class="acf-button button" href="#">' + edit + '</a>');

            }

        },

        onClick: function(e, $el) {

            var title = this.$labelWrap().find('label').text().trim();
            var $modal = this.$el.find('> .acf-input > .acfe-modal').addClass('acfe-modal-edit-' + this.get('name') + ' acfe-modal-edit-' + this.get('key'));


            // Title
            if (!title.length) {
                title = this.get('acfeCloneModalButton');
            }

            // Close
            var close = false;

            if (this.has('acfeCloneModalClose')) {
                close = acf.__('Close');
            }

            // Size
            var size = 'large';

            if (this.has('acfeCloneModalSize')) {
                size = this.get('acfeCloneModalSize');
            }

            // Open modal
            new acfe.Popup($modal, {
                title: title,
                size: size,
                footer: close
            });

        },

        onDuplicate: function(e, $el, $duplicate) {
            $duplicate.find('a[data-name="edit"]').remove();
        }

    });

    acf.registerFieldType(Clone);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Code Editor
     */
    var CodeEditor = acf.Field.extend({

        wait: 'ready',

        type: 'acfe_code_editor',

        events: {
            'showField': 'onShow',
            'duplicateField': 'onDuplicate'
        },

        $control: function() {

            return this.$el.find('> .acf-input > .acf-input-wrap');

        },

        $input: function() {

            return this.$el.find('> .acf-input > .acf-input-wrap > textarea');

        },

        input: function() {

            return this.$input()[0];

        },

        rows: function() {

            return this.$input().attr('rows');

        },

        initialize: function() {

            this.rows = this.$control().data('rows');
            this.max_rows = this.$control().data('max-rows');

            this.mode = this.$control().data('mode');
            this.lines = this.$control().data('lines');
            this.indentUnit = this.$control().data('indent-unit');

            var codeEditor = [];

            // Default WP settings
            var wpCodeMirror = wp.codeEditor.defaultSettings.codemirror;

            // Field settings
            var CodeMirror = {
                lineNumbers: this.lines,
                lineWrapping: true,
                styleActiveLine: false,
                continueComments: true,
                indentUnit: this.indentUnit,
                tabSize: 1,
                indentWithTabs: false,
                mode: this.mode,
                extraKeys: {
                    Tab: function(cm) {
                        cm.execCommand("indentMore")
                    },
                    "Shift-Tab": function(cm) {
                        cm.execCommand("indentLess")
                    },
                },
            };

            // Merge settings
            var codeMirror = jQuery.extend(wpCodeMirror, CodeMirror);

            // Push CodeMirror settings to codemirror property
            codeEditor.codemirror = codeMirror;

            // Init WP Code Editor
            this.editor = wp.codeEditor.initialize(this.input(), codeEditor);

            if (this.rows || this.max_rows) {

                if (this.rows) {

                    this.editor.codemirror.getScrollerElement().style.minHeight = this.rows * 18.5 + 'px';

                }

                if (this.max_rows) {

                    this.editor.codemirror.getScrollerElement().style.maxHeight = this.max_rows * 18.5 + 'px';

                }

                this.editor.codemirror.refresh();

            }

            field = this;

            this.editor.codemirror.on('change', function() {

                field.editor.codemirror.save();
                field.$input().change();

            });

        },

        onShow: function() {

            if (this.editor.codemirror) {

                this.editor.codemirror.refresh();

            }

        },

        onDuplicate: function(e, $el, $duplicate) {

            $duplicate.find('.CodeMirror:last').remove();

        },

    });

    acf.registerFieldType(CodeEditor);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Column
     */
    var Column = acf.Field.extend({

        wait: 'new_field',

        type: 'acfe_column',

        $control: function() {
            return this.$('.acf-fields:first');
        },

        initialize: function() {

            if (this.$el.is('td')) {

                var $table = this.$el.closest('.acf-table').find('th[data-type="acfe_column"]').remove();
                this.remove();

            }

            if (this.get('endpoint')) {

                this.$el.find('> .acf-label').remove();
                this.$el.find('> .acf-input').remove();

                return;

            }

            var $field = this.$el;
            var $label = this.$el.find('> .acf-label');
            var $input = this.$inputWrap();
            var $wrap = this.$control();

            $label.remove();

            var $parent = $field.parent();
            $parent.addClass('acfe-column-wrapper');
            $wrap.addClass($parent.hasClass('-left') ? '-left' : '');
            $wrap.addClass($parent.hasClass('-clear') ? '-clear' : '');

            $wrap.append($field.nextUntil('.acf-field-acfe-column', '.acf-field'));

        }

    });

    acf.registerFieldType(Column);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;

    /*
     * Actions
     */
    model.events['click .acf-fc-layout-handle'] = 'acfeEditLayoutTitleToggleHandle';
    model.acfeEditLayoutTitleToggleHandle = function(e, $el) {

        var flexible = this;

        // Title Edition
        if (!flexible.has('acfeFlexibleTitleEdition'))
            return;

        // Vars
        var $layout = $el.closest('.layout');

        if ($layout.hasClass('acfe-flexible-title-edition')) {

            $layout.find('> .acf-fc-layout-handle > .acfe-layout-title > input.acfe-flexible-control-title').trigger('blur');

        }

    }

    model.events['click .acfe-layout-title-text'] = 'acfeEditLayoutTitle';
    model.acfeEditLayoutTitle = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Title Edition
        if (!flexible.has('acfeFlexibleTitleEdition'))
            return;

        // Stop propagation
        e.stopPropagation();

        // Toggle
        flexible.acfeEditLayoutTitleToggle(e, $el);

    }

    model.events['blur input.acfe-flexible-control-title'] = 'acfeEditLayoutTitleToggle';
    model.acfeEditLayoutTitleToggle = function(e, $el) {

        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        var $title = $handle.find('.acfe-layout-title');

        if ($layout.hasClass('acfe-flexible-title-edition')) {

            var $input = $title.find('> input[data-acfe-flexible-control-title-input]');

            if ($input.val() === '')
                $input.val($input.attr('placeholder')).trigger('input');

            $layout.removeClass('acfe-flexible-title-edition');

            $input.insertAfter($handle);

        } else {

            var $input = $layout.find('> input[data-acfe-flexible-control-title-input]');

            var $input = $input.appendTo($title);

            $layout.addClass('acfe-flexible-title-edition');
            $input.focus().attr('size', $input.val().length);

        }

    }

    // Layout: Edit Title
    model.events['click input.acfe-flexible-control-title'] = 'acfeEditLayoutTitlePropagation';
    model.acfeEditLayoutTitlePropagation = function(e, $el) {

        e.stopPropagation();

    }

    // Layout: Edit Title Input
    model.events['input [data-acfe-flexible-control-title-input]'] = 'acfeEditLayoutTitleInput';
    model.acfeEditLayoutTitleInput = function(e, $el) {

        // Vars
        var $layout = $el.closest('.layout');
        var $title = $layout.find('> .acf-fc-layout-handle .acfe-layout-title .acfe-layout-title-text');

        var val = $el.val();

        $el.attr('size', val.length);

        $title.html(val);

    }

    // Layout: Edit Title Input Enter
    model.events['keypress [data-acfe-flexible-control-title-input]'] = 'acfeEditLayoutTitleInputEnter';
    model.acfeEditLayoutTitleInputEnter = function(e, $el) {

        // Enter Key
        if (e.keyCode !== 13)
            return;

        e.preventDefault();
        $el.blur();

    }

    // Layout: Settings
    model.events['click [data-acfe-flexible-settings]'] = 'acfeLayoutSettings';
    model.acfeLayoutSettings = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout');

        // Modal data
        var $modal = $layout.find('> .acfe-modal.-settings');
        var $handle = $layout.find('> .acf-fc-layout-handle');

        var $layout_order = $handle.find('> .acf-fc-layout-order').outerHTML();
        var $layout_title = $handle.find('.acfe-layout-title-text').text();

        // Open modal
        new acfe.Popup($modal, {
            title: $layout_order + ' ' + $layout_title,
            footer: acf.__('Close'),
            onClose: function() {

                if (!flexible.has('acfeFlexiblePreview'))
                    return;

                flexible.closeLayout($layout);

            }
        });

    }

    /*
     * Layout: Toggle Action
     */
    model.events['click [data-acfe-flexible-control-toggle]'] = 'acfeLayoutToggle';
    model.acfeLayoutToggle = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout');

        var $field = $layout.find('> .acfe-flexible-layout-toggle');

        if (!$field.length)
            return;

        if ($field.val() === '1') {

            $layout.removeClass('acfe-flexible-layout-hidden');
            $field.val('');

        } else {

            $layout.addClass('acfe-flexible-layout-hidden');
            $field.val('1');

        }

    }

    /*
     * Layout: Toggle Spawn
     */
    acf.addAction('acfe/flexible/layouts', function($layout, flexible) {

        if (!flexible.has('acfeFlexibleToggle'))
            return;

        // Layout Closed
        var $field = $layout.find('> .acfe-flexible-layout-toggle');

        if (!$field.length)
            return;

        if ($field.val() === '1') {

            $layout.addClass('acfe-flexible-layout-hidden');

        } else {

            $layout.removeClass('acfe-flexible-layout-hidden');

        }

    });

    // Layout: Clone
    model.events['click [data-acfe-flexible-control-clone]'] = 'acfeCloneLayout';
    model.acfeCloneLayout = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout');
        var layout_name = $layout.data('layout');

        // Popup min/max
        var $popup = $(flexible.$popup().html());
        var $layouts = flexible.$layouts();

        var countLayouts = function(name) {
            return $layouts.filter(function() {
                return $(this).data('layout') === name;
            }).length;
        };

        // vars
        var $a = $popup.find('[data-layout="' + layout_name + '"]');
        var min = $a.data('min') || 0;
        var max = $a.data('max') || 0;
        var count = countLayouts(layout_name);

        // max
        if (max && count >= max) {

            $el.addClass('disabled');
            return false;

        } else {

            $el.removeClass('disabled');

        }

        // Fix inputs
        flexible.acfeFixInputs($layout);

        var $_layout = $layout.clone();

        // Clean Layout
        flexible.acfeCleanLayouts($_layout);

        var parent = $el.closest('.acf-flexible-content').find('> input[type=hidden]').attr('name');

        // Clone
        var $layout_added = flexible.acfeDuplicate({
            layout: $_layout,
            before: $layout,
            parent: parent
        });

    }

    // Layout: Copy
    model.events['click [data-acfe-flexible-control-copy]'] = 'acfeCopyLayout';
    model.acfeCopyLayout = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout').clone();
        var source = flexible.$control().find('> input[type=hidden]').attr('name');

        // Fix inputs
        flexible.acfeFixInputs($layout);

        // Clean layout
        flexible.acfeCleanLayouts($layout);

        // Get layout data
        var data = JSON.stringify({
            source: source,
            layouts: $layout[0].outerHTML
        });

        // Append Temp Input
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:none;position:absolute;" value="" />').appendTo($('body'));
        $input.attr('value', data).select();

        // Command: Copy
        if (document.execCommand('copy'))
            alert('Layout has been transferred to your clipboard');

        // Prompt
        else
            prompt('Copy the following layout data to your clipboard', data);

        // Remove the temp input
        $input.remove();

    }

    // Flexible: Copy Layouts
    model.acfeCopyLayouts = function() {

        // Get Flexible
        var flexible = this;

        // Get layouts
        var $layouts = flexible.$layoutsWrap().clone();
        var source = flexible.$control().find('> input[type=hidden]').attr('name');

        // Fix inputs
        flexible.acfeFixInputs($layouts);

        // Clean layout
        flexible.acfeCleanLayouts($layouts);

        // Get layouts data
        var data = JSON.stringify({
            source: source,
            layouts: $layouts.html()
        });

        // Append Temp Input
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:none;position:absolute;" value="" />').appendTo(flexible.$el);
        $input.attr('value', data).select();

        // Command: Copy
        if (document.execCommand('copy'))
            alert('Layouts have been transferred to your clipboard');

        // Prompt
        else
            prompt('Copy the following layouts data to your clipboard', data);

        $input.remove();

    }

    // Flexible: Paste Layouts
    model.acfePasteLayouts = function() {

        // Get Flexible
        var flexible = this;

        var paste = prompt('Paste layouts data in the following field');

        // No input
        if (paste == null || paste === '')
            return;

        try {

            // Paste HTML
            var data = JSON.parse(paste);
            var source = data.source;
            var $html = $(data.layouts);

            // Parsed layouts
            var $html_layouts = $html.closest('[data-layout]');

            if (!$html_layouts.length)
                return alert('No layouts data available');

            // Popup min/max
            var $popup = $(flexible.$popup().html());
            var $layouts = flexible.$layouts();

            var countLayouts = function(name) {
                return $layouts.filter(function() {
                    return $(this).data('layout') === name;
                }).length;
            };

            // init
            var validated_layouts = [];

            // Each first level layouts
            $html_layouts.each(function() {

                var $this = $(this);
                var layout_name = $this.data('layout');

                // vars
                var $a = $popup.find('[data-layout="' + layout_name + '"]');
                var min = $a.data('min') || 0;
                var max = $a.data('max') || 0;
                var count = countLayouts(layout_name);

                // max
                if (max && count >= max)
                    return;

                // Validate layout against available layouts
                var get_clone_layout = flexible.$clone($this.attr('data-layout'));

                // Layout is invalid
                if (!get_clone_layout.length)
                    return;

                // Add validated layout
                validated_layouts.push($this);

            });

            // Nothing to add
            if (!validated_layouts.length)
                return alert('No layouts could be pasted');

            // Add layouts
            $.each(validated_layouts, function() {

                var $layout = $(this);
                var search = source + '[' + $layout.attr('data-id') + ']';
                var target = flexible.$control().find('> input[type=hidden]').attr('name');

                flexible.acfeDuplicate({
                    layout: $layout,
                    before: false,
                    search: search,
                    parent: target
                });

            });

        } catch (e) {

            console.log(e);
            alert('Invalid data');

        }

    }

    // Flexible: Dropdown
    model.events['click [data-name="acfe-flexible-control-button"]'] = 'acfeControl';
    model.acfeControl = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Vars
        var $dropdown = $el.next('.tmpl-acfe-flexible-control-popup').html();

        // Init Popup
        var Popup = acf.models.TooltipConfirm.extend({
            render: function() {
                this.html(this.get('text'));
                this.$el.addClass('acf-fc-popup');
            }
        });

        // New Popup
        var popup = new Popup({
            target: $el,
            targetConfirm: false,
            text: $dropdown,
            context: flexible,
            confirm: function(e, $el) {

                if ($el.attr('data-acfe-flexible-control-action') === 'paste')
                    flexible.acfePasteLayouts();

                else if ($el.attr('data-acfe-flexible-control-action') === 'copy')
                    flexible.acfeCopyLayouts();

            }
        });

        popup.on('click', 'a', 'onConfirm');

    }

    // Flexible: Duplicate
    model.acfeDuplicate = function(args) {

        // Arguments
        args = acf.parseArgs(args, {
            layout: '',
            before: false,
            parent: false,
            search: '',
            replace: '',
        });

        // Validate
        if (!this.allowAdd())
            return false;

        var uniqid = acf.uniqid();

        if (args.parent) {

            if (!args.search) {

                args.search = args.parent + '[' + args.layout.attr('data-id') + ']';

            }

            args.replace = args.parent + '[' + uniqid + ']';

        }

        var duplicate_args = {
            target: args.layout,
            search: args.search,
            replace: args.replace,
            append: this.proxy(function($el, $el2) {

                // Add class to duplicated layout
                $el2.addClass('acfe-layout-duplicated');

                // Reset UniqID
                $el2.attr('data-id', uniqid);

                // append before
                if (args.before) {

                    // Fix clone: Use after() instead of native before()
                    args.before.after($el2);

                }

                // append end
                else {

                    this.$layoutsWrap().append($el2);

                }

                // enable
                acf.enable($el2, this.cid);

                // render
                this.render();

            })
        }

        if (acfe.versionCompare(acf.get('acf_version'), '<', '5.9')) {

            // Add row
            var $el = acf.duplicate(duplicate_args);

            // Hotfix for ACF Pro 5.9
        } else {

            // Add row
            var $el = model.acfeNewAcfDuplicate(duplicate_args);

        }

        // trigger change for validation errors
        this.$input().trigger('change');

        // Fix tabs conditionally hidden
        var tabs = acf.getFields({
            type: 'tab',
            parent: $el,
        });

        if (tabs.length) {

            $.each(tabs, function() {

                if (this.$el.hasClass('acf-hidden')) {

                    this.tab.$el.addClass('acf-hidden');

                }

            });

        }


        // return
        return $el;

    }

    /*
     * Based on acf.duplicate (5.9)
     *
     * doAction('duplicate) has been commented out
     * This fix an issue with the WYSIWYG editor field during copy/paste since ACF 5.9
     */
    model.acfeNewAcfDuplicate = function(args) {

        // allow jQuery
        if (args instanceof jQuery) {
            args = {
                target: args
            };
        }

        // defaults
        args = acf.parseArgs(args, {
            target: false,
            search: '',
            replace: '',
            rename: true,
            before: function($el) {},
            after: function($el, $el2) {},
            append: function($el, $el2) {
                $el.after($el2);
            }
        });

        // compatibility
        args.target = args.target || args.$el;

        // vars
        var $el = args.target;

        // search
        args.search = args.search || $el.attr('data-id');
        args.replace = args.replace || acf.uniqid();

        // before
        // - allow acf to modify DOM
        // - fixes bug where select field option is not selected
        args.before($el);
        acf.doAction('before_duplicate', $el);

        // clone
        var $el2 = $el.clone();

        // rename
        if (args.rename) {
            acf.rename({
                target: $el2,
                search: args.search,
                replace: args.replace,
                replacer: (typeof args.rename === 'function' ? args.rename : null)
            });
        }

        // remove classes
        $el2.removeClass('acf-clone');
        $el2.find('.ui-sortable').removeClass('ui-sortable');

        // after
        // - allow acf to modify DOM
        args.after($el, $el2);
        acf.doAction('after_duplicate', $el, $el2);

        // append
        args.append($el, $el2);

        /**
         * Fires after an element has been duplicated and appended to the DOM.
         *
         * @date    30/10/19
         * @since   5.8.7
         *
         * @param   jQuery $el The original element.
         * @param   jQuery $el2 The duplicated element.
         */
        //acf.doAction('duplicate', $el, $el2 );

        // append
        acf.doAction('append', $el2);

        // return
        return $el2;
    };

    // Flexible: Fix Inputs
    model.acfeFixInputs = function($layout) {

        $layout.find('input').each(function() {

            $(this).attr('value', this.value);

        });

        $layout.find('textarea').each(function() {

            $(this).html(this.value);

        });

        $layout.find('input:radio,input:checkbox').each(function() {

            if (this.checked)
                $(this).attr('checked', 'checked');

            else
                $(this).attr('checked', false);

        });

        $layout.find('option').each(function() {

            if (this.selected)
                $(this).attr('selected', 'selected');

            else
                $(this).attr('selected', false);

        });

    }

    // Flexible: Clean Layout
    model.acfeCleanLayouts = function($layout) {

        // Clean WP Editor
        $layout.find('.acf-editor-wrap').each(function() {

            var $input = $(this);

            $input.find('.wp-editor-container div').remove();
            $input.find('.wp-editor-container textarea').css('display', '');

        });

        // Clean Date
        $layout.find('.acf-date-picker').each(function() {

            var $input = $(this);

            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');

        });

        // Clean Time
        $layout.find('.acf-time-picker').each(function() {

            var $input = $(this);

            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');

        });

        // Clean DateTime
        $layout.find('.acf-date-time-picker').each(function() {

            var $input = $(this);

            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');

        });

        // Clean Code Editor
        $layout.find('.acfe-field-code-editor').each(function() {

            var $input = $(this);

            $input.find('.CodeMirror').remove();

        });

        // Clean Color Picker
        $layout.find('.acf-color-picker').each(function() {

            var $input = $(this);

            var $color_picker = $input.find('> input');
            var $color_picker_proxy = $input.find('.wp-picker-container input.wp-color-picker').clone();

            $color_picker.after($color_picker_proxy);

            $input.find('.wp-picker-container').remove();

        });

        // Clean Post Object
        $layout.find('.acf-field-post-object').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();

        });

        // Clean Page Link
        $layout.find('.acf-field-page-link').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();

        });

        // Clean Select2
        $layout.find('.acf-field-select').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();

        });

        // Clean FontAwesome
        $layout.find('.acf-field-font-awesome').each(function() {

            var $input = $(this);

            $input.find('> .acf-input span').remove();

            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden');

        });


        // Clean Tab
        $layout.find('.acf-tab-wrap').each(function() {

            var $wrap = $(this);

            var $content = $wrap.closest('.acf-fields');

            var tabs = [];
            $.each($wrap.find('li a'), function() {

                tabs.push($(this));

            });

            $content.find('> .acf-field-tab').each(function() {

                $current_tab = $(this);

                $.each(tabs, function() {

                    var $this = $(this);

                    if ($this.attr('data-key') !== $current_tab.attr('data-key'))
                        return;

                    $current_tab.find('> .acf-input').append($this);

                });

            });

            $wrap.remove();

        });

        // Clean Accordion
        $layout.find('.acf-field-accordion').each(function() {

            var $input = $(this);

            $input.find('> .acf-accordion-title > .acf-accordion-icon').remove();

            // Append virtual endpoint after each accordion
            $input.after('<div class="acf-field acf-field-accordion" data-type="accordion"><div class="acf-input"><div class="acf-fields" data-endpoint="1"></div></div></div>');

        });

    }

    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible) {

        // ACFE: Lock
        if (flexible.has('acfeFlexibleLock')) {

            flexible.removeEvents({
                'mouseover': 'onHover'
            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;

    /*
     * Actions
     */
    model.events['click [data-action="acfe-flexible-modal-edit"]'] = 'acfeModalEdit';
    model.acfeModalEdit = function(e, $el) {

        var flexible = this;

        // Layout
        var $layout = $el.closest('.layout');

        // Modal data
        var $modal = $layout.find('> .acfe-modal.-fields').addClass('acfe-modal-edit-' + flexible.get('name') + ' acfe-modal-edit-' + flexible.get('key')).addClass('acfe-modal-edit-' + $layout.data('layout'));
        var $handle = $layout.find('> .acf-fc-layout-handle');

        var $layout_order = $handle.find('> .acf-fc-layout-order').outerHTML();
        var $layout_title = acfe.getTextNode($handle.find('.acfe-layout-title-text'));

        var close = false;
        if (flexible.has('acfeFlexibleCloseButton')) {
            close = acf.__('Close');
        }

        // Open modal
        new acfe.Popup($modal, {
            title: $layout_order + ' ' + $layout_title,
            footer: close,
            onOpen: function() {

                flexible.openLayout($layout);

            },
            onClose: function() {

                flexible.closeLayout($layout);

            }
        });

    };

    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible) {

        if (flexible.has('acfeFlexibleModalEdition') && (flexible.has('acfeFlexiblePlaceholder') || flexible.has('acfeFlexiblePreview'))) {

            // Remove Collapse Action
            flexible.removeEvents({
                'click [data-name="collapse-layout"]': 'onClickCollapse'
            });

            // Remove placeholder Collapse Action
            flexible.removeEvents({
                'click .acfe-fc-placeholder': 'onClickCollapse'
            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;

    /*
     * Actions
     */
    model.acfeModalSelect = function(e, $el) {

        // Get Flexible
        var flexible = this;

        // Validate
        if (!flexible.validateAdd())
            return false;

        // Layout
        var $layout_source = null;

        if ($el.hasClass('acf-icon'))
            $layout_source = $el.closest('.layout');

        // Get Available Layouts
        var layouts = flexible.getPopupHTML();

        // Init Categories
        var categories = {
            array: [],
            html: ''
        };

        function SearchArray(element, array) {

            var len = array.length,
                str = element.toString().toLowerCase();

            for (var i = 0; i < len; i++) {
                if (array[i].toLowerCase() === str) {
                    return i;
                }
            }

            return -1;

        }

        // Get Categories
        $(layouts).find('li a span[data-acfe-flexible-category]').each(function() {

            var spanCats = $(this).data('acfe-flexible-category');

            $.each(spanCats, function() {

                if (SearchArray(this, categories.array) !== -1)
                    return;

                categories.array.push(this);

            });

        });

        // Categories HTML
        if (categories.array.length) {

            categories.array.sort();

            categories.html += '<h2 class="acfe-flexible-categories nav-tab-wrapper">';

            categories.html += '<a href="#" data-acfe-flexible-category="acfe-all" class="nav-tab nav-tab-active"><span class="dashicons dashicons-menu"></span></a>';

            $(categories.array).each(function(k, category) {

                categories.html += '<a href="#" data-acfe-flexible-category="' + category + '" class="nav-tab">' + category + '</a>';

            });

            categories.html += '</h2>';

        }

        // Modal Title
        var modalTitle = acf.__('Add Row');

        if (flexible.has('acfeFlexibleModalTitle')) {
            modalTitle = flexible.get('acfeFlexibleModalTitle');
        }

        // Create Modal
        var $modal = $('' +
            '<div class="acfe-modal acfe-modal-select-' + flexible.get('name') + ' acfe-modal-select-' + flexible.get('key') + '">' +

            categories.html +
            '<div class="acfe-flex-container">' +
            layouts +
            '</div>' +

            '</div>'

        ).appendTo('body');

        // Open Modal
        new acfe.Popup($modal, {
            title: modalTitle,
            size: flexible.get('acfeFlexibleModalSize'),
            destroy: true
        });

        // Modal: Columns
        if (flexible.has('acfeFlexibleModalCol')) {

            $modal.find('.acfe-modal-content .acfe-flex-container').addClass('acfe-col-' + flexible.get('acfeFlexibleModalCol'));

        }

        // Modal: Columns
        if (flexible.has('acfeFlexibleThumbnails')) {

            $modal.find('.acfe-modal-content .acfe-flex-container').addClass('acfe-flex-thumbnails');

        }

        // Modal: ACF autofocus fix
        $modal.find('li:first-of-type a').blur();

        // Modal: Layouts Badges
        $modal.find('li a span.badge').each(function() {

            $(this).addClass('acf-js-tooltip dashicons dashicons-info');

        });

        // Modal: Categories Click
        $modal.find('.acfe-flexible-categories a').click(function(e) {

            e.preventDefault();

            var $link = $(this);

            $link.closest('.acfe-flexible-categories').find('a').removeClass('nav-tab-active');
            $link.addClass('nav-tab-active');

            var selected_category = $link.data('acfe-flexible-category');

            $modal.find('a[data-layout] span[data-acfe-flexible-category]').each(function() {

                // Get span
                var $span = $(this);

                // Show All
                $span.closest('li').show();

                var category = $span.data('acfe-flexible-category');

                // Specific category
                if (selected_category !== 'acfe-all') {

                    // Hide All
                    $span.closest('li').hide();

                    $.each(category, function(i, c) {

                        if (selected_category.toLowerCase() === c.toLowerCase()) {

                            $span.closest('li').show();

                            return false;

                        }

                    });

                }

            });

        });

        // Modal: Click Add Layout
        $modal.on('click', 'a[data-layout]', function(e) {

            e.preventDefault();

            // Close modal
            acfe.closePopup();

            // Add layout
            flexible.add({
                layout: $(this).data('layout'),
                before: $layout_source
            });

        });

    }

    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible) {

        if (!flexible.has('acfeFlexibleModal'))
            return;

        // Vars
        var $clones = flexible.$clones();

        if ($clones.length <= 1)
            return;

        // Remove native ACF Tooltip action
        flexible.removeEvents({
            'click [data-name="add-layout"]': 'onClickAdd'
        });

        // Add ACF Extended Modal action
        flexible.addEvents({
            'click [data-name="add-layout"]': 'acfeModalSelect'
        });

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Flexible Content Overwrite
     */
    var FlexibleContent = acf.models.FlexibleContentField;

    acf.models.FlexibleContentField = FlexibleContent.extend({

        addSortable: function(self) {

            // bail early if max 1 row
            if (this.get('max') == 1) {
                return;
            }

            // add sortable
            this.$layoutsWrap().sortable({
                items: ' > .layout',
                handle: '> .acf-fc-layout-handle',
                forceHelperSize: false, // Changed to false
                forcePlaceholderSize: true,
                revert: 50,
                tolerance: "pointer", // Changed to pointer
                scroll: true,
                stop: function(event, ui) {
                    self.render();
                },
                update: function(event, ui) {
                    self.$input().trigger('change');
                }
            });

        },

        acfeOneClick: function(e, $el) {

            // Vars
            var $clones = this.$clones();
            var $layout_name = $($clones[0]).data('layout');

            // Source
            var $layout_source = null;

            if ($el.hasClass('acf-icon')) {
                $layout_source = $el.closest('.layout');
            }

            // Add
            this.add({
                layout: $layout_name,
                before: $layout_source
            });

            // Hide native tooltip
            var acfPopup = $('.acf-fc-popup');

            if (acfPopup.length) {
                acfPopup.hide();
            }


        },

        acfeLayoutInit: function($layout) {

            // flexible content
            var key = this.get('key');
            var name = this.get('name');
            var $el = this.$el;

            // layout
            var layout = $layout.data('layout');
            var index = $layout.index();

            // Placeholder
            var $placeholder = $layout.find('> .acfe-fc-placeholder');

            // Placeholder: Show
            $placeholder.removeClass('acf-hidden');

            // If no modal edition & opened: Hide Placeholder
            if (!this.has('acfeFlexibleModalEdition') && !this.isLayoutClosed($layout)) {

                $placeholder.addClass('acf-hidden');

            }

            // Flexible has Preview
            if (this.isLayoutClosed($layout) && this.has('acfeFlexiblePreview') && !$placeholder.hasClass('-loading')) {

                $placeholder.addClass('acfe-fc-preview -loading').find('> .acfe-flexible-placeholder').prepend('<span class="spinner"></span>');
                $placeholder.find('> .acfe-fc-overlay').addClass('-hover');

                // vars
                var $input = $layout.children('input');
                var prefix = $input.attr('name').replace('[acf_fc_layout]', '');

                // ajax data
                var ajaxData = {
                    action: 'acfe/flexible/layout_preview',
                    field_key: key,
                    i: index,
                    layout: layout,
                    value: acf.serialize($layout, prefix)
                };

                acf.doAction('acfe/fields/flexible_content/before_preview', $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/before_preview/name=' + name, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/before_preview/key=' + key, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/before_preview/name=' + name + '&layout=' + layout, $el, $layout, ajaxData);
                acf.doAction('acfe/fields/flexible_content/before_preview/key=' + key + '&layout=' + layout, $el, $layout, ajaxData);

                // on success
                var onSuccess = function(response) {

                    if (response) {
                        $placeholder.find('> .acfe-flexible-placeholder').html(response);
                    } else {
                        $placeholder.removeClass('acfe-fc-preview');
                    }

                    acf.doAction('acfe/fields/flexible_content/preview', response, $el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/name=' + name, response, $el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/key=' + key, response, $el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/name=' + name + '&layout=' + layout, response, $el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/key=' + key + '&layout=' + layout, response, $el, $layout, ajaxData);

                };

                // on complete
                var onComplete = function() {

                    $placeholder.find('> .acfe-fc-overlay').removeClass('-hover');
                    $placeholder.removeClass('-loading').find('> .acfe-flexible-placeholder > .spinner').remove();

                };

                // ajax
                $.ajax({
                    url: acf.get('ajaxurl'),
                    data: acf.prepareForAjax(ajaxData),
                    dataType: 'html',
                    type: 'post',
                    success: onSuccess,
                    complete: onComplete
                });

            }

        }

    });

    /*
     * Field: Flexible Content
     */
    new acf.Model({

        actions: {
            'new_field/type=flexible_content': 'newField',
            'acfe/flexible/layouts': 'newLayouts',
            'show': 'onShow',
            'hide': 'onHide',
            'append': 'onAppend',
            'invalid_field': 'onInvalidField',
            'valid_field': 'onValidField',
        },

        newField: function(flexible) {

            // Vars
            var $clones = flexible.$clones();
            var $layouts = flexible.$layouts();

            // Do Actions
            $layouts.each(function() {

                var $layout = $(this);
                var $name = $layout.data('layout');

                acf.doAction('acfe/flexible/layouts', $layout, flexible);
                acf.doAction('acfe/flexible/layout/name=' + $name, $layout, flexible);

            });

            // ACFE: 1 layout available - OneClick
            if ($clones.length === 1) {

                // Remove native ACF Tooltip action
                flexible.removeEvents({
                    'click [data-name="add-layout"]': 'onClickAdd'
                });

                // Add ACF Extended Modal action
                flexible.addEvents({
                    'click [data-name="add-layout"]': 'acfeOneClick'
                });

            }

            flexible.addEvents({
                'click .acfe-fc-placeholder': 'onClickCollapse'
            });

            flexible.addEvents({
                'click .acfe-flexible-opened-actions > a': 'onClickCollapse'
            });

            // Flexible: Ajax
            if (flexible.has('acfeFlexibleAjax')) {

                flexible.add = function(args) {

                    // Get Flexible
                    var flexible = this;

                    // defaults
                    args = acf.parseArgs(args, {
                        layout: '',
                        before: false
                    });

                    // validate
                    if (!this.allowAdd()) {
                        return false;
                    }

                    // ajax
                    $.ajax({
                        url: acf.get('ajaxurl'),
                        data: acf.prepareForAjax({
                            action: 'acfe/flexible/models',
                            field_key: this.get('key'),
                            layout: args.layout,
                        }),
                        dataType: 'html',
                        type: 'post',
                        beforeSend: function() {
                            $('body').addClass('-loading');
                        },
                        success: function(html) {
                            if (html) {

                                var $layout = $(html);
                                var uniqid = acf.uniqid();

                                var search = 'acf[' + flexible.get('key') + '][acfcloneindex]';
                                var replace = flexible.$control().find('> input[type=hidden]').attr('name') + '[' + uniqid + ']';

                                // add row
                                var $el = acf.duplicate({
                                    target: $layout,
                                    search: search,
                                    replace: replace,
                                    append: flexible.proxy(function($el, $el2) {

                                        // append
                                        if (args.before) {
                                            args.before.before($el2);
                                        } else {
                                            flexible.$layoutsWrap().append($el2);
                                        }

                                        // enable
                                        acf.enable($el2, flexible.cid);

                                        // render
                                        flexible.render();
                                    })
                                });

                                // Fix data-id
                                $el.attr('data-id', uniqid);

                                // trigger change for validation errors
                                flexible.$input().trigger('change');

                                // return
                                return $el;

                            }
                        },
                        'complete': function() {
                            $('body').removeClass('-loading');
                        }
                    });

                };

            }

        },

        newLayouts: function($layout, flexible) {

            // Layout Closed
            if (flexible.isLayoutClosed($layout)) {

                // Placeholder
                $layout.find('> .acfe-fc-placeholder').removeClass('acf-hidden');

                if (flexible.has('acfeFlexibleOpen')) {

                    acfe.enableFilter('acfeFlexibleOpen');

                    flexible.openLayout($layout);

                    acfe.disableFilter('acfeFlexibleOpen');

                }

            }

        },

        onShow: function($layout, type) {

            if (type !== 'collapse' || !$layout.is('.layout')) {
                return;
            }

            var flexible = acf.getInstance($layout.closest('.acf-field-flexible-content'));

            // Hide Placeholder
            if (!flexible.has('acfeFlexibleModalEdition')) {
                $layout.find('> .acfe-fc-placeholder').addClass('acf-hidden');
            }

        },

        onHide: function($layout, type) {

            if (type !== 'collapse' || !$layout.is('.layout') || $layout.is('.acf-clone')) {
                return;
            }

            // Get Flexible
            var flexible = acf.getInstance($layout.closest('.acf-field-flexible-content'));

            // Remove Ajax Title
            if (flexible.has('acfeFlexibleRemoveAjaxTitle')) {
                flexible.renderLayout = function($layout) {};
            }

            // Preview Ajax
            flexible.acfeLayoutInit($layout);

        },

        onAppend: function($el) {

            // Bail early if layout is not layout
            if (!$el.is('.layout')) {
                return;
            }

            // Get Flexible
            var flexible = acf.getInstance($el.closest('.acf-field-flexible-content'));

            // Open Layout
            if (!$el.is('.acfe-layout-duplicated')) {

                // Modal Edition: Open
                if (flexible.has('acfeFlexibleModalEdition')) {
                    $el.find('> [data-action="acfe-flexible-modal-edit"]:first').trigger('click');
                }

                // Normal Edition: Open
                else {
                    flexible.openLayout($el);
                }

            }

            flexible.acfeLayoutInit($el);

            var $modal = flexible.$el.closest('.acfe-modal.-open');

            if ($modal.length) {

                // Scroll to new layout
                $modal.find('> .acfe-modal-wrapper > .acfe-modal-content').animate({
                    scrollTop: parseInt($el.offset().top) - 200
                }, 200);

            } else {

                if (acfe.versionCompare(acf.get('acf_version'), '<', '5.9')) {

                    // Scroll to new layout
                    $('html, body').animate({
                        scrollTop: parseInt($el.offset().top) - 200
                    }, 200);

                } else {

                    // Avoid native ACF duplicate
                    if (!$el.hasClass('-focused')) {

                        // Scroll to new layout
                        $('html, body').animate({
                            scrollTop: parseInt($el.offset().top) - 200
                        }, 200);

                    }

                }

            }

        },

        onInvalidField: function(field) {

            field.$el.parents('.layout').addClass('acfe-flexible-modal-edit-error');

        },

        onValidField: function(field) {

            field.$el.parents('.layout').each(function() {

                var $layout = $(this);

                if (!$layout.find('.acf-error').length) {
                    $layout.removeClass('acfe-flexible-modal-edit-error');
                }

            });

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Group
     */
    var Group = acf.Field.extend({

        wait: false,

        type: 'group',

        events: {
            'click [data-name="edit"]': 'onClick',
            'duplicateField': 'onDuplicate'
        },

        initialize: function() {

            if (this.has('acfeGroupModal')) {

                var edit = this.get('acfeGroupModalButton');

                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a data-name="edit" class="acf-button button" href="#">' + edit + '</a>');

            }

        },

        onClick: function(e, $el) {

            var title = this.$labelWrap().find('label').text().trim();
            var $modal = this.$el.find('> .acf-input > .acfe-modal').addClass('acfe-modal-edit-' + this.get('name') + ' acfe-modal-edit-' + this.get('key'));

            // Title
            if (!title.length) {
                title = this.get('acfeGroupModalButton');
            }

            // Close
            var close = false;

            if (this.has('acfeGroupModalClose')) {
                close = acf.__('Close');
            }

            // Size
            var size = 'large';

            if (this.has('acfeGroupModalSize')) {
                size = this.get('acfeGroupModalSize');
            }

            // Open modal
            new acfe.Popup($modal, {
                title: title,
                size: size,
                footer: close
            });

        },

        onDuplicate: function(e, $el, $duplicate) {
            $duplicate.find('a[data-name="edit"]').remove();
        }

    });

    acf.registerFieldType(Group);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Radio
     */
    new acf.Model({

        actions: {
            'new_field/type=radio': 'newField',
        },

        newField: function(field) {

            // bail early
            if (!field.has('acfeLabels')) return;

            // allow options group
            $.each(field.get('acfeLabels'), function(group, key) {

                field.$control().find('input[type=radio][value="' + key + '"]').closest('li').addClass('parent').prepend('<strong>' + group + '</strong>');

            });

            // horizontal rule
            if (field.$control().hasClass('acf-hl')) {

                field.$control().find('li.parent').each(function() {

                    $(this).nextUntil('li.parent').addBack().wrapAll('<li><ul></ul></li>');

                });

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: reCaptcha
     */
    var reCaptcha = acf.Field.extend({

        type: 'acfe_recaptcha',

        wait: 'load',

        actions: {
            'validation_failure': 'validationFailure'
        },

        $control: function() {
            return this.$('.acfe-field-recaptcha');
        },

        $input: function() {
            return this.$('input[type="hidden"]');
        },

        $selector: function() {
            return this.$control().find('> div');
        },

        selector: function() {
            return this.$selector()[0];
        },

        initialize: function() {

            if (this.get('version') === 'v2') {

                this.renderV2(this);

            } else if (this.get('version') === 'v3') {

                this.renderV3();

            }

        },

        renderV2: function(self) {

            // selectors
            var selector = this.selector();
            var $input = this.$input();

            // vars
            var sitekey = this.get('siteKey');
            var theme = this.get('theme');
            var size = this.get('size');

            // request
            this.recaptcha = grecaptcha.render(selector, {
                'sitekey': sitekey,
                'theme': theme,
                'size': size,

                'callback': function(response) {
                    acf.val($input, response, true);
                    self.removeError();
                },

                'error-callback': function() {
                    acf.val($input, '', true);
                    self.showError('An error has occured');
                },

                'expired-callback': function() {
                    acf.val($input, '', true);
                    self.showError('reCaptcha has expired');
                }
            });

        },

        renderV3: function() {

            // vars
            var $input = this.$input();
            var sitekey = this.get('siteKey');

            // request
            grecaptcha.ready(function() {
                grecaptcha.execute(sitekey, {
                    action: 'homepage'
                }).then(function(response) {

                    acf.val($input, response, true);

                });
            });

        },

        validationFailure: function($form) {

            if (this.get('version') === 'v2') {
                grecaptcha.reset(this.recaptcha);
            }

        }

    });

    acf.registerFieldType(reCaptcha);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Init
     */
    var repeater = acf.getFieldType('repeater');
    var model = repeater.prototype;

    // Repeater: Lock Layouts
    model.acfeOnHover = function() {

        var repeater = this;

        // remove event
        repeater.off('mouseover');

    }

    /*
     * Spawn
     */
    acf.addAction('new_field/type=repeater', function(repeater) {

        // ACFE: Lock
        if (repeater.has('acfeRepeaterLock')) {

            repeater.removeEvents({
                'mouseover': 'onHover'
            });

            repeater.addEvents({
                'mouseover': 'acfeOnHover'
            });

        }

        // ACFE: Remove Actions
        if (repeater.has('acfeRepeaterRemoveActions')) {

            repeater.$actions().remove();

            repeater.$el.find('thead:first > tr > th.acf-row-handle:last').remove();
            repeater.$rows().find('> .acf-row-handle:last').remove();

            repeater.$control().find('> .acfe-repeater-stylised-button').remove();


        }

        // ACFE: Stylised button
        if (repeater.has('acfeRepeaterStylisedButton')) {

            repeater.$button().removeClass('button-primary');
            repeater.$actions().wrap('<div class="acfe-repeater-stylised-button" />');

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Select2 Hooks
     */
    new acf.Model({

        actions: {
            'select2_init': 'selectInit',
        },

        filters: {
            'select2_args': 'selectArgs',
            'select2_ajax_data': 'selectAjax',
        },

        selectInit: function($select, options, data, field, instance) {

            acf.doAction('select2_init/type=' + field.get('type'), $select, options, data, field, instance);
            acf.doAction('select2_init/name=' + field.get('name'), $select, options, data, field, instance);
            acf.doAction('select2_init/key=' + field.get('key'), $select, options, data, field, instance);

        },

        selectArgs: function(options, $select, data, field, instance) {

            options = acf.applyFilters('select2_args/type=' + field.get('type'), options, $select, data, field, instance);
            options = acf.applyFilters('select2_args/name=' + field.get('name'), options, $select, data, field, instance);
            options = acf.applyFilters('select2_args/key=' + field.get('key'), options, $select, data, field, instance);

            return options;

        },

        selectAjax: function(ajaxData, data, $el, field, instance) {

            ajaxData = acf.applyFilters('select2_ajax_data/type=' + field.get('type'), ajaxData, data, $el, field, instance);
            ajaxData = acf.applyFilters('select2_ajax_data/name=' + field.get('name'), ajaxData, data, $el, field, instance);
            ajaxData = acf.applyFilters('select2_ajax_data/key=' + field.get('key'), ajaxData, data, $el, field, instance);

            if (ajaxData.action) {
                ajaxData = acf.applyFilters('select2_ajax_data/action=' + ajaxData.action, ajaxData, data, $el, field, instance);
            }

            return ajaxData;

        }

    });

    /*
     * Field: Select
     */
    new acf.Model({

        actions: {
            'new_field/type=select': 'selectNew',
            'select2_init': 'selectInit',
        },

        filters: {
            'select2_args': 'selectArgs',
        },

        selectNew: function(field) {

            // inherit properties
            field.inherit(field.$input());

            // remove "- -" characters from placeholder
            // ACF already apply it to all Select2
            if (!field.get('ui') && field.get('allow_null')) {

                field.$input().find('option').each(function(i, option) {

                    if (option.value) {
                        return;
                    }

                    if (!option.text.startsWith('- ') || !option.text.endsWith(' -')) {
                        return;
                    }

                    option.text = option.text.substring(2);
                    option.text = option.text.substring(0, option.text.length - 2);

                });

            }

            // prepend / append
            if (field.has('acfePrepend') || field.has('acfeAppend')) {

                if (!field.$input().parent('.acf-input-wrap').length) {

                    field.$input().wrapAll('<div class="acf-input-wrap"></div>');

                    if (field.get('ui')) {
                        field.$('.acf-input-wrap:first').append(field.$('.select2'));
                    }

                    if (field.has('acfePrepend')) {
                        field.$('.acf-input-wrap:first').before('<div class="acf-input-prepend">' + field.get('acfePrepend') + '</div>');
                        field.$input().addClass('acf-is-prepended');
                    }

                    if (field.has('acfeAppend')) {
                        field.$('.acf-input-wrap:first').before('<div class="acf-input-append">' + field.get('acfeAppend') + '</div>');
                        field.$input().addClass('acf-is-appended');
                    }

                }

            }

        },

        selectInit: function($select, options, data, field, instance) {

            // Add Class on Dropdown with Field Name + key for developers <3
            if ($select.data('select2')) {

                $select.data('select2').$dropdown
                    .addClass('select2-dropdown-acf')
                    .addClass('select2-dropdown-acf-field-' + field.get('name'))
                    .addClass('select2-dropdown-acf-field-' + field.get('key'));

            }

            // only in single mode
            if (!field.get('multiple')) {

                // search placeholder
                if (field.get('acfeSearchPlaceholder')) {

                    $select.on('select2:open', function(e) {

                        $('.select2-search.select2-search--dropdown > .select2-search__field').attr('placeholder', field.get('acfeSearchPlaceholder'));

                    });

                }

            }

        },

        selectArgs: function(options, $select, data, field, instance) {

            // Allow Custom tags
            if (field.get('acfeAllowCustom')) {

                options.tags = true;

                options.createTag = function(params) {

                    var term = $.trim(params.term);

                    if (term === '') {
                        return null;
                    }

                    var optionsMatch = false;

                    this.$element.find('option').each(function() {

                        if (this.value.toLowerCase() !== term.toLowerCase()) return;

                        optionsMatch = true;
                        return false;

                    });

                    if (optionsMatch) {
                        return null;
                    }

                    return {
                        id: term,
                        text: term
                    };

                };

                options.insertTag = function(data, tag) {

                    var found = false;

                    $.each(data, function() {

                        if ($.trim(tag.text).toUpperCase() !== $.trim(this.text).toUpperCase()) return;

                        found = true;
                        return false;

                    });

                    if (!found) {
                        data.unshift(tag);
                    }

                };

            }

            return options;

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Slug
     */
    var ACFE_Slug = acf.Field.extend({

        type: 'acfe_slug',

        events: {
            'input input': 'onInput',
            'focusout input': 'onFocusOut',
        },

        onInput: function(e, $el) {

            $el.val($el.val().toLowerCase()
                .replace(/\s+/g, '-') // Replace spaces with -
                .replace(/[^\w\-]+/g, '') // Remove all non-word chars
                .replace(/\-\-+/g, '-') // Replace multiple - with single -
                .replace(/\_\_+/g, '_') // Replace multiple _ with single _
                .replace(/^-+/, '')); // Trim - from start of text

        },

        onFocusOut: function(e, $el) {

            $el.val($el.val().toLowerCase()
                .replace(/-+$/, '') // Trim - from end of text
                .replace(/_+$/, '')); // Trim _ from end of text

        },

    });

    acf.registerFieldType(ACFE_Slug);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Tab
     */
    new acf.Model({

        actions: {
            'prepare_field/type=tab': 'prepareField',
        },

        prepareField: function(field) {

            if (!field.has('noPreference')) return;

            var $tabs = field.findTabs();
            var tabs = acf.getInstances($tabs);
            var key = field.get('key');

            if (tabs.length) {

                var preference = acf.getPreference('this.tabs');

                if (!preference) return;

                $.each(tabs, function(i, group) {

                    var groupIndex = group.get('index');

                    if (group.data.key !== key)
                        return;

                    preference[groupIndex] = 0;

                });

                // update
                acf.setPreference('this.tabs', preference);

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Textarea
     */
    var Textarea = acf.Field.extend({

        type: 'textarea',

        events: {
            'keydown textarea': 'onInput',
        },

        onInput: function(e, $el) {

            //check for mode
            if (!this.has('acfeTextareaCode')) return;

            // check for tab input
            if (e.keyCode !== 9) return;

            e.preventDefault();

            var input = this.$el.find('textarea')[0];

            var s = input.selectionStart;

            this.$el.find('textarea').val(function(i, v) {
                return v.substring(0, s) + "    " + v.substring(input.selectionEnd)
            });

            input.selectionEnd = s + 4;

        },

    });

    acf.registerFieldType(Textarea);

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: WYSIWYG Overwrite
     */
    var Wysiwyg = acf.models.WysiwygField;

    acf.models.WysiwygField = Wysiwyg.extend({

        initialize: function() {

            // initialize Editor if no delay and not already initialized
            if (!this.has('id') && !this.$control().hasClass('delay')) {
                this.initializeEditor();
            }

        }

    });

    /*
     * Field: WYSIWYG
     */
    new acf.Model({

        actions: {
            'show_field/type=wysiwyg': 'showField',
            'ready_field/type=wysiwyg': 'showField',
        },

        showField: function(field) {

            if (!field.has('acfeWysiwygAutoInit') || !field.$el.is(':visible') || field.has('id') || acfe.isFilterEnabled('acfeFlexibleOpen')) {
                return;
            }

            this.initializeEditor(field);

        },

        initializeEditor: function(field) {

            var $wrap = field.$control();

            if ($wrap.hasClass('delay')) {

                $wrap.removeClass('delay');
                $wrap.find('.acf-editor-toolbar').remove();

                // initialize
                field.initializeEditor();

            }

        },

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Checkbox & Radio
     */
    acf.registerConditionForFieldType('contains', 'checkbox');
    acf.registerConditionForFieldType('contains', 'radio');

    /*
     * Code Editor
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_code_editor');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_code_editor');
    acf.registerConditionForFieldType('patternMatch', 'acfe_code_editor');
    acf.registerConditionForFieldType('contains', 'acfe_code_editor');
    acf.registerConditionForFieldType('hasValue', 'acfe_code_editor');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_code_editor');

    /*
     * Date Picker
     */
    acf.registerConditionForFieldType('equalTo', 'date_picker');
    acf.registerConditionForFieldType('notEqualTo', 'date_picker');
    acf.registerConditionForFieldType('patternMatch', 'date_picker');
    acf.registerConditionForFieldType('contains', 'date_picker');
    acf.registerConditionForFieldType('greaterThan', 'date_picker');
    acf.registerConditionForFieldType('lessThan', 'date_picker');

    /*
     * Date Time Picker
     */
    acf.registerConditionForFieldType('equalTo', 'date_time_picker');
    acf.registerConditionForFieldType('notEqualTo', 'date_time_picker');
    acf.registerConditionForFieldType('patternMatch', 'date_time_picker');
    acf.registerConditionForFieldType('contains', 'date_time_picker');

    /*
     * Forms
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_forms');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_forms');
    acf.registerConditionForFieldType('patternMatch', 'acfe_forms');
    acf.registerConditionForFieldType('contains', 'acfe_forms');
    acf.registerConditionForFieldType('hasValue', 'acfe_forms');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_forms');

    /*
     * Hidden
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_hidden');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_hidden');
    acf.registerConditionForFieldType('patternMatch', 'acfe_hidden');
    acf.registerConditionForFieldType('contains', 'acfe_hidden');
    acf.registerConditionForFieldType('hasValue', 'acfe_hidden');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_hidden');

    /*
     * Post Status
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_post_statuses');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_post_statuses');
    acf.registerConditionForFieldType('patternMatch', 'acfe_post_statuses');
    acf.registerConditionForFieldType('contains', 'acfe_post_statuses');
    acf.registerConditionForFieldType('hasValue', 'acfe_post_statuses');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_post_statuses');

    /*
     * Post Types
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_post_types');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_post_types');
    acf.registerConditionForFieldType('patternMatch', 'acfe_post_types');
    acf.registerConditionForFieldType('contains', 'acfe_post_types');
    acf.registerConditionForFieldType('hasValue', 'acfe_post_types');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_post_types');

    /*
     * Slug
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_slug');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_slug');
    acf.registerConditionForFieldType('patternMatch', 'acfe_slug');
    acf.registerConditionForFieldType('contains', 'acfe_slug');
    acf.registerConditionForFieldType('hasValue', 'acfe_slug');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_slug');

    /*
     * Taxonomies
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_taxonomies');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_taxonomies');
    acf.registerConditionForFieldType('patternMatch', 'acfe_taxonomies');
    acf.registerConditionForFieldType('contains', 'acfe_taxonomies');
    acf.registerConditionForFieldType('hasValue', 'acfe_taxonomies');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_taxonomies');

    /*
     * Taxonomy
     */
    acf.registerConditionForFieldType('equalTo', 'taxonomy');
    acf.registerConditionForFieldType('notEqualTo', 'taxonomy');
    acf.registerConditionForFieldType('patternMatch', 'taxonomy');
    acf.registerConditionForFieldType('contains', 'taxonomy');
    acf.registerConditionForFieldType('hasValue', 'taxonomy');
    acf.registerConditionForFieldType('hasNoValue', 'taxonomy');

    /*
     * Taxonomy Terms
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('patternMatch', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('contains', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('hasValue', 'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_taxonomy_terms');

    /*
     * Time Picker
     */
    acf.registerConditionForFieldType('equalTo', 'time_picker');
    acf.registerConditionForFieldType('notEqualTo', 'time_picker');
    acf.registerConditionForFieldType('patternMatch', 'time_picker');
    acf.registerConditionForFieldType('contains', 'time_picker');

    /*
     * User Roles
     */
    acf.registerConditionForFieldType('equalTo', 'acfe_user_roles');
    acf.registerConditionForFieldType('notEqualTo', 'acfe_user_roles');
    acf.registerConditionForFieldType('patternMatch', 'acfe_user_roles');
    acf.registerConditionForFieldType('contains', 'acfe_user_roles');
    acf.registerConditionForFieldType('hasValue', 'acfe_user_roles');
    acf.registerConditionForFieldType('hasNoValue', 'acfe_user_roles');

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Field: Instructions
     */
    new acf.Model({

        field: false,
        placement: false,

        actions: {
            'new_field': 'newField',
        },

        newField: function(field) {

            this.field = field;

            if (field.has('instructionTooltip')) {
                this.setTooltip();
            }

            if (field.has('instructionAboveField')) {
                this.setAboveField();
            }

            if (field.has('instructionPlacement')) {
                this.overridePlacement(field.get('instructionPlacement'));
            }

        },

        setTooltip: function() {

            this.field.$labelWrap().prepend('<span class="acfe-field-tooltip acf-js-tooltip dashicons dashicons-info" title="' + _.escape(this.field.get('instructionTooltip')) + '"></span>');
            this.field.$labelWrap().find('.description').remove();

        },

        setAboveField: function() {

            this.field.$inputWrap().prepend('<p class="description">' + this.field.get('instructionAboveField') + '</p>');
            this.field.$labelWrap().find('.description').remove();

        },

        overridePlacement: function(target) {

            var current = this.getPlacement();

            // No instruction
            if (!current)
                return;

            // Placement is correct
            if (current === target)
                return;

            this.setPlacement(target);

        },

        getPlacement: function() {

            var placement = false;

            if (this.field.$labelWrap().find('>.description').length)
                placement = 'label';

            else if (this.field.$inputWrap().find('>.description:first-child').length)
                placement = 'above_field';

            else if (this.field.$inputWrap().find('>.description:last-child').length)
                placement = 'field';

            else if (this.field.$labelWrap().find('>.acfe-field-tooltip').length)
                placement = 'tooltip';

            this.placement = placement;

            return this.placement;

        },

        $getInstruction: function() {

            var placement = this.getPlacement();

            if (placement === 'label') {

                return this.field.$labelWrap().find('>.description');

            } else if (placement === 'above_field') {

                return this.field.$inputWrap().find('>.description:first-child');

            } else if (placement === 'field') {

                return this.field.$inputWrap().find('>.description:last-child');

            } else if (placement === 'tooltip') {

                return this.field.$labelWrap().find('>.acfe-field-tooltip');

            }

            return false;

        },

        setPlacement: function(target) {

            var $instruction = this.$getInstruction();

            if (this.placement === 'tooltip') {

                var text = $instruction.attr('title');

                $instruction.remove();
                $instruction = $('<p class="description">' + text + '</p>');

            }

            if (target === 'label') {

                this.field.$labelWrap().append($instruction);

            } else if (target === 'above_field') {

                this.field.$inputWrap().prepend($instruction);

            } else if (target === 'field') {

                this.field.$inputWrap().append($instruction);

            } else if (target === 'tooltip') {

                this.field.$labelWrap().prepend($('<span class="acfe-field-tooltip acf-js-tooltip dashicons dashicons-info" title="' + _.escape($instruction.html()) + '"></span>'));
                $instruction.remove();

            }

        }

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * Proxy
     */
    /*new acf.Model({

        actions: {
            'new_field': 'field',
        },

        field: function(field){

            // bail early
            if(!field.has('proxyType')) return;

            // vars
            var type = field.get('type');
            var proxyType = field.get('proxyType');

            // set new $el
            field.$el.data('type', proxyType);
            field.$el.attr('data-type', proxyType);

            // remove proxy type
            field.$el.removeData('proxy-type');
            field.$el.removeAttr('data-proxy-type');

            // save original field & cleanup
            //field.$el.removeData('acf');

            // init
            acf.newField(field.$el);

        },

        newField: function( $field ){

            // vars
            var type = $field.data('type');
            var mid = modelId( type );
            var model = acf.models[ mid ] || acf.Field;

            // instantiate
            var field = new model( $field );

            // actions
            acf.doAction('new_field', field);

            // return
            return field;

        },

    });*/

})(jQuery);
(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * ACFE Form
     */
    new acf.Model({

        actions: {
            'prepare': 'prepare',
            'new_field/type=date_picker': 'datePicker',
            'new_field/type=date_time_picker': 'datePicker',
            'new_field/type=time_picker': 'datePicker',
            'new_field/type=google_map': 'googleMap',
            'invalid_field': 'invalidField',
            'validation_begin': 'validationBegin',
        },

        prepare: function() {

            if (acfe.get('is_admin')) return;

            // Fix Image/File WP Media upload
            if (acf.isset(window, 'wp', 'media', 'view', 'settings', 'post')) {

                // Unset Post ID
                wp.media.view.settings.post = false;

            }

            if ($('.acfe-form[data-hide-unload="1"]').length) {
                acf.unload.disable();
            }

            var $form_success = $('.acfe-form-success');

            if ($form_success.length) {

                // Prevent refresh sending post fields again
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }

                $form_success.each(function() {

                    var form_name = $(this).data('form-name');
                    var form_id = $(this).data('form-id');

                    acf.doAction('acfe/form/submit/success');
                    acf.doAction('acfe/form/submit/success/id=' + form_id);
                    acf.doAction('acfe/form/submit/success/name=' + form_name);

                });

            }

            // Prevent form submission click spam
            $('.acf-form .button, .acf-form [type="submit"], .acfe-form .button, .acfe-form [type="submit"]').click(function(e) {

                if (!$(this).hasClass('disabled')) return;

                e.preventDefault();

            });

        },

        // Datepicker: Add field class
        datePicker: function(field) {

            if (acfe.get('is_admin')) return;

            var $form = field.$el.closest('.acfe-form');

            if (!$form.length) return;

            var field_class = $form.data('fields-class');

            if (field_class) {
                field.$inputText().addClass(field_class);
            }

        },

        // Google Maps: Add field class
        googleMap: function(field) {

            if (acfe.get('is_admin')) return;

            var $form = field.$el.closest('.acfe-form');

            if (!$form.length) return;

            var field_class = $form.data('fields-class');

            if (field_class) {
                field.$search().addClass(field_class);
            }

        },

        // Error: Move error
        invalidField: function(field) {

            if (acfe.get('is_admin')) return;

            var $form = field.$el.closest('.acfe-form');

            if (!$form.length) return;

            var errors_position = $form.data('errors-position');
            var errors_class = $form.data('errors-class');

            // Class
            if (errors_class && errors_class.length) {
                field.$el.find('.acf-notice.-error').addClass(errors_class);
            }

            // Move below
            if (errors_position && errors_position === 'below') {

                if (field.$control().length) {

                    field.$el.find('.acf-notice.-error').insertAfter(field.$control());

                } else if (field.$input().length) {

                    field.$el.find('.acf-notice.-error').insertAfter(field.$input());

                }

                var $selector = false;

                if (field.$control().length) {

                    $selector = field.$control();

                } else if (field.$input().length) {

                    $selector = field.$input();

                }

                if ($selector) {
                    field.$el.find('.acf-notice.-error').insertAfter($selector);
                }

            }

            // Group errors
            else if (errors_position && errors_position === 'group') {

                var label = field.$el.find('.acf-label label').text().trim();
                var placeholder = field.$el.find('.acf-input-wrap [placeholder!=""]').attr('placeholder');
                var message = field.$el.find('.acf-notice.-error').text().trim();

                field.$el.find('.acf-notice.-error').remove();

                // Try label
                if (label && label.length && label !== '*') {
                    message = label + ': ' + message;
                }

                // Try placeholder
                else if (placeholder && placeholder.length && placeholder !== '') {
                    message = placeholder + ': ' + message;
                }

                // If everything fails, use field name
                else {
                    message = field.get('name') + ': ' + message;
                }

                var $form_error = $form.find('> .acfe-form-error')

                if (!$form_error.length) {
                    $form_error = $('<div class="acf-notice -error acf-error-message acfe-form-error" />').prependTo($form);
                }

                $form_error.append('<p>' + message + '</p>');

            }

            // Hide errors
            else if (errors_position && errors_position === 'hide') {
                field.$el.find('.acf-notice.-error').remove();
            }

        },

        // Ajax Validation
        validationBegin: function($form) {

            if (acfe.get('is_admin')) return;

            if (typeof $form === 'undefined') return;

            $form.find('.acf-error-message').remove();

        }

    });

    // ACF 5.11.4
    var ensureFieldPostBoxIsVisible = function($el) {
        // Find the postbox element containing this field.
        var $postbox = $el.parents('.acf-postbox');

        if ($postbox.length) {
            var acf_postbox = acf.getPostbox($postbox);

            // ACFE: use class check instead of isHiddenByScreenOptions() for older ACF versions
            if (acf_postbox && (acf_postbox.$el.hasClass('hide-if-js') || acf_postbox.$el.css('display') == 'none')) {
                // Rather than using .show() here, we don't want the field to appear next reload.
                // So just temporarily show the field group so validation can complete.
                acf_postbox.$el.removeClass('hide-if-js');
                acf_postbox.$el.css('display', '');
            }
        }
    };

    // ACF 5.11.4
    // added current element as argument
    var ensureInvalidFieldVisibility = function($el) {

        var $inputs = $('.acf-field input');

        // retrieve the current element parents form
        var $form = $el.closest('form');

        // find fields inside the current form only
        if ($form.length) {
            $inputs = $form.find('.acf-field input');
        }

        $inputs.each(function() {
            if (!this.checkValidity()) {
                ensureFieldPostBoxIsVisible($(this));
            }
        });

    };

    // ACF 5.11
    // Fix double front-end form that trigger validation for the other form
    acf.validation.onClickSubmit = function(e, $el) {

        ensureInvalidFieldVisibility($el);

        this.set('originalEvent', e);

    }

    // Rewrite ACF New Condition
    // Allow conditions to work within wrapped div
    acf.newCondition = function(rule, conditions) {

        // currently setting up conditions for fieldX, this field is the 'target'
        var target = conditions.get('field');

        // use the 'target' to find the 'trigger' field.
        // - this field is used to setup the conditional logic events
        var field = target.getField(rule.field);

        // ACF Extended: Check in all form if targeted field not found
        if (target && !field) {

            field = acf.getField(rule.field);

        }

        // bail ealry if no target or no field (possible if field doesn't exist due to HTML error)
        if (!target || !field) {
            return false;
        }

        // vars
        var args = {
            rule: rule,
            target: target,
            conditions: conditions,
            field: field
        };

        // vars
        var fieldType = field.get('type');
        var operator = rule.operator;

        // get avaibale conditions
        var conditionTypes = acf.getConditionTypes({
            fieldType: fieldType,
            operator: operator,
        });

        // instantiate
        var model = conditionTypes[0] || acf.Condition;

        // instantiate
        var condition = new model(args);

        // return
        return condition;

    };

})(jQuery);