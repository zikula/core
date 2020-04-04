(function ($) {
    $(document).ready(function () {
        $('.pwstrength').each(function (index) {
            var options = {};
            options.common = {
                minChar: $(this).attr('minlength'),
            };
            if ($(this).data('uname-id') !== '') {
                options.common.usernameField = '#' + $(this).data('uname-id');
            }
            options.ui = {
                showVerdictsInsideProgressBar: true,
                progressExtraCssClasses: 'mt-2'
            };
            $(this).pwstrength(options);
        });
    });
})(jQuery);
