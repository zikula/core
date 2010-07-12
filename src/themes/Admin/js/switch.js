/**
* Admin UI StyleSwitcher modified by dmm
* Based on work of Kelvin Luck ( http://www.kelvinluck.com/ )
* with no Restrictions
**/
jQuery.noConflict();
(function(jQuery)
	{
		// Local vars for toggle the Admin-Styles
		var availableStylesheets = [];
		var activeStylesheetIndex = 0;
		
		// loop through available Admin-Styles
		// e.g. blue.css, grey.css, and so on...
		jQuery.stylesheetToggle = function()
		{
			activeStylesheetIndex ++;
			activeStylesheetIndex %= availableStylesheets.length;
			jQuery.stylesheetSwitch(availableStylesheets[activeStylesheetIndex]);
		};
		
		// To switch to a specific named stylesheet
		jQuery.stylesheetSwitch = function(styleName)
		{
			jQuery('link[@rel*=style][title]').each(
				function(i) 
				{
					this.disabled = true;
					if (this.getAttribute('title') == styleName) {
						this.disabled = false;
						activeStylesheetIndex = i;
					}
				}
			);
			createCookie('style', styleName, 365);
		};
		
		// To initialise the stylesheet with it's 
		jQuery.stylesheetInit = function()
		{
			jQuery('link[rel*=style][title]').each(
				function(i) 
				{
					availableStylesheets.push(this.getAttribute('title'));
				}
			);
			var c = readCookie('style');
			if (c) {
				jQuery.stylesheetSwitch(c);
			}
		};
	}
)(jQuery);

// cookie functions original from http://www.quirksmode.org/js/cookies.html
// without restrictions, modified by dmm
function createCookie(name,value,days)
{
	if (days)
	{
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}
function readCookie(name)
{
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++)
	{
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
function eraseCookie(name)
{
	createCookie(name,"",-1);
}