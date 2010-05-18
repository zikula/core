/*
 *  $Id: modules_admin_hooks.htm 18648 2006-04-04 19:35:08Z markwest $ 
 */
 
/**
 * create the onload function to enable the drag&drop for sequencing
 *
 */
Event.observe(window, 'load', function()
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
            });
    });