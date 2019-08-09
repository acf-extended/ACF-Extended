jQuery(document).ready(function($){
    
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
            size: 'small'
        });
        
    });
    
});