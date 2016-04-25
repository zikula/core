// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $.widget('ZikulaUI.zPanels', $.ZikulaUI.zAccordion, {
        options: {
            active: [],
            heightStyle: 'content' // overwrite default 'auto', which does not work well on panels
        },
        _create: function() {
            // fix animation when $.uiBackCompat is on
            if (_(this.options.animate).isNull()) {
                this.options.animate = {};
            }
            this.element.addClass(this.widgetName);
            this._superApply(arguments);
        },
        _activate: function(index) {
            this._setState(index, true);
        },
        _deactivate: function(index) {
            this._setState(index, false);
        },
        _setState: function(indexes, activate) {
            indexes = !_(indexes).isArray() ? [indexes] : indexes;
            activate = activate || false;

            _(indexes).each(function(index){
                var header = this._findActive(index).get(0);

                // trying to activate active or deactivate inactive panel
                if (_(header).isUndefined() || activate == this.active.is(header)) {
                    return;
                }

                this._eventHandler({
                    target: header,
                    currentTarget: header,
                    preventDefault: $.noop
                });
            }.bind(this));
        },
        _findActive: function(active) {
            active = !_(active).isArray() ? [active] : active;
            return this.headers.filter(function(i) {
                return _(active).contains(i);
            });
        },
        _eventHandler: function(event) {
            var options = this.options,
                header = $(event.currentTarget),
                panel = header.next(),
                collapsing = this.active.is(header),
                eventData = {
                    oldHeader:  collapsing ? header : $(),
                    oldPanel: collapsing ? panel : $(),
                    newHeader: collapsing ? $() : header,
                    newPanel: collapsing ? $() : panel,
                    data: {
                        header: header,
                        panel: panel,
                        collapsing: collapsing
                    }
                };

            event.preventDefault();

            if (collapsing) {
                this.active = this.active.not(header);
            } else {
                this.active = this.active.add(header);
            }
            options.active = this.active.map(_(function(index, element) {
                return this.headers.index(element);
            }).bind(this)).get();

            this.prevShow = this.prevHide = $();
            this._toggle(eventData);

            // switch classes
            // corner classes on the previously active header stay after the animation
            if (collapsing) {
                header.removeClass('ui-accordion-header-active ui-state-active');
                if (options.icons) {
                    header.children('.ui-accordion-header-icon')
                        .removeClass(options.icons.activeHeader)
                        .addClass(options.icons.header);
                }
            } else {
                header
                    .removeClass('ui-corner-all')
                    .addClass('ui-accordion-header-active ui-state-active ui-corner-top');
                if (options.icons) {
                    header.children('.ui-accordion-header-icon')
                        .removeClass(options.icons.header)
                        .addClass(options.icons.activeHeader);
                }
                panel.addClass('ui-accordion-content-active');
            }
        },
        _toggleComplete: function(data) {
            // get back new, panels, data
            data = data.data;

            if (data.collapsing) {
                data.header
                    .removeClass('ui-corner-top')
                    .addClass('ui-corner-all');
                data.panel.removeClass('ui-accordion-content-active');
                // Work around for rendering bug in IE (#5421)
                data.header.parent()[0].className = data.header.parent()[0].className
            }

            this._trigger('activate', null, data);
        },
        _activateOnHash: function() {
            var hash = '#' + window.location.hash.replace('#',''),
                onHash = this._getIndex(hash),
                active = this.options.active;
            if (onHash > -1) {
                active.push(onHash);
                this.options.active = _(active).uniq();
            }
        },
        _preservationState: function() {
            if (!this.options.active.length && this.options.preserveState) {
                this._getPreservedState();
            }
        },
        activate: function(header) {
            this._activate(header);
        },
        deactivate: function(header) {
            this._deactivate(header);
        },
        activateAll: function() {
            this._activate(_(this.headers.length).range());
        },
        deactivateAll: function() {
            this._deactivate(_(this.headers.length).range());
        }
    })
})(jQuery);
