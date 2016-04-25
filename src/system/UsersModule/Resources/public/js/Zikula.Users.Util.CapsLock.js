// Copyright Zikula Foundation, licensed MIT.

var ZikulaUsersUtilCapsLock = {};

(function($) {
    ZikulaUsersUtilCapsLock.capsLockChecker = function (inputElement, toggleElement) {
        $(inputElement).keypress(function(e) {
            function isCapsLockPressed(e) {
                if (!Boolean(window.chrome) && !Boolean(window.webkit)) {
                    kc = e.keyCode ? e.keyCode : e.which;
                    sk = e.shiftKey ? e.shiftKey : ((kc == 16) ? true : false);
                    if ((((kc >= 65 && kc <= 90) && !sk)||((kc >= 97 && kc <= 122) && sk))) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    e = (e) ? e : window.event;

                    var charCode = false;
                    if (e.which) {
                        charCode = e.which;
                    } else if (e.keyCode) {
                        charCode = e.keyCode;
                    }

                    var shifton = false;
                    if (e.shiftKey) {
                        shifton = e.shiftKey;
                    } else if (e.modifiers) {
                        shifton = !!(e.modifiers & 4);
                    }

                    if (charCode >= 97 && charCode <= 122 && shifton) {
                        return true;
                    }

                    if (charCode >= 65 && charCode <= 90 && !shifton) {
                        return true;
                    }

                    return false;
                }
            }

            if (isCapsLockPressed(e)) {
                $(toggleElement).removeClass('hide');
            } else {
                $(toggleElement).addClass('hide');
            }
        });
    };

    $(document).ready(function() {
        ZikulaUsersUtilCapsLock.capsLockChecker('#users_login_pass', '#capsLok');
    });
})(jQuery);
