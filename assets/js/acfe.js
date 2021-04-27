(function($) {

    if (typeof acf === 'undefined')
        return;

    /*
     * ACF Data
     */
    acf.data.acfe = {};

    /*
     * ACFE
     */
    var acfe = {};

    window.acfe = acfe;

    /*
     * Get
     */
    acfe.get = function(name) {
        return acf.data.acfe[name] || null;
    };

    /*
     * Has
     */
    acfe.has = function(name) {
        return this.get(name) !== null;
    };

    /*
     * Set
     */
    acfe.set = function(name, value) {
        acf.data.acfe[name] = value;
        return this;
    };

    /*
     * Popup
     */
    var popups = [];

    acfe.Popup = acf.Model.extend({

        data: {
            title: false,
            footer: false,
            size: false,
            destroy: false,
            onOpen: function() {},
            onClose: function() {},
        },

        events: {
            'click .acfe-modal-title>.close': 'onClickClose',
            'click .acfe-modal-footer>button': 'onClickClose',
        },

        setup: function($content, args) {

            $.extend(this.data, args);

            this.$el = $content;
            this.render();

        },

        initialize: function() {

            this.open();

        },

        render: function() {

            // Size
            if (this.get('size')) {

                this.$el.addClass('-' + this.get('size'));

            }

            // Wrapper
            if (!this.$('> .acfe-modal-wrapper').length) {

                this.$el.wrapInner('<div class="acfe-modal-wrapper" />');

            }

            var $wrapper = this.$('> .acfe-modal-wrapper');

            // Content
            if (!$wrapper.find('> .acfe-modal-content').length) {

                $wrapper.wrapInner('<div class="acfe-modal-content" />');

            }

            // Title
            if (this.get('title')) {

                $wrapper.prepend('<div class="acfe-modal-title"><span class="title">' + this.get('title') + '</span><button class="close"></button></div>');

            }

            // Overlay
            $wrapper.prepend('<div class="acfe-modal-wrapper-overlay"></div>');

            // Footer
            if (this.get('footer')) {

                $wrapper.append('<div class="acfe-modal-footer"><button class="button button-primary">' + this.get('footer') + '</button></div>');

            }

        },

        open: function() {

            this.$el.addClass('-open');

            popups.push(this);

            acfe.syncPopup();

            // Get sub fields
            var getSubFields = acf.getFields({
                parent: this.$el,
                visible: true,
            });

            // Show sub fields
            getSubFields.map(function(field) {
                acf.doAction('show_field', field, 'group');
            }, this);

            acf.doAction('acfe/modal/open', this.$el, this.data);

            this.get('onOpen').apply(this.$el);

        },

        close: function() {

            this.$('.acfe-modal-wrapper-overlay').remove();
            this.$('.acfe-modal-title').remove();
            this.$('.acfe-modal-footer').remove();

            this.$el.removeAttr('style');
            this.$el.removeClass('-open');

            acfe.syncPopup();

            acf.doAction('acfe/modal/close', this.$el, this.data);

            this.get('onClose').apply(this.$el);

            this.remove();

            if (this.get('destroy')) {

                this.$el.remove();

            }

        },

        remove: function() {

            this.removeEvents();
            this.removeActions();
            this.removeFilters();

        },

        onClickClose: function(e) {

            e.preventDefault();

            if (!popups.length)
                return false;

            popups.pop().close();

        }

    });

    /*
     * Popup: Close
     */
    acfe.closePopup = function() {

        if (!popups.length)
            return false;

        popups.pop().close();

    };

    /*
     * Popup: Sync
     */
    acfe.syncPopup = function() {

        var $body = $('body');

        if (popups.length) {

            // Prepare Body
            if (!$body.hasClass('acfe-modal-opened')) {

                $body.addClass('acfe-modal-opened').append($('<div class="acfe-modal-overlay" />'));

                $('.acfe-modal-overlay').on('click', function(e) {

                    e.preventDefault();
                    acfe.closePopup();

                });

            }

            // Prepare Multiple
            popups.map(function(self, i) {

                if (i === popups.length - 1) {
                    return self.$el.removeClass('acfe-modal-sub').css('margin-left', '');
                }

                self.$el.addClass('acfe-modal-sub').css('margin-left', -(500 / (i + 1)));

            });

        } else {

            $('.acfe-modal-overlay').remove();
            $body.removeClass('acfe-modal-opened');

        }

    };

    $(window).on('keydown', function(e) {

        if (e.keyCode !== 27 || !$('body').hasClass('acfe-modal-opened'))
            return;

        e.preventDefault();
        acfe.closePopup();

    });

    // Compatibility
    acfe.modal = {

        open: function($modal, args) {

            new acfe.Popup($modal, args);

        },

        close: function() {

            acfe.closePopup();

        }

    };

    /*
     * Filters
     */
    var filters = [];

    acfe.disableFilters = function() {
        filters = [];
    };

    acfe.getFilters = function() {
        return filters;
    };

    acfe.isFilterEnabled = function(name) {
        return filters.indexOf(name) > -1;
    };

    acfe.enableFilter = function(name) {

        if (filters.indexOf(name) === -1)
            filters.push(name);

    };

    acfe.disableFilter = function(name) {

        for (var i = filters.length; i--;) {

            if (filters[i] !== name)
                continue;

            filters.splice(i, 1);

        }

    };

    /*
     * Parse String
     */
    acfe.parseString = function(val) {
        return val ? '' + val : '';
    };

    /*
     * In Array
     */
    acfe.inArray = function(v1, array) {

        array = array.map(function(v2) {
            return acfe.parseString(v2);
        });

        return (array.indexOf(v1) > -1);

    }

    /*
     * Parse URL
     */
    acfe.parseURL = function(url) {

        url = url || acfe.currentURL;

        var params = {};

        var queryString = url.replace(/^[^\?]+\??/, '');

        if (!queryString)
            return params;

        var Pairs = queryString.split(/[;&]/);

        for (var i = 0; i < Pairs.length; i++) {

            var KeyVal = Pairs[i].split('=');

            if (!KeyVal || KeyVal.length !== 2)
                continue;

            var key = decodeURI(KeyVal[0]);
            var val = decodeURI(KeyVal[1]);

            val = val.replace(/\+/g, ' ');

            params[key] = val;

        }

        return params;

    };

    /*
     * Current URL
     */
    acfe.currentURL = function() {

        return self.location.href;

    };

    /*
     * Current Path
     */
    acfe.currentPath = function() {

        return self.location.pathname;

    };

    /*
     * Current Filename
     */
    acfe.currentFilename = function() {

        return acfe.currentPath().split('/').pop();

    };

    /*
     * Parent Object
     */
    acfe.parentObject = function(obj) {
        return Object.getPrototypeOf(Object.getPrototypeOf(obj));
    }

    /*
     * Tooltip
     */
    new acf.Model({

        tooltip: false,

        events: {
            'click .acfe-field-tooltip': 'showTitle',
        },

        showTitle: function(e, $el) {

            // vars
            var title = $el.attr('title');

            // bail ealry if no title
            if (!title) {
                return;
            }

            // clear title to avoid default browser tooltip
            $el.attr('title', '');

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

        }

    });

    /*
     * Fix acf.getFileInputData
     * Source: /advanced-custom-fields-pro/assets/js/acf.js:1927
     */
    acf.getFileInputData = function($input, callback) {

        // vars
        var value = $input.val();

        // bail early if no value
        if (!value) {
            return false;
        }

        // data
        var data = {
            url: value
        };

        // fix
        var file = $input[0].files.length ? acf.isget($input[0].files, 0) : false;

        if (file) {

            // update data
            data.size = file.size;
            data.type = file.type;

            // image
            if (file.type.indexOf('image') > -1) {

                // vars
                var windowURL = window.URL || window.webkitURL;
                var img = new Image();

                img.onload = function() {

                    // update
                    data.width = this.width;
                    data.height = this.height;

                    callback(data);
                };
                img.src = windowURL.createObjectURL(file);
            } else {
                callback(data);
            }
        } else {
            callback(data);
        }
    };

})(jQuery);