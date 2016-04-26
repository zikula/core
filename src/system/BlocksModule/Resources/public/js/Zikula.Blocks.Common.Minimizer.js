// Copyright Zikula Foundation, licensed MIT.

// Javascript functions to minimize/hide and maximize/show a block when clicking on a small adjacent icon.
(function($) {
    $(document).ready(function() {
        $('.z-block').children(':header').each(function() {
            var nextElement = $(this).next();
            if (!nextElement.hasClass('nonCollapsible') && nextElement.length > 0) {
                var titleText = $(this).text();
                var nextId, classes, icon;
                if (typeof nextElement.attr('id') === 'undefined') {
                    // create an id
                    nextId = titleText.replace(/([|!&;$%@"<>()+,])|(^\s*)|(\s*$)|( )/g, '').toLowerCase(); // strip all bad chars, spaces, make lowercase
                    nextElement.attr('id', nextId); // set the new id
                } else {
                    nextId = nextElement.attr('id');
                }
                if (nextId && localStorage.getItem(nextId) === 'true' ) {
                    classes = 'collapse';
                    icon = 'expand';
                } else {
                    classes = 'collapse in';
                    icon = 'compress';
                }
                nextElement.addClass(classes);
                $(this).html(titleText + ' <a role="button" data-toggle="collapse" href="#' + nextId + '"><i class="fa fa-' + icon + ' small block-expander"></i></a>');
            }
        });
        $('.collapse').on('hide.bs.collapse', function() {
            if (this.id) {
                localStorage.setItem(this.id, 'true');
            }
        }).on('show.bs.collapse', function() {
            if (this.id) {
                localStorage.removeItem(this.id);
            }
        });
        $('.block-expander').on('click', function() {
            $(this).toggleClass('fa-compress fa-expand');
        });
    });
})(jQuery);
