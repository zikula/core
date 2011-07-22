// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * create the onload function to enable the drag&drop for sequencing
 *
 */
document.observe('dom:loaded', function() 
    {
        // show link to extended hook settings 
        $('extendedhookslinks').removeClassName('z-hide');
        $('extendedhookslinks').addClassName('z-show');
    }
); 