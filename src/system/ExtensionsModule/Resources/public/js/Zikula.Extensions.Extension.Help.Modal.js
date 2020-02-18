// Copyright Zikula Foundation, licensed MIT.

(function ($) {
    $(document).ready(function () {
        if (1 > $('.module-help').length) {
            return;
        }

        $('.module-help').click(function (event) {
            event.preventDefault();
            var modal = `
                <div id="helpModal" class="modal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-times"></i> ${Translator.trans('Close help')}</button>
                                <button id="btnOpenSeparateHelp" type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fas fa-window-restore"></i> ${Translator.trans('Open in new window')}</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#helpModal').remove();
            $('body').append(modal);

            var helpUrl = $(this).attr('href');
            $('#helpModal .modal-body').html('<iframe id="helpFrame" width="100%" onload="updateIframeHeight()" src="' + helpUrl + '?raw=1" frameborder="0" scrolling="yes"></iframe>');
            $('#helpModal').modal('show');
            $('#btnOpenSeparateHelp').click(function (innerEvent) {
                window.open(helpUrl);
            });
        });
    });
})(jQuery);

function updateIframeHeight() {
    jQuery('#helpFrame').height(jQuery('#helpFrame').contents().height());
}
