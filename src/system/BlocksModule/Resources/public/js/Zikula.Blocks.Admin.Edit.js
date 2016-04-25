// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#add-filter').click(function(e) {
            e.preventDefault();

            var filterList = $('ul#filters');

            // grab the prototype template
            var newWidget = filterList.attr('data-prototype');
            // replace the "__name__" used in the id and name of the prototype with a unique number
            newWidget = newWidget.replace(/__name__/g, filterCount);
            filterCount++;

            // create a new list element and add it to the list
            $(newWidget).appendTo(filterList);
        });
        $('.delete-filter').click(function(e) {
            e.preventDefault();
            var row = $(this).closest('li');
            row.remove();
        });
        $('#filters').on('change', '.attribute-selector', function() {
            var value = $(this).val();
            var queryParamInput = $(this).parent('li').find(".queryParameter");
            if (value == 'query param' || value == '_route_params') {
                queryParamInput.prop('disabled', false);
            } else {
                queryParamInput.prop('disabled', true);
            }
        });
    })
})(jQuery);
