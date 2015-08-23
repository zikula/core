{adminheader}
{include file="Admin/modifymenu.tpl"}

<h4>{gt text="Edit page configuration assignment"}</h4>

<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_updatepageconfigurationassignment'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <input type="hidden" name="pcname" value="{$pcname|safetext}" />

        {if !isset($modules.$pagemodule) and ($pagemodule neq 'master') and ($pagemodule|strpos:'*' !== 0)}
        <div class="alert alert-warning">{gt text='The module specified on this assignment [%s] seems to not be available on the system.' tag1=$pagemodule|safetext}</div>
        {/if}

        <fieldset>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagemodule">{gt text="Module"}</label>
                <div class="col-sm-9">
                <select class="form-control" id="theme_pagemodule" name="pagemodule">
                    <option value="">&nbsp;</option>
                    {foreach from=$pagetypes key='pagevalue' item='pagetext'}
                    <option value="{$pagevalue}"{if $pagemodule eq $pagevalue} selected="selected"{/if}>{$pagetext}</option>
                    {/foreach}
                    {html_options options=$modules selected=$pagemodule}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagetype">{gt text="Function type"}</label>
                <div class="col-sm-9">
                <input id="theme_pagetype" type="text" class="form-control" name="pagetype" size="30" value="{$pagetype|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagefunc">{gt text="Function name"}</label>
                <div class="col-sm-9">
                <input id="theme_pagefunc" type="text" class="form-control" name="pagefunc" size="30" value="{$pagefunc|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_pagecustomargs">{gt text="Function arguments"}</label>
                <div class="col-sm-9">
                <input id="theme_pagecustomargs" type="text" class="form-control" name="pagecustomargs" size="30" value="{$pagecustomargs|safetext}" />
                <em class="help-block sub">{gt text="Notice: This is a list of arguments found in the page URL, separated by '/'."}</em>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_filename">{gt text="Configuration file"}</label>
                <div class="col-sm-9">
                <select class="form-control" id="theme_filename" name="filename">
                    {html_options values=$existingconfigs output=$existingconfigs selected=$filename}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_important">{gt text="Important"}</label>
                <div class="col-sm-9">
                <input id="theme_important" type="checkbox" name="pageimportant" value="1"{if $pageimportant|default:0} checked="checked"{/if} />
                <em class="help-block sub">{gt text="Any match with this assignment will be consider over the following others."}</em>
            </div>
            <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                    <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                    <a class="btn btn-danger" href="{route name='zikulathememodule_admin_pageconfigurations' themename=$themename}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}