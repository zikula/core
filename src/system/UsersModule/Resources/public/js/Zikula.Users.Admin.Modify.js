// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#users_modify_pass_wrap').addClass('hide');

        $('#users_modify_setpass_yes').click( function() {
            $('#users_modify_pass_wrap').removeClass('hide');
            $('#users_modify_pass, #users_modify_passagain').attr('required', 'required');
        });
        $('#users_modify_setpass_no').click( function() {
            $('#users_modify_pass_wrap').addClass('hide');
            $('#users_modify_pass, #users_modify_passagain').removeAttr('required');
        });
    });
})(jQuery);
