// Copyright Zikula, licensed MIT.

jQuery.noConflict();

// setup ajax for jQuery
(function($) {
    var defaultOptions = {
        type: 'POST',
        timeout: Zikula.Config.ajaxtimeout || 5000
    };
    $.ajaxSetup(defaultOptions);
})(jQuery);
