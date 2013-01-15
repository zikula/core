(function($) {
    Zikula.define('Modules.Admin');

    function adminTabs() {
        $('#admintabs').sortable({
            items: '> li.admintab',
            placeholder: 'ui-state-highlight',
            forcePlaceholderSize: true,
            axis: 'x',
            update: adminTabsOrder
        }).disableSelection();
    }

    function adminTabsOrder() {
        $.ajax('index.php?module=Admin&type=ajax&func=sortCategories', {
            data: $('#admintabs').sortable('serialize')
        }).fail(console.log);
    }

    function setupNotices() {
        $('#admin-systemnotices').zPanels({
            header: 'div > strong',
            preserveState: true
        })
    }

    function tabContextMenu() {
        $.zContextMenu({
            selector: '.admintab .z-admindrop',
            trigger: 'left',
            forceRight: true,
            items: {
                edit: {name: Zikula.__('Edit category')},
                remove: {name: Zikula.__('Delete category')},
                makeDefault: {name: Zikula.__('Make default category')}
            },
            build: function($trigger, event) {
                $trigger = $trigger.parents('.admintab');
                return $.zContextMenu.buildFromData($trigger, event);
            },
            callback: tabContextMenuAction,
            events: {
                show: function(options) {
                    options.$trigger.addClass('hover');
                },
                hide: function(options) {
                    options.$trigger.removeClass('hover');
                }
            }
        });
        $(document).on('click', '.context-menu-submenu', function(event) {
            var data = $(this).data('contextMenu');
            if (data.href) {
                window.location = data.href;
            }
        })
    }

    function tabContextMenuAction(key, options) {
        var tab = options.$trigger.parents('li.admintab');
        switch (key) {
            case 'edit':
                editTab(tab);
                break;
            case 'remove':
                removeTab(tab);
                break;
            case 'makeDefault':
                makeDefaultTab(tab);
                break;
        }
    }

    function removeTab(tab) {
        $.ajax('index.php?module=Admin&type=ajax&func=deleteCategory', {
            data: {
                cid: $(tab).data('cid')
            }
        }).done(function(){
                $(tab).remove();
        }).fail(function(xhr) {
            alert(xhr.zikula.getMessage());
        });
    }

    function makeDefaultTab(tab) {
        $.ajax('index.php?module=Admin&type=ajax&func=defaultCategory', {
            data: {
                cid: $(tab).data('cid')
            }
        }).fail(function(xhr) {
            alert(xhr.zikula.getMessage());
        });
    }

    function editTab(tab) {
        var link = tab.find('a'),
            tplData = {
                id: tab.data('cid'),
                title: link.text()
            },
            form = $(Zikula.Plugins.Template('#admintab-edit', tplData).render()),
            eventData = {
                tab: tab,
                form: form,
                tplData: tplData
            };
        link.hide().after(form);
        form.on('submit.zikula.tabedit', eventData, saveTab);
        form.on('blur.zikula.tabedit', 'input', eventData, saveTab);
        form.on('keyup.zikula.tabedit', function(event) {
            if (event.which === 27) {
                hideForm(tab, link, form);
            }
        });
        $('#admintabs').sortable('disable').enableSelection();
    }

    function hideForm(tab, link, form) {
        form.off('.tabedit').remove();
        link.show();
        $('#admintabs').sortable('enable').disableSelection();
    }

    function saveTab(event) {
        event.preventDefault();
        var tab = event.data.tab,
            form = event.data.form,
            link = tab.find('a'),
            tplData = event.data.tplData,
            title = form.find('[name=title]').val();
        if (tplData.title === title) {
            hideForm(tab, link, form);
            return;
        }
        $.ajax('ajax.php?module=Admin&type=ajax&func=editCategory', {
            data: {
                cid: tab.data('cid'),
                name: title
            }
        }).done(function(){
            link.html(title);
            hideForm(tab, link, form);
        }).fail(function(){
            hideForm(tab, link, form);
        });
    }

    $(document).ready(function() {
        setupNotices();
        tabContextMenu();
        adminTabs();
    });
})(jQuery);
