Event.observe(window, 'load', categories_edit_init, false);

function categories_edit_init()
{
	categories_advlink_onchange();
	$('categories_advlink').removeClassName('z-hide');
	Event.observe('categories_advlink', 'click', categories_advlink_onchange, false);
}

function categories_advlink_onchange()
{
	$('categories_meta').toggle();
	$('categories_additionaldata').toggle();
	$('categories_sort_value_container').toggle();
}
