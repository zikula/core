// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('#modulelist .dropdown-toggle').click(function() {
            var container = $(this).parent().parent().parent().parent();
            var containerTop = container.position().top;
            var itemTop = $(this).parent().position().top;
            var availableHeight = container.height() - (itemTop-containerTop);
            var neededHeight = $(this).parent().find('ul').height() + 10;
            if (neededHeight > availableHeight) {
                container.height(container.height() + neededHeight - availableHeight + 30);
            }
        });
    });
})(jQuery);
