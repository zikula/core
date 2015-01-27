// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var ZikulaCategories = {};

( function($) {

    $(document).ready(function() {
        if ($('#category_attributes_add').length > 0) {
            ZikulaCategories.InitAttributes();
        }
        ZikulaCategories.InitCollapse();
    });

    ZikulaCategories.InitCollapse = function() {
        $('.categories_collapse_control')
            .click(ZikulaCategories.ClickCollapse)
            .addClass('z-toggle-link')
            .each(function(index) {
                var details = $(this).parent('legend').next('.categories_collapse_details');
                if (details && details.is(':visible')) {
                    details.removeClass('z-toggle-link-open').hide();
                }
            });
    };

    ZikulaCategories.ClickCollapse = function(event) {
        event.preventDefault();
        var collapse, details;

        collapse = $(event.target);
        details = collapse.parent('legend').next('.categories_collapse_details');

        if (details.hasClass('z-toggle-link-open')) {
            details.removeClass('z-toggle-link-open').hide();
        } else {
            details.addClass('z-toggle-link-open').show();
        }
    };

    ZikulaCategories.InitAttributes = function() {
        $('#category_attributes_add').click(ZikulaCategories.AddAttribute);
        $('.category_attributes_remove').click(ZikulaCategories.RemoveAttribute);
    };

    ZikulaCategories.AddAttribute = function(event) {
        event.preventDefault();
        if ($('#new_attribute_name').val() == '' || $('#new_attribute_value').val() == '') {
            return false;
        }

        var table, tr, tbody, newRow;

        tr = $(event.target).parent('tr');
        tbody = tr.parent('tbody');
        newRow = $('<tr>');

        var newTd1 = $('<td>')
            .append($('<input>')
                .attr({ name: 'attribute_name[]', value: $('#new_attribute_name').val() })
            );
        $('#new_attribute_name').val('');
        newRow.append(newTd1);

        var newTd2 = $('<td>')
            .append($('<input>')
                .attr({ name: 'attribute_value[]', value: $('#new_attribute_value').val(), size: 50 })
            );
        $('#new_attribute_value').val('');
        newRow.append(newTd2);

        var newTd3 = $('<td>')
            .append($('<input>')
                .attr({ type: 'image', class: 'category_attributes_remove', src: Zikula.Config.baseURL + 'images/icons/extrasmall/edit_remove.png' })
            );
        newRow.append(newTd3);

        // add new row to after the row containing the button that was clicked
        $(this).closest('tr').after('<tr>' + newRow.html() + '</tr>');

        // reinitialise delete buttons
        $('.category_attributes_remove').unbind('click').click(ZikulaCategories.RemoveAttribute);

        $('#new_attribute_name').focus();

        return true;
    };

    ZikulaCategories.RemoveAttribute = function(event) {
        $(this).parent().parent().remove();
    };
})(jQuery);
