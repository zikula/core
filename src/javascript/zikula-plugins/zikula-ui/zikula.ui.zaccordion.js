// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $.widget('ZikulaUI.zAccordion', $.ui.accordion, {
        options: {
            active: null, // when preserveState=false, active evaluate to 0
            activateOnHash: false, // panel headers require ids
            preserveState: false // accordion element requires id,
        },
        _create: function() {
            this._preservationState();
            this._activationOnHash();
            this.element.addClass(this.widgetName);
            this._superApply(arguments);
        },
        _destroy: function() {
            this.element.removeClass(this.widgetName);
            this._superApply(arguments);
        },
        _trigger: function(type, event, data) {
            if (type === 'activate' && this.options.preserveState) {
                this._setPreservedState(data);
            }
            return this._superApply(arguments);
        },
        _activationOnHash: function() {
            if (this.options.activateOnHash && window.location.hash) {
                this._activateOnHash();
            }
        },
        _activateOnHash: function() {
            var hash = '#' + window.location.hash.replace('#',''),
                active = this._getIndex(hash);
            if (active > -1) {
                this.options.active = active;
            }
        },
        _preservationState: function() {
            if (_(this.options.active).isNull() && this.options.preserveState) {
                this._getPreservedState();
            }
        },
        _getPreservedState: function() {
            var id = this.element.attr('id'),
                active;
            if (id) {
                active = localStorage.getItem(this._getStorageKey(id));
                if (_(active).isJSON()) {
                    this.options.active = JSON.parse(active);
                }
            }
        },
        _setPreservedState: function(data) {
            var id = this.element.attr('id');
            if (id) {
                localStorage.setItem(this._getStorageKey(id), JSON.stringify(this.options.active));
            }
        },
        _getIndex: function(item) {
            var headers = this.headers || this.element.find(this.options.header);
            if (_(item).isNumber()) {
                return item < headers.length ? item : -1;
            } else {
                return headers.index(headers.filter(item));
            }
        },
        getIndex: function(item) {
            return this._getIndex(item);
        },
        _getStorageKey: function(id) {
            return this.widgetFullName + '-' + id;
        },
        activate: function(header) {
            this._activate(this._getIndex(header));
        },
        deactivate: function() {
            this._activate();
        }
    });
})(jQuery);
