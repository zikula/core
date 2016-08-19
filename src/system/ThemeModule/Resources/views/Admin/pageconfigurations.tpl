{adminheader}
{include file="Admin/modifymenu.tpl"}
<h4>{gt text="Page configuration assignments"}</h4>

<table class="table table-bordered table-striped">
    <colgroup>
        <col id="cName" />
        <col id="cConfigurationFile" />
        <col id="cImportant" />
        <col id="cActions" />
    </colgroup>
    <thead>
        <tr>
            <th id="hName" scope="col">{gt text='Name'}</th>
            <th id="hConfigurationFile" scope="col">{gt text='Configuration file'}</th>
            <th id="hImportant" scope="col">{gt text='Important'}</th>
            <th id="hActions" scope="col" class="text-right">{gt text='Actions'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach key='name' item='filesection' from=$pageconfigurations}
        <tr>
            <td headers="hName">{$name|safetext}</td>
            <td headers="hConfigurationFile">{$filesection.file|safetext}</td>
            <td headers="hImportant">{$filesection.important|default:0|yesno}</td>
            <td headers="hActions" class="actions">
                <a class="fa fa-pencil" href="{route name='zikulathememodule_admin_modifypageconfigurationassignment' themename=$themename pcname=$name|urlencode}" title="{gt text='Edit'}"></a>
                <a class="fa fa-trash-o" href="{route name='zikulathememodule_theme_deletepageconfigurationassignment' themename=$themename pcname=$name|urlencode}" title="{gt text='Delete'}"></a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<h4>{gt text="Page configurations in use"}</h4>

<table class="table table-bordered table-striped">
    <colgroup>
        <col id="cPageConfigFile" />
        <col id="cPageConfigFileFound" />
        <col id="cPageActions" />
    </colgroup>
    <thead>
        <tr>
            <th id="hPageConfigFile" scope="col">{gt text='Configuration file'}</th>
            <th id="hPageConfigFileFound" scope="col">{gt text='Configuration file found'}</th>
            <th id="hPageActions" scope="col" class="text-right">{gt text='Actions'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach key='filename' item='fileexists' from=$pageconfigs}
        <tr>
            <td headers="hPageConfigFile">{$filename|safetext}</td>
            <td headers="hPageConfigFileFound">{$fileexists|yesno}</td>
            <td headers="hPageActions" class="actions">
                <a class="fa fa-pencil" href="{route name='zikulathememodule_admin_modifypageconfigtemplates' themename=$themename filename=$filename}" title="{gt text='Edit'}"></a>
                <a class="ffa-trash-osh" href="{route name='zikulathememodule_admin_variables' themename=$themename filename=$filename}" title="{gt text='Variables'}"></a>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<p class="alert alert-info">{gt text="Notice: Any configuration files that Zikula cannot find must be created in 'themes/%s/templates/config'." tag1=$themename|safetext}</p>

<h4>{gt text="Create new page configuration assignment"}</h4>

<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_updatepageconfigurationassignment'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagemodule">{gt text="Module"}</label>
                <div class="col-sm-9">
                <select class="form-control" id="theme_pagemodule" name="pagemodule">
                    <option value="">&nbsp;</option>
                    {foreach key='pagevalue' item='pagetext' from=$pagetypes}
                        <option value="{$pagevalue}">{$pagetext}</option>
                    {/foreach}
                    {html_options options=$modules}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagetype">{gt text="Function type"}</label>
                <div class="col-sm-9">
                <input id="theme_pagetype" type="text" class="form-control" name="pagetype" size="30" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagefunc">{gt text="Function name"}</label>
                <div class="col-sm-9">
                <input id="theme_pagefunc" type="text" class="form-control" name="pagefunc" size="30" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagecustomargs">{gt text="Function arguments"}</label>
                <div class="col-sm-9">
                <input id="theme_pagecustomargs" type="text" class="form-control" name="pagecustomargs" size="30" />
                <em class="help-block sub">{gt text="Notice: This is a list of arguments found in the page URL, separated by '/'."}</em>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_filename">{gt text="Configuration file"}</label>
                <div class="col-sm-9">
                <select class="form-control" id="theme_filename" name="filename">
                    {html_options values=$existingconfigs output=$existingconfigs}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_important">{gt text="Important"}</label>
                <div class="col-sm-9">
                <input id="theme_important" type="checkbox" name="pageimportant" value="1" />
                <em class="help-block sub">{gt text="Any match with this assignment will be consider over the following others."}</em>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                    <a class="btn btn-danger" href="{route name='zikulathememodule_theme_view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
            </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}
