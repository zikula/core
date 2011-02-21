// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Zikula.define('Categories');

Zikula.Categories.InitEditView = function() {
    if ($('categories_meta_collapse')) {
        Zikula.Categories.Meta.Init();
    }
    if ($('category_attributes_add')) {
        Zikula.Categories.Attributes.Init();
    }
};
$(document).observe('dom:loaded', Zikula.Categories.InitEditView);

Zikula.define('Categories.Meta');

Zikula.Categories.Meta.Init = function() {
    $('categories_meta_collapse').observe('click', Zikula.Categories.Meta.Click);
    $('categories_meta_collapse').addClassName('z-toggle-link');
    if ($('categories_meta_details').visible()) {
        $('categories_meta_collapse').removeClassName('z-toggle-link-open');
        $('categories_meta_details').hide();
    }
};

Zikula.Categories.Meta.Click = function(event)
{
    event.preventDefault();
    if ($('categories_meta_details').visible()) {
        Element.removeClassName.delay(0.9, $('categories_meta_collapse'), 'z-toggle-link-open');
    } else {
        $('categories_meta_collapse').addClassName('z-toggle-link-open');
    }
    Zikula.switchdisplaystate('categories_meta_details');
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
