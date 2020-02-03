'use strict';

function initStyleSwitcher() {
    if (jQuery('#themeStyleSwitcher').length < 1) {
        return;
    }

    jQuery('#themeStyleSwitcher #themeStyle').change(function (event) {
        event.preventDefault();
        jQuery.ajax({
            method: 'POST',
            url: Routing.generate('zikulabootstraptheme_ajax_changeuserstyle'),
            data: {
                style: jQuery(this).val()
            }
        }).done(function (data) {
            if (true === data.result) {
                window.location.reload();
            } else {
                alert(Translator.trans('Sorry, the style could not be changed properly. Please try again.'));
            }
        });
    });
}

jQuery(document).ready(initStyleSwitcher);
