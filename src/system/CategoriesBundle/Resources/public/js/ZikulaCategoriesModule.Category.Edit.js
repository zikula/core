// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        var attributeCount = $('#attributeDefinitions').data('count');
        function removeTr(event) {
            event.preventDefault();
            $(this).closest('tr').remove();
        }
        function toggleHandler(event) {
            event.preventDefault();
            var icon = $(this).children('i');
            if (icon.hasClass('fa-expand')) {
                icon.removeClass('fa-expand').addClass('fa-compress');
            } else if (icon.hasClass('fa-compress')) {
                icon.removeClass('fa-compress').addClass('fa-expand');
            }
        }
        $(document).on('click', 'a.category_attributes_remove', removeTr);
        $(document).on('click', 'a[data-toggle]', toggleHandler);
        $(document).on('click', '#add-another-attribute', function(event) {
            event.preventDefault();
            var attributeTable = $('#attribute-table');
            var newWidget = attributeTable.attr('data-prototype');
            newWidget = newWidget.replace(/__name__/g, attributeCount);
            attributeCount++;
            var newTr = jQuery('<tr />').html(newWidget);
            newTr.appendTo(attributeTable);
            newTr.find('a.category_attributes_remove').on('click', removeTr);
        });
    });
})(jQuery);
