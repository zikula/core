/**
 * Zikula Application Framework
 * 
 * @copyright (c) Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Javascript
 * @subpackage Utilities
 */

if (typeof(Zikula) == 'undefined')
    Zikula = {};

// Requires prototype, pnajax, scriptaculous
Zikula.itemlist = Class.create({
    /**
     * Initialize the list
     * @param   listid    string   ID of the list to work with
     * @param   options   array    enable or disable specific options
     */
    initialize: function(listid, options) {
        this.id  = listid;
        this.options = {
            headerpresent: false,
            firstidiszero: false,
            sortable: true,
            quotekeys: false,
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

        if (size == offset) {
            this.appenditem();
        }

        // define a rule to delete a menuitem when the trash icon is clicked
        var buttondeleteselector = '#'+this.id+' .buttondelete';
        $$(buttondeleteselector).invoke('observe','click',this.deleteitem.bindAsEventListener(this));

        if (this.options.sortable) {
            Sortable.create(this.id,
                            { 
                              only: 'z-sortable',
                              constraint: false,
                              onUpdate: this.itemlistrecolor.bind(this)
                            });
            $A($(this.id).getElementsByClassName('z-sortable')).each(
                function(node) 
                {
                    var listid = node.id;
                    Element.addClassName(listid, 'z-itemsort')
                }
            );
        }
    },

    /**
     * Parses the ID and generate an standard name
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
     */
    itemlistrecolor: function()
    {
        pnrecolor(this.id, 'listheader');
    },

    /**
     * Appends a new item by cloning a predefined one
     * @return int last item id
     */
    appenditem: function()
    {
        // clone the new item
        var newitem = $(this.id+'_emptyitem').cloneNode(true);

        this.lastitemid++;
        lastid = this.lastitemid;
        newitem.id = 'li_'+this.id+'_'+lastid;

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
                if (node.hasAttribute('id')) {node.id = node.id.replace(/X/g, lastid);}
                if (node.hasAttribute('value')) {node.writeAttribute('value', lastid);}
                node.update(lastid)
            }
        );

        $(this.id).appendChild(newitem);

        // add observer for delete button
        newitem.down('.buttondelete').observe('click',this.deleteitem.bindAsEventListener(this));

        if (this.options.sortable) {
            Sortable.create(this.id,
                            { 
                              only: 'z-sortable',
                              constraint: false,
                              onUpdate: this.itemlistrecolor.bind(this)
                            });
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
    deleteitem: function(event) {
        var button = event.element();
        var itemid = button.id.replace('buttondelete', 'li');
        if ($(itemid)) {
          $(itemid).remove();
        }
        // recolor the list trusting in the var name convention
        this.itemlistrecolor();
    }
});
