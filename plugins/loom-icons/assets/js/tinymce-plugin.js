(function() {
    tinymce.PluginManager.add('icon_manager', function(editor) {
        editor.addButton('icon_manager_button', {
            text: 'Icon',
            icon: 'dashicon dashicons-art',
            onclick: function() {
                jQuery('#icon-manager-tinymce-modal .icon-manager-modal-backdrop').fadeIn();
            }
        });
    });
})();