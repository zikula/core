// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * create the onload function to enable the drag&drop for sequencing
 *
 */
document.observe('dom:loaded', function()
     {
        $A(document.getElementsByClassName('z-sortable')).each(
        function(node) 
        {
            node.setStyle({'cursor': 'move'}); 
        });
        // create the sortable divs
        $A(document.getElementsByClassName('hookcontainer')).each(function(el)
            {
                //var id = el.id;
                Sortable.create($(el.id),
                                {
                                    tag:  'div',
                                    only: 'z-sortable'
                                });
            }
	);
    }
);
