// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var PageLock = {};

PageLock.Timer = null;

// Called on window load for unlocked page
PageLock.UnlockedPage = function()
{
    clearTimeout(PageLock.Timer);
    PageLock.Timer = setTimeout(PageLock.RefreshLock, PageLock.PingTime*1000);
}


// Called on window load for locked page
PageLock.LockedPage = function()
{
    PageLock.ShowOverlay();
    $('pageLockCancelButton').focus();
    clearTimeout(PageLock.Timer);
    PageLock.Timer = setTimeout(PageLock.CheckLock, PageLock.PingTime*1000);
}


// Button event handler for "break lock"
PageLock.BreakLock = function()
{
    if (!confirm(PageLock.BreakLockWarning))
        return false;
    PageLock.StopLocking(false);
    return true;
}


// Button event handler for "back"
PageLock.Cancel = function()
{
    PageLock.HideOverlay();
    window.location = PageLock.ReturnUrl;
    return true;
}


// Ajax method for refreshing existing lock
PageLock.RefreshLock = function()
{
    var pars = "lockname=" + PageLock.LockName;
    
    new Zikula.Ajax.Request(
      Zikula.Config.baseURL + "ajax.php?module=PageLock&func=refreshpagelock",
      {
          method: 'post', 
          parameters: pars,
          onComplete: PageLock.RefreshLockComplete
      });
}


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
}


// Ajax method for trying to fetch lock (waiting for a lock)
PageLock.CheckLock = function()
{
    new Effect.Highlight('pageLockOverlayLED', { startcolor: "#FF3030", endcolor: "#B0001D" });

    var pars = "lockname=" + PageLock.LockName;
    
    new Zikula.Ajax.Request(
      Zikula.Config.baseURL + "ajax.php?module=PageLock&func=checkpagelock",
      {
          method: 'post', 
          parameters: pars,
          onComplete: PageLock.CheckLockComplete
      });
}


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
}


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
}


// Display locked window overlay and form
PageLock.ShowOverlay = function()
{
    var body = document.getElementsByTagName('body')[0];
    new Insertion.Top(body, PageLock.LockedHTML);

    var overlay = $('pageLockOverlay');
    var form = $('pageLockOverlayForm');
    
    var pageSize = PageLock.getPageSize();
    
    overlay.style.height = pageSize.pageHeight+"px";
    overlay.style.width = pageSize.pageWidth+"px";
    overlay.style.display = 'block';

    form.style.left = (pageSize.windowWidth/2 - 150) + "px";
    form.style.top = "100px"; //(pageSize.windowHeight/2 - 100) + "px";
}


PageLock.HideOverlay = function()
{
    var overlay = $('pageLockOverlay');
    var form = $('pageLockOverlayForm');
    
    overlay.style.display = 'none';
    form.style.display = 'none';
}





// getPageSize()
// Returns array with page width, height and window width, height
// Core code from - quirksmode.org
// Edit for Firefox by pHaez
//
PageLock.getPageSize = function()
{
	var xScroll, yScroll;
	
	if (window.innerHeight && window.scrollMaxY) {	
		xScroll = document.body.scrollWidth;
		yScroll = window.innerHeight + window.scrollMaxY;
	} else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	} else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}
	
	var windowWidth, windowHeight;
	if (self.innerHeight) {	// all except Explorer
		windowWidth = self.innerWidth;
		windowHeight = self.innerHeight;
	} else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	} else if (document.body) { // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}	
	
	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight){
		pageHeight = windowHeight;
	} else { 
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth){	
		pageWidth = windowWidth;
	} else {
		pageWidth = xScroll;
	}

	arrayPageSize = 
	{
	  pageWidth: pageWidth,
	  pageHeight: pageHeight,
	  windowWidth: windowWidth,
	  windowHeight: windowHeight
	}; 
	return arrayPageSize;
}
