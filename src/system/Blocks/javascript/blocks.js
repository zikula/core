/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage Blocks
 */


/**
 * Inits block sorting
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
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
 *@author Frank Schummertz
 */
function blockorderchanged()
{
    var pars = "module=Blocks&func=changeblockorder&authid=" + $F('blocksauthid')
               + "&position=" + $F('position')
               + "&" + Sortable.serialize('assignedblocklist', { 'name': 'blockorder' });
    var myAjax = new Ajax.Request(
        "ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: blockorderchanged_response
        });
}

/**
 * Ajax response function for updating new sort order: cleanup
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function blockorderchanged_response(req)
{
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }

    var json = pndejsonize(req.responseText);
    pnupdateauthids(json.authid);

    pnrecolor('assignedblocklist', 'assignedblocklistheader');
    pnrecolor('unassignedblocklist', 'unassignedblocklistheader');
    
}

/**
 * Toggle a blocks active/inactive status
 *
 *@params bid;
 *@return none;
 *@author Frank Schummertz
 */
function toggleblock(bid)
{
    var pars = "module=Blocks&func=toggleblock&bid=" + bid;
    var myAjax = new Ajax.Request(
        "ajax.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: toggleblock_response
        });
}

/**
 * Ajax response function for updating block status: cleanup
 *
 *@params none;
 *@return none;
 *@author Frank Schummertz
 */
function toggleblock_response(req)
{
    if (req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }

    var json = pndejsonize(req.responseText);

    $('active_' + json.bid).toggle();
    $('inactive_' + json.bid).toggle();
    $('activity_' + json.bid).update((($('activity_' + json.bid).innerHTML == msgBlockStatusInactive) ? msgBlockStatusActive : msgBlockStatusInactive));
}
