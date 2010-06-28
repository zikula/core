// Copyright Zikula Foundation 2010 - license GNU/LGPLv3 (or at your option, any later version).

if (typeof(Zikula) == 'undefined')
    Zikula = {};

Zikula.recursiveSortable = Class.create({
    initialize: function() {
        this.config = Object.extend({
            id:                 '',// ul or ol id
            listTag:            'ul', //ol or li
            handler:            null,
            onDragClass:        'onDragClass',
            dropOnClass:        'dropOnClass',
            dropAfterClass:     'dropAfterClass',
            dropBeforeClass:    'dropBeforeClass',
            dropAfterOverlap:   [0.3, 0.7],
            only:               null,
            onUpdate:           Prototype.emptyFunction,
            maxDepth:           0,
            inputName:          'order',
            nodeIdPattern:       /^[^_\-](?:[A-Za-z0-9\-\_]*)[_](.*)$/,
            langLabels:         {}
        }, arguments[0] || { });

        this.config.only = [this.config.only].flatten();

        this.config.langLabels = Object.extend({
            maxdepthreached:    'Maximum depth reached. Limit is: ',
            warnbeforeunload:   'You have unsaved changes!'
        },this.config.langLabels);

        this.list = $(this.config.id).cleanWhitespace();
        this.list.select('li').each(this.initNode.bind(this));

        Draggables.addObserver(new Zikula.recursiveSortableObserver(this.list,this.config.onUpdate));

        this.unsaved = false;
        this.serialize();
    },
    initNode: function(node) {
        if(this.isAccepted(node)) {
            //init Draggable
            new Draggable(node,{
                handle:this.config.handler,
                onEnd: this.endDrag.bind(this),
                onStart : this.startDrag.bind(this),
                revert:true,
                endeffect: function(element) {
                    new Effect.Highlight(element);
                },
                scroll: window
            });
            //init Droppables
            Droppables.add(node, {
                accept:this.config.only,
                hoverclass:this.config.dropOnClass,
                overlap:'vertical',
                onDrop: this.dropNode.bind(this),
                onHover:this.hoverNode.bind(this)
            });
        }
    },
    isAccepted: function(node) {
        return this.config.only.length == 0 || this.config.only.any(function(c) { return node.hasClassName(c);});
    },
    getId: function(node) {
        return Number(node.identify().match(this.config.nodeIdPattern)[1]);
    },
    startDrag: function(draggable) {
        this.dropCache = {};
        draggable.element.addClassName(this.config.onDragClass);
    },
    endDrag: function(draggable) {
        if(this.dropCache.lastElement) {
            this.insertNode(draggable.element,this.dropCache.lastElement);
        }
        this.list.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.list.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        draggable.element.removeClassName(this.config.onDragClass);
    },
    hoverNode: function(node,dropOnNode,overlap) {
        this.list.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.list.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        if (overlap > this.config.dropAfterOverlap[1]) {
            dropOnNode.addClassName(this.config.dropBeforeClass);
            this.dropCache.lastElement = ['before',dropOnNode.identify()];
        } else if (overlap < this.config.dropAfterOverlap[0]) {
            dropOnNode.addClassName(this.config.dropAfterClass);
            this.dropCache.lastElement = ['after',dropOnNode.identify()];
        } else {
            this.dropCache.element = dropOnNode;
            dropOnNode.removeClassName(this.config.dropAfterClass);
            dropOnNode.removeClassName(this.config.dropBeforeClass);
        }
    },
    dropNode: function(node,dropOnNode,point) {
        var insertion = false;
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
        this.dropCache = {};
    },
    insertNode: function(node,params) {
        var dropOnNode = $(params[1]),
            position = params[0],
            newlevel = position == 'bottom',
            oldParent = $(node.up(this.config.listTag,0).identify());
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
            var ul = dropOnNode.down(this.config.listTag,0);
            if (ul == undefined) {
                ul = new Element(this.config.listTag);
                dropOnNode.insert(ul);
            }
            ul.show();
            dropOnNode = ul;
        }
        var obj = {};
        obj[position] = node;
        dropOnNode.insert(obj);
        if (oldParent.down('li') == undefined) {
            oldParent.remove();
        }
        this.unsaved = true;
        this.serialize();
        return true;
    },
    countLevels : function(node,mode,stop) {
        var levels = 0;
        if (mode == 'up') {
            stop = (stop == undefined) ? this.list : stop;
            var ancestors = node.ancestors();
            levels = stop.select(this.config.listTag)
                .select(function(item) {
                    return ancestors.include(item);
                }).size();
        } else if (mode == 'down') {
            levels = node.select('li').max(function(subnode) {
                return this.countLevels(subnode,"up",node);
            }.bind(this));
        }
        return isNaN(levels) ? 0 : levels;
    },
    serialize: function() {
        this.saved = new Hash();
        this.list.select('li').each(function(node){
            if(this.isAccepted(node)) {
                var item = new Hash();
                item.set('parentid',(node.up('li') != undefined) ? this.getId(node.up('li')) : null);
                item.set('haschildren',(node.down('li') != undefined) ? true : false);
                this.saved.set(this.getId(node),item);
            }
        }.bind(this));
        $(this.config.inputName).value = this.saved.toJSON();
        return this.saved;
    },
    beforeUnloadHandler: function (event) {
        if(this.unsaved && this.config.langLabels.warnbeforeunload) {
            return event.returnValue = this.config.langLabels.warnbeforeunload;
        }
        return false;
    }
});

Zikula.recursiveSortableObserver = Class.create({
    initialize: function(element,func) {
        this.list = $(element);
        this.func = func;
    },
    onEnd: function() {
        this.func(this.list);
    }
});