// Copyright Zikula Foundation, licensed MIT.

if (typeof(Zikula) == 'undefined')
    Zikula = {};

// Requires prototype, pnajax, scriptaculous
Zikula.itemlist = Class.create(/** @lends Zikula.itemlist.prototype */{
    /**
     * Helper for creating sortable lists based on ul/ol html lists
     *
     * @example
     * // note - $super param is omited
     * var list = new Zikula.itemlist('menuitemlist', {headerpresent: true, firstidiszero: true});
     *
     * @class Zikula.itemlist
     * @constructs
     *
     * @param {HTMLElement|String} listid ID of the list to work with
     * @param {Object} [options] Config object
     * @param {Boolean} [options.headerpresent=false] Should first item list be treated as header
     * @param {Boolean} [options.firstidiszero=false] Ids starts from zero
     * @param {Boolean} [options.sortable=true] Should list be sortable
     * @param {Boolean} [options.recursive=false] Should list be recursivly sortable (allows to create multilevel lists)
     * @param {Boolean} [options.quotekeys=false] Should node names be enclosed by quotes
     * @param {String} [options.inputName=''] For recusive list - input used for storing JSON encoded list order
     * @param {Function} [options.afterInitialize] Callback called after initialization
     * @param {Function} [options.beforeAppend] Callback called before new item append
     * @param {Function} [options.afterAppend] Callback called after new item append
     *
     * @return {Zikula.itemlist} New Zikula.itemlist instance
     */
    initialize: function(listid, options) {
        this.id  = listid;
        this.options = {
            headerpresent: false,
            firstidiszero: false,
            sortable: true,
            recursive: false,
            quotekeys: false,
            inputName: '',
            afterInitialize: Prototype.emptyFunction,
            beforeAppend: Prototype.emptyFunction,
            afterAppend: Prototype.emptyFunction
        };
        Object.extend(this.options, options || {});

        this.lastitemid = Number(!this.options.firstidiszero)-1;
        var size = $(this.id).immediateDescendants().size();
        var offset = 0;
        if (this.options.headerpresent) {
            offset = 1;
        }

        var maxId = $(this.id).select('.itemid').max(function(node) {
            return Number(node.innerHTML);
        });
        this.lastitemid = isNaN(maxId) ? this.lastitemid : maxId;

        // define a rule to delete a menuitem when the trash icon is clicked
        var buttondeleteselector = '#' + this.id + ' .buttondelete';
        $$(buttondeleteselector).invoke('observe', 'click', this.deleteitem.bindAsEventListener(this));

        if (this.options.sortable) {
            if (this.options.recursive) {
                var sortableConfig = {
                    id:         this.id,
                    only:       'z-sortable',
                    listTag:    'ol',
                    inputName:  this.options.inputName,
                    onUpdate:   this.itemlistrecolor.bind(this)
                };
                this.sortable = new Zikula.recursiveSortable(sortableConfig);
            } else {
                Sortable.create(this.id, { 
                    only: 'z-sortable',
                    constraint: false,
                    onUpdate: this.itemlistrecolor.bind(this)
                });
            }
            $A($(this.id).getElementsByClassName('z-sortable')).each(
                function(node) 
                {
                    var listid = node.id;
                    Element.addClassName(listid, 'z-itemsort')
                }
            );
        }
        if (size == offset) {
            this.appenditem();
        }

    },

    /**
     * Parses the ID and generate an standard name
     * @private
     * @param {String} id Node id
     * @return {String} Node name
     */
    getnamefromid: function(id) {
        var chunks = id.split('_');
        var result = chunks[0];

        chunks = chunks.slice(1);
        chunks.each( function(chunk){
            if (this.options.quotekeys && chunk != '') {
                result += "['"+chunk+"']";
            } else {
                result += '['+chunk+']';
            }
        }.bind(this));

        return result;
    },

    /**
     * Recolor the itemlist
     * @return void
     */
    itemlistrecolor: function()
    {
        Zikula.recolor(this.id, 'listheader');
    },

    /**
     * Appends a new item by cloning a predefined one
     * @return {Number} Last item id
     */
    appenditem: function()
    {
        // clone the new item
        var newitem = $(this.id+'_emptyitem').cloneNode(true);

        this.lastitemid++;
        lastid = this.lastitemid;
        newitem.id = 'li_' + this.id + '_' + lastid;

        if ($(newitem).hasClassName('z-odd')) {
            $(newitem).removeClassName('z-odd');
            $(newitem).addClassName('z-even');
        } else {
            $(newitem).removeClassName('z-even');
            $(newitem).addClassName('z-odd');
        }

        $A(newitem.getElementsByClassName('listinput')).each(
            function(node) {
                node.id   = node.id.replace(/X/g, lastid);
                node.name = this.getnamefromid(node.id);
                // prevent duplicated IDs for simple IDs like "var_"
                if (this.id.endsWith('_')) {
                    this.id += lastid;
                }
            }.bind(this)
        );
        $A(newitem.getElementsByTagName('button')).each(
            function(node) {
                node.id = node.id.replace(/X/g, lastid);
            }
        );
        $A(newitem.getElementsByClassName('itemid')).each(
            function(node) {
                if (node.hasAttribute('id')) {
                    node.id = node.id.replace(/X/g, lastid);
                }
                if (node.hasAttribute('value')) {
                    node.writeAttribute('value', lastid);
                }
                node.update(lastid)
            }
        );

        $(this.id).appendChild(newitem);

        // add observer for delete button
        newitem.down('.buttondelete').observe('click', this.deleteitem.bindAsEventListener(this));

        if (this.options.sortable) {
            if (this.options.recursive) {
                this.sortable.initNode(newitem);
            } else {
                Sortable.create(this.id, {
                    only: 'z-sortable',
                    constraint: false,
                    onUpdate: this.itemlistrecolor.bind(this)
                });
            }
            $A(document.getElementsByClassName('z-sortable')).each(
                function(node) 
                {
                    var listid = node.id;
                    Element.addClassName(listid, 'z-itemsort')
                }
            );
        }
        this.itemlistrecolor();

        return lastid;
    },
    /**
     * Event handler for deleting item from list
     * @private
     * @param {Event} event
     * @return void
     */
    deleteitem: function(event) {
        var button = event.findElement('.buttondelete');
        var itemid = button.id.replace('buttondelete', 'li');
        if ($(itemid)) {
            $(itemid).remove();
        }
        // recolor the list trusting in the var name convention
        this.itemlistrecolor();
    }
});

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
            maxdepthreached:    Zikula.__('Maximum depth reached. Limit is: '),
            warnbeforeunload:   Zikula.__('You have unsaved changes!')
        }, this.config.langLabels);

        this.list = $(this.config.id).cleanWhitespace();
        this.list.select('li').each(this.initNode.bind(this));

        Draggables.addObserver(new Zikula.recursiveSortableObserver(this.list, this.config.onUpdate));

        this.unsaved = false;
        this.serialize();
    },
    initNode: function(node) {
        if (this.isAccepted(node)) {
            //init Draggable
            new Draggable(node,{
                handle:this.config.handler,
                onEnd: this.endDrag.bind(this),
                onStart: this.startDrag.bind(this),
                revert: true,
                endeffect: function(element) {
                    new Effect.Highlight(element);
                },
                scroll: window
            });
            //init Droppables
            Droppables.add(node, {
                accept: this.config.only,
                hoverclass: this.config.dropOnClass,
                overlap: 'vertical',
                onDrop: this.dropNode.bind(this),
                onHover: this.hoverNode.bind(this)
            });
        }
    },
    isAccepted: function(node) {
        return this.config.only.length == 0 || this.config.only.any(function(c) {return node.hasClassName(c);});
    },
    getId: function(node) {
        return Number(node.identify().match(this.config.nodeIdPattern)[1]);
    },
    startDrag: function(draggable) {
        this.dropCache = {};
        draggable.element.addClassName(this.config.onDragClass);
    },
    endDrag: function(draggable) {
        if (this.dropCache.lastElement) {
            this.insertNode(draggable.element,this.dropCache.lastElement);
        }
        this.list.select('.' + this.config.dropAfterClass)
            .invoke('removeClassName', this.config.dropAfterClass);
        this.list.select('.' + this.config.dropBeforeClass)
            .invoke('removeClassName', this.config.dropBeforeClass);
        draggable.element.removeClassName(this.config.onDragClass);
    },
    hoverNode: function(node,dropOnNode,overlap) {
        this.list.select('.' + this.config.dropAfterClass)
            .invoke('removeClassName', this.config.dropAfterClass);
        this.list.select('.' + this.config.dropBeforeClass)
            .invoke('removeClassName', this.config.dropBeforeClass);
        if (overlap > this.config.dropAfterOverlap[1]) {
            dropOnNode.addClassName(this.config.dropBeforeClass);
            this.dropCache.lastElement = ['before', dropOnNode.identify()];
        } else if (overlap < this.config.dropAfterOverlap[0]) {
            dropOnNode.addClassName(this.config.dropAfterClass);
            this.dropCache.lastElement = ['after', dropOnNode.identify()];
        } else {
            this.dropCache.element = dropOnNode;
            dropOnNode.removeClassName(this.config.dropAfterClass);
            dropOnNode.removeClassName(this.config.dropBeforeClass);
        }
    },
    dropNode: function(node,dropOnNode,point) {
        var insertion = false;
        if (dropOnNode.hasClassName(this.config.dropAfterClass)) {
            insertion = this.insertNode(node, ['after', dropOnNode]);
        } else if (dropOnNode.hasClassName(this.config.dropBeforeClass)) {
            insertion = this.insertNode(node, ['before', dropOnNode]);
        } else {
            insertion = this.insertNode(node, ['bottom', dropOnNode]);
        }
        if (!insertion) {
            return false;
        }
        this.dropCache = {};
    },
    insertNode: function(node,params) {
        var dropOnNode = $(params[1]),
            position = params[0],
            newlevel = position == 'bottom',
            oldParent = $(node.up(this.config.listTag, 0).identify());
        if (this.config.maxDepth > 0) {
            var dropOnNodeLevel = this.countLevels(dropOnNode, 'up'),
                nodeLevel = this.countLevels(node, 'down'),
                treeDepth = dropOnNodeLevel + nodeLevel + Number(newlevel) + 1;
            if (treeDepth > this.config.maxDepth) {
                alert(this.config.langLabels.maxdepthreached + this.config.maxDepth);
                this.dropCache = {};
                return false;
            }
        }
        if (newlevel) {
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
                return this.countLevels(subnode, 'up', node);
            }.bind(this));
        }
        return isNaN(levels) ? 0 : levels;
    },
    serialize: function() {
        this.saved = new Hash();
        this.list.select('li').each(function(node){
            if (this.isAccepted(node)) {
                var item = new Hash();
                item.set('parentid', (node.up('li') != undefined) ? this.getId(node.up('li')) : null);
                item.set('haschildren', (node.down('li') != undefined) ? true : false);
                this.saved.set(this.getId(node), item);
            }
        }.bind(this));
        $(this.config.inputName).value = Object.toJSON(this.saved.toJSON());
        return this.saved;
    },
    beforeUnloadHandler: function (event) {
        if (this.unsaved && this.config.langLabels.warnbeforeunload) {
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