Zikula.define('Menutree');

Zikula.Menutree.Tree = Class.create(Zikula.TreeSortable,{
    initialize: function($super, element, config) {
        $super(element,config);
    },
    initNode: function($super,node) {
        node.select('a[lang!='+'en'+']').invoke('hide');
        $super(node);
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