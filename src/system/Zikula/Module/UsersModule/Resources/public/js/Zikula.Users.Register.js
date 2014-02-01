// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

jQuery(function() {
    jQuery('#users_register_uname').blur(function(){toLowerCase(jQuery(this));});
    jQuery('#users_register_email').blur(function(){toLowerCase(jQuery(this));});
    jQuery('#users_register_emailagain').blur(function(){toLowerCase(jQuery(this));});

    function toLowerCase(element) {
        element.val(element.val().toLowerCase());
    }
});
