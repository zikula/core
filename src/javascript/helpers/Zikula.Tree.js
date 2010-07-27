// Copyright Zikula Foundation 2010 - license GNU/LGPLv3 (or at your option, any later version).

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
            toggler:            'z-tree-toggle',
            icon:               'z-tree-icon',
            imagesDir:          'javascript/helpers/Tree/',
            images:             {}
        }, config || { });
        this.config.images = Object.extend({
            plus:               'plus.gif',
            minus:              'minus.gif',
            parent:             'folder.png',
            parentOpen:         'folder_open.png',
            item:               'filenew.png'
        },this.config.images);
        // extend each image with base url and images dir
        for (var item in this.config.images) {
            if (this.config.images.hasOwnProperty(item)) {
                this.config.images[item] = Zikula.Config.baseURL + this.config.imagesDir + this.config.images[item];
            }
        }
        // bind toggle action
        this.tree.select('.'+this.config.toggler).invoke('observe','click',this.toggleNode.bindAsEventListener(this));
        // bind also empty spans
        this.tree.select('li.z-tree-parent > span').invoke('observe','click',this.toggleNode.bindAsEventListener(this));
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
        this.status = Zikula.Cookie.get(this.id) ? $H(Zikula.Cookie.get(this.id)) : new Hash();
    },
    saveStatus: function() {
        Zikula.Cookie.set(this.id,this.status,3600*24*7);
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

Zikula._TreeSortable = Class.create(Zikula._Tree, {
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            nodeLeaf:           'z-tree-leaf',
            nodeLast:           'z-tree-last',
            disabled:           [],
            disabledForDrag:    [],
            disabledForDrop:    [],
            draggableClass:     'z-tree-draggable',
            droppableClass:     'z-tree-droppable',
            onDragClass:        'z-tree-onDragClass',
            dropOnClass:        'z-tree-dropOnClass',
            dropAfterClass:     'z-tree-dropAfterClass',
            dropBeforeClass:    'z-tree-dropBeforeClass',
            dropAfterOverlap:   [0.3, 0.7],
            expandTimeout:      1500,
            maxDepth:           0,
            onSave:             null
        }, config || { });
        $super(element,config);
        this.tree.addClassName('z-tree-sortable');
        this.tree.select('li').each(this.initNode.bind(this));
    },
    initNode: function(node) {
        if(this.config.disabled.include(this.getNodeId(node))) {
            return;
        }
        if(!this.config.disabledForDrag.include(this.getNodeId(node))) {
            node.addClassName(this.config.draggableClass);
            new Draggable(node,{
                handle:this.config.icon,
                onEnd: this.endDrag.bind(this),
                onStart : this.startDrag.bind(this),
                revert:true,
                starteffect:null,
                scroll: window
            });
        }
        if(!this.config.disabledForDrop.include(this.getNodeId(node))) {
            node.addClassName(this.config.droppableClass);
            Droppables.add(node, {
                accept:this.config.draggableClass,
                hoverclass:this.config.dropOnClass,
                overlap:'vertical',
                onDrop:this.dropNode.bind(this),
                onHover:this.hoverNode.bind(this)
            });
        }
    },
    startDrag: function(draggable) {
        this.dropCache = {};
        draggable.element.addClassName(this.config.onDragClass);
    },
    endDrag: function(draggable) {
        if(this.dropCache.lastElement) {
            this.insertNode(draggable.element,this.dropCache.lastElement);
            this.drawNodes();
        }
        this.tree.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.tree.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        draggable.element.removeClassName(this.config.onDragClass);
    },
    hoverNode: function(node,dropOnNode,overlap) {
        window.clearTimeout(this.dropCache.timeout);
        this.tree.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.tree.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        if (overlap > this.config.dropAfterOverlap[1]) {
            dropOnNode.addClassName(this.config.dropBeforeClass);
            this.dropCache.lastElement = ['before',dropOnNode.id];
        } else if (overlap < this.config.dropAfterOverlap[0]) {
            dropOnNode.addClassName(this.config.dropAfterClass);
            this.dropCache.lastElement = ['after',dropOnNode.id];
        } else {
            this.dropCache.expand = this.expandOne.bindAsEventListener(this);
            this.dropCache.element = dropOnNode;
            this.dropCache.timeout = window.setTimeout(this.dropCache.expand, this.config.expandTimeout);
            dropOnNode.removeClassName(this.config.dropAfterClass);
            dropOnNode.removeClassName(this.config.dropBeforeClass);
        }
    },
    dropNode: function(node,dropOnNode,point) {
        var insertion = true;
        if (dropOnNode.hasClassName(this.config.dropAfterClass)) {
            insertion = this.insertNode(node,['after',dropOnNode]);
        } else if (dropOnNode.hasClassName(this.config.dropBeforeClass)) {
            insertion = this.insertNode(node,['before',dropOnNode]);
        } else {
            insertion = this.insertNode(node,['bottom',dropOnNode]);
        }
        if(!insertion) {
            return false;
        }
        return true;
    },
    insertNode: function(node,params,revert) {
        var dropOnNode = $(params[1]),
            position = params[0],
            newlevel = position == 'bottom';
        this.prevPosition = {
                node: node,
                parent: node.up('li') || null,
                previous: node.previous('li') || null,
                next: node.next('li') || null
        };
        if(this.config.maxDepth > 0) {
            var dropOnNodeLevel = this.countLevels(dropOnNode,'up'),
                nodeLevel = this.countLevels(node,'down'),
                treeDepth = dropOnNodeLevel + nodeLevel + Number(newlevel) + 1;
            if(treeDepth > this.config.maxDepth) {
                alert(this.config.langLabels.maxdepthreached + this.config.maxDepth);
                this.dropCache = {};
                return false;
            }
        }
        if(newlevel) {
            var ul = dropOnNode.down('ul',0);
            if (ul == undefined) {
                ul = new Element('ul');
                dropOnNode.insert(ul);
            }
            ul.show();
            dropOnNode = ul;
        }
        var obj = {}
        obj[position] = node;
        dropOnNode.insert(obj);
        if(!revert && Object.isFunction(this.config.onSave)
            && !this.config.onSave(node,params,this.serialize())) {
            this.revertInsertion();
            return false;
        }
        this.dropCache = {};
        this.drawNodes();
        return true;
    },
    revertInsertion: function(){
        if(this.prevPosition == undefined) {
            return;
        }
        if(this.prevPosition.previous) {
            var ref = this.prevPosition.previous,
                pos = 'after';
        } else if (this.prevPosition.next) {
            var ref = this.prevPosition.next,
                pos = 'before';
        } else if (this.prevPosition.parent) {
            var ref = this.prevPosition.parent,
                pos = 'bottom';
        }
        this.insertNode(this.prevPosition.node,[pos,ref],true);
    },
    countLevels : function(node,mode,stop) {
        var levels = 0;
        if (mode == 'up') {
            stop = (stop == undefined) ? this.tree : stop;
            var ancestors = node.ancestors();
            levels = stop.select('ul')
                .select(function(item) {
                    return ancestors.include(item);
                }).size();
        } else if (mode == 'down') {
            levels = node.select('li').max(function(subnode) {
                return this.countLevels(subnode,'up',node);
            }.bind(this));
        }
        return isNaN(levels) ? 0 : levels;
    },
    expandOne: function() {
        if(this.dropCache.element && this.dropCache.element.down('ul') != undefined) {
            this.showNode(this.dropCache.element.down('ul'));
            this.dropCache.element = false;
            this.saveStatus();
        }
    },
    expandAll: function(node) {
        var base = node ? node : this.tree;
        base.select('ul').each(function(ul){
            this.showNode(ul);
        }.bind(this));
        this.saveStatus();
    },
    collapseAll: function(node) {
        var base = node ? node : this.tree;
        base.select('ul').reverse(true).each(function(ul){
            this.hideNode(ul);
        }.bind(this));
        this.saveStatus();
    },
    drawNodes: function() {
        this.tree.select('li').each(this.drawNode.bind(this));
    },
    drawNode: function (node) {
        if (node.next() == undefined) {
            node.addClassName(this.config.nodeLast);
        } else {
            node.removeClassName(this.config.nodeLast);
        }
        if (node.down('li') == undefined) {
            node.addClassName(this.config.nodeLeaf);
            node.down('.'+this.config.icon).writeAttribute('src',this.config.images.item);
            if (node.down('ul') != undefined) {
                node.down('ul').remove();
            }
        } else {
            node.removeClassName(this.config.nodeLeaf);
            if(node.down('ul').visible()) {
                node.down('.'+this.config.toggler).writeAttribute({src: this.config.images.minus});
                node.down('.'+this.config.icon).writeAttribute('src',this.config.images.parentOpen);
            } else {
                node.down('.'+this.config.icon).writeAttribute('src',this.config.images.parent);
            }
        }
    }
});

Zikula.TreeSortable = {
    add: function(element,config) {
        if (!this.hasOwnProperty(element)) {
            this[element] = new Zikula._TreeSortable(element,config);
        }
    }
}