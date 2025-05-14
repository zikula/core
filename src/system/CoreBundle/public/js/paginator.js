// Copyright Zikula, licensed MIT.

(function($) {
    var paginationLinks = [];
    var pagers = null;
    function bootstrapifyPager(pager) {
        if (null === pager) {
            return;
        }
        pager.find('li').addClass('page-item');
        pager.find('li > span').addClass('page-link');
        pager.find('li > a').each(function (index) {
            var pageNumber = $(this).attr('href');
            $(this).attr('href', paginationLinks[pageNumber]);
        });
    };
    $(document).ready(function() {
        pagers = $('.pagination');
        pagers.each(function (index) {
            var pager = $(this);
            pager.addClass('d-none');
            pager.find('li > a').each(function (index) {
                var pageNumber = $(this).data('page');
                if (pageNumber) {
                    paginationLinks[pageNumber] = $(this).attr('href');
                }
            });
            pager.pagination({
                pages: pager.data('pages'),
                currentPage: pager.data('currentpage'),
                hrefTextPrefix: '',
                prevText: '<i class="fas fw fa-arrow-left"></i>',
                nextText: '<i class="fas fw fa-arrow-right"></i>',
                onPageClick: function (pageNumber, event) {
                    bootstrapifyPager(pager);
                    if (paginationLinks[pageNumber]) {
                        document.location = paginationLinks[pageNumber];

                        return;
                    }
                },
                onInit: function () {
                    bootstrapifyPager(pager);
                    pager.removeClass('d-none');
                }
            });
        });
    });
})(jQuery);
