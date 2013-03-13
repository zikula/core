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
 * activate all buttons to (de-)activate blocks
 *
 */
function initactivationbuttons()
{
    $$('a.activationbutton').each(function(item) {
        item.removeClassName('activationbutton');
    });
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

/**
 * Toggle a blocks active/inactive status
 *
 *@params bid;
 *@return none;
 */
function toggleblock(bid)
{
    var pars = "bid=" + bid;

    new Zikula.Ajax.Request(
        "index.php?module=Blocks&type=ajax&func=toggleblock",
        {
            parameters: pars,
            onComplete: toggleblock_response
        });
}

/**
 * Ajax response function for updating block status: cleanup
 *
 *@params none;
 *@return none;
 */
function toggleblock_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    $('active_' + data.bid).toggle();
    $('inactive_' + data.bid).toggle();
    $('activity_' + data.bid).update((($('activity_' + data.bid).innerHTML == msgBlockStatusInactive) ? msgBlockStatusActive : msgBlockStatusInactive));
}
