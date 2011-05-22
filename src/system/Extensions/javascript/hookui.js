// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).
var appendItemBeforeResponse = true;
var removeItemBeforeResponse = true;
var cloneDraggedItem = false;

/**
 * Bind a provider's area to a subscribers's area
 *
 *@params subscriberarea;
 *@params providerarea;
 *@return none;
 */
function bindSubscriberAreaToProviderArea(sarea, parea)
{
    subscriberAreaToggle(sarea, parea);
}

/**
 * Unbind a provider's area from a subscribers's area
 *
 *@params subscriberarea;
 *@params providerarea;
 *@return none;
 */
function unbindProviderAreaFromSubscriberArea(sarea_id, sarea, parea_id, parea)
{
    if (removeItemBeforeResponse) {
        removeProviderAreaFromSubscriberArea(sarea_id, parea_id);
    }
    
    subscriberAreaToggle(sarea, parea);
}

/**
 * Toggle a subscribers's area attached/detached status
 *
 *@params subscriberarea;
 *@params providerarea;
 *@return none;
 */
function subscriberAreaToggle(sarea, parea)
{   
    var pars = {
        subscriberarea: sarea,
        providerarea: parea
    };

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
    
    if (data.action == 'bind') {
        if (!appendItemBeforeResponse) {
            appendProviderAreaToSubscriberArea(data.subscriberarea_id, data.subscriberarea, data.providerarea_id);
        }
    } else if (data.action == 'unbind') {
        if (!removeItemBeforeResponse) {
            removeProviderAreaFromSubscriberArea(data.subscriberarea_id, data.providerarea_id)
        }
    }
}

/**
 * append dragged area to our list of attached areas
 *
 *@params none;
 *@return none;
 */
function appendProviderAreaToSubscriberArea(sarea_id, sarea_name, parea_id)
{   
    var area_to_attach = $('availablearea_' + parea_id);
    var area_to_attach_to = $('attachedareassortlist_' + sarea_id);
    var empty_area = $('attachedarea_empty_' + sarea_id);
    
    // if cloneDraggedItem is set to true, clone the dragged item before use. 
    // otherwise just use the dragged item
    if (cloneDraggedItem) {
        var newitem = area_to_attach.cloneNode(true);
    } else {
        var newitem = area_to_attach;
    }
    
    newitem.id = newitem.id.replace('availablearea_', 'attachedarea_');
    newitem.innerHTML = newitem.innerHTML.replace(new RegExp('availablearea_', 'g'), 'attachedarea_');
    newitem.innerHTML = newitem.innerHTML.replace(' z-hide', '');
    newitem.innerHTML = newitem.innerHTML.replace('##id', sarea_id);
    newitem.innerHTML = newitem.innerHTML.replace('##name', sarea_name);
    newitem.removeClassName('z-draggable');
    newitem.removeClassName('z-itemdragleft');
    newitem.addClassName('z-sortable');
    newitem.addClassName('z-itemsort');
    newitem.style.opacity = 1;
    newitem.style.top = 0;
    newitem.style.left = 0;
    
    // hide empty_area if is visible
    if (!empty_area.hasClassName('z-hide')) {
        empty_area.addClassName('z-hide');
    }
    
    // append dragged item to our list
    area_to_attach_to.appendChild(newitem);

    // create the sortable area
    createSortable(area_to_attach_to.id);

    // create the dropable area
    createDroppable(area_to_attach_to.id);

    // recolor
    Zikula.recolor(area_to_attach_to.id, 'z-itemheader');
}

/**
 * append dragged area to our list of attached areas
 *
 *@params none;
 *@return none;
 */
function removeProviderAreaFromSubscriberArea(sarea_id, parea_id)
{
    var area_to_detach = $('attachedarea_' + parea_id);
    area_to_detach.remove();
    
    // is area now empty?
    var total_areas_attached = 0;
    var area_to_detach_from = $('attachedareassortlist_' + sarea_id);
    $$('#' + area_to_detach_from.id + ' li.z-sortable').each(function(element) {
        total_areas_attached++;
    });
    
    // if there no more areas attached, show empty_area
    if (total_areas_attached == 0) {
        $('attachedarea_empty_' + sarea_id).removeClassName('z-hide');
    }

    // recolor
    Zikula.recolor(area_to_detach_from.id, 'z-itemheader');
}

