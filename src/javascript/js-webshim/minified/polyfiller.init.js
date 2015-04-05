(function($) {
    $.webshim.bugs.bustedValidity = !('minLength' in document.createElement('input'));
    $.webshims.setOptions('basePath', Zikula.Config.baseURL+'javascript/js-webshim/minified/shims/');
    $.webshims.setOptions('debug', true);
    $.webshims.activeLang(Zikula.Config.lang);
    $.webshims.polyfill(Zikula.Config.polyfillFeatures);
})(jQuery);
