
(function($) {
    $(document).ready(function() {
        // workaround for modals used together with mmenu, see #2903
        $('.modal').on('show.bs.modal', function (e) {
            $('body .mm-slideout').css('position', 'static');
        });
        $('.modal').on('hidden.bs.modal', function (e) {
            $('body .mm-slideout').css('position', 'relative');
        });
    });
})(jQuery)
