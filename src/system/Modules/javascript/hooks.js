// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

/**
 * create the onload function to enable the drag&drop for sequencing
 *
 */
 Event.observe(window, 'load', function() 
	{
		// show link to extended hook settings 
		$('extendedhookslinks').removeClassName('z-hide');
		$('extendedhookslinks').addClassName('z-show');
	}
); 