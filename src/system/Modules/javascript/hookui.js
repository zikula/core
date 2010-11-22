/**
 * Toggle a subscribers's attached/detached status
 *
 *@params subscriber;
 *@params provider;
 *@return none;
 */
function togglesubscriberstatus(subscriber, provider)
{
    var pars = "subscriber=" + subscriber + "&provider=" + provider;

    new Zikula.Ajax.Request(
        "ajax.php?module=Modules&func=togglesubscriberstatus",
        {
            method: 'get',
            parameters: pars,
            onComplete: togglesubscriberstatus_response
        });
}

/**
 * Ajax response function for updating subscriber module status
 *
 *@params req Ajax response;
 *@return none;
 */
function togglesubscriberstatus_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();
    
    $('attached_' + data.id).toggle();
    $('detached_' + data.id).toggle();
}

/**
 * Inits sorting of providers
 *
 *@params none;
 *@return none;
 */
function initprovidersorting()
{
    Sortable.create('providerssortlist',
                    {
                      dropOnEmpty: true,
                      only: 'z-sortable',
                      containment:['providerssortlist'],
                      onUpdate: changeproviderorder
                    });

    $A(document.getElementsByClassName('z-sortable')).each(
        function(node) {
            var thisproviderid = node.id.split('_')[1];
            Element.addClassName('provider_' + thisproviderid, 'z-itemsort');
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
function changeproviderorder()
{
    var pars = 'subscriber=' + subscriber +
               '&' + Sortable.serialize('providerssortlist', { 'name': 'providersorder' });

    new Zikula.Ajax.Request(
        'ajax.php?module=Modules&func=changeproviderorder',
        {
            method: 'get',
            parameters: pars,
            onComplete: changeproviderorder_response
        });
}

/**
 * Ajax response function for updating new sort order
 *
 *@params req;
 *@return none;
 */
function changeproviderorder_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }
    
    pnrecolor('providerssortlist', 'providerssortlistheader');
}