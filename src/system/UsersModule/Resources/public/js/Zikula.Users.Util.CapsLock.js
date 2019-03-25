// Copyright Zikula Foundation, licensed MIT.

var ZikulaUsersUtilCapsLock = {};

(function($) {
    ZikulaUsersUtilCapsLock.capsLockChecker = function (inputElement, toggleElement) {
        $(inputElement).keypress(function (event) {
            function isCapsLockPressed (event) {
                if (!Boolean(window.chrome) && !Boolean(window.webkit)) {
                    kc = event.keyCode ? event.keyCode : event.which;
                    sk = event.shiftKey ? event.shiftKey : (kc === 16);
                    return !!(((kc >= 65 && kc <= 90) && !sk) || ((kc >= 97 && kc <= 122) && sk));
                }
                event = (event) ? event : window.event;

                var charCode = false;
                if (event.which) {
                    charCode = event.which;
                } else if (event.keyCode) {
                    charCode = event.keyCode;
                }

                var shifton = false;
                if (event.shiftKey) {
                    shifton = event.shiftKey;
                } else if (event.modifiers) {
                    shifton = !!(event.modifiers & 4);
                }

                if (charCode >= 97 && charCode <= 122 && shifton) {
                    return true;
                }

                return charCode >= 65 && charCode <= 90 && !shifton;
            }

            if (isCapsLockPressed(event)) {
                $(toggleElement).removeClass('hidden');
            } else {
                $(toggleElement).addClass('hidden');
            }
        });
    };

    $(document).ready(function() {
        ZikulaUsersUtilCapsLock.capsLockChecker('#users_login_pass', '#capsLok');
    });
})(jQuery);
