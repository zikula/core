// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Zikula.Tree and Zikula.TreeSortable
 */
if (typeof(Zikula) == 'undefined') {
    Zikula = {};
}

Zikula.Tree = Class.create(/** @lends Zikula.Tree.prototype */
{
    /**
     * Class allowing to convert unordered list (ul/li) to collapsible trees.
     * Works the best with Zikula_Tree class, which prepare html output for tree.
     *
     * @class Zikula.Tree
     * @constructs
     *
     * @todo Allow to cunstruct tree over simple unordered list
     *
     * @param {HTMLElement|String} element HTML list which will be converted to tree
     * @param {Object|String} [config] Config object or JSON string
     * @param {RegExp} [config.nodeIdPattern] RegExp to match nodes Id
     * @param {String} [config.toggler] CSS class for element which will toggle nodes (collapse,expand)
     * @param {String} [config.icon] CSS class for element which will hold nodes icons
     * @param {String} [config.imagesDir] Path for images
     * @param {Object} [config.images] Object with used icons
     * @param {String} [config.images.plus] Expand icon for toggler
     * @param {String} [config.images.minus] Collapse icon for toggler
     * @param {String} [config.images.parent] Icon for parent nodes
     * @param {String} [config.images.parentOpen] Icon for expanded parent nodes
     * @param {String} [config.images.item] Icon for leaf nodes
     *
     * @return {Zikula.Tree} New Zikula.Tree instance
     */
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
        this.tree.select('li').each(this.initNode.bind(this));
        // initialy hide childnodes
        this.getStatus();
        this.tree.select('ul').each(function(u) {
            if (!this.status.get(u.up('li').identify())) {
                this.hideNode(u);
            }
        }.bind(this));
    },

    /**
     * Prepares nodes for draggin and dropping
     * @private
     * @param {HTMLElement} node Node to prepare
     * @return void
     */
    initNode: function(node) {
        var toogler = node.down('.'+this.config.toggler),
            span = node.down('li.z-tree-parent > span');
        // bind toggle action
        if (toogler) {
            toogler.observe('click',this.toggleNode.bindAsEventListener(this));
        }
        // bind also empty spans
        if (span) {
            span.observe('click',this.toggleNode.bindAsEventListener(this));
        }
    },

    /**
     * Event handler for toggling nodes
     * @private
     * @todo Make it public, allow to pass node as param
     * @param {Event} event Click event on node toggler
     * @return void
     */
    toggleNode: function(event) {
        var ul = event.element().up('li').down('ul')
        if (ul != undefined) {
            if (ul.visible()) {
                this.hideNode(ul);
            } else {
                this.showNode(ul);
            }
            this.saveStatus();
        }
    },

    /**
     * Expand selected node
     * @private
     * @param {HTMLElement} node Node to expand
     * @return void
     */
    showNode: function(node) {
        node.show();
        node.previous('.'+this.config.toggler).writeAttribute('src', this.config.images.minus);
        node.previous('.'+this.config.icon).writeAttribute('src', this.config.images.parentOpen);
        this.status.set(node.up('li').identify(), node.visible());
    },

    /**
     * Collapse selected node
     * @private
     * @param {HTMLElement} node Node to collapse
     * @return void
     */
    hideNode: function(node) {
        node.hide();
        node.previous('.'+this.config.toggler).writeAttribute('src', this.config.images.plus);
        node.previous('.'+this.config.icon).writeAttribute('src', this.config.images.parent);
        this.status.set(node.up('li').identify(), node.visible());
    },

    /**
     * Reads tree status (list of collapsed/expaned nodes) from cookie
     * @private
     * @return void
     */
    getStatus: function() {
        this.status = Zikula.Cookie.get(this.id) ? $H(Zikula.Cookie.get(this.id)) : new Hash();
    },

    /**
     * Saves tree status to cookie
     * @private
     * @return void
     */
    saveStatus: function() {
        Zikula.Cookie.set(this.id, this.status,3600*24*7);
    },

    /**
     * Decode node id using config.nodeIdPattern RegExp
     * @private
     * @param {HTMLElement} node
     * @return {Nubmer} Numeric Id
     */
    getNodeId: function(node) {
        return node.id.match(this.config.nodeIdPattern)[1];
    },

    /**
     * Checks if config passed to initialize methdod is JSON and if so - decodes it
     * @private
     * @param {mixed} config Config to decode
     * @return {mixed} Decoded config
     */
    decodeConfig: function(config) {
        if (Object.isString(config) && config.isJSON()) {
            config = config.evalJSON(true);
        }
        return config;
    },

    /**
     * Expands whole tree or given tree branch
     * @param {HTMLElement} [node] Branch to expand, if not provided - whole tree is expanded
     * @return void
     */
    expandAll: function(node) {
        var base = Object.isElement(node) ? node : this.tree;
        base.select('ul').each(function(ul) {
            this.showNode(ul);
        }.bind(this));
        this.saveStatus();
    },

    /**
     * Collapse whole tree or given tree branch
     * @param {HTMLElement} [node] Branch to collapse, if not provided - whole tree is collapsed
     * @return void
     */
    collapseAll: function(node) {
        var base = Object.isElement(node) ? node : this.tree;
        base.select('ul').reverse(true).each(function(ul) {
            this.hideNode(ul);
        }.bind(this));
        this.saveStatus();
    },

    /**
     * Serialize tree data and returns it as JSON.
     * When called without branch param - will serialize whole tree.
     * When branch is defined - will serialize only choosen node (and it's subnodes).
     * Internaly calls serializeNode for each node.
     * @param {HTMLElement|String} [branch] Empty for whole tree or selected node
     * @return {String} JSON object
     */
    serialize: function(branch) {
        this.serialized = {};
        branch = branch == undefined ? this.tree : branch;
        $(branch).select('li').each(function(node, index) {
            this.serialized[this.getNodeId(node)] = this.serializeNode(node, index);
        }.bind(this));
        return Object.toJSON(this.serialized);
    },

    /**
     * Internal procedure for serializing nodes.
     * Reads node id, node name and parent id. If privides - adds to data lineno - sequence number.
     * @param {HTMLElement} node Node to serialize
     * @param {Nubmer} [index] Sequence nubmer
     * @return {Object} Node data
     */
    serializeNode: function(node,index) {
        return {
            id:     this.getNodeId(node),
            name:   node.down('a').innerHTML,
            lineno: index || null,
            parent: node.up('#'+this.tree.id+' li') ? this.getNodeId(node.up('#'+this.tree.id+' li')) : 0
        };
    }
});

