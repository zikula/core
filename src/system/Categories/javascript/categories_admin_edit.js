// Copyright Zikula Foundation 2010 - license GNU/LGPLv3 (or at your option, any later version).

Event.observe(window, 'load', categories_edit_init);

function categories_edit_init()
{
    if ($('categories_meta_collapse')) {
        categories_meta_init();
    }
}


function categories_meta_init()
{
    $('categories_meta_collapse').observe('click', categories_meta_click);
    $('categories_meta_collapse').addClassName('z-toggle-link');
    if ($('categories_meta_details').style.display != "none") {
        $('categories_meta_collapse').removeClassName('z-toggle-link-open');
        $('categories_meta_details').hide();
    }
}

function categories_meta_click()
{
    if ($('categories_meta_details').style.display != "none") {
        Element.removeClassName.delay(0.9, $('categories_meta_collapse'), 'z-toggle-link-open');
    } else {
        $('categories_meta_collapse').addClassName('z-toggle-link-open');
    }
    switchdisplaystate('categories_meta_details');
}