// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

Event.observe(window, 'load', function() {
    Zikula.TreeSortable.trees.categoriesTree.config.onSave = CategoriesSave;
    $('catExpand').observe('click',function(e){
        e.preventDefault();
        Zikula.TreeSortable.trees.categoriesTree.expandAll();
    });
    $('catCollapse').observe('click',function(e){
        e.preventDefault();
        Zikula.TreeSortable.trees.categoriesTree.collapseAll();
    });
});

function CategoriesSave(node,params,data) {
    // do not allow inserts on root level
    if (node.up('li') == undefined) {
        return false;
    }
    var pars = {
            'data': data
    }
    var request = new Zikula.Ajax.Request(
        "ajax.php?module=Categories&func=resequence",
        {
            method: 'post',
            parameters: pars,
            authid: 'categoriesauthid',
            onComplete: CategoriesSaveResponse
        });
    return request.success();
}

function CategoriesSaveResponse(req)
{
    if (!req.isSuccess()) {
    	Zikula.showajaxerror(req.getMessage());
        return Zikula.TreeSortable.categoriesTree.revertInsertion();
    }
    return true;
}
