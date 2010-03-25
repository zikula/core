function CheckAll(formtype) {
    $$('.' + formtype + '_radio').each(function(el) { el.checked = $('toggle_' + formtype).checked;});
}

function CheckCheckAll(formtype) {
    var totalon = 0;
    $$('.' + formtype + '_radio').each(function(el) { if (el.checked) { totalon++; } });
    $('toggle_' + formtype).checked = ($$('.' + formtype + '_radio').length==totalon);
}