Object.extend(Zikula.Tree,/** @lends Zikula.Tree.prototype */
{
    /**
     * List of initilized trees.
     * Trees initilized via add method are avaiable as Zikula.Tree.trees[element.id]
     * @static
     * @name Zikula.Tree.tree
     */
    trees: {},
    /**
     * Static method allowing to initialize global avaiable Zikula.Tree instances
     * @see Zikula.Tree construct for details
     * @static
     * @name Zikula.Tree.add
     * @function
     * @param {HTMLElement|String} element Element id or reference
     * @param {Object} [config] Config object
     * @retun void
     */
    add: function(element,config) {
        if (!this.trees.hasOwnProperty(element)) {
            this.trees[element] = new Zikula.Tree(element,config);
        }
    }
});

Zikula.TreeSortable = Class.create(Zikula.Tree,/** @lends Zikula.TreeSortable.prototype */
{
    /**
     * Extension for {@link Zikula.Tree}. Allows to create sortable trees.<br />
     * After each tree change config.onSave callback is called. As params are passed:<br />
     * - node - node which is moved<br />
     * - params - array with insertion params, which are [relativenode, dir];
     * "dir" is a string with value "after', "before" or "bottom" and says
     * that affected node is insert after, before or as last child of "relativenode" <br />
     * - tree data - serialized to JSON tree data<br />
     * Callback need to return true on succes - otherwise change will be reverted
     *
     * @class Zikula.TreeSortable
     * @extends Zikula.Tree
     * @constructs
     *
     * @todo Allow to cunstruct tree over simple unordered list
     *
     * @param {Zikula.Tree} $super Reference to super class, this is private param, do not use it.
     * @param {HTMLElement|String} element HTML list which will be converted to tree
     * @param {Object|String} [config] Config object or JSON string. Extends {@link Zikula.Tree} config
     * @param {String} [config.nodeLeaf='z-tree-leaf'] CSS class for leaf node
     * @param {String} [config.nodeLast='z-tree-last'] CSS class for last node in branch
     * @param {Array} [config.disabled] List of nodes id, disabled for drag and drop
     * @param {Array} [config.disabledForDrag] List of nodes id, disabled for drag
     * @param {Array} [config.disabledForDrop] List of nodes id, disabled for drop
     * @param {String} [config.draggableClass='z-tree-draggable'] CSS class for draggable elements
     * @param {String} [config.droppableClass='z-tree-droppable'] CSS class for droppable elements
     * @param {String} [config.onDragClass='z-tree-onDragClass'] CSS class added to node while dragging
     * @param {String} [config.dropOnClass='z-tree-dropOnClass'] CSS class indicating that current drag node will be dropped into this node
     * @param {String} [config.dropAfterClass='z-tree-dropAfterClass'] CSS class indicating that current drag node will be dropped after this node
     * @param {String} [config.dropBeforeClass='z-tree-dropBeforeClass'] CSS class indicating that current drag node will be dropped before this node
     * @param {Number} [config.expandTimeout=1500] When node is hover during drag it will expand after specified time in milliseconds
     * @param {Number} [config.maxDepth=0] Limit for tree depth, default 0 means no limit
     * @param {Function} [config.onSave] Callback called after node will be moved. It must return true on success and false on failure. When false is return node move will be reverted
     *
     * @return {Zikula.TreeSortable} New Zikula.TreeSortable instance
     */
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            nodeSingle:         'z-tree-single',
            nodeFirst:          'z-tree-first',
            nodeLast:           'z-tree-last',
            nodeParent:         'z-tree-last',
            nodeLeaf:           'z-tree-leaf',
            fixedParent:        'z-tree-fixedparent',
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
//        this.tree.select('li').each(this.initNode.bind(this));
    },

    /**
     * Prepares nodes for draggin and dropping
     * @private
     * @param {HTMLElement} node Node to prepare
     * @return void
     */
    initNode: function($super, node) {
        $super(node);
        if (this.config.disabled.include(this.getNodeId(node))) {
            return;
        }
        if (!this.config.disabledForDrag.include(this.getNodeId(node))) {
            node.addClassName(this.config.draggableClass);
            new Draggable(node, {
                handle: this.config.icon,
                onEnd: this.endDrag.bind(this),
                onStart: this.startDrag.bind(this),
                revert: true,
                starteffect: null,
                scroll: window
            });
        }
        node.addClassName(this.config.droppableClass);
        Droppables.add(node, {
            accept: this.config.draggableClass,
            hoverclass: this.config.dropOnClass,
            overlap: 'vertical',
            onDrop: this.dropNode.bind(this),
            onHover: this.hoverNode.bind(this)
        });
    },

    /**
     * Draggable callback called when drag is started.
     * Clearch internal cache and marks dragging element
     * @private
     * @param {Object} draggable Draggable object
     * @return void
     */
    startDrag: function(draggable) {
        this.dropCache = {};
        draggable.element.addClassName(this.config.onDragClass);
    },

    /**
     * Draggable callback called when drag is finished.
     * Remove dragging indicators from tree.
     * Check dropCache if dragged node should be inserted - is so performs insertion
     * @private
     * @param {Object} draggable Draggable object
     * @return void
     */
    endDrag: function(draggable) {
        if (this.dropCache.lastElement) {
            this.insertNode(draggable.element,this.dropCache.lastElement);
            this.drawNodes();
        }
        this.tree.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName',this.config.dropAfterClass);
        this.tree.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName',this.config.dropBeforeClass);
        draggable.element.removeClassName(this.config.onDragClass);
    },

    /**
     * Droppables callback called when node is hovered by dragged node
     * When hover takes time longer then defined in config.expandTimeout it expand hovered node
     * @private
     * @param {HTMLElement} node Dragged node
     * @param {HTMLElement} dropOnNode Hover node
     * @param {Number} overlap
     * @return void
     */
    hoverNode: function(node, dropOnNode, overlap) {
        window.clearTimeout(this.dropCache.timeout);
        this.tree.select('.'+this.config.dropAfterClass)
            .invoke('removeClassName', this.config.dropAfterClass);
        this.tree.select('.'+this.config.dropBeforeClass)
            .invoke('removeClassName', this.config.dropBeforeClass);
        var o0 = this.config.disabledForDrop.include(this.getNodeId(dropOnNode)) ? 0.5 : this.config.dropAfterOverlap[0],
            o1 = this.config.disabledForDrop.include(this.getNodeId(dropOnNode)) ? 0.5 : this.config.dropAfterOverlap[1];
        if (overlap > o1) {
            dropOnNode.addClassName(this.config.dropBeforeClass);
            this.dropCache.lastElement = ['before', dropOnNode.id];
        } else if (overlap <= o0) {
            dropOnNode.addClassName(this.config.dropAfterClass);
            this.dropCache.lastElement = ['after', dropOnNode.id];
        } else {
            this.dropCache.expand  = this.expandOne.bindAsEventListener(this);
            this.dropCache.element = dropOnNode;
            this.dropCache.timeout = window.setTimeout(this.dropCache.expand, this.config.expandTimeout);
            dropOnNode.removeClassName(this.config.dropAfterClass);
            dropOnNode.removeClassName(this.config.dropBeforeClass);
        }
    },

    /**
     * Droppables callback which handle node insertions
     * @private
     * @param {HTMLElement} node Dragged node
     * @param {HTMLElement} dropOnNode Hover node
     * @param {Event} point
     * @return {Boolean} True on succes, false otherwise
     */
    dropNode: function(node, dropOnNode, point) {
        var insertion = true;
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
        return true;
    },

    /**
     * Procedure to hanlde node insertions
     * Checks if maxDepth is not exceeded and inserts node on specified position.
     * After insertion calles config.onSave callback - and if false is returned reverts changes.
     * @private
     * @param {HTMLElement} node Inserted node
     * @param {Array} params Insertion params
     * @param {Boolean} [revert] Tells if insertion is for revert purposes
     * @return {Boolean} True on success, false otherwise
     */
    insertNode: function(node, params, revert) {
        var dropOnNode = $(params[1]),
            position = params[0],
            newlevel = position == 'bottom';
        this.prevPosition = {
                node: node,
                parent: node.up('li') || null,
                previous: node.previous('li') || null,
                next: node.next('li') || null
        };
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
        if (!revert && Object.isFunction(this.config.onSave)
            && !this.config.onSave(node, params, this.serialize())) {
            this.revertInsertion();
            return false;
        }
        this.dropCache = {};
        this.drawNodes();
        return true;
    },

    /**
     * Reverts last insertion
     * @private
     * @return void
     */
    revertInsertion: function(){
        if (this.prevPosition == undefined) {
            return;
        }
        if (this.prevPosition.previous) {
            var ref = this.prevPosition.previous,
                pos = 'after';
        } else if (this.prevPosition.next) {
            var ref = this.prevPosition.next,
                pos = 'before';
        } else if (this.prevPosition.parent) {
            var ref = this.prevPosition.parent,
                pos = 'bottom';
        }
        this.insertNode(this.prevPosition.node, [pos,ref], true);
    },

    /**
     * Counts given node depth
     * @private
     * @param {HTMLElement} node
     * @param {String} mode Count up od down
     * @param {Boolean} stop Where to stop counting
     * @return {Nubmer} Node depth
     */
    countLevels : function(node, mode, stop) {
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
                return this.countLevels(subnode, 'up', node);
            }.bind(this));
        }
        return isNaN(levels) ? 0 : levels;
    },

    /**
     * Callback used for expaning node hovered while dragging other node
     * @private
     * @return void
     */
    expandOne: function() {
        if (this.dropCache.element && this.dropCache.element.down('ul') != undefined) {
            this.showNode(this.dropCache.element.down('ul'));
            this.dropCache.element = false;
            this.saveStatus();
        }
    },

    /**
     * Redraws whole tree
     * @private
     * @return void
     */
    drawNodes: function() {
        this.tree.select('li').each(this.drawNode.bind(this));
    },

    /**
     * Redraws selected node - sets proper class names on node, removes orphaned ul elements
     * @private
     * @param {HTMLElement} node Node to draw
     * @return void
     */
    drawNode: function (node) {
        [
            this.config.nodeSingle,
            this.config.nodeFirst,
            this.config.nodeLast,
            this.config.nodeParent,
            this.config.nodeLeaf
        ].each(function(cn) {
            $(node).removeClassName(cn)
        });
        if (node.next() == undefined) {
            node.addClassName(this.config.nodeLast);
        }
        if (node.up('ul').select('li').size() == 1) {
            node.addClassName(this.config.nodeSingle);
        }
        if (node.down('li') == undefined && node.down('ul') != undefined) {
            node.down('ul').remove();
        }
        if (node.down('a').hasClassName(this.config.fixedParent) || node.down('li') != undefined) {
            if (node.down('ul')) {
                node.addClassName(this.config.nodeParent);
                if (node.down('ul').visible()) {
                    node.down('.'+this.config.toggler).writeAttribute({src: this.config.images.minus});
                    node.down('.'+this.config.icon).writeAttribute('src', this.config.images.parentOpen);
                } else {
                    node.down('.'+this.config.icon).writeAttribute('src', this.config.images.parent);
                }
            } else {
                node.addClassName(this.config.nodeLeaf);
                node.down('.'+this.config.icon).writeAttribute('src', this.config.images.parent);
            }
        } else {
            node.addClassName(this.config.nodeLeaf);
            node.down('.'+this.config.icon).writeAttribute('src', this.config.images.item);
        }
    }
});

Object.extend(Zikula.TreeSortable,/** @lends Zikula.TreeSortable.prototype */
{
    /**
     * List of initilized trees.
     * Trees initilized via add method are avaiable as Zikula.TreeSortable.trees[element.id]
     * @static
     * @name Zikula.TreeSortable.trees
     */
    trees: {},
    /**
     * Static method allowing to initialize global avaiable Zikula.TreeSortable instances
     * @see Zikula.TreeSortable construct for details
     * @static
     * @name Zikula.TreeSortable.add
     * @function
     * @param {HTMLElement|String} element Element id or reference
     * @param {Object} [config] Config object
     * @retun void
     */
    add: function(element,config) {
        if (!this.trees.hasOwnProperty(element)) {
            this.trees[element] = new Zikula.TreeSortable(element, config);
        }
    }
});

Event.observe(window, 'load', function() { 
    $$('a.leaf').each(function(s) {
        if (typeof s.innerText != 'undefined') {
            s.innerHTML = s.innerText
        }
    });
});
