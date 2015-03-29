(function($) {
    $.webshim.bugs.bustedValidity = !('minLength' in document.createElement('input'));
    $.webshims.setOptions('basePath', Zikula.Config.baseURL+'javascript/js-webshim/minified/shims/'); 
    $.webshims.activeLang(Zikula.Config.lang);
    $.webshims.polyfill(Zikula.Config.polyfillFeatures);
})(jQuery);
