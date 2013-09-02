{adminheader}
{include file="Admin/modifymenu.tpl"}
<h4>{gt text="Page configuration assignments"}</h4>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Configuration file"}</th>
            <th>{gt text="Important"}</th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$pageconfigurations item=filesection key=name}
        <tr class="{cycle name=pageconfigurations values=z-odd,z-even}">
            <td>{$name|safetext}</td>
            <td>{$filesection.file|safetext}</td>
            <td>{$filesection.important|default:0|yesno}</td>
            <td class="z-right">
                <a href="{modurl modname=Theme type=admin func=modifypageconfigurationassignment themename=$themename pcname=$name|urlencode}">{img modname=core src=xedit.png set=icons/extrasmall __alt="Edit" __title="Edit"}</a>
                <a href="{modurl modname=Theme type=admin func=deletepageconfigurationassignment themename=$themename pcname=$name|urlencode}">{img modname=core src=14_layer_deletelayer.png set=icons/extrasmall __alt="Delete" __title="Delete"}</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<h4>{gt text="Page configurations in use"}</h4>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>{gt text="Configuration file"}</th>
            <th>{gt text="Configuration file found"}</th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$pageconfigs item='fileexists' key='filename'}
        <tr class="{cycle name=pageconfigs values=z-odd,z-even}">
            <td>{$filename|safetext}</td>
            <td>{$fileexists|yesno}</td>
            <td class="z-right">
                <a href="{modurl modname=Theme type=admin func=modifypageconfigtemplates themename=$themename filename=$filename}">{img modname=core src=xedit.png set=icons/extrasmall __alt="Edit" __title="Edit"}</a>
                <a href="{modurl modname=Theme type=admin func=variables themename=$themename filename=$filename}">{img modname=core src=configure.png set=icons/extrasmall __alt="Variables" __title="Variables"}</a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<p class="alert alert-info">{gt text="Notice: Any configuration files that Zikula cannot find must be created in 'themes/%s/templates/config'." tag1=$themename|safetext}</p>

<h4>{gt text="Create new page configuration assignment"}</h4>

<form class="form-horizontal" role="form" action="{modurl modname="Theme" type="admin" func="updatepageconfigurationassignment"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <fieldset>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="theme_pagemodule">{gt text="Module"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="theme_pagemodule" name="pagemodule">
                    <option value="">&nbsp;</option>
                    {foreach from=$pagetypes key='pagevalue' item='pagetext'}
                    <option value="{$pagevalue}">{$pagetext}</option>
                    {/foreach}
                    {html_options options=$modules}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="theme_pagetype">{gt text="Function type"}</label>
                <div class="col-lg-9">
                <input id="theme_pagetype" type="text" class="form-control" name="pagetype" size="30" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="theme_pagefunc">{gt text="Function name"}</label>
                <div class="col-lg-9">
                <input id="theme_pagefunc" type="text" class="form-control" name="pagefunc" size="30" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="theme_pagecustomargs">{gt text="Function arguments"}</label>
                <div class="col-lg-9">
                <input id="theme_pagecustomargs" type="text" class="form-control" name="pagecustomargs" size="30" />
                <em class="help-block z-sub">{gt text="Notice: This is a list of arguments found in the page URL, separated by '/'."}</em>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="theme_filename">{gt text="Configuration file"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="theme_filename" name="filename">
                    {html_options values=$existingconfigs output=$existingconfigs}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="theme_important">{gt text="Important"}</label>
                <div class="col-lg-9">
                <input id="theme_important" type="checkbox" name="pageimportant" value="1" />
                <em class="help-block z-sub">{gt text="Any match with this assignment will be consider over the following others."}</em>
            </div>
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                    <a class="btn btn-default" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}
