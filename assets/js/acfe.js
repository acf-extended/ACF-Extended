(function($) {

    if (typeof acf === 'undefined') {
        return;
    }

    /**
     * acfe
     *
     * @type {{}}
     */
    window.acfe = {};

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.getArray
     *
     * Forces val as an array, also allows integer 0
     *
     * @param val
     * @returns {*[]}
     */
    acfe.getArray = function(val) {
        val = val === 0 ? '0' : val;
        return [].concat(val || []);
    };


    /**
     * acfe.getObject
     *
     * Forces val as an object
     *
     * @param val
     * @returns {*[]}
     */
    acfe.getObject = function(val) {
        return acf.parseArgs(val);
    };


    /**
     * acfe.extractVar
     *
     * Extracts a value from an object
     *
     * @param obj
     * @param prop
     * @param def
     * @returns {null|*}
     */
    acfe.extractVar = function(obj, prop, def = null) {

        var value = obj[prop];
        delete obj[prop];

        if (acfe.isUndefined(value)) {
            return def;
        }

        return value;

    }


    /**
     * acfe.extractVars
     *
     * Extracts multiple values from an object
     *
     * @param obj
     * @param props
     * @returns {{}}
     */
    acfe.extractVars = function(obj, ...props) {

        var value = {};
        for (var prop of props) {

            var extract = acfe.extractVar(obj, prop, '!!undefined!!');

            if (extract !== '!!undefined!!') {
                value[prop] = extract;
            }

        }

        return value;

    }


    /**
     * acfe.getEntries
     *
     * Get object entries to use in for(var [x, y] of obj){}
     *
     * @param obj
     * @returns {[string, unknown][]}
     */
    acfe.getEntries = function(obj) {
        return Object.entries(obj);
    }


    /**
     * acfe.inArray
     *
     * @param v1
     * @param array
     * @param strict
     * @returns {boolean}
     */
    acfe.inArray = function(v1, array, strict = false) {

        if (!strict) {

            v1 = acfe.getString(v1);
            array = array.map(function(v2) {
                return acfe.getString(v2);
            });

        }

        for (var i = 0; i < array.length; i++) {
            if (array[i] === v1) {
                return true;
            }
        }

        return false;

    };


    /**
     * acfe.arrayGet
     *
     * Get array/object value using dot notation
     *
     * https://github.com/callmecavs/dotnot/blob/master/src/dotnot.js
     *
     * @param obj
     * @param path
     * @param def
     * @returns {null|*}
     */
    acfe.arrayGet = function(obj, path, def = null) {

        // get path array
        path = acfe.normalizePath(path);

        // length of path array
        var len = path.length;

        // loop through path updating the reference to child objects
        var current = obj;
        var name;

        for (var index = 0; index < len; index++) {

            // current key name
            name = path[index];

            // stop searching if a child object is missing
            if (acfe.isUndefined(current[name])) {
                return def;
            }

            current = current[name];
        }

        return current;

    }


    /**
     * acfe.arrayHas
     *
     * Check array/object has key using dot notation
     * '!!undefined!!' is workaround allowing 'null' to be considered as a valid value
     *
     * @param obj
     * @param path
     * @returns {*}
     */
    acfe.arrayHas = function(obj, path) {
        return acfe.arrayGet(obj, path, '!!undefined!!') !== '!!undefined!!';
    }


    /**
     * acfe.arraySet
     *
     * Set array/object value using dot notation
     *
     * https://github.com/callmecavs/dotnot/blob/master/src/dotnot.js
     *
     * @param obj
     * @param path
     * @param val
     */
    acfe.arraySet = function(obj, path, val) {

        // get path array
        path = acfe.normalizePath(path);

        // length of path array
        var len = path.length;

        // loop through path updating the reference to child objects
        var current = obj;
        var name;

        for (var index = 0; index < len; index++) {

            // current key name
            name = path[index];

            // set value on last key
            if (index === len - 1) {
                current[name] = val;

            } else if (current[name]) {

                if (!acfe.isObject(current[name])) {
                    current[name] = {};
                }

                current = current[name];

            } else {
                current[name] = {};
                current = current[name];

            }

        }

        // re-index array
        obj = acfe.arrayReindex(obj);

        // return
        return obj;

    }


    /**
     * acfe.arrayDelete
     *
     * Delete array/object key via dot notation
     *
     * https://github.com/callmecavs/dotnot/blob/master/src/dotnot.js
     *
     * @param obj
     * @param path
     * @returns {*}
     */
    acfe.arrayDelete = function(obj, path) {

        // get path array
        path = acfe.normalizePath(path);

        // length of path array
        var len = path.length;

        // loop through path updating the reference to child objects
        var current = obj;
        var name;

        for (var index = 0; index < len; index++) {

            // current key name
            name = path[index];

            // set value on last key
            if (index === len - 1) {
                delete current[name];
            } else {
                current = current[name] || {};
            }

        }

        // re-index array
        obj = acfe.arrayReindex(obj);

        // return
        return obj;

    }


    /**
     * acfe.arrayPluck
     *
     * '!!undefined!!' is workaround allowing 'null' to be considered as a valid value
     *
     * @param obj
     * @param path
     * @param id
     * @returns {*[]}
     */
    acfe.arrayPluck = function(obj, path, id = false) {

        // vars
        var idPath;
        var idObj = {};
        var collect = [];

        // prepare id
        if (id) {

            idPath = path.split('.');
            idPath.pop();
            idPath.push(id);
            idPath = idPath.join('.');

        }

        // loop
        for (var row of obj) {

            var result = acfe.arrayGet(row, path, '!!undefined!!');

            // bail early
            if (result === '!!undefined!!') {
                continue;
            }

            // push to collection
            collect.push(result);

            if (id) {
                var key = acfe.arrayGet(row, idPath, '!!undefined!!');

                if (key !== '!!undefined!!') {
                    idObj[key] = result;
                }
            }

        }

        // collect id
        if (id) {
            collect = idObj;
        }

        // return
        return collect;

    }


    /**
     * acfe.normalizePath
     *
     * Converts a dot notation path as array
     *
     * @param path
     * @returns {*[]|string[]}
     */
    acfe.normalizePath = function(path) {

        // convert to string
        path = acfe.getString(path);

        // split dot notation
        path = path.split('.');

        // return
        return path;

    }


    /**
     * acfe.arrayReindex
     *
     * Re-index array after delete usage
     *
     * @param obj
     * @returns {*}
     */
    acfe.arrayReindex = function(obj) {

        // filter
        if (acfe.isArray(obj)) {
            obj = obj.filter(function(val) {
                return !acfe.isUndefined(val);
            });
        }

        // return
        return obj;

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }


    /**
     * acfe.isACF65
     *
     * Check if ACF version is 6.5+
     *
     * @returns {boolean|number}
     */
    acfe.isACF65 = function() {
        return acfe.versionCompare(acf.get('acf_version'), '>=', '6.5');
    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acf.data.acfe
     *
     * @type {{}}
     */
    acf.data.acfe = {};


    /**
     * acfe.get
     *
     * @param name
     * @param def
     * @returns {*|null}
     */
    acfe.get = function(name = null, def = null) {
        return name === null ? acf.data.acfe : acfe.arrayGet(acf.data.acfe, name, def);
    };


    /**
     * acfe.has
     *
     * @param name
     * @returns {boolean}
     */
    acfe.has = function(name) {
        return acfe.arrayGet(acf.data.acfe, name) !== null;
    };


    /**
     * acfe.set
     *
     * @param name
     * @param value
     * @returns {acfe}
     */
    acfe.set = function(name, value) {
        acfe.arraySet(acf.data.acfe, name, value);
        return this;
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.deprecatedFunction
     *
     * @param fnc
     * @param version
     * @param replacement
     */
    acfe.deprecatedFunction = function(fnc, version, replacement = '') {

        replacement = replacement ? '! Use ' + replacement + ' instead.' : ' with no alternative available.';

        console.log('ACF Extended: ' + fnc + ' is deprecated since version ' + version + replacement);

    }


    /**
     * acfe.deprecatedHook
     *
     * @param hook
     * @param version
     * @param replacement
     */
    acfe.deprecatedHook = function(hook, version, replacement = '') {

        replacement = replacement ? '! Use ' + replacement + ' instead.' : ' with no alternative available.';

        console.log('ACF Extended: Hook ' + hook + ' is deprecated since version ' + version + replacement);

    }


    /**
     * acfe.applyFiltersDeprecated
     *
     * @param hook
     * @param args
     * @param version
     * @param replacement
     * @returns {*}
     */
    acfe.applyFiltersDeprecated = function(hook, args, version, replacement = '') {

        // bail early
        if (!acfe.hasFilter(hook)) {
            return args[0];
        }

        // log deprecated
        acfe.deprecatedHook(hook, version, replacement);

        // appply filters
        return acf.applyFilters(hook, ...args);

    }


    /**
     * acfe.doActionDeprecated
     *
     * @param hook
     * @param args
     * @param version
     * @param replacement
     * @returns {*}
     */
    acfe.doActionDeprecated = function(hook, args, version, replacement = '') {

        // bail early
        if (!acfe.hasAction(hook)) {
            return;
        }

        // log deprecated
        acfe.deprecatedHook(hook, version, replacement);

        // appply filters
        return acf.doAction(hook, ...args);

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * isFieldKey
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isFieldKey = function(name) {
        return acfe.isString(name) && name.startsWith('field_');
    }


    /**
     * acfe.isFieldName
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isFieldName = function(name) {
        return acfe.isString(name) && !acfe.isFieldKey(name);
    }


    /**
     * isGroupKey
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isGroupKey = function(name) {
        return acfe.isString(name) && name.startsWith('group_');
    }


    /**
     * acfe.getField
     *
     * Allow to query a single field with name, or arguments
     *
     * @param name
     * @returns {[]|*}
     */
    acfe.getField = function(name) {

        // field name
        if (acfe.isFieldName(name)) {

            return acf.getFields({
                name: name,
                limit: 1,
                suppressFilters: true
            }).shift();

            // arguments
        } else if (acfe.isObject(name)) {

            name = acf.parseArgs(name, {
                limit: 1,
                suppressFilters: true
            });

            return acf.getFields(name).shift();

        }

        // default getField
        return acf.getField(name);

    };


    /**
     * find_fields_selector
     *
     * Allows to use multiple field types (array) as argument
     *
     * Available in ACF 6.0.
     */
    acf.addFilter('find_fields_selector', function(selector, args) {

        // args.types
        // allow types array
        if (args.types && args.types.length && acfe.isArray(args.types)) {

            // vars
            var array = [];

            // loop
            for (var type of args.types) {
                array.push(selector + '[data-type="' + type + '"]');
            }

            selector = array.join(',');

        }

        // return
        return selector;

    });

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * storage
     *
     * @type {*[]}
     */
    var filters = [];


    /**
     * acfe.disableFilters
     */
    acfe.disableFilters = function() {
        filters = [];
    };


    /**
     * acfe.getFilters
     *
     * @returns {*[]}
     */
    acfe.getFilters = function() {
        return filters;
    };


    /**
     * acfe.isFilterEnabled
     *
     * @param name
     * @returns {boolean}
     */
    acfe.isFilterEnabled = function(name) {
        return filters.indexOf(name) > -1;
    };


    /**
     * acfe.enableFilter
     *
     * @param name
     */
    acfe.enableFilter = function(name) {
        if (filters.indexOf(name) === -1) {
            filters.push(name);
        }
    };


    /**
     * acfe.disableFilter
     *
     * @param name
     */
    acfe.disableFilter = function(name) {
        for (var i = filters.length; i--;) {
            if (filters[i] === name) {
                filters.splice(i, 1);
            }
        }
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.findSubmitWrap
     *
     * @param $form
     * @returns {{length}|*|jQuery|HTMLElement}
     */
    acfe.findSubmitWrap = function($form) {

        var $wrap;
        $form = $form || $('form');

        // default post submit div
        $wrap = $form.find('#submitdiv');
        if ($wrap.length) {
            return $wrap;
        }

        // 3rd party publish box
        $wrap = $form.find('#submitpost');
        if ($wrap.length) {
            return $wrap;
        }

        // term, user
        $wrap = $form.find('p.submit').last();
        if ($wrap.length) {
            return $wrap;
        }

        // front end form
        $wrap = $form.find('.acf-form-submit');
        if ($wrap.length) {
            return $wrap;
        }

        // default
        return $form;

    };


    /**
     * acfe.findSubmit
     *
     * @param $form
     * @returns {*|jQuery}
     */
    acfe.findSubmit = function($form) {

        $form = $form || $('form');
        return this.findSubmitWrap($form).find('.button, [type="submit"]');

    }


    /**
     * acfe.findSpinner
     *
     * @param $form
     * @returns {*|jQuery}
     */
    acfe.findSpinner = function($form) {

        $form = $form || $('form');
        return this.findSubmitWrap($form).find('.spinner, .acf-spinner');

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.hasFilter
     *
     * @param hook
     * @param callback
     * @returns {boolean}
     */
    acfe.hasFilter = function(hook, callback = false) {
        return acfe.hasHook('filters', hook, callback);
    }


    /**
     * acfe.hasAction
     *
     * @param hook
     * @param callback
     * @returns {boolean}
     */
    acfe.hasAction = function(hook, callback = false) {
        return acfe.hasHook('actions', hook, callback);
    }


    /**
     * acfe.hasHook
     *
     * @param type
     * @param hook
     * @param callback
     * @returns {boolean}
     */
    acfe.hasHook = function(type, hook, callback = false) {

        // get hooks storage
        var storage = acf.hooks.storage();

        // no hooks
        if (!acfe.arrayGet(storage[type], hook)) {
            return false;
        }

        // no callback specified
        if (!callback) {
            return true;
        }

        // loop on hooks
        for (var row of storage[type][hook]) {

            // callback name found
            if (acfe.arrayGet(row, 'callback.name') === callback) {
                return true;
            }

        }

        return false;

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * modal initializer
     *
     * @type {string[]}
     */
    var actions = ['prepare', 'ready', 'load', 'append', 'remove', 'unmount', 'remount', 'sortstart', 'sortstop', 'show', 'hide', 'unload'];

    actions.map(function(action) {

        acf.addAction(action, function($el) {

            // initialize modals
            acfe.getModals({
                parent: $el
            });

        });

    });


    /**
     * acfe.getModals
     *
     * @param $modals
     * @returns {*[]}
     */
    acfe.getModals = function($modals) {

        // jquery element allowed
        // if not jquery perform query
        if (!acfe.isJquery($modals)) {
            $modals = acfe.findModals($modals);
        }

        // loop
        var modals = [];
        $modals.each(function() {
            var modal = acfe.getModal($(this));
            modals.push(modal);
        });

        // return
        return modals;

    }


    /**
     * acfe.getModal
     *
     * @param $modal
     * @param args
     * @returns {boolean|*}
     */
    acfe.getModal = function($modal, args) {

        // args query
        if (!acfe.isJquery($modal)) {
            $modal = acfe.findModal($modal);
        }

        // no modal found
        // check class just in case of unrelated element
        if (!$modal.length || !$modal.hasClass('acfe-modal')) {
            return false;
        }

        // return
        return acfe.newModal($modal, args);

    };


    /**
     * acfe.findModals
     *
     * @param args
     * @returns {*}
     */
    acfe.findModals = function(args) {

        // vars
        var selector = '.acfe-modal';
        var $modals = false;

        // args
        args = acf.parseArgs(args, {
            modal: '', // The modal's name (data-attribute).
            is: '', // jQuery selector to compare against.
            parent: false, // jQuery element to search within.
            sibling: false, // jQuery element to search alongside.
            limit: false, // The number of modals to find.
            open: false, // Whether to only return open modals.
            close: false, // Whether to only return open modals.
            suppressFilters: false, // Whether to allow filters to add/remove results. Default behaviour will ignore clone fields.
        });

        // modal
        if (args.modal) {
            selector += '[data-modal="' + args.modal + '"]';
        }

        // is
        if (args.is) {
            selector += args.is;
        }

        // open
        if (args.open) {
            selector += ':visible';
        }

        // close
        if (args.close) {
            selector += ':hidden';
        }

        // query
        if (args.parent) {
            $modals = args.parent.find(selector);
        } else if (args.sibling) {
            $modals = args.sibling.siblings(selector);
        } else {
            $modals = $(selector);
        }

        // filter
        if (!args.suppressFilters) {
            $modals = $modals.not('.acf-clone .acfe-modal');
        }

        // limit
        if (args.limit) {
            $modals = $modals.slice(0, args.limit);
        }

        // return
        return $modals;

    };


    /**
     * acfe.findModal
     *
     * @param modal
     * @param $parent
     * @returns {*}
     */
    acfe.findModal = function(modal, $parent) {

        // todo: allow args object to be passed to allow get opened/closed modals
        return acfe.findModals({
            modal: modal,
            limit: 1,
            parent: $parent,
        });
    };


    /**
     * acfe.findClosestModal
     *
     * @param $el
     * @returns {*|jQuery|boolean}
     */
    acfe.findClosestModal = function($el) {

        // move up through each parent and try again
        for (var parent of $el.parents()) {

            var $parent = $(parent);
            var $modal = $parent.find('>.acfe-modal');

            if ($modal.length) {
                return $modal;
            }

        }

        return false;

    };


    /**
     * acfe.getClosestModal
     *
     * @param $el
     * @param args
     * @returns {boolean|*|jQuery}
     */
    acfe.getClosestModal = function($el, args) {
        var $modal = acfe.findClosestModal($el);

        if (!$modal.length) {
            return false;
        }

        return this.getModal($modal, args);
    };


    /**
     * acfe.newModal
     *
     * @param $modal
     * @param args
     * @returns {*}
     */
    acfe.newModal = function($modal, args) {

        // jquery object
        if (acfe.isJquery($modal)) {

            var modal = $modal.data('acf');
            if (modal) {
                return modal.update(args);
            }

            // instantiate
            modal = new acfe.Modal($modal, args);

            // actions
            acf.doAction('acfe/new_modal', modal);
            acfe.doActionDeprecated('new_modal', [modal], '0.9.0.5', 'acfe/new_modal');

            // return
            return modal;

        }

        // default
        args = acf.parseArgs($modal, {
            open: true
        });

        // instantiate
        modal = new acfe.Modal(args);

        // actions
        acf.doAction('acfe/new_modal', modal);
        acfe.doActionDeprecated('new_modal', [modal], '0.9.0.5', 'acfe/new_modal');

        // return
        return modal;

    }


    /**
     * acfe.Modal
     */
    acfe.Modal = acf.Model.extend({

        data: {},
        modal: '', // modal id
        title: '',
        content: '',
        footer: '',
        class: '',
        size: 'medium',
        width: 0,
        open: false,
        destroy: false,

        eventScope: '.acfe-modal',

        onOpen: function() {},
        onClose: function() {},

        setup: function($el, args) {

            // default case: 2 arguments
            // acfe.newModal($el, {title: 'Hello World'})

            // case: only one argument
            // acfe.newModal({title: 'Hello World'}) > create jquery element
            if (!acfe.isJquery($el)) {
                args = $el;
                $el = $('<div class="acfe-modal"></div>').appendTo('body'); // append modal to body
            }

            // set $el
            // inherit: <div class="acfe-modal" data-title="Hello World" data-size="large"></div>
            this.$el = $el;
            $.extend(this, $el.data());

            // cast args as object
            // inherit from args
            args = acfe.getObject(args);
            $.extend(this, args);

            // render
            this.prepareRender();
            this.render();

        },

        update: function(args) {

            // bail early
            if (typeof args === 'undefined') {
                return this;
            }

            // cast as object
            args = acfe.getObject(args);
            $.extend(this, args);

            // render
            this.render();

            if (this.open) {
                this.show();
            }

            // allow chaining
            return this;

        },

        initialize: function() {

            // action
            acf.doAction(`acfe/modal/init`, this);
            acf.doAction(`acfe/modal/init/id=${this.modal}`, this);

            if (this.open) {
                this.show();
            }

            // add custom events
            this.addEvents({
                'click .close': 'onClickClose',
            });

        },

        on: function() {

            acf.Model.prototype.on.apply(this, arguments);

            // allow chaining
            return this;

        },

        $wrapper: function() {
            return this.$('> .acfe-modal-wrapper');
        },

        $title: function(val) {
            return !acfe.isUndefined(val) ? this.$('> .acfe-modal-wrapper > .acfe-modal-title > .title').html(val) : this.$('> .acfe-modal-wrapper > .acfe-modal-title');
        },

        $content: function(val) {
            return !acfe.isUndefined(val) ? this.$('> .acfe-modal-wrapper > .acfe-modal-content').html(val) : this.$('> .acfe-modal-wrapper > .acfe-modal-content');
        },

        $footer: function(val) {
            return !acfe.isUndefined(val) ? this.$('> .acfe-modal-wrapper > .acfe-modal-footer').html(val) : this.$('> .acfe-modal-wrapper > .acfe-modal-footer');
        },

        prepareRender: function() {

            if (!this.$wrapper().length) {
                this.$el.wrapInner('<div class="acfe-modal-wrapper"></div>');
            }

            if (!this.$content().length) {
                this.$wrapper().wrapInner('<div class="acfe-modal-content"></div>');
            }

        },

        render: function() {

            // render content
            this.renderContent();

            // clear class
            this.$el.removeClass('-medium -large -full');

            if (this.size) {
                this.$el.addClass('-' + this.size);
            }

            if (this.class) {
                this.$el.addClass(this.class);
            }

            if (this.width) {
                this.$wrapper().css('max-width', this.width + 'px');
            }

            // hide tinymce buttons dropdown when scrolling modal
            // fix position issues
            if (typeof tinymce !== 'undefined' && acf.isset(tinymce, 'ui', 'FloatPanel')) {
                this.$content().off('scroll.tinymcePanel').on('scroll.tinymcePanel', function(e) {
                    tinymce.ui.FloatPanel.hideAll();
                });
            }

        },

        renderContent: function() {

            // title
            if (!this.$title().length && this.title) {
                this.$wrapper().prepend('<div class="acfe-modal-title"><span class="title"></span><button class="close"></button></div>');

            } else if (!this.title) {
                this.$title().remove();
            }

            // footer
            if (!this.$footer().length && this.footer) {
                this.$wrapper().append('<div class="acfe-modal-footer"></div>');

            } else if (!this.footer) {
                this.$footer().remove();
            }

            // title
            this.$title(acfe.isFunction(this.title) ? this.title.apply(this) : this.title);

            // content
            if (this.content) {
                this.$content(acfe.isFunction(this.content) ? this.content.apply(this) : this.content);
            }

            // footer
            this.$footer(acfe.isFunction(this.footer) ? this.footer.apply(this) : '<button class="button button-large button-primary close">' + this.footer + '</button>');

        },

        show: function() {

            // add class
            this.$el.addClass('-open');

            // action
            acf.doAction(`acfe/modal/open`, this);
            acf.doAction(`acfe/modal/open/id=${this.modal}`, this);

            // function
            this.onOpen.apply(this);

            // event
            this.trigger('open');

            // property
            this.open = true;

        },

        close: function() {

            // remove style & class
            this.$el.removeAttr('style');
            this.$el.removeClass('-open');

            // action
            acf.doAction(`acfe/modal/close`, this);
            acf.doAction(`acfe/modal/close/id=${this.modal}`, this);

            // function
            this.onClose.apply(this);

            // event
            this.trigger('close');

            // property
            this.open = false;

            // destroy
            if (this.destroy) {
                this.remove();
            }

        },

        onClickClose: function(e, $el) {

            e.preventDefault();
            this.close();

        }

    });


    /**
     * acfe.closeModal
     *
     * @param $el
     * @returns {*}
     */
    acfe.closeModal = function($el) {

        // last modal
        if (acfe.isUndefined($el)) {
            return modalManager.closeLastModal();

            // jQuery element
        } else if (acfe.isJquery($el)) {

            var modal = acfe.getModal($el);
            if (modal) {
                modal.close();
            }

        }

    };


    /**
     * modal manager
     *
     * @type {acf.Model}
     */
    var modalManager = new acf.Model({

        actions: {
            'acfe/modal/open': 'onOpen',
            'acfe/modal/close': 'onClose',
        },

        events: {
            'click .acfe-modal-overlay': 'onClick',
            'keyup': 'onKeyUp',
        },

        getModals: function() {

            return acfe.getModals({
                open: true,
            });

        },

        onOpen: function(modal) {

            this.syncModals();

            var $body = $('body');

            if (!$body.hasClass('acfe-modal-opened')) {
                $body.addClass('acfe-modal-opened').append($('<div class="acfe-modal-overlay" />'));
            }

            // show subfields
            acf.getFields({
                parent: modal.$el,
                visible: true,
            }).map(function(field) {
                acf.doAction('show_field', field, 'group');
            });

        },

        onClose: function(modal) {

            this.syncModals();

            if (!this.getModals().length) {
                $('.acfe-modal-overlay').remove();
                $('body').removeClass('acfe-modal-opened');
            }

        },

        onClick: function(e) {

            e.preventDefault();
            this.closeLastModal();

        },

        onKeyUp: function(e) {
            if (e.keyCode === 27 && $('body').hasClass('acfe-modal-opened')) {
                e.preventDefault();
                this.closeLastModal();
            }
        },

        closeLastModal: function() {

            var modals = this.getModals();

            if (modals.length) {
                modals[modals.length - 1].close();
            }

        },

        syncModals: function() {

            // multiple popups: add css margin
            // add acfe-modal-sub to all modals, except the last one
            this.getModals().map(function(modal, i) {

                // last modal
                if (i === this.getModals().length - 1) {
                    return modal.$el.removeClass('acfe-modal-sub').css('margin-left', '');
                }

                // other popups
                modal.$el.addClass('acfe-modal-sub').css('margin-left', -(500 / (i + 1)));

            }, this);

        }

    });


    /**
     * link manager
     *
     * @type {acf.Model}
     */
    var linkManager = new acf.Model({

        events: {
            'click a[data-modal]': 'onClick',
            'click button[data-modal]': 'onClick',
            'click input[data-modal]': 'onClick',
        },

        onClick: function(e, $el) {

            // prevent default
            e.preventDefault();

            // vars
            var data = $el.data();
            var modal = data.modal ? acfe.getModal(data.modal) : acfe.getClosestModal($el);

            if (modal) {

                modal.update(data);
                modal.show();

            }

        },

    });


    /**
     * acfe.Popup
     *
     * @deprecated
     */
    acfe.Popup = function($modal, args) {

        // notice
        acfe.deprecatedFunction('acfe.Popup', '0.8.8.11', 'acfe.newModal');

        // jquery object
        if (acfe.isJquery($modal)) {

            // default
            args = acf.parseArgs(args, {
                open: true
            });

            return acfe.newModal($modal, args);

        }

        // default
        args = acf.parseArgs($modal, {
            open: true
        });

        return acfe.newModal(args);

    }

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acf.Model.get
     *
     * Extends acf.Model.get to allow dot notation & default value
     *
     * @param name
     * @param def
     * @returns {*|null}
     */
    acf.Model.prototype.get = function(name = null, def = null) {
        return name === null ? this.data : acfe.arrayGet(this.data, name, def);
    };


    /**
     *
     * acf.Model.has
     *
     * Extends acf.Model.has to allow dot notation
     *
     * @param name
     * @returns {boolean}
     */
    acf.Model.prototype.has = function(name) {
        return acfe.arrayGet(this.data, name) !== null;
    };


    /**
     * acf.get
     *
     * Extends acf.get to allow dot notation & default value
     *
     * @param name
     * @param def
     * @returns {*|null}
     */
    acf.get = function(name = null, def = null) {
        return name === null ? this.data : acfe.arrayGet(this.data, name, def);
    };


    /**
     * acf.has
     *
     * Extends acf.has to allow dot notation
     *
     * @param name
     * @returns {boolean}
     */
    acf.has = function(name) {
        return acfe.arrayGet(this.data, name) !== null;
    };


    /**
     *
     * acf.set
     *
     * Extends acf.set to allow dot notation & default value
     *
     * @param name
     * @param value
     * @returns {acf}
     */
    acf.set = function(name, value) {
        acfe.arraySet(this.data, name, value);
        return this;
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acf helpers
     *
     * acf.strReplace()
     * acf.strCamelCase()
     * acf.strPascalCase()
     * acf.strSlugify()
     * acf.strSanitize()
     * acf.strMatch()
     */


    /**
     * acfe.getString
     *
     * Parse value as string, also allows integer 0
     *
     * @param val
     * @returns {string|string}
     */
    acfe.getString = function(val) {

        if (acfe.isObject(val)) {
            return JSON.stringify(val);
        }

        return !acfe.isEmpty(val) ? '' + val : '';
    };


    /**
     * acfe.getTextNode
     *
     * @param $selector
     * @returns {*}
     */
    acfe.getTextNode = function($selector) {

        if ($selector.exists()) {
            for (row of $selector.contents()) {

                var text = $.trim($(row).text());
                if (text) {
                    return text;
                }

            }
        }

        return '';

    };


    /**
     *
     * @param string
     * @returns {string}
     */
    acfe.ucFirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * Tooltip
     *
     * A fixed version of acf-js-tooltip which allow multiple tooltips on different elements with onclick support
     */
    new acf.Model({
        tooltip: false,
        events: {
            'mouseenter .acfe-js-tooltip': 'showTitle',
            'mouseup .acfe-js-tooltip': 'hideTitle',
            'mouseleave .acfe-js-tooltip': 'hideTitle',
            'focus .acfe-js-tooltip': 'showTitle',
            'blur .acfe-js-tooltip': 'hideTitle',
            'keyup .acfe-js-tooltip': 'onKeyUp'
        },
        showTitle: function(e, $el) {

            // vars
            var title = $el.attr('title');

            // bail early if no title
            if (!title) {
                return;
            }

            // clear title to avoid default browser tooltip
            $el.attr('title', '');
            $el.data('acfe-js-tooltip-title', title);

            // esc html
            title = acf.escHtml(title);

            // create
            if (!this.tooltip) {

                this.tooltip = acf.newTooltip({
                    text: title,
                    target: $el
                });

                // update
            } else {

                this.tooltip.update({
                    text: title,
                    target: $el
                });

            }
        },
        hideTitle: function(e, $el) {

            // hide tooltip
            this.tooltip.hide();

            // restore title
            $el.attr('title', $el.data('acfe-js-tooltip-title'));

        },
        onKeyUp: function(e, $el) {
            if (e.key === 'Escape') {
                this.hideTitle(e, $el);
            }
        }
    });


    /**
     * Field Tooltip
     *
     * Toggleable tooltip for fields
     */
    var fieldTooltip = new acf.Model({

        tooltips: {},

        events: {
            'click .acfe-field-tooltip': 'clickTooltip',
        },

        clickTooltip: function(e, $el) {

            // title
            var title = $el.attr('title');
            if (!title) {
                return;
            }

            // get field
            var field = acf.getClosestField($el);
            if (!field) {
                return;
            }

            this.toggle(field, $el, title);

        },

        toggle: function(field, $el, title) {

            // clear title to avoid default browser tooltip
            $el.attr('title', '');

            // open
            if (!this.tooltips[field.cid]) {
                this.open(field, $el, title);

                // close
            } else {
                this.close(field, $el, title);
            }

        },

        open: function(field, $el, title) {

            this.tooltips[field.cid] = acf.newTooltip({
                text: title,
                target: $el
            });

            if (acfe.versionCompare(acf.get('wp_version'), '>=', '5.5')) {
                $el.removeClass('dashicons-info-outline').addClass('dashicons-remove');
            }

        },

        close: function(field, $el, title) {

            // hide tooltip
            this.tooltips[field.cid].hide();

            // restore title
            $el.attr('title', this.tooltips[field.cid].get('text'));

            this.tooltips[field.cid] = false;

            if (acfe.versionCompare(acf.get('wp_version'), '>=', '5.5')) {
                $el.removeClass('dashicons-remove').addClass('dashicons-info-outline');
            }

        }

    });

    new acf.Model({
        actions: {
            'hide_field': 'onHideField',
        },
        onHideField: function(field) {
            if (fieldTooltip.tooltips[field.cid]) {
                fieldTooltip.close(field, field.$el.find('.acfe-field-tooltip:first'));
            }
        }
    })

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.isArray
     *
     * Copy acf.isArray
     *
     * @param a
     * @returns {*}
     */
    acfe.isArray = function(a) {
        return Array.isArray(a);
    }


    /**
     * acfe.isObject
     *
     * Copy acf.isObject enhanced
     *
     * @param a
     * @returns {*}
     */
    acfe.isObject = function(a) {
        return typeof a === 'object' && !acfe.isArray(a);
    }


    /**
     * acfe.isNumeric
     *
     * Copy acf.isNumeric
     *
     * @param a
     * @returns {*}
     */
    acfe.isNumeric = function(a) {
        return !isNaN(parseFloat(a)) && isFinite(a);
    }


    /**
     * acfe.isString
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isString = function(a) {
        return typeof a === 'string';
    }


    /**
     * acfe.isUndefined
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isUndefined = function(a) {
        return typeof a === 'undefined';
    }


    /**
     * acfe.isFunction
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isFunction = function(a) {
        return typeof a === 'function';
    }


    /**
     * acfe.isBool
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isBool = function(a) {
        return typeof a === 'boolean';
    }


    /**
     * acfe.isInt
     *
     * @param a
     * @returns {boolean}
     */
    acfe.isInt = function(a) {
        return Number.isInteger(a);
    }


    /**
     * acfe.isJquery
     *
     * @param $a
     * @returns {boolean}
     */
    acfe.isJquery = function($a) {
        return $a instanceof jQuery
    }


    /**
     * acfe.isEmpty
     *
     * Allows integer 0 to be used
     *
     * @param val
     * @returns {boolean}
     */
    acfe.isEmpty = function(val) {

        // array
        if (acfe.isArray(val)) {
            return !val.length;

            // object
        } else if (acfe.isObject(val)) {
            return val && Object.keys(val).length === 0 && Object.getPrototypeOf(val) === Object.prototype

            // string
        } else if (acfe.isString(val)) {
            return !val.length;

        }

        // integer 0
        return !val && !acfe.isNumeric(val);

    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }

    /**
     * acfe.slugify
     *
     * @param str
     * @returns {string}
     */
    acfe.slugify = function(str) {
        return str.replace(/[\s\./]+/g, '-').replace(/[^\p{L}\p{N}_-]+/gu, '').replace(/-+$/, '').toLowerCase();
    }

    /**
     * acfe.addQueryArgs
     *
     * @returns {string|any|string}
     */
    acfe.addQueryArgs = function() {

        let url = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
        let args = arguments.length > 1 ? arguments[1] : undefined;

        // If no arguments are to be appended, return original URL.
        if (!args || !Object.keys(args).length) {
            return url;
        }

        let baseUrl = url; // Determine whether URL already had query arguments.

        const queryStringIndex = url.indexOf('?');

        if (queryStringIndex !== -1) {
            // Merge into existing query arguments.
            args = Object.assign(acfe.getQueryArgs(url), args); // Change working base URL to omit previous query arguments.

            baseUrl = baseUrl.substr(0, queryStringIndex);
        }

        return baseUrl + '?' + buildQueryString(args);

    };


    /**
     * acfe.getQueryArgs
     *
     * @param url
     * @returns {string}
     */
    acfe.getQueryArgs = function(url) {

        return (getQueryString(url) || '' // Normalize space encoding, accounting for PHP URL encoding
            // corresponding to `application/x-www-form-urlencoded`.
            //
            // See: https://tools.ietf.org/html/rfc1866#section-8.2.1
        ).replace(/\+/g, '%20').split('&').reduce((accumulator, keyValue) => {
            const [key, value = ''] = keyValue.split('=') // Filtering avoids decoding as `undefined` for value, where
                // default is restored in destructuring assignment.
                .filter(Boolean).map(decodeURIComponent);

            if (key) {
                const segments = key.replace(/\]/g, '').split('[');
                setPath(accumulator, segments, value);
            }

            return accumulator;
        }, Object.create(null));

    };


    /**
     * acfe.getFragment
     *
     * getFragment('http://localhost:8080/this/is/a/test?query=true#fragment'); // '#fragment'
     * getFragment('https://wordpress.org#another-fragment?query=true');        // '#another-fragment'
     *
     * @param url
     * @returns {string}
     */
    acfe.getFragment = function(url) {

        const matches = /^\S+?(#[^\s\?]*)/.exec(url);

        if (matches) {
            return matches[1];
        }

        return '';

    };


    /**
     * acfe.getCurrentUrl
     *
     * @returns {*}
     */
    acfe.getCurrentUrl = function() {
        return self.location.href;
    };


    /**
     * acfe.getCurrentPath
     *
     * @returns {string|string|string|*}
     */
    acfe.getCurrentPath = function() {
        return self.location.pathname;
    };


    /**
     * acfe.getCurrentFilename
     *
     * @returns {string}
     */
    acfe.getCurrentFilename = function() {
        return acfe.getFilename(acfe.getCurrentPath());
    };


    /**
     * acfe.getFilename
     *
     * @param path
     * @returns {*}
     */
    acfe.getFilename = function(path) {
        return path.split('/').pop();
    };


    /**
     * buildQueryString
     *
     * wp-includes/js/dist/url.js
     *
     * @param data
     * @returns {string}
     */
    var buildQueryString = function(data) {

        let string = '';
        const stack = Object.entries(data);
        let pair;

        while (pair = stack.shift()) {
            let [key, value] = pair; // Support building deeply nested data, from array or object values.

            const hasNestedData = Array.isArray(value) || value && value.constructor === Object;

            if (hasNestedData) {
                // Push array or object values onto the stack as composed of their
                // original key and nested index or key, retaining order by a
                // combination of Array#reverse and Array#unshift onto the stack.
                const valuePairs = Object.entries(value).reverse();

                for (const [member, memberValue] of valuePairs) {
                    stack.unshift([`${key}[${member}]`, memberValue]);
                }
            } else if (value !== undefined) {
                // Null is treated as special case, equivalent to empty string.
                if (value === null) {
                    value = '';
                }

                string += '&' + [key, value].map(encodeURIComponent).join('=');
            }
        } // Loop will concatenate with leading `&`, but it's only expected for all
        // but the first query parameter. This strips the leading `&`, while still
        // accounting for the case that the string may in-fact be empty.


        return string.substr(1);

    };


    /**
     * getQueryString
     *
     * wp-includes/js/dist/url.js
     *
     * @param url
     * @returns {string}
     */
    var getQueryString = function(url) {
        let query;

        try {
            query = new URL(url, 'http://example.com').search.substring(1);
        } catch (error) {}

        if (query) {
            return query;
        }
    };


    /**
     * setPath
     *
     * wp-includes/js/dist/url.js
     *
     * @param object
     * @param path
     * @param value
     */
    var setPath = function(object, path, value) {

        const length = path.length;
        const lastIndex = length - 1;

        for (let i = 0; i < length; i++) {
            let key = path[i];

            if (!key && Array.isArray(object)) {
                // If key is empty string and next value is array, derive key from
                // the current length of the array.
                key = object.length.toString();
            }

            key = ['__proto__', 'constructor', 'prototype'].includes(key) ? key.toUpperCase() : key; // If the next key in the path is numeric (or empty string), it will be
            // created as an array. Otherwise, it will be created as an object.

            const isNextKeyArrayIndex = !isNaN(Number(path[i + 1]));
            object[key] = i === lastIndex ? // If at end of path, assign the intended value.
                value : // Otherwise, advance to the next object in the path, creating
                // it if it does not yet exist.
                object[key] || (isNextKeyArrayIndex ? [] : {});

            if (Array.isArray(object[key]) && !isNextKeyArrayIndex) {
                // If we current key is non-numeric, but the next value is an
                // array, coerce the value to an object.
                object[key] = {
                    ...object[key]
                };
            } // Update working reference object to the next in the path.


            object = object[key];
        }

    };

})(jQuery);
(function($) {

    if (typeof acf === 'undefined' || typeof acfe === 'undefined') {
        return;
    }


    /**
     * acfe.copyClipboard
     *
     * @param data
     * @param message
     */
    acfe.copyClipboard = function(data, message) {

        // default message
        message = acf.parseArgs(message, {
            auto: acf.__('Data has been copied to your clipboard.'),
            manual: acf.__('Please copy the following data to your clipboard.'),
        });

        // fallback for browsers that don't support navigator.clipboard
        var fallbackCopy = function(data, message) {

            var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:none;position:absolute;" value="" />').appendTo($('body'));
            $input.attr('value', data).select();

            if (document.execCommand('copy')) {
                alert(message.auto);
            } else {
                prompt(message.manual, data);
            }

            $input.remove();

        }

        // navigator clipboard
        if (navigator.clipboard) {

            navigator.clipboard.writeText(data).then(function() {
                alert(message.auto);
                return true;
            }).catch(function() {
                fallbackCopy(data, message);
            });

            // fallback
        } else {
            fallbackCopy(data, message);
        }

    }


    /**
     * acfe.scrollTo
     *
     * Scroll to element, if needed with acf.isInView()
     *
     * @param $el
     * @param scrollTime
     * @constructor
     */
    acfe.scrollTo = function($el, scrollTime = 500) {

        if (!acf.isInView($el)) {
            $('body, html').animate({
                scrollTop: $el.offset().top - $(window).height() / 2
            }, scrollTime);
        }

    }


    /**
     * acfe.versionCompare
     *
     * https://locutus.io/php/info/version_compare/
     *
     * @param v1
     * @param operator
     * @param v2
     * @returns {null|boolean|number}
     */
    acfe.versionCompare = function(v1, operator, v2) {
        let i
        let x
        let compare = 0

        const vm = {
            dev: -6,
            alpha: -5,
            a: -5,
            beta: -4,
            b: -4,
            RC: -3,
            rc: -3,
            '#': -2,
            p: 1,
            pl: 1
        }

        const _prepVersion = function(v) {
            v = ('' + v).replace(/[_\-+]/g, '.')
            v = v.replace(/([^.\d]+)/g, '.$1.').replace(/\.{2,}/g, '.')
            return (!v.length ? [-8] : v.split('.'))
        }

        const _numVersion = function(v) {
            return !v ? 0 : (isNaN(v) ? vm[v] || -7 : parseInt(v, 10))
        }
        v1 = _prepVersion(v1)
        v2 = _prepVersion(v2)
        x = Math.max(v1.length, v2.length)
        for (i = 0; i < x; i++) {
            if (v1[i] === v2[i]) {
                continue
            }
            v1[i] = _numVersion(v1[i])
            v2[i] = _numVersion(v2[i])
            if (v1[i] < v2[i]) {
                compare = -1
                break
            } else if (v1[i] > v2[i]) {
                compare = 1
                break
            }
        }
        if (!operator) {
            return compare
        }

        switch (operator) {
            case '>':
            case 'gt':
                return (compare > 0)
            case '>=':
            case 'ge':
                return (compare >= 0)
            case '<=':
            case 'le':
                return (compare <= 0)
            case '===':
            case '=':
            case 'eq':
                return (compare === 0)
            case '<>':
            case '!==':
            case 'ne':
                return (compare !== 0)
            case '':
            case '<':
            case 'lt':
                return (compare < 0)
            default:
                return null
        }

    };

})(jQuery);