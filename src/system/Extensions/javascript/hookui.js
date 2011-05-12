// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * Toggle a subscribers's area attached/detached status
 *
 *@params subscriberarea;
 *@params providerarea;
 *@return none;
 */
function subscriberAreaToggle()
{
    var pars = this.value.replace("#", "&");

    new Zikula.Ajax.Request(
        "ajax.php?module=Extensions&func=togglesubscriberareastatus",
        {
            parameters: pars,
            onComplete: togglesubscriberareastatus_response
        });
}

/**
 * Ajax response function for updating subscriber's area binding status
 *
 *@params req Ajax response;
 *@return none;
 */
function togglesubscriberareastatus_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    // in case module is SubscriberSelfCapable
    // refresh page to reload attached areas
    if (data.isSubscriberSelfCapable) {
        $$('input[type=checkbox]').each(function(e) {
            if (e.type == 'checkbox') {
                e.disabled = true;
            }
        });
        window.location = data.refreshURL;
    }
}

/**
 * Inits sorting of provider areas
 *
 *@params none;
 *@return none;
 */
function initproviderareassorting()
{
    for (var i=0; i < providerareas.length; i++) {
        var area = 'providerareassortlist_'+providerareas[i];

        Sortable.create(area,
        {
            dropOnEmpty: true,
            only: 'z-sortable',
            containment:[area],
            onUpdate: changeproviderareaorder
        });

        $A(document.getElementsByClassName('z-sortable')).each(
            function(node) {
                var thisproviderarea = node.id.split('_')[1];
                Element.addClassName('providerarea_' + thisproviderarea, 'z-itemsort');
            }
        )
    }
}

/**
 * Stores the new sort order. This function gets called automatically
 * from the Sortable when a 'drop' action has been detected
 *
 *@params none;
 *@return none;
 */
function changeproviderareaorder()
{
    // this will be the id of the ol
    var ol_id = this.element.id;

    // the area of our subscriber
    var subscriber_area = $(ol_id+'_h').value;

    // the areas of the providers that are attached to the area of our subscriber
    // note that the loop starts from 1 and not 0, because the first item (0)
    // is the area of our subscriber (which we already have)
    var providers_areas = '';
    var areas = $$('#' + ol_id + ' input');
    for (var i=1 ; i < areas.length ; i++) {
        providers_areas += '&providerarea[]=' + areas[i].value;
    }

    var pars = 'ol_id=' + ol_id +
               '&subscriberarea=' + subscriber_area +
               providers_areas;

    new Zikula.Ajax.Request(
        'ajax.php?module=Extensions&func=changeproviderareaorder',
        {
            parameters: pars,
            onComplete: changeproviderareaorder_response
        });
}

/**
 * Ajax response function for updating new sort order
 *
 *@params req;
 *@return none;
 */
function changeproviderareaorder_response(req)
{
    if (!req.isSuccess()) {
        Zikula.showajaxerror(req.getMessage());
        return;
    }

    var data = req.getData();

    Zikula.recolor(data.ol_id, $(data.ol_id).down(0).id);
}