// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

Event.observe(window, 'load', function() {
    Zikula.TreeSortable.trees.categoriesTree.config.onSave = CategoriesSave;
});

function CategoriesSave(node,params,data) {
    // do not allow inserts on root level
    if (node.up('li') == undefined) {
        return false;
    }
    var pars = {
            'data': data,
            'authid': $F('categoriesauthid')
    }
    var request = new Ajax.Request(
        "ajax.php?module=Categories&func=resequence",
        {
            method: 'post',
            parameters: pars,
            onSuccess: CategoriesSuccessResponse,
            onFailure: CategoriesFailureResponse
        });
    return request.success();
}

function CategoriesSuccessResponse(response)
{
    var responseText = pndejsonize(response.responseText);

    pnupdateauthids(responseText.authid);
    $('categoriesauthid').value = responseText.authid;
    return true;
}

function CategoriesFailureResponse(response)
{
    pnshowajaxerror(response.responseText);
    return Zikula.TreeSortable.categoriesTree.revertInsertion();
}
