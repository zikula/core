// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var PageLock = {};

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
    Zikula.UI.Confirm(Zikula.__('Are you sure you want to break this lock?'),Zikula.__('Confirmation prompt'),function(res){
        if (res) {
            PageLock.StopLocking(false);
            PageLock.LockBroken = true;
            PageLock.HideOverlay();
        }
    });
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
    var pars = {
        lockname: PageLock.LockName
    }
    
    new Zikula.Ajax.Request(
      Zikula.Config.baseURL + "ajax.php?module=PageLock&func=refreshpagelock",
      {
          parameters: pars,
          onComplete: PageLock.RefreshLockComplete
      });
};


PageLock.RefreshLockComplete = function(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    if (!data.hasLock)
    {
        alert(data.message);
    }

    clearTimeout(PageLock.Timer);
    PageLock.Timer = setTimeout(PageLock.RefreshLock, PageLock.PingTime*1000);
};


// Ajax method for trying to fetch lock (waiting for a lock)
PageLock.CheckLock = function()
{
    new Effect.Highlight('pageLockOverlayLED', { startcolor: "#FF3030", endcolor: "#B0001D" });

    var pars = {
        lockname: PageLock.LockName
    }
    
    new Zikula.Ajax.Request(
      Zikula.Config.baseURL + "ajax.php?module=PageLock&func=checkpagelock",
      {
          parameters: pars,
          onComplete: PageLock.CheckLockComplete
      });
};


PageLock.CheckLockComplete = function(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    if (data.hasLock)
    {
      //alert("Got lock!");
      PageLock.StopLocking(true);
    }
    else
    {
      //alert("Still waiting ...");
      clearTimeout(PageLock.Timer);
      PageLock.Timer = setTimeout(PageLock.CheckLock, PageLock.PingTime*1000);
    }
};


// Function to stop showing locked window overlay and form
PageLock.StopLocking = function(doReload)
{
    if (doReload)
    {
        // Reload in order to refresh data
        window.location = window.location;
    }
    else
    {
        PageLock.HideOverlay();
    }
};

// Display locked window overlay and form
PageLock.ShowOverlay = function()
{
    PageLock.Dialog = new Zikula.UI.Dialog(
        PageLock.LockedHTML,
        [
            {name: 'Cancel', value: 'Cancel', label: Zikula.__('Back')},//, action: PageLock.Cancel
            {name: 'CheckLock', value: 'CheckLock', label: Zikula.__('Check again'), close: false},
            {name: 'BreakLock', value: 'BreakLock', label: Zikula.__('Ignore Lock'), close: false},
        ],
        {modal: true, callback: PageLock.DialogCallback}
    );
    PageLock.Dialog.open();
};

PageLock.DialogCallback = function(res) {
    switch (res.value) {
        case 'BreakLock':
            PageLock.BreakLock();
            break;
        case 'CheckLock':
            PageLock.CheckLock();
            break;
        case 'Cancel':
        default:
            if (!PageLock.LockBroken) {
                PageLock.Cancel();
            }
            break;
    }
};

PageLock.HideOverlay = function()
{
    PageLock.Dialog.close();
    return;
};
