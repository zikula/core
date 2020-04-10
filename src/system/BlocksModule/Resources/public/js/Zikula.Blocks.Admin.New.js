// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('#zikulablocksmodule_newblock_choose').addClass('d-none');
        $('#zikulablocksmodule_newblock_bkey').change(function() {
            this.form.submit();
        });
    });
})(jQuery);
