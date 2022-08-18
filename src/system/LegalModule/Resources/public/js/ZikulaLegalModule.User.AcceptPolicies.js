// Copyright Zikula, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('.policy-link').on('click', function (event) {
            event.preventDefault();
            $('#modal-policy-title').text($(this).text());
            $('#modal-policy-body').load($(this).attr('href'));
            $('#modal-policy').modal('show');
        });
        $('#modal-policy').on('hidden.bs.modal', function (event) {
            $('#modal-policy-body').html('<i class="fas fa-spin fa-cog fa-2x"></i>');
        });
    });
})(jQuery);
