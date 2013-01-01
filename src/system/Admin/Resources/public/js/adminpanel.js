(function($) {
    function setupNotices() {
        $('#admin-systemnotices').zPanels({
            header: 'div > strong',
            preserveState: true
        })
    }
    $(document).ready(function() {
        setupNotices();
    });
})(jQuery);