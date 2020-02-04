// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(document).ready(function() {
        var idElem = $('#setPassIdentifiers');
        var setPassEle = $('#' + idElem.data('setpassid'));
        var setPassAlert = setPassEle.parent().find('.alert');
        var setPassWrap = $('#' + idElem.data('setpassid') + '_wrap');
        var passFirst = $('#' + idElem.data('passid') + '_first');
        var passSecond = $('#' + idElem.data('passid') + '_second');
        var showPasswordsRequired = function() {
            passFirst.prop('required', true);
            passFirst.parents('.form-group').find('label').addClass('required');
            passSecond.prop('required', true);
            passSecond.parents('.form-group').find('label').addClass('required');
        };
        var showPasswordsOptional = function() {
            passFirst.prop('required', false);
            passFirst.parents('.form-group').find('label').removeClass('required');
            passSecond.prop('required', false);
            passSecond.parents('.form-group').find('label').removeClass('required');
        };

        setPassAlert.addClass('collapse show');
        showPasswordsOptional();

        // ensure wrap is shown on form re-draw
        if (setPassEle.is(':checked')) {
            setPassWrap.collapse('show');
            setPassAlert.collapse('hide');
            showPasswordsRequired();
        }

        // add/remove required UI for password fields
        setPassWrap.on('show.bs.collapse', function() {
            setPassAlert.collapse('hide');
            showPasswordsRequired();
        }).on('hide.bs.collapse', function() {
            setPassAlert.collapse('show');
            showPasswordsOptional();
        });
    });
})(jQuery);
