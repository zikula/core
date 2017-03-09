// Copyright Zikula Foundation, licensed MIT.

jQuery.noConflict();
/*
 Fix provided by http://softec.lu/site/DevelopersCorner/BootstrapPrototypeConflict
 */
if (typeof Prototype !== 'undefined' && Prototype.BrowserFeatures.ElementExtensions) {
    var disablePrototypeJS = function (method, pluginsToDisable) {
            var handler = function (event) {
                event.target[method] = undefined;
                setTimeout(function () {
                    delete event.target[method];
                }, 0);
            };
            pluginsToDisable.each(function (plugin) {
                jQuery(window).on(method + '.bs.' + plugin, handler);
            });
        },
        pluginsToDisable = ['collapse', 'dropdown', 'modal', 'tooltip', 'popover', 'tab'];
    disablePrototypeJS('show', pluginsToDisable);
    disablePrototypeJS('hide', pluginsToDisable);
}

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
