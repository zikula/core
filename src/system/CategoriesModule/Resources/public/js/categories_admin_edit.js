// Copyright Zikula Foundation, licensed MIT.

var ZikulaCategories = {};

( function($) {

    $(document).ready(function() {
        ZikulaCategories.init();
    });

    ZikulaCategories.init = function() {
        if ($('#category_attributes_add').length > 0) {
            ZikulaCategories.InitAttributes();
        }
        $('a[data-toggle]').click(ZikulaCategories.categoriesToggleHandler);
    };

    ZikulaCategories.categoriesToggleHandler = function(e) {
        e.preventDefault();
        var icon = $(this).children('i');
        if (icon.hasClass('fa-expand')) {
            icon.removeClass('fa-expand').addClass('fa-compress');
        } else if (icon.hasClass('fa-compress')) {
            icon.removeClass('fa-compress').addClass('fa-expand');
        }
    };

    ZikulaCategories.InitAttributes = function() {
        $('#category_attributes_add').click(ZikulaCategories.AddAttribute);
        $('.category_attributes_remove').click(ZikulaCategories.RemoveAttribute);
    };

    ZikulaCategories.AddAttribute = function(event) {
        event.preventDefault();
        var newAttrName = $('#new_attribute_name');
        var newAttrValue = $('#new_attribute_value');
        if (newAttrName.val() == '' || newAttrValue.val() == '') {
            return false;
        }

        var tr, tbody, newRow;

        tr = $(event.target).parent('tr');
        tbody = tr.parent('tbody');
        newRow = $('<tr>');

        var newTd1 = $('<td>')
            .append($('<input>')
                .attr({ 'name': 'attribute_name[]', 'class': 'form-control input-sm', 'value': newAttrName.val() })
            );
        newAttrName.val('');
        newRow.append(newTd1);

        var newTd2 = $('<td>')
            .append($('<input>')
                .attr({ 'name': 'attribute_value[]', 'class': 'form-control input-sm', 'value': newAttrValue.val(), size: 50 })
            );
        newAttrValue.val('');
        newRow.append(newTd2);

        var newTd3 = $('<td>')
            .append($('<a>')
                .attr({ 'href': '#', 'class': 'category_attributes_remove', 'title': /*Zikula.__(*/'Delete'/*)*/ })
                .html('<i class="fa fa-minus-square fa-lg text-danger"></i>')
            );
        newRow.append(newTd3);

        // add new row to after the row containing the button that was clicked
        $(this).closest('tr').after('<tr>' + newRow.html() + '</tr>');

        // reinitialise delete buttons
        $('.category_attributes_remove').unbind('click').click(ZikulaCategories.RemoveAttribute);

        newAttrName.focus();

        return true;
    };

    ZikulaCategories.RemoveAttribute = function(event) {
        event.preventDefault();
        $(this).closest('tr').remove();
    };
})(jQuery);
