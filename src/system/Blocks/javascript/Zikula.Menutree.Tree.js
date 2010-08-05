Zikula.define('Menutree');

Zikula.Menutree.Tree = Class.create(Zikula.TreeSortable,{
    initialize: function($super, element, config) {
        config = this.decodeConfig(config);
        config = Object.extend({
            unactiveClass:           'unactive',
            langs: ['en'],
            onSave: this.save
        }, config || { });
        $super(element,config);
        this.tree.up('form').insert(new Element('input',{type:'hidden','id':'menutree_content',name:'menutree_content'}));
    },
    initNode: function($super,node) {
        node.select('a[lang!='+'en'+']').invoke('hide');
        $super(node);
    },
    serializeNode: function($super,node,index) {
        var link, nodeData = {};
        this.config.langs.each(function(lang) {
            link =  node.down('a[lang='+lang+']');
            nodeData[lang] = {
                id:         this.getNodeId(node),
                name:       link.innerHTML,
                title:      link.readAttribute('title'),
                className:  $w(link.className).without(this.config.unactiveClass).join(' '),
                state:      !link.hasClassName(this.config.unactiveClass),
                href:       link.readAttribute('href'),
                lang:       link.readAttribute('lang'),
                lineno:     index || null,
                parent:     node.up('#'+this.tree.id+' li') ? this.getNodeId(node.up('#'+this.tree.id+' li')) : 0
            };
        }.bind(this));
        return nodeData;
    },
    save: function(node,params,data) {
        $('menutree_content').setValue(Zikula.urlsafeJsonEncode(data, false));
        return true;
    }
});
Object.extend(Zikula.Menutree.Tree,{
    trees: {},
    add: function(element,config) {
        if (!this.trees.hasOwnProperty(element)) {
            this.trees[element] = new Zikula.Menutree.Tree(element,config);
        }
    }
});