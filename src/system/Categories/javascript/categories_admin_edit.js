// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', categories_edit_init);

function categories_edit_init()
{
    categories_advlink_onchange();
    $('categories_advlink').removeClassName('z-hide');
    $('categories_advlink').observe('click', categories_advlink_onchange);
}

function categories_advlink_onchange()
{
    $('categories_meta').toggle();
    $('categories_additionaldata').toggle();
    $('categories_sort_value_container').toggle();
}
