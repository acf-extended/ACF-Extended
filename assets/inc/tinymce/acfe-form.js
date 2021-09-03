(function($) {

    if (typeof acf === 'undefined')
        return;

    if (typeof window.wp.mce === 'undefined')
        return;

    tinymce.PluginManager.add('acfe_form', function(editor, url) {

        window.wp.mce.views.register('acfe_form', {

            initialize: function() {

                // send ajax
                $.ajax({
                    url: acf.get('ajaxurl'),
                    data: acf.prepareForAjax({
                        action: 'acfe/form/shortcode',
                        args: this.shortcode.attrs.named,
                    }),
                    type: 'post',
                    dataType: 'html',
                    context: this,
                    beforeSend: function() {
                        this.render('<div style="border:1px solid #ddd; padding:120px 25px; background:#f8f8f8; text-align:center;"></div>');
                    },
                    success: function(response) {
                        this.render(response, true);
                    },
                });

            },

            edit: function(text, update) {

                editor.windowManager.open({
                    width: 800,
                    height: 62,
                    title: 'Shortcode',
                    body: [{
                        label: '',
                        name: 'content',
                        type: 'textbox',
                        value: text,
                    }, ],
                    onsubmit: function(e) {
                        update(e.data.content);
                    },
                });

            },

        });

    });


})(jQuery);