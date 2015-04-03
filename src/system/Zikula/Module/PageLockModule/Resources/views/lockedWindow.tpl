<div class="modal fade" id="pageLockModal" tabindex="-1" role="dialog" aria-labelledby="pageLockModalLabel" aria-hidden="true">
    <div class="modal-dialog text-left">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{gt text='Close'}</span></button>
                <h4 class="modal-title" id="pageLockModalLabel"><i class="fa fa-circle-o-notch fa-spin"></i> {gt text='This page is locked'}</h4>
            </div>
            <div class="modal-body">
                <p>{gt text='This page is locked because another user is working on it. Please wait: the page will be unlocked automatically when the other user has finished, and you will be informed.'}</p>
                <div id="pageLockOverlayLED"></div>
                <p>{gt text='Locked by %s.' tag1=$lockedBy}</p>
            </div>
            <div class="modal-footer">
                <button type="button" id="pageLockBackButton" class="btn btn-default" data-dismiss="modal">{gt text='Back'}</button>
                <button type="button" id="pageLockRecheckButton" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-refresh"></i> {gt text='Check again'}</button>
                <button type="button" id="pageLockIgnoreButton" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-warning"></i> {gt text='Ignore Lock'}</button>
            </div>
        </div>
    </div>
</div>
