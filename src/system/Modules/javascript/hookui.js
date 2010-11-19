/**
 * activate all buttons to (de-)attach modules
 *
 */
function initactionbuttons()
{
    $$('a.actionbutton').each(function(item) {
        item.removeClassName('actionbutton');
    });
}

/**
 * Toggle a module's attached/detached status
 *
 *@params id;
 *@params provider;
 *@return none;
 */
function togglemodule(id, provider)
{
    var pars = "id=" + id + "&provider=" + provider;

    new Zikula.Ajax.Request(
        "ajax.php?module=Modules&func=togglemodule",
        {
            method: 'get',
            parameters: pars,
            onComplete: togglemodule_response
        });
}

/**
 * Ajax response function for updating module status
 *
 *@params req Ajax response;
 *@return none;
 */
function togglemodule_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    $('attached_' + data.id).toggle();
    $('detached_' + data.id).toggle();
}