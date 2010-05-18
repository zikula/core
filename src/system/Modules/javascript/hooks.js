/*
 *  $Id: modules_admin_hooks.htm 18648 2006-04-04 19:35:08Z markwest $ 
 */
 
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