// Copyright Zikula Foundation 2015 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {
    $(document).ready(function() {
        $('#zikulablocksmodule-block-view-modal').on('show.bs.modal', function (e) {
            var link = $(e.relatedTarget);
            $(this).find(".modal-body").load(link.attr("href"));
        });
    })
})(jQuery);
