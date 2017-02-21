// Copyright Zikula Foundation, licensed MIT.

var appendItemBeforeResponse = true;
var removeItemBeforeResponse = true;
var cloneDraggedItem = true;

( function($) {

    /**
     * Sets z-odd / z-even on each li after append, move and delete.
     *
     * @param   {String} listclass   Class applied to the list of items.
     * @param   {String} headerclass Class applied to the header of the list.
     *
     * @return  void
     */
    recolorListElements = function(listclass, headerclass) {
        var odd = true;

        $('.' + listclass).children().each(function(index) {
            var elem = $(this);
            if (!elem.hasClass(headerclass)) {
                elem.removeClass('z-odd');
                elem.removeClass('z-even');

                if (odd == true) {
                    elem.addClass('z-odd');
                } else {
                    elem.addClass('z-even');
                }
                odd = !odd;
            }
        });
    };

    initHookAccordion = function(containerId)
    {
        $('#' + containerId + ' h4').addClass('z-panel-header z-pointer')
        $('#' + containerId).accordion({
            header: 'h4',
            activate: function(event, ui) {
                $('#' + containerId + ' h4').removeClass('z-panel-active');
                $(this).addClass('z-panel-active');
            }
        });
    };

    initHookSubscriber = function()
    {
        initAreasSortables();

        // init dragging of available provider areas
        $('ol li.z-draggable')
            .css('cursor', 'move')
            .draggable({
                cursor: 'move',
                revert: true
            });

        initAreasDroppables();

        initHookAccordion('hookProviderAreas');
        initHookAccordion('hookSubscriberAreas');

        $('#hookSubscriberAreas h4').addClass('attachedarea-header')
        $('#hookSubscriberAreas div').addClass('attachedarea-content');

        $('.sareas_category div').each(function(index) {
            $(this).data('panelIndex', index);
        });

        $('.detachlink').hover(
            function() {
                $(this).parent().parent().addClass('attachedarea_detach');
            }, function() {
                $(this).parent().parent().removeClass('attachedarea_detach');
            }
        );
    };

    /**
     * Inits sorting of attached provider areas.
     *
     * @params none;
     * @return none;
     */
    initAreasSortables = function()
    {
        // add class 'z-itemsort' to all items with class 'z-sortable'
        $('.z-sortable').addClass('z-itemsort');

        // loop through module's subscriber areas and create sortables
        for (var i = 0; i < subscriberAreas.length; i++) {
            createSortable(subscriberAreas[i]);
        }
    };

    /**
     * Creates a sortable area, given the area id.
     *
     * @params none;
     * @return none;
     */
    createSortable = function(area_id)
    {
        var listId = area_id + '_list';

        $('#' + listId).sortable({
            items: '.z-sortable',
            placeholder: 'ui-state-highlight',
            containment: $('#' + listId),
            update: changeAttachedAreaOrder
        });
        $('#' + listId).disableSelection();
    };

    /**
     * Binds a provider's area to a subscribers's area.
     *
     * @params subscriberarea;
     * @params providerarea;
     * @return none;
     */
    bindSubscriberAreaToProviderArea = function(sarea, parea)
    {
        subscriberAreaToggle(sarea, parea);
    };

    /**
     * Unbinds a provider's area from a subscribers's area.
     *
     * @params subscriberarea;
     * @params providerarea;
     * @return none;
     */
    unbindProviderAreaFromSubscriberArea = function(sarea_id, sarea, parea_id, parea)
    {
        if (removeItemBeforeResponse) {
            removeProviderAreaFromSubscriberArea(sarea_id, parea_id);
        }

        subscriberAreaToggle(sarea, parea);
    };

    /**
     * Toggles a subscribers's area attached/detached status.
     *
     * @params subscriberarea;
     * @params providerarea;
     * @return none;
     */
    subscriberAreaToggle = function(sarea, parea)
    {
        var pars = {
            subscriberarea: sarea,
            providerarea: parea
        };

        $.ajax({
            url: Routing.generate('zikula_hook_hook_togglesubscribeareastatus'),
            data: pars
        }).success(function(result) {
            var data = result.data;

            if (data.action == 'bind') {
                if (!appendItemBeforeResponse) {
                    appendProviderAreaToSubscriberArea(data.subscriberarea_id, data.subscriberarea, data.providerarea_id);
                }
            } else if (data.action == 'unbind') {
                if (!removeItemBeforeResponse) {
                    removeProviderAreaFromSubscriberArea(data.subscriberarea_id, data.providerarea_id)
                }
            }
        }).error(function(result) {
            alert(result.status + ': ' + result.statusText);
        });
    };

    /**
     * Appends a dragged area to our list of attached areas.
     *
     * @params subscriberarea;
     * @params providerarea;
     * @return none;
     */
    appendProviderAreaToSubscriberArea = function(sarea_id, sarea_name, parea_id)
    {
        var areaToAttach = $('#availablearea_' + parea_id + '-sarea_identifier');
        var areaToAttachTo = $('#sarea_' + sarea_id);
        var areaListToAttachTo = $('#sarea_' + sarea_id + '_list');
        var emptyArea = $('#sarea_empty_' + sarea_id);

        var newItem = null;
        // if cloneDraggedItem is set to true, clone the dragged item before use.
        // otherwise just use the dragged item
        if (cloneDraggedItem) {
            newItem = areaToAttach.get(0).cloneNode(true);
            newItem = $(newItem);
        } else {
            newItem = areaToAttach;
        }

        var newId = newItem.attr('id');
        newId = newId.replace('availablearea_', 'attachedarea_');
        newId = newId.replace('sarea_identifier', sarea_id);
        newItem.attr('id', newId);

        var newContent = newItem.html();
        newContent = newContent.replace(new RegExp('availablearea_', 'g'), 'attachedarea_');
        newContent = newContent.replace(new RegExp('sarea_identifier', 'g'), sarea_id);
        newContent = newContent.replace(' hide', '');
        newContent = newContent.replace('##id', sarea_id);
        newContent = newContent.replace('##name', sarea_name);
        newItem.html(newContent);

        // replace arrow-left icon by move icon (for later sorting)
        newItem.find('i.fa-long-arrow-left').removeClass('fa-long-arrow-left').addClass('fa-arrows');

        newItem
            .removeClass('z-draggable')
            .removeClass('z-itemdragleft')
            .addClass('z-sortable')
            .addClass('z-itemsort')
            .css({
                opacity: 1,
                top: 0,
                left: 0
            }
        );

        // hide empty_area if it is visible
        if (!emptyArea.hasClass('hide')) {
            emptyArea.addClass('hide');
        }

        // append dragged item to our list
        areaListToAttachTo.append(newItem);
        areaToAttachTo.css('height', (areaToAttachTo.height() + newItem.height() + 4) + 'px');

        // create the sortable area
        createSortable('sarea_' + sarea_id);

        // create the dropable area
        createDroppable('sarea_' + sarea_id);

        // recolor
        recolorListElements(areaListToAttachTo.attr('id'), 'z-itemheader');
    };

    /**
     * Removes an area from our list of attached areas.
     *
     * @params none;
     * @return none;
     */
    removeProviderAreaFromSubscriberArea = function(sarea_id, parea_id)
    {
        var areaToDetach = $('#attachedarea_' + parea_id + '-' + sarea_id);
        var heightOfDetachedArea = areaToDetach.height();
        areaToDetach.remove();

        var areaToDetachFrom = $('#sarea_' + sarea_id);

        // is area now empty?
        var amountOfAttachedAreas = 0;
        areaToDetachFrom.find('li.z-sortable').each(function(element) {
            amountOfAttachedAreas++;
        });

        // if there no more areas attached, show empty_area
        if (amountOfAttachedAreas == 0) {
            $('#sarea_empty_' + sarea_id).removeClass('hide');
        } else {
            areaToDetachFrom.css('height', (areaToDetachFrom.height() - heightOfDetachedArea) + 'px');
        }

        // recolor
        recolorListElements(areaToDetachFrom.attr('id'), 'z-itemheader');
    };

    /**
     * Stores the new sort order. This function gets called automatically
     * from the Sortable when a 'drop' action has been detected.
     *
     * @params none;
     * @return none;
     */
    changeAttachedAreaOrder = function(event, ui)
    {
        // this will be the id of the ol
        var listId = ui.item.parent().attr('id');

        // the area of our subscriber
        var subscriberArea = $('#' + listId.replace('_list', '_a')).val();

        // the areas of the providers that are attached to the area of our subscriber
        var providersAreas = '';
        $('#' + listId + ' li.z-sortable').each(function(index) {
            providersAreas += '&providerarea[]=' + $($(this).attr('id') + '_a').val();
        });

        var pars = 'ol_id=' + listId +
                '&subscriberarea=' + subscriberArea +
                providersAreas;

        $.ajax({
            url: Routing.generate('zikula_hook_hook_changeproviderareaorder'),
            data: pars
        }).success(function(result) {
            var data = result.data;

            // update new sort order
            recolorListElements(data.ol_id, $('#' + data.ol_id).down(0).attr('id'));
        }).error(function(result) {
            alert(result.status + ': ' + result.statusText);
        });
    };

    /**
     * Inits creation of droppables from the available subscriber areas
     *
     * @params none;
     * @return none;
     */
    initAreasDroppables = function()
    {
        // loop through module's subscriber areas and create droppables
        for (var i = 0; i < subscriberAreas.length; i++) {
            createDroppable(subscriberAreas[i]);
        }
    };

    /**
     * Create a droppable area, given the area id
     *
     * @params none;
     * @return none;
     */
    createDroppable = function(area_id)
    {
        $('#' + area_id).droppable({
            accept: '.z-draggable',
            hoverClass: 'z-hook-droppable-active',
            over: function(event, ui) {
                $('#hookSubscriberAreas').accordion({
                    active: $(this).data('panelIndex')
                });
            },
            drop: function(event, ui) {
                var subscriberId, providerId;

                subscriberId = $(this).attr('id');
                providerId = ui.draggable.attr('id');
                //alert('Dragged: ' + providerId + ' - Dropped onto: ' + subscriberId);

                // gather some info about the subscriber
                var subscriber = {
                    'category': $('#' + subscriberId + '_c').val(),
                    'area': $('#' + subscriberId + '_a').val(),
                    'identifier': $('#' + subscriberId + '_i').val()
                };

                // gather some info about the provider
                var provider = {
                    'category': $('#' + providerId + '_c').val(),
                    'area': $('#' + providerId + '_a').val(),
                    'identifier': $('#' + providerId + '_i').val()
                };

                // allow connections of the same category only
                if (provider.category != subscriber.category) {
                    return;
                }

                // is the provider area already attached?
                // loop though all attached areas of the subscriber area to find out
                var alreadyAttached = false;
                $('#' + subscriberId + ' li.z-sortable').each(function(index) {
                    if ($(this).attr('id').split('_')[1].split('-')[0] == provider.identifier) {
                        alreadyAttached = true;

                        // break the loop
                        return false;
                    }
                });

                if (alreadyAttached) {
                    return;
                }

                // attach provider area to subscriber area
                bindSubscriberAreaToProviderArea(subscriber.area, provider.area);

                if (appendItemBeforeResponse) {
                    appendProviderAreaToSubscriberArea(subscriber.identifier, subscriber.area, provider.identifier);
                }
            }
        });
    };
})(jQuery);
