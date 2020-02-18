// Copyright Zikula Foundation, licensed MIT.

(function ($) {
    $(document).ready(function () {
        if (1 > $('.module-help').length) {
            return;
        }

        $('.module-help').click(function (event) {
            event.preventDefault();

            var sidebarClass = 'fixed-' + $(this).data('help-mode').replace('sidebar-', '');
            var sidebar = `
                <nav id="helpBar" class="navbar navbar-expand-md navbar-dark bg-primary ${sidebarClass}">
                </nav>
            `;

            $('#helpBar').remove();
            $('body').addClass('help-open').append(sidebar);

            var helpUrl = $(this).attr('href');
            $('#helpBar').html(`
                <iframe id="helpFrame" width="100%" onload="updateIframeHeight()" src="${helpUrl}?raw=1" frameborder="0" scrolling="yes"></iframe>
                <button id="btnCloseHelp" type="button" class="btn btn-secondary btn-sm my-3"><i class="fas fa-times"></i> ${Translator.trans('Close help')}</button>
                <button id="btnOpenSeparateHelp" type="button" class="btn btn-secondary btn-sm"><i class="fas fa-window-restore"></i> ${Translator.trans('Open in new window')}</button>
            `);
            $('#btnCloseHelp').click(function (innerEvent) {
                $('#helpBar').remove();
                $('body').removeClass('help-open');
            });
            $('#btnOpenSeparateHelp').click(function (innerEvent) {
                window.open(helpUrl);
            });
        });
    });
})(jQuery);

function updateIframeHeight() {
    jQuery('#helpFrame').height(jQuery('#helpFrame').contents().height() + 100);
}
