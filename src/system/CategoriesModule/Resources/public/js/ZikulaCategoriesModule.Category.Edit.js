// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        function removeTr(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
        }
        function toggleHandler(e) {
            e.preventDefault();
            var icon = $(this).children('i');
            if (icon.hasClass('fa-expand')) {
                icon.removeClass('fa-expand').addClass('fa-compress');
            } else if (icon.hasClass('fa-compress')) {
                icon.removeClass('fa-compress').addClass('fa-expand');
            }
        }
        $('a.category_attributes_remove').on('click', removeTr);
        $('a[data-toggle]').on('click', toggleHandler);
        $('#add-another-attribute').click(function(e) {
            e.preventDefault();
            var attributeTable = $('#attribute-table');
            var newWidget = attributeTable.attr('data-prototype');
            newWidget = newWidget.replace(/__name__/g, attributeCount);
            attributeCount++;
            var newTr = jQuery('<tr></tr>').html(newWidget);
            newTr.appendTo(attributeTable);
            newTr.find('a.category_attributes_remove').on('click', removeTr);
        });
    });
})(jQuery);
