// Copyright Zikula Foundation, licensed MIT.

if (typeof(Zikula) == 'undefined') {
    var Zikula = {};
}

// defaults
Zikula.Config = {
    "entrypoint": "index.php",
    "baseURL": "http:\/\/localhost\/",
    "baseURI": "",
    "ajaxtimeout": "5000",
    "lang": "en",
    "sessionName": "_zsid",
    "uid": "2"
};

// site-specific override
if (jQuery('#zkJsConfig').length > 0) {
    Zikula.Config = jQuery('#zkJsConfig').data('parameters');
}
