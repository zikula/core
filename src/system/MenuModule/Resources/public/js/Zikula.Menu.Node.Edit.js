// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        var optionCount = $('#optionDefinitions').data('count');
        $(document).on('click', '#add-option', function (event) {
            event.preventDefault();

            var optionList = $('ul#options');

            // grab the prototype template
            var newWidget = optionList.attr('data-prototype');
            // replace the "__name__" used in the id and name of the prototype with a unique number
            newWidget = newWidget.replace(/__name__/g, optionCount);
            optionCount++;

            // create a new list element and add it to the list
            $(newWidget).appendTo(optionList);
        });
        $(document).on('click', '.delete-option', function (event) {
            event.preventDefault();

            var row = $(this).closest('li');
            row.remove();
        });
    })
})(jQuery);
