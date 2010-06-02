/**
 * Zikula Application Framework
 * @version $Id$
 *
 * Licensed to the Zikula Foundation under one or more contributor license
 * agreements. This work is licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option any later version).
 *
 * Please see the NOTICE and LICENSE files distributed with this source
 * code for further information regarding copyright ownership and licensing.
 */

if (typeof(Zikula) == 'undefined') {
    Zikula = {};
}
/**
 * Requires prototype.js, effects.js and dragdrop.js
 */
Zikula._Tree = Class.create({
    initialize: function(element,config) {
        this.tree = $(element);
        this.id = this.tree.identify();
        config = this.decodeConfig(config);
        this.config = Object.extend({
            nodeIdPattern:       /^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,
            toggler:            'toggle',
            icon:               'icon',
            imagesDir:          'javascript/helpers/Tree/',
            images:             {}
        }, config || { });
        this.config.images = Object.extend({
            plus:               'plus.gif',
            minus:              'minus.gif',
            parent:             'folder.gif',
            parentOpen:         'folder_open.gif',
            item:               'filenew.gif'
        },this.config.images);
        // extend each image with base url and images dir
        for (var item in this.config.images) {
            if (this.config.images.hasOwnProperty(item)) {
                this.config.images[item] = document.location.pnbaseURL + this.config.imagesDir + this.config.images[item];
            }
        }
        // bind toggle action
        this.tree.select('.'+this.config.toggler).invoke('observe','click',this.toggleNode.bindAsEventListener(this));
        // bind also empty spans
        this.tree.select('li.parent > span').invoke('observe','click',this.toggleNode.bindAsEventListener(this));
        // initialy hide childnodes
        this.getStatus();
        this.tree.select('ul').each(function(u) {
            if(!this.status.get(u.up('li').identify())) {
                this.hideNode(u);
            }
        }.bind(this));
    },
    toggleNode: function(event) {
        var ul = event.element().up('li').down('ul')
        if (ul != undefined) {
            if(ul.visible()) {
                this.hideNode(ul);
            } else {
                this.showNode(ul);
            }
            this.saveStatus();
        }
    },
    showNode: function(node) {
        node.show();
        node.previous('.'+this.config.toggler).writeAttribute('src',this.config.images.minus);
        node.previous('.'+this.config.icon).writeAttribute('src',this.config.images.parentOpen);
        this.status.set(node.up('li').identify(),node.visible());
    },
    hideNode: function(node) {
        node.hide();
        node.previous('.'+this.config.toggler).writeAttribute('src',this.config.images.plus);
        node.previous('.'+this.config.icon).writeAttribute('src',this.config.images.parent);
        this.status.set(node.up('li').identify(),node.visible());
    },
    getStatus: function() {
        this.status = Cookie.get(this.id) ? $H(Cookie.get(this.id).evalJSON()) : new Hash();
    },
    saveStatus: function() {
        Cookie.set(this.id,this.status.toJSON());
    },
    getNodeId: function(node) {
        return Number(node.id.match(this.config.nodeIdPattern)[1]);
    },
    decodeConfig: function(config) {
        if(Object.isString(config) && config.isJSON()) {
            config = config.evalJSON(true);
        }
        return config;
    },
    serialize: function(branch) {
        this.serialized = {};
        branch = branch == undefined ? this.tree : branch;
        $(branch).select('li').each(function(node,index) {
            this.serialized[this.getNodeId(node)] = this.serializeNode(node,index);
        }.bind(this));
        return Object.toJSON(this.serialized);
    },
    serializeNode: function(node,index) {
        return {
            id:         this.getNodeId(node),
            name:       node.down('a').innerHTML,
            lineno:     index,
            parent:     node.up('#'+this.tree.id+' li') ? this.getNodeId(node.up('#'+this.tree.id+' li')) : 0
        };
    }
});
Zikula.Tree = {
    add: function(element,config) {
        if (!this.hasOwnProperty(element)) {
            this[element] = new Zikula._Tree(element,config);
        }
    }
}