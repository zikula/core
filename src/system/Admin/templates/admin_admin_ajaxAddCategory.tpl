<div id="ajaxNewCatHidden" class="z-hide">
    <form id="ajaxNewCatForm" class="z-clearfix" onsubmit="return Admin.Category.Add(this);" action="javascript:Admin.Category.Cancel(this)">
        <div>
            <input type="text" class="ajaxNewCat" name="catName" id="ajaxNewCat" />&nbsp;
            <a href="javascript:Admin.Category.Add(this)" id="ajaxCatImage" onclick="return Admin.Category.Add(this);" class="ajaxCatImage">{img modname=core src=button_ok.png set=icons/extrasmall __alt="Save" height="13"}</a>
            <a href="javascript:Admin.Category.Cancel(this)" onclick="return Admin.Category.Cancel(this);" class="ajaxCatImage">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Save" height="13"}</a>
        </div>
    </form>
</div>
