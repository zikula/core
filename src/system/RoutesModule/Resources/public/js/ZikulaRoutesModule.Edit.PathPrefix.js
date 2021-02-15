'use strict';

function updatePathPrefix() {
    var i18n = jQuery('#zikularoutesmodule_route_translatable').prop('checked');
    var bundlePrefix = jQuery('#zikularoutesmodule_route_prependBundlePrefix').prop('checked');
    var baseUrl = jQuery('#pathPrefixInfo').data('base-url');
    var extensionUrlNames = jQuery('#pathPrefixInfo').data('extension-url-names');

    if (bundlePrefix) {
        //var bundle = jQuery('#zikularoutesmodule_route_bundle').val();
        var controllerParts = jQuery('#zikularoutesmodule_route_routeController_controller').val().split('###');
        controllerParts = controllerParts[1].split('\\');
        var bundle = controllerParts[0] + controllerParts[1];
        bundlePrefix = '/' + extensionUrlNames[bundle];
    } else {
        bundlePrefix = '';
    }

    if (i18n) {
        i18n = '/{' + Translator.trans('lang') + '}';
    } else {
        i18n = '';
    }

    var pathPrefix = baseUrl + i18n + bundlePrefix;
    jQuery('#pathPrefix').text(pathPrefix.replace('"', ''));
}

jQuery(document).ready(function () {
    updatePathPrefix();
    //jQuery('#zikularoutesmodule_route_bundle, #zikularoutesmodule_route_prependBundlePrefix, #zikularoutesmodule_route_translatable').change(updatePathPrefix);
    jQuery('#zikularoutesmodule_route_routeController_controller, #zikularoutesmodule_route_prependBundlePrefix, #zikularoutesmodule_route_translatable').change(updatePathPrefix);
});
