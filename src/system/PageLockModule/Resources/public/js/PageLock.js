// Copyright Zikula Foundation, licensed MIT.

var PageLock = {};

(function ($) {
    PageLock.Timer = null;

    // Called on window load for unlocked page
    PageLock.UnlockedPage = function()
    {
        clearTimeout(PageLock.Timer);
        PageLock.Timer = setTimeout(PageLock.RefreshLock, PageLock.PingTime*1000);
    };

    // Called on window load for locked page
    PageLock.LockedPage = function()
    {
        PageLock.ShowOverlay();
        clearTimeout(PageLock.Timer);
        PageLock.Timer = setTimeout(PageLock.CheckLock, PageLock.PingTime*1000);
    };

    // Button event handler for "break lock"
    PageLock.BreakLock = function()
    {
        if (confirm(/*Zikula.__(*/'Are you sure you want to break this lock?'/*)*/) == false) {
            return;
        }

        // action has been confirmed

        PageLock.StopLocking(false);
        PageLock.LockBroken = true;
        PageLock.HideOverlay();
    };

    // Button event handler for "back"
    PageLock.Cancel = function()
    {
        PageLock.HideOverlay();
        window.location = PageLock.ReturnUrl;
        return true;
    };

    // Ajax method for refreshing existing lock
    PageLock.RefreshLock = function()
    {
        $.ajax({
            url: Routing.generate('zikulapagelockmodule_lock_refreshpagelock'),
            data: {
                lockname: PageLock.LockName
            },
            success: function (result) {
                if (!result.data.hasLock) {
                    alert(result.data.message);
                }

                clearTimeout(PageLock.Timer);
                PageLock.Timer = setTimeout(PageLock.RefreshLock, PageLock.PingTime*1000);
            }
        });
    };

    // Ajax method for trying to fetch lock (waiting for a lock)
    PageLock.CheckLock = function()
    {
        PageLock.BlinkAnimation('#pageLockOverlayLED');

        $.ajax({
            url: Routing.generate('zikulapagelockmodule_lock_checkpagelock'),
            data: {
                lockname: PageLock.LockName
            },
            success: function (result) {
                if (result.data.hasLock) {
                    PageLock.StopLocking(true);
                } else {
                    clearTimeout(PageLock.Timer);
                    PageLock.Timer = setTimeout(PageLock.CheckLock, PageLock.PingTime*1000);
                }
            }
        });
    };

    // Function to stop showing locked window overlay and form
    PageLock.StopLocking = function(doReload)
    {
        if (doReload) {
            // Reload in order to refresh data
            window.location = window.location;
        } else {
            PageLock.HideOverlay();
        }
    };

    // Display locked window overlay and form
    PageLock.ShowOverlay = function()
    {
        $('#pageLockModal').modal({
            backdrop: 'static',
            keyboard: false
        });
        $('#pageLockBackButton').on('click', function () {
            if (!PageLock.LockBroken) {
                PageLock.Cancel();
            }
        });
        $('#pageLockRecheckButton').on('click', function () {
            PageLock.CheckLock();
        });
        $('#pageLockIgnoreButton').on('click', function () {
            PageLock.BreakLock();
        });
    };

    PageLock.HideOverlay = function()
    {
        $('#pageLockModal').modal('hide');
        return;
    };

    // see http://stackoverflow.com/questions/5205445/jquery-blinking-highlight-effect-on-div
    PageLock.BlinkAnimation = function(id)
    {
        $(id).animate({ backgroundColor: '#FF3030' }, {
            duration: 100, 
            complete: function() {
                // reset
                $(id).delay(100).animate({ backgroundColor: '#B0001D' }, {
                    duration: 100,
                });

            }
        });
    }
})(jQuery);
