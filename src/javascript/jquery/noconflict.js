jQuery.noConflict();
// setup ajax for jQuery
(function($) {
    var defaultOptions = {
        type: 'POST',
        timeout: Zikula.Config.ajaxtimeout || 5000
    };
    if (Zikula.Config.sessionName) {
        var sessionId = new RegExp(Zikula.Config.sessionName + '=(.*?)(;|$)').exec(document.cookie);
        if (sessionId && sessionId[1]) {
            defaultOptions.headers = {
                'X-ZIKULA-AJAX-TOKEN': sessionId[1]
            };
        }
    }
    $.ajaxSetup(defaultOptions);
})(jQuery);
