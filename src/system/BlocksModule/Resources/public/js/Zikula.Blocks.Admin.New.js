// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#form_choose').addClass('hidden');
        $('#form_bkey').change(function() {
            this.form.submit();
        });
    })
})(jQuery);
