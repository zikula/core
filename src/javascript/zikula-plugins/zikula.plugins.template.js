// Copyright Zikula Foundation, licensed MIT.

(function($) {
/*
Zikula.Plugins.Template(tpl, data).render();
view = new Zikula.Plugins.Template();
view.template(tpl).render(data);
view = new Zikula.Plugins.Template();
view.template(tpl).assign(data).render()

var view = new Zikula.Plugins.Template('Hello <%= vars.name %>!', {name: 'Jusuff'});
view.render();

var view = new Zikula.Plugins.Template();
view.template('Hello <%= vars.name %>!').render({name: 'Jusuff'});

Zikula.Plugins.Template('Hello <%= vars.name %>!', {name: 'Jusuff'}).render()

Zikula.Plugins.Template($('#template'), {name: 'Jusuff'}).render()
 */
    Zikula.define('Plugins');

    Zikula.Plugins.Template = Zikula.Class.create( /** @lends Zikula.Plugins.Template.prototype */{
        init: function(template, data, options) {
            this.setOptions(options);
            this.template(template);
            this.assign(data);
        },
        setOptions: function(options) {
            this.options = _.extend(Zikula.Plugins.Template.options, options || { });
        },
        assign: function(data) {
            this.data = data || {};
            return this;
        },
        template: function(template) {
            if (template) {
                if ('jquery' in Object(template)) {
                    template = template.html();
                } else if (_(template).startsWith('#') && !_(template).stringInclude('<%')) {
                    template = $(template).html() || template;
                }
                this.tpl = template || '';
                this.compile(true);
            }
            return this;
        },
        render: function(data) {
            if (!_(data).isUndefined()) {
                this.assign(data);
            }
            return this.compile()(this.data);
        },
        // privates
        compile: function(force) {
            if (force || _(this.compiled).isUndefined()) {
                this.compiled = _(this.tpl || '').template(undefined, this.options);
            }
            return this.compiled;
        }
    });
    Zikula.Class.extend(Zikula.Plugins.Template, /** @lends Zikula.Plugins.Template */{
        options: {
            variable: 'vars'
        }
    });

/*
var view = new Zikula.Plugins.RemoteTemplate();
view.template(['system/Admin/Resources/public/js/test.js', 'my.cache.id']);
view.assign({name: 'Jusuff'})
view.render(function(html){
    console.log('result', html);
})

var view = new Zikula.Plugins.RemoteTemplate();
view.template('system/Admin/Resources/public/js/test.js');
view.assign({name: 'Jusuff'})
view.render(function(html){
    console.log('result', html);
})


*/

    Zikula.Plugins.RemoteTemplate = Zikula.Class.create(Zikula.Plugins.Template /** @lends Zikula.Plugins.RemoteTemplate.prototype */, {
        init: function(template, data, options) {
            this.fetchPromise = new $.Deferred();
            this.renderPromise = new $.Deferred();
            this._super('init', template, data, options);
        },
        setOptions: function(options) {
            this._super('setOptions', options);
            this.cacheOptions = _.extend(Zikula.Plugins.RemoteTemplate.cacheOptions, this.options.cacheOptions || { });
            return this;
        },
        template: function(template) {
            if (template) {
                if (_(template).isArray()) {
                    this.tplPath = template[0] || '';
                    var cacheKey = template[1] || [];
                    this.cacheKey = _(cacheKey).isArray() ? _(cacheKey).clone() : cacheKey.split('.');
                    this.cacheKey.push(this.tplPath);
                } else {
                    this.tplPath = this.cacheKey = template;
                }
                this.fetch();
            }
            return this;
        },
        render: function(data, callback) {
            if (_(data).isFunction()) {
                callback = data;
            } else if (!_(data).isUndefined()) {
                this.assign(data);
            }
            this.renderPromise.then(callback);
            this.fetchPromise.then(_(function() {
                this.renderPromise.resolveWith(this, [this.compile()(this.data)]);
            }).bind(this));

            return this.renderPromise.promise();
        },
        fetch: function() {
            var template;
            if (this.cacheOptions.cache) {
                template = this.getCache(this.cacheKey);
            }
            if (!template) {
                $.get(this.tplPath, _(this.resolveFetch).bind(this), 'text');
            } else {
                this.tpl = template;
                this.resolveFetch(template);
            }
        },
        resolveFetch: function(template, msg) {
            if (msg === 'success') {
                this.setCache(this.cacheKey, template);
            }
            this.tpl = template;
            this.compile(true);
            this.fetchPromise.resolveWith(this, [template]);
        },
        getCache: function(cacheKey) {
            return Zikula.Plugins.RemoteTemplate.getCache.apply(this, arguments);
        },
        setCache: function(cacheKey, template) {
            return Zikula.Plugins.RemoteTemplate.setCache.apply(this, arguments);
        }
    });
    Zikula.Class.extend(Zikula.Plugins.RemoteTemplate, /** @lends Zikula.RemoteTemplate.Template */{
        cacheOptions: {
            cache: true,
            cacheLifeTime: 3600, // seconds
            cacheStorageKey: 'Zikula.Plugins.Template.cache'
        },
        getCache: function(cacheKey) {
            var cacheData = Zikula.Plugins.RemoteTemplate.readCache(),
                cache = _(cacheData).objectGetPath(cacheKey);
            if (cache && cache.timestamp > ((new Date) - this.cacheOptions.cacheLifeTime * 1000)) {
                return cache.template;
            }
            return false;
        },
        setCache: function(cacheKey, template) {
            var cacheData = Zikula.Plugins.RemoteTemplate.readCache(),
                cacheItem = {
                    timestamp: +(new Date),
                    template: template
                };
            _(cacheData).objectSetPath(cacheKey, cacheItem);
            Zikula.Plugins.RemoteTemplate.writeCache(cacheData);
        },
        clearCache: function(cacheKey) {
            var cacheData = Zikula.Plugins.RemoteTemplate.readCache();
            _(cacheData).objectUnsetPath(cacheKey);
            Zikula.Plugins.RemoteTemplate.writeCache(cacheData);
        },
        readCache: function() {
            var data = JSON.parse(localStorage.getItem(this.cacheOptions.cacheStorageKey));
            data = _(data).isObject() ? data : {};
            return data;
        },
        writeCache: function(data) {
            localStorage.setItem(this.cacheOptions.cacheStorageKey, JSON.stringify(data));
        }
    });

})(jQuery);