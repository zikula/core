(function($) {
    $.webshims.setOptions('basePath', Zikula.Config.baseURL+'javascript/js-webshim/dev/shims/'); 
    $.webshims.activeLang(Zikula.Config.lang);
    $.webshims.polyfill(Zikula.Config.polyfillFeatures);
})(jQuery);
