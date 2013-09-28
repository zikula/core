// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {$(document).ready(function() {

/*******************************************************************************
 * Sort tabs 
*******************************************************************************/
        
$('#admintabs').sortable({
    cursor: 'move',
    containment: 'parent',
    update: function( event, ui ) {
       var tab = new Array();
       $('#admintabs li').each( function() {
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
$('#admintabs').sortable('disable');

/*******************************************************************************
 * Add tab
*******************************************************************************/

$('.admintabs-add a').popover({
    content: function(ele) { return $('#admintabs-add-popover').html(); },
    html: true
});

$(document).on('click', '.admintabs-add a', function (e) {
    e.preventDefault();
    $('#admintabs-add-name').focus();
});

$(document).on('click', '.admintabs-add .icon-remove', function (e) {
    $('.admintabs-add a').popover('hide');
});

$(document).on('click', '.admintabs-add .icon-ok', function (e) {
    $('.admintabs-add a').popover('hide')
    var name = $('#admintabs-add-name').val();
    if (name === '') {
        alert(('You must enter a name for the new category'));
    }
    $.ajax({
        url: 'index.php?module=ZikulaAdminModule&type=ajax&func=addCategory',
        data: {
            name: name
        },
        success: function(response) {
            var newtab = '<li class="dropdown droppable nowrap" data-catid='+response.data.id+'>'+
                         '<a class="dropdown-toggle" href="#" data-toggle="dropdown"'+
                         '">'+
                         '<span class="icon icon-move admintabs-unlock"></span> '+
                         response.data.name+
                         ' <span class="icon icon-caret-down"></span>'+
                         '</a>'+
                         '<ul class="admintabs-new dropdown-menu"></ul>'+
                         '</li>';
            $('#admintabs .admintabs-add').before(newtab);
            for (var i = 0; i < 6; i++) {
                $('#admintabs ul:first > li:nth-child('+i+')').clone().appendTo('.admintabs-new')
            }            
            $('.admintabs-new').removeClass('admintabs-new');
            $('#admintabs-add a').popover('hide');
        },
        error: function (response) {
            alert($.parseJSON(response.responseText).core.statusmsg);                    
        }
    })
});

/*******************************************************************************
 * Drag and drop modules to admin tabs 
*******************************************************************************/

$('.droppable').droppable({
    over: function( event, ui ) {
       $(this).find('a:first').addClass('admintabs-dropover');
    },
    out: function( event, ui ) {
       $(this).find('a:first').removeClass('admintabs-dropover');
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
        $(this).find('a:first').removeClass('admintabs-dropover');

        // do nothing is icon was moved to the current category
        var categoryId = $(this).data('catid');
        var currentCategoryId = $('#admintabs li.active').data('catid');
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
$('#modulelist').sortable('disable');

/*******************************************************************************
 * Module functions dropdown
*******************************************************************************/

$('.dropdown-toggle').click( function() {
    var container = $(this).parent().parent().parent().parent();
    var containerTop = container.position().top;
    var itemTop      = $(this).parent().position().top;
    var avaibleHeight = container.height() - (itemTop-containerTop);
    var neededHeight = $(this).parent().find('ul').height()+10;
    if (neededHeight > avaibleHeight) {
        container.height(container.height() + neededHeight - avaibleHeight + 30);
    }
});

/*******************************************************************************
 * Click and mouse over dropdown hack
*******************************************************************************/

/*$('#admintabs .icon-caret-down').click(
    function(e) {
        e.preventDefault();
        var li =  $(this).parent().parent()
        var dropdown = li.find('.dropdown-menu');
        dropdown.stop(true, true).delay(200).fadeIn();
        li.bind("mouseleave", function() {
            dropdown.stop(true, true).delay(200).fadeOut();
        });
    }
);
    
/*$('ul.nav-mouseover li.dropdown').hover(function() {
    $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn();
}, function() {
    $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut();
});
*/
    
/*******************************************************************************
 * Lock/Unlock
*******************************************************************************/

$('#admintabs-locker a').click(
    function(e) {
        e.preventDefault();
        var s = $(this).find('span');
        
        if (s.hasClass('icon-lock')) {
            $('#admintabs').sortable('enable');
            $('#modulelist').sortable('enable');
            $('.admintabs-lock').addClass('admintabs-unlock').removeClass('admintabs-lock');
            s.removeClass('icon-lock').addClass('icon-unlock');
            
        } else {
            $('#admintabs').sortable('disable');
            $('#modulelist').sortable('disable');
            $('.admintabs-unlock').addClass('admintabs-lock').removeClass('admintabs-unlock');
            s.removeClass('icon-unlock').addClass('icon-lock');
        }        
    }
);    

/*******************************************************************************
 * Make category default action
*******************************************************************************/

$(document).on('click', '.admintabs-makedefault', function (e) {
    e.preventDefault();
    var catid = $(this).parent().parent().data('catid');
    var e = $(this);
    $.ajax({
        url: 'index.php?module=adminpanel&type=ajax&func=defaultCategory',
        data: {cid: catid},
        success: function() {
            $('.admintabs-makedefault').removeClass('hide');
            e.addClass('hide');  
        },
        error: function (response) {
            alert($.parseJSON(response.responseText).core.statusmsg);
        }
    });
});

/*******************************************************************************
 * Delete category
*******************************************************************************/

$(document).on('click', '.admintabs-delete', function (e) {
    e.preventDefault();
    var li = $(this).parent().parent();
    var catid = li.data('catid');
    $.ajax({
        url: 'index.php?module=adminpanel&type=ajax&func=deleteCategory',
        data: {cid: catid},
        success: function () {
            li.remove();
        },
        error: function (response) {
            alert($.parseJSON(response.responseText).core.statusmsg);
        }
    });
});
    
/*******************************************************************************
 * Rename category
*******************************************************************************/

var renameCategoryId = null;
var renameTitleElement = null;

$(document).on('click', '.admintabs-edit', function (e) {
    e.preventDefault();
    li = $(this).parent().parent();
    renameCategoryId = li.data('catid');
    renameTitleElement = li.find('span:nth-child(2)');
    $('#admintabs-rename-category-modal input').val(renameTitleElement.text());
    $('#admintabs-rename-category-modal input').focus();
});
$('#admintabs-rename-category-modal .btn-primary').click(
    function() {
        var name = $('#admintabs-rename-category-modal input').val();
        $.ajax({
            url: 'index.php?module=adminpanel&type=ajax&func=editCategory',
            data: {
                cid: renameCategoryId,
                name: name
            },
            success: function() {
                renameTitleElement.text(name);
            },
            error: function (response) {
                alert($.parseJSON(response.responseText).core.statusmsg);
            }
        });
    }
);


});})(jQuery);