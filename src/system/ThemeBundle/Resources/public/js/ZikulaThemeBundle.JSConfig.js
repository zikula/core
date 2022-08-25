// Copyright Zikula, licensed MIT.

if (typeof(Zikula) == 'undefined') {
    var Zikula = {};
}

// defaults
Zikula.Config = {
    "baseURL": "http:\/\/localhost\/",
    "baseURI": "",
    "lang": "en",
    "uid": "1"
};

// site-specific override
if (0 < jQuery('#zkJsConfig').length) {
    Zikula.Config = jQuery('#zkJsConfig').data('parameters');
}
