(function() {
    $("#form_lang, #form_require, #form_dbinformation, #form_createadmin").submit(function() {
            $('#ZikulaOverlay').show();
            $.ajax({
            url: this.action,
            type: this.method,
            data: $(this).serialize(),
            success: function() {
            $('#ZikulaOverlay').hide();
            }
        });
        return false;
    });
});