// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {$(document).ready(function() {

/*******************************************************************************
 * Sort admin tabs 
*******************************************************************************/
        
$('#admintab').sortable({
    cursor: 'move',
    containment: 'parent',
    update: function( event, ui ) {
       var tab = new Array();
       $('#admintab li').each( function() {
           var catid = $(this).data('catid');
           if (catid !== undefined) {
                tab.push($(this).data('catid'));
           }
      });

      $.ajax({
            url: 'index.php?module=adminpanel&type=ajax&func=sortCategories',
            data: {admintabs: tab},
            error: function (response) {
                alert($.parseJSON(response.responseText).core.statusmsg);
            }
        });
    },
});

/**a****************************************************************************
 * Sort admin tabs 
*******************************************************************************/
        
$('#admintab').sortable({
    cursor: 'move',
    containment: 'parent',
    update: function(event, ui) {
       var tabs = new Array();
       $('#admintab li').each( function() {
           var catid = $(this).data('catid');
           if (catid !== undefined) {
                tabs.push(catid);
           }
      });
      $.ajax({
            url: "index.php?module=adminpanel&type=ajax&func=sortCategories",
            data: {admintabs: tabs},
            error: function (response) {
                alert($.parseJSON(response.responseText).core.statusmsg);
            }
        });
    },
});

/*******************************************************************************
 * Add admin tab
*******************************************************************************/

$('#admintab-addcat-link').popover({
    content: function(ele) { return $('#admintab-addcat-popover').html(); },
    html: true
 });

$(document).on('click', '#admintab-addcat-link', function (e) {
    e.preventDefault();
    $('#admintab-addcat-name').focus();
});

$(document).on('click', '#admintab-addcat-cancel', function (e) {
    $('#admintab-addcat-link').popover('hide')
});

$(document).on('click', '#admintab-addcat-save', function (e) {
    $('#admintab-addcat-link').popover('hide')
    var name = $('#admintab-addcat-name').val();
    if (name === '') {
        alert(('You must enter a name for the new category'));
    }
    $.ajax({
        url: 'index.php?module=ZikulaAdminModule&type=ajax&func=addCategory',
        data: {
            name: name
        },
        success: function(response) {
            console.log(response);
            var newtab = '<li class="dropdown">'+
                         '<a class="dropdown-toggle" href="'+
                         response.data.url+
                         '">'
                         +response.data.name+
                         '</a></li>';
            $('#admintab li').last().before(newtab);
            $('#admintab-addcat-link').popover('hide');
        },
        error: function (response) {
            alert($.parseJSON(response.responseText).core.statusmsg);                    
        }
    })
});

/*******************************************************************************
 * Drag and drop modules to admin tabs 
*******************************************************************************/

// dragable
$('.draggable').draggable({
    revert: 'invalid',
    containment: 'document',
    helper: 'clone',
    cursor: 'move',
    handle: 'span.modulelist-drag',
});

$('.droppable').droppable({
    over: function( event, ui ) {
       $(this).find('a:first').addClass('admintab-dropover');
    },
    out: function( event, ui ) {
       $(this).find('a:first').removeClass('admintab-dropover');
    },
    accept: '.draggable',
    tolerance: 'pointer',
    drop: function( event, ui ) {
        // prevent mouse over
        $('ul.nav-mouseover li.dropdown').unbind('hover');
        $(this).off('click').on('mouseout', function rebindNavMouseOver() {
            $('ul.nav-mouseover li.dropdown').hover(function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn();
            }, function() {
                $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut();
            });
        });
        $(this).find('a:first').removeClass('admintab-dropover');

        // do nothing is icon was moved to the current category
        var categoryId = $(this).data('catid');
        var currentCategoryId = $('#admintab li.active').data('catid');
        if (categoryId === currentCategoryId) {
            return false;
        }

        $.ajax({
            url: 'index.php?module=adminpanel&type=ajax&func=changeModuleCategory',
            data: {
                modid: ui.draggable.data('modid'),
                cat: categoryId
            },
            success: function(response) {
                ui.draggable.remove();
            },
            error: function (response) {
                alert($.parseJSON(response.responseText).core.statusmsg);
            }
        });
    }
});

/*******************************************************************************
 * Sort modules 
*******************************************************************************/

$('#modulelist').sortable({
    cursor: 'move',
    containment: 'parent',
    handle: 'span.modulelist-sort',
    update: function(event, ui) {
        var modules = new Array();
        $('#modulelist li').each( function() {
            var modid = $(this).data('modid');
            if (modid !== undefined) {
                modules.push($(this).data('modid'));
            }
        });
        $.ajax({
            url: 'index.php?module=adminpanel&type=ajax&func=sortModules',
            data: {modules: modules},
            error: function (response) {
                alert($.parseJSON(response.responseText).core.statusmsg);
            }
        });
    },
});

/*******************************************************************************
 * Module functions dropdown
*******************************************************************************/

$('.dropdown-toggle').click( function() {
    var container = $(this).parent().parent().parent().parent();
    var containerTop = container.position().top;
    var itemTop      = $(this).parent().position().top;
    var avaibleHeight = container.height() - (itemTop-containerTop);
    var neededHeight = $(this).parent().find('ul').height();
    if (neededHeight > avaibleHeight) {
        container.height(container.height() + neededHeight - avaibleHeight + 5);
    }
});

/*******************************************************************************
 * Toggle developer notices
*******************************************************************************/

$(document).on('click', '#z-developernotices strong', function(e) {
    var ul = $('#z-developernotices ul');
    var span = $('#z-developernotices span');
    if( $('#z-developernotices ul').is(':visible') ) {
        ul.slideUp();
        span.removeClass('icon-caret-down');
        span.addClass('icon-caret-right');
    } else {
        ul.slideDown();
        span.removeClass('icon-caret-right');
        span.addClass('icon-caret-down');
    } 
});


});})(jQuery);