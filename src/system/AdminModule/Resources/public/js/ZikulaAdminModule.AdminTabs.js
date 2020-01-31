// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(document).ready(function() {
        /*******************************************************************************
         * Sort tabs
         *******************************************************************************/

        $('#admintabs').sortable({
            cursor: 'move',
            placeholder: 'ui-state-highlight',
            containment: 'parent',
            update: function(event, ui) {
                var tab = [];
                $('#admintabs li').each( function() {
                   var catid = $(this).data('catid');
                   if (catid !== undefined) {
                        tab.push($(this).data('catid'));
                   }
                });

                $.ajax({
                    url: Routing.generate('zikulaadminmodule_ajax_sortcategories'),
                    data: { admintabs: tab }
                }).fail(function (jqXHR, textStatus) {
                    alert('Request failed: ' + textStatus);
                });
             },
        });
        $('#admintabs').sortable('disable');

        /*******************************************************************************
         * Add tab
        *******************************************************************************/

        $('.admintabs-add a').popover({
            container: 'body',
            content: function (ele) {
                return $('#admintabs-add-popover').html();
            },
            html: true,
            sanitize: false
        });
        $('.admintabs-add a').click(function (event) {
            event.preventDefault();
        });
        $('.admintabs-add a').on('shown.bs.popover', function () {
            $('.popover.show #admintabs-add-name').focus();
            $('.popover.show .fa-times').click(function (event) {
                event.preventDefault();
                $('.admintabs-add a').popover('hide');
            });
            $('.popover.show .fa-check').click(function (event) {
                event.preventDefault();
                var name = $(this).parent().prev('.admintabs-add-name').first().val();
                $('.admintabs-add a').popover('hide');
                if ('' === name) {
                    alert(Translator.trans('You must enter a name for the new category'));
                    return;
                }
                $.ajax({
                    url: Routing.generate('zikulaadminmodule_ajax_addcategory'),
                    data: {
                        name: name
                    }
                }).done(function (data) {
                    var newTab = '<li class="nav-item dropdown droppable nowrap ui-sortable-handle ui-droppable" data-catid=' + data.id + '>' +
                        '<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">' +
                        '<span class="fas fa-arrows-alt admintabs-unlock"></span> ' +
                        '<span><i class="text-muted fa-fw fas fa-file"></i> ' + data.name + '</span>' +
                        '</a>' +
                        '<ul class="admintabs-new dropdown-menu"></ul>' +
                        '</li>'
                    ;
                    $('#admintabs #admintabs-locker').before(newTab);
                    for (var i = 0; i < 6; i++) {
                        $('#admintabs ul:first > li:nth-child('+i+')').clone().appendTo('.admintabs-new')
                    }
                    $('.admintabs-new').removeClass('admintabs-new');
                    $('#admintabs-add a').popover('hide');
                }).fail(function (jqXHR, textStatus) {
                    alert('Request failed: ' + textStatus);
                });
            });
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
                    url: Routing.generate('zikulaadminmodule_ajax_changemodulecategory'),
                    data: {
                        modid: ui.draggable.data('modid'),
                        cat: categoryId
                    }
                }).done(function (data) {
                    ui.draggable.remove();
                }).fail(function (jqXHR, textStatus) {
                    alert('Request failed: ' + textStatus);
                });
            }
        });

        /*******************************************************************************
         * Sort modules
        *******************************************************************************/

        $('#modulelist').sortable({
            cursor: 'move',
            placeholder: 'ui-state-highlight',
            update: function(event, ui) {
                var modules = [];
                $('#modulelist li').each( function() {
                    var modid = $(this).data('modid');
                    if (modid !== undefined) {
                        modules.push($(this).data('modid'));
                    }
                });
                $.ajax({
                    url: Routing.generate('zikulaadminmodule_ajax_sortmodules'),
                    data: { modules: modules }
                }).fail(function (jqXHR, textStatus) {
                    alert('Request failed: ' + textStatus);
                });
            },
        });
        $('#modulelist').sortable('disable');

        /*******************************************************************************
         * Module functions dropdown
         *******************************************************************************/

        $('#modulelist .dropdown-toggle').click(function() {
            var container = $(this).parent().parent().parent().parent();
            var containerTop = container.position().top;
            var itemTop = $(this).parent().position().top;
            var availableHeight = container.height() - (itemTop-containerTop);
            var neededHeight = $(this).parent().find('ul').height() + 10;
            if (neededHeight > availableHeight) {
                container.height(container.height() + neededHeight - availableHeight + 30);
            }
        });

        /*******************************************************************************
         * Lock/Unlock
         *******************************************************************************/

        $('#admintabs-locker a').click(function (event) {
            event.preventDefault();
            var s = $(this).find('span');

            if (s.hasClass('fa-lock')) {
                $('#admintabs').sortable('enable');
                $('#modulelist').sortable('enable');
                $('.admintabs-lock').addClass('admintabs-unlock').removeClass('admintabs-lock');
                s.removeClass('fa-lock').addClass('fa-unlock');

            } else {
                $('#admintabs').sortable('disable');
                $('#modulelist').sortable('disable');
                $('.admintabs-unlock').addClass('admintabs-lock').removeClass('admintabs-unlock');
                s.removeClass('fa-unlock').addClass('fa-lock');
            }
        });

        /*******************************************************************************
         * Make category default action
         *******************************************************************************/

        $(document).on('click', '.admintabs-makedefault', function (event) {
            event.preventDefault();
            var catid = $(this).parent().parent().data('catid');
            var elem = $(this);
            $.ajax({
                url: Routing.generate('zikulaadminmodule_ajax_defaultcategory'),
                data: { cid: catid }
            }).done(function () {
                $('.admintabs-makedefault').removeClass('d-none');
                elem.addClass('d-none');
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            });
        });

        /*******************************************************************************
         * Delete category
         *******************************************************************************/

        $(document).on('click', '.admintabs-delete', function (event) {
            event.preventDefault();
            var li = $(this).parent().parent();
            var catid = li.data('catid');
            $.ajax({
                url: Routing.generate('zikulaadminmodule_ajax_deletecategory'),
                data: { cid: catid }
            }).done(function () {
                li.remove();
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            });
        });

        /*******************************************************************************
         * Rename category
         *******************************************************************************/

        var renameCategoryId = null;
        var renameTitleElement = null;

        $(document).on('click', '.admintabs-edit', function (event) {
            event.preventDefault();
            li = $(this).parent().parent();
            renameCategoryId = li.data('catid');
            renameTitleElement = li.find('span:nth-child(2)');
            $('#admintabs-rename-category-modal input').val(renameTitleElement.text().trim());
            $('#admintabs-rename-category-modal input').focus();
        });
        $('#admintabs-rename-category-modal .btn-primary').click(function () {
            var name = $('#admintabs-rename-category-modal input').val();
            if ('' === name) {
                alert(Translator.trans('You must enter a name for the new category'));
                return;
            }
            $.ajax({
                url: Routing.generate('zikulaadminmodule_ajax_editcategory'),
                data: {
                    cid: renameCategoryId,
                    name: name
                }
            }).done(function () {
                renameTitleElement.text(name);
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            });
        });
    });
})(jQuery);
