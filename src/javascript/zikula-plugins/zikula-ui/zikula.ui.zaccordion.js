(function($){
    $.widget('ZikulaUI.zAccordion', $.ui.accordion, {
        options: {
            active: null, // when preserveState=false, active evaluate to 0
            activateOnHash: false, // panel headers require ids
            preserveState: false // accordion requires id
        },
        _create: function() {
            var options = this.options;
            if (_(options.active).isNull() && options.preserveState) {
                this._getPreservedState();
            }
            if (options.activateOnHash) {
                this._activateOnHash();
            }
            this._super();
        },
        _trigger: function(type, event, data) {
            if (type === 'activate' && this.options.preserveState) {
                this._setPreservedState(data.newHeader);
            }
            return this._superApply(arguments);
        },
        _activateOnHash: function() {
            if (window.location.hash) {
                var hash = '#' + window.location.hash.replace('#',''),
                    active = this._getIndex(hash);
                if (active > -1) {
                    this.options.active = active;
                }
            }
        },
        _getPreservedState: function() {
            var id = this.element.attr('id'),
                active;
            if (id) {
                active = parseInt(localStorage.getItem(this._getStorageKey(id)), 10);
                if (_(active).isNumber() && !_(active).isNaN()) {
                    this.options.active = active;
                }
            }
        },
        _setPreservedState: function(item) {
            var index = this._getIndex(item),
                id = this.element.attr('id');
            if (id) {
                localStorage.setItem(this._getStorageKey(id), index);
            }
        },
        _getIndex: function(item) {
            var headers = this.headers || this.element.find(this.options.header);
            return headers.index(headers.filter(item));
        },
        _getStorageKey: function(id) {
            return this.widgetFullName + '-' + id;
        }
    });
})(jQuery);