/**
 * Inits sorting of attached provider areas
 *
 *@params none;
 *@return none;
 */
function initAreasSortables()
{
    // add class 'z-itemsort' to all items with class 'z-sortable'
    $A(document.getElementsByClassName('z-sortable')).each(
        function(node) {
            var thisattachedarea = node.id.split('_')[1];
            Element.addClassName('attachedarea_' + thisattachedarea, 'z-itemsort');
        }
    )
    
    // loop through module's subscriber areas and create sortables
    for (var i=0; i < subscriber_areas.length; i++) {
        var area_id = 'attachedareassortlist_'+subscriber_areas[i];
        createSortable(area_id);
    }
}

/**
 * Create a sortable area, given the area id
 *
 *@params none;
 *@return none;
 */
function createSortable(area_id)
{
    Sortable.create(area_id,
    {
        dropOnEmpty: true,
        only: 'z-sortable',
        containment:[area_id],
        onUpdate: changeAttachedAreaOrder
    });
}

/**
 * Stores the new sort order. This function gets called automatically
 * from the Sortable when a 'drop' action has been detected
 *
 *@params none;
 *@return none;
 */
function changeAttachedAreaOrder()
{
    // this will be the id of the ol
    var ol_id = this.element.id;

    // the area of our subscriber
    var subscriber_area = $(ol_id+'_a').value;

    // the areas of the providers that are attached to the area of our subscriber
    var providers_areas = '';
    $$('#' + ol_id + ' li.z-sortable').each(function(el) {
        providers_areas += '&providerarea[]=' + $(el.id + '_a').value;
    });

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

/**
 * Inits dragging of available provider areas
 *
 *@params none;
 *@return none;
 */
function initAreasDraggables()
{
    $$('ol li.z-draggable').each(function(node) {
        var thisavailablearea = node.id.split('_')[1];
        Element.addClassName('availablearea_' + thisavailablearea, 'z-itemdragleft');
        new Draggable('availablearea_' + thisavailablearea, {
            revert: true,
            ghosting: false
        });
    });
}

/**
 * Inits creation of droppables from the available subscriber areas
 *
 *@params none;
 *@return none;
 */
function initAreasDroppables()
{   
    // loop through module's subscriber areas and create droppables
    for (var i=0; i < subscriber_areas.length; i++) {
        
        var area_id = 'attachedareassortlist_'+subscriber_areas[i];
        createDroppable(area_id);
    }
}

/**
 * Create a droppable area, given the area id
 *
 *@params none;
 *@return none;
 */
function createDroppable(area_id)
{
    Droppables.add(area_id,
    {
        accept: 'z-draggable',
        onDrop: function(dragged, dropped, event) {

            //alert('Dragged: ' + dragged.id + ' - Dropped onto: ' + dropped.id);

            // gather some info about the subscriber
            var subscriber = {
                'category': $(dropped.id+'_c').value,
                'area': $(dropped.id+'_a').value,
                'identifier': $(dropped.id+'_i').value
            };

            // gather some info about the provider
            var provider = {
                'category': $(dragged.id+'_c').value,
                'area': $(dragged.id+'_a').value,
                'identifier': $(dragged.id+'_i').value
            };

            // allow connections of the same category only
            if (provider.category != subscriber.category) {
                return;
            }

            // is the provider area already attached?
            // loop though all attached areas of the subscriber area to find out
            var already_attached = false;
            var subscriber_area_ol_id = 'attachedareassortlist_' + subscriber.identifier;
            $$('#' + subscriber_area_ol_id + ' li.z-sortable').each(function(element) {
                if (element.id.split('_')[1] == provider.identifier) {
                    already_attached = true;
                    throw $break;
                }
            });
            if (already_attached) {
                return;
            }

            // attach provider area to subscriber area
            bindSubscriberAreaToProviderArea(subscriber.area, provider.area);
            
            if (appendItemBeforeResponse) {
                appendProviderAreaToSubscriberArea(subscriber.identifier, subscriber.area, provider.identifier);
            }
        }
    });
}