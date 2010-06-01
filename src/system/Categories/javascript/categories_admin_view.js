Event.observe(window,'load', function() {
    Zikula.TreeSortable.categoriesTree.config.onSave = CategoriesSave;
});
function CategoriesSave(node,params,data) {
    var categoryId = Zikula.TreeSortable.categoriesTree.getNodeId(node),
        parentId = Zikula.TreeSortable.categoriesTree.getNodeId(node.up('li'));
        pars = {
        'categoryId': categoryId,
        'parentId': parentId,
        'data': data,
        'authid': $F('categoriesauthid')
    }
    var res = null,
        request = new Ajax.Request(
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