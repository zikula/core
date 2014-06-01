// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {
    $( document ).ready(function() {

        // Automatically convert uname and email fields to lower case after input
        $('#users_modify_uname, #users_modify_email, #users_modify_emailagain').blur(function() {
            var $this = $(this);
            $this.val($this.val().toLowerCase());
        });

        // check if email fields are identical
        var email1 = document.getElementById('users_modify_email');
        var email2 = document.getElementById('users_modify_emailagain');
        var checkEmailValidity = function() {
            if (email1.value != email2.value) {
                email1.setCustomValidity($('#users_modify_email').data('match'));
                email2.setCustomValidity($('#users_modify_email').data('match'));
            } else {
                email1.setCustomValidity('');
                email2.setCustomValidity('');
            }
        };
        email1.addEventListener('change', checkEmailValidity, false);
        email2.addEventListener('keyup', checkEmailValidity, false);

        // check if email fields are identical
        var password1 = document.getElementById('users_modify_pass');
        var password2 = document.getElementById('users_modify_passagain');
        var setPassYes = document.getElementById('users_modify_setpass_yes');
        var checkPasswordValidity = function() {
            if (!setPassYes.checked) {
                password1.setCustomValidity('');
                password2.setCustomValidity('');

                return;
            }

            if (password1.value != password2.value) {
                password1.setCustomValidity($('#users_modify_pass').data('match'));
                password2.setCustomValidity($('#users_modify_pass').data('match'));
            } else {
                password1.setCustomValidity('');
                password2.setCustomValidity('');
            }
        };
        password1.addEventListener('change', checkPasswordValidity, false);
        password2.addEventListener('keyup', checkPasswordValidity, false);
        setPassYes.addEventListener('RadioStateChange', checkPasswordValidity, false);

    });
})(jQuery);