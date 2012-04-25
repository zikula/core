// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Zikula.define('Categories');

Zikula.Categories.InitEditView = function() {
    if ($('category_attributes_add')) {
        Zikula.Categories.Attributes.Init();
    }
    Zikula.Categories.Collapse.Init();
};
$(document).observe('dom:loaded', Zikula.Categories.InitEditView);

Zikula.define('Categories.Collapse');

Zikula.Categories.Collapse.Init = function() {
    $$('.categories_collapse_control')
        .invoke('observe','click', Zikula.Categories.Collapse.Click)
        .invoke('addClassName','z-toggle-link')
        .each(function(collapse) {
            var details = collapse.up('legend').next('.categories_collapse_details');
            if (details && details.visible()) {
                details.removeClassName('z-toggle-link-open').hide();
            }
        });
};

Zikula.Categories.Collapse.Click = function(event) {
    event.preventDefault();
    var collapse = event.findElement('.categories_collapse_control'),
        details = collapse.up('legend').next('.categories_collapse_details');
    if (details.visible()) {
        Element.removeClassName.delay(0.9, details, 'z-toggle-link-open');
    } else {
        details.addClassName('z-toggle-link-open');
    }
    Zikula.switchdisplaystate(details);
};

Zikula.define('Categories.Attributes');

Zikula.Categories.Attributes.Init = function () {
    $('category_attributes_add').observe('click', Zikula.Categories.Attributes.Add);
    $$('.category_attributes_remove').invoke('observe','click', Zikula.Categories.Attributes.Remove);
};

Zikula.Categories.Attributes.Add = function(event) {
    event.preventDefault();
    var inputElement = event.element();
    if ($('new_attribute_name').getValue().empty() || $('new_attribute_value').getValue().empty()) {
        return false;
    }

    var td = inputElement.up('td'),
        tr = td.up('tr'),
        table = tr.up('table'),
        newRow = table.insertRow(tr.rowIndex+1);

    var newTd1 = newRow.insertCell(0),
        newInput1 = new Element('input',{name: 'attribute_name[]', value: $('new_attribute_name').getValue()});
    $('new_attribute_name').clear();
    newTd1.appendChild(newInput1);

    var newTd2 = newRow.insertCell(1),
        newInput2 = new Element('input',{name: 'attribute_value[]', value: $('new_attribute_value').getValue()});
    $('new_attribute_value').clear();
    newTd2.appendChild(newInput2);

    var newTd3 = newRow.insertCell(2),
        newInput3 = new Element('input',{type: 'image', src: 'images/icons/extrasmall/edit_remove.png'}).observe('click',Zikula.Categories.Attributes.Remove);
    newTd3.appendChild(newInput3);

    $('new_attribute_name').focus();

    return true;
};

Zikula.Categories.Attributes.Remove = function(event) {
    event.preventDefault();
    var inputElement = event.element(),
        tr = inputElement.up('tr'),
        table = tr.up('table');
    table.deleteRow(tr.rowIndex);
    return true;
};
