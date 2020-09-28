(function($){
    
    if(typeof acf === 'undefined')
        return;

    /*
     * Field Attribute: data-after="field_name"
     */
    var fieldAfterManager = new acf.Model({

        actions: {
            'new_field' : 'onNewField'
        },

        onNewField: function(field){

            // bail early if not after
            if(!field.has('after'))
                return;

            // vars
            var after = field.get('after');
            var $sibling = field.$el.siblings('[data-name="' + after + '"]').first();

            // bail early if no sibling
            if(!$sibling.length)
                return;

            $sibling.after(field.$el);

        }
    });

    /*
     * Field Group Conditional Logic: Init fields
     */
    var conditionalLogicFields = new acf.Model({

        wait: 'ready',

        actions:{
            'append':                           'onAppend',
            'acfe/field_group/rule_refresh':    'refreshFields'
        },

        initialize: function(){
            this.$el = $('#acf-field-group-locations');
        },

        onAppend: function($el){

            if(!$el.is('.rule-group') && !$el.parent().parent().parent().is('.rule-group'))
                return;

            this.refreshFields();

        },

        refreshFields: function(){

            var fields = acf.getFields({
                parent: this.$('td.value')
            });

            $.each(fields, function(){

                var field = this;

                if(field.get('type') === 'date_picker' || field.get('type') === 'date_time_picker' || field.get('type') === 'time_picker'){

                    field.$inputText().removeClass('hasDatepicker').removeAttr('id');

                    field.initialize();

                }

            });

        }

    });

    /*
     * ACF Extended: 0.8.4.5
     * Field Flexible Content: Fix duplicated "layout_settings" & "layout_title"
     */
    acf.addAction('ready_field_object', function(field){
        
        // field_acfe_layout_abc123456_settings + field_acfe_layout_abc123456_title
        if(!field.get('key').startsWith('field_acfe_layout_'))
            return;
        
        field.delete();
        
    });

    /*
     * Field: WYSIWYG
     */
    var acfe_repeater_remove_primary_class = function(field){
        
        field.$('.acf-button').removeClass('button-primary');
        
    };
    
    acf.addAction('new_field/name=acfe_meta', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_settings', acfe_repeater_remove_primary_class);
    acf.addAction('new_field/name=acfe_validate', acfe_repeater_remove_primary_class);
    
    $(function(){

        /*
         * Field Setting: Data
         */
        $('.button.edit-field').each(function(k, v){
            
            var tbody = $(this).closest('tbody');
            $(tbody).find('.acfe_modal_open:first').insertAfter($(this));
            $(tbody).find('.acfe-modal:first').appendTo($('body'));
            $(tbody).find('tr.acf-field-setting-acfe_field_data:first').remove();
            
        });
        
        $('.acfe_modal_open').click(function(e){
            
            e.preventDefault();
            
            var key = $(this).attr('data-modal-key');
            
            var $modal = $('.acfe-modal[data-modal-key=' + key + ']');
            
            acfe.modal.open($modal, {
                title: 'Data',
                size: 'medium'
            });
            
        });
    
    });
    
})(jQuery);