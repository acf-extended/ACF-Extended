(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /**
     * ACF Extended: 0.8.4.5
     * Field Flexible Content: Fix duplicated "layout_settings" & "layout_title"
     */
    acf.addAction('ready_field_object', function(field){
        
        // field_acfe_layout_abc123456_settings + field_acfe_layout_abc123456_title
        if(!field.get('key').startsWith('field_acfe_layout_'))
            return;
        
        field.delete();
        
    });
    
    $(function(){
        
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
        
        $('.acf-field[data-name="active"]').after($('.acf-field[data-name="acfe_form"]'));
        
        $('.acf-field-setting-acfe_settings > .acf-input > .acf-repeater .acf-actions .acf-button').removeClass('button-primary');
        $('.acf-field-setting-acfe_validate > .acf-input > .acf-repeater .acf-actions .acf-button').removeClass('button-primary');
        $('.acf-field-acfe-meta > .acf-input > .acf-repeater .acf-actions .acf-button').removeClass('button-primary');
    
    });
    
})(jQuery);