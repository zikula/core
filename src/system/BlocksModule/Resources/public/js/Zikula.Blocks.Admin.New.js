// Copyright Zikula Foundation 2015 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {
    $(document).ready(function() {
        $('#form_choose').hide();
        $('#form_bkey').change(function() {
            this.form.submit();
        });
    })
})(jQuery);
