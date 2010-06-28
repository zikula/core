// Copyright Zikula Foundation 2010 - license GNU/LGPLv3 (or at your option, any later version).

Zikula._TreeSortable = Class.create(Zikula._Tree, {
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            nodeLeaf:           'leaf',
            nodeLast:           'last',
            disabled:           [],
            disabledForDrag:    [],
            disabledForDrop:    [],
            draggableClass:     'draggable',
            droppableClass:     'droppable',
            onDragClass:        'onDragClass',
            dropOnClass:        'dropOnClass',
            dropAfterClass:     'dropAfterClass',
            dropBeforeClass:    'dropBeforeClass',
            dropAfterOverlap:   [0.3, 0.7],
            expandTimeout:      1500,
            maxDepth:           0,
            onSave:             null
        }, config || { });
        $super(element,config);
        this.tree.addClassName('sortable');
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