// Copyright Zikula Foundation, licensed MIT.

// todo - docs
(function($) {
    Zikula.define('Plugins');

    // promise to handle calls before context menu is ready
    Zikula.Plugins.ContextMenu = function() {
        var args = arguments,
            menu = this.menu;
        if (_(menu).isUndefined()) {
            Zikula.Core.when('contextMenu').then(function(name, service) {
                menu = service;
                _(Zikula.Plugins.ContextMenu).extend(menu);
                service.apply(jQuery, args);
            });
            return jQuery;
        } else {
            return this.menu.apply(jQuery, args);
        }
    };

    // make sure context menu is loaded
    Zikula.Core.loadService('contextMenu', {
        test: jQuery.contextMenu,
        nope: [
            'javascript/plugins/jQuery-contextMenu/jquery.contextMenu.js',
            'javascript/plugins/jQuery-contextMenu/jquery.contextMenu.css'
        ]
    }, function() {
        return setupContextMenu();
    });

    function setupContextMenu(){
        var zContextMenu = function(operation, options) {
            if (!_(operation).isString()) {
                options = operation;
                operation = 'create';
            }

            if (_(options).isString()) {
                options = {selector: options};
            } else if (_(options).isUndefined()) {
                options = {};
            }

            options = $.extend(true, {}, zContextMenu.defaults, options);

            // extend items with original event data (this will allow to detect left/right clicks etc)
            if (operation === 'create' && options.zikula) {
                $(document).on('mouseup.contextMenu', '.context-menu-item', zContextMenu.preserveEvent);
            }

            return $.contextMenu.call(this, operation, options);
        };
        zContextMenu.preserveEvent = function(event) {
            var data = $(this).data(),
                item = data.contextMenuRoot.commands[data.contextMenuKey];
            if (!_(item).isUndefined()) {
                item.event = event;
            }
        };
        zContextMenu.urlCallback = function(name, options) {
            var href = options.commands[name].href;
            if (!_(href).isUndefined()) {
                window.location = href;
            }
        };
        zContextMenu.buildFromData = function($trigger, event) {
            var sharedItems = event.data.items,
                dataItems = $trigger.data('menuitems') || {},
                separator = _(sharedItems).size() && _(dataItems).size() ? {separator: '-'} : {};
            return _(dataItems).size() ? {
                items: _(separator).extend(dataItems)
            } : (_(sharedItems).size() ? {} : false);
        };

        // copy static properties
        $.extend(true, zContextMenu, jQuery.contextMenu);
        // extend default settings
        $.extend(zContextMenu.defaults, {
            zikula: true,
            forceRight: false,
            className: 'z-context-menu',
            build: zContextMenu.buildFromData,
            stopEvent: false
        });
        /*
         item.type = 'url'
         item.href
         item._name (display name)
         */
        $.contextMenu.types.url = zContextMenu.types.url = function(item, opt, root) {
            $('<span></span>').html(item._name || item.name).appendTo(this);
            item.callback = item.callback || zContextMenu.urlCallback;
        };

        // patch context menu handler to:
        // - allow showing original context menu when trigger not 'right'
        // - handle forceRight
        jQuery.contextMenu.handle.contextmenu = zContextMenu.handle.contextmenu = (function () {
            var _handle_contextmenu = zContextMenu.handle.contextmenu;
            return function (event) {
                var data = event.data || {};
                if (data.zikula) {
                    if ((_(data.stopEvent).isFunction() && data.stopEvent(event))) {
                        return;
                    } else if (data.trigger != 'right' && event.originalEvent) {
                        // handle forceRight
                        if (data.forceRight) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            $(this).contextMenu({
                                x: event.pageX,
                                y: event.pageY
                            });
                        }
                        return;
                    }
                }
                _handle_contextmenu.apply(this, arguments);
            };
        })();

        // patch click menu handler to allow call callback on submenus
        jQuery.contextMenu.handle.contextmenu = zContextMenu.handle.contextmenu = (function () {
            var proxied = zContextMenu.handle.contextmenu;
            return function (event) {
                var data = event.data || {};
                if (data.zikula) {
                    if ((_(data.stopEvent).isFunction() && data.stopEvent(event))) {
                        return;
                    } else if (data.trigger != 'right' && event.originalEvent) {
                        // handle forceRight
                        if (data.forceRight) {
                            event.preventDefault();
                            event.stopImmediatePropagation();
                            $(this).contextMenu({
                                x: event.pageX,
                                y: event.pageY
                            });
                        }
                        return;
                    }
                }
                proxied.apply(this, arguments);
            };
        })();

        return zContextMenu;
    }
    $.zContextMenu = Zikula.Plugins.ContextMenu;

})(jQuery);