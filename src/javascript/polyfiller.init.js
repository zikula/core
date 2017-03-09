(function($) {
    $.webshim.bugs.bustedValidity = !('minLength' in document.createElement('input'));
    $.webshims.setOptions('basePath', Zikula.Config.baseURL + Zikula.Config.baseURI + '/web/webshim/js-webshim/minified/shims/');
    $.webshims.setOptions('ajax', {
        method: 'GET'
    });
    $.webshims.activeLang(Zikula.Config.lang);
    $.webshims.polyfill(Zikula.Config.polyfillFeatures || []);
})(jQuery);
