'use strict';

function updatePathPrefix() {
    var i18n = jQuery('#zikularoutesmodule_route_translatable').prop('checked');
    var bundlePrefix = jQuery('#zikularoutesmodule_route_prependBundlePrefix').prop('checked');
    var baseUrl = jQuery('#pathPrefixInfo').data('base-url');
    var moduleUrlNames = jQuery('#pathPrefixInfo').data('module-url-names');

    if (bundlePrefix) {
        var bundle = jQuery('#zikularoutesmodule_route_bundle').val();
        bundlePrefix = '/' + moduleUrlNames[bundle];
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
    jQuery('#zikularoutesmodule_route_bundle, #zikularoutesmodule_route_prependBundlePrefix, #zikularoutesmodule_route_translatable').change(updatePathPrefix);
});
