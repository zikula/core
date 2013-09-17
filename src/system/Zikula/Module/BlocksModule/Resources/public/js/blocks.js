// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * Inits block sorting
 *
 *@params none;
 *@return none;
 */
function blocksmodifyinit()
{
    Sortable.create("assignedblocklist",
                    {
                      dropOnEmpty: true,
                      only: 'z-sortable',
                      containment:["assignedblocklist","unassignedblocklist"],
                      onUpdate: blockorderchanged
                    });

    Sortable.create("unassignedblocklist",
                    {
                      dropOnEmpty: true,
                      only: 'z-sortable',
                      containment:["assignedblocklist","unassignedblocklist"]
                    });

    initactivationbuttons();
    $A(document.getElementsByClassName('z-sortable')).each(
        function(node) 
        {
            var thisblockid = node.id.split('_')[1];
            Element.addClassName('block_' + thisblockid, 'z-itemsort')
        }
    )
}

/**
 * Stores the new sort order. This function gets called automatically
 * from the Sortable when a 'drop' action has been detected
 *
 *@params none;
 *@return none;
 */
function blockorderchanged()
{
    var pars = "position=" + $F('position')
               + "&" + Sortable.serialize('assignedblocklist', { 'name': 'blockorder' });

    new Zikula.Ajax.Request(
        "index.php?module=Blocks&type=ajax&func=changeblockorder",
        {
            parameters: pars,
            onComplete: blockorderchanged_response
        });
}

/**
 * Ajax response function for updating new sort order: cleanup
 *
 *@params none;
 *@return none;
 */
function blockorderchanged_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    Zikula.recolor('assignedblocklist', 'assignedblocklistheader');
    Zikula.recolor('unassignedblocklist', 'unassignedblocklistheader');
    
}