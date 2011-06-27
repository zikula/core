{adminheader}
{include file="theme_admin_modifymenu.tpl"}

<h4>{gt text="Edit page configuration assignment"}</h4>

<form class="z-form" action="{modurl modname="Theme" type="admin" func="updatepageconfigurationassignment"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <input type="hidden" name="pcname" value="{$pcname|safetext}" />

        {if !isset($modules.$pagemodule) and ($pagemodule neq 'master') and ($pagemodule|strpos:'*' !== 0)}
        <div class="z-warningmsg">{gt text='The module specified on this assignment [%s] seems to not be available on the system.' tag1=$pagemodule|safetext}</div>
        {/if}

        <fieldset>
            <div class="z-formrow">
                <label for="theme_pagemodule">{gt text="Module"}</label>
                <select id="theme_pagemodule" name="pagemodule">
                    <option value="">&nbsp;</option>
                    {foreach from=$pagetypes key='pagevalue' item='pagetext'}
                    <option value="{$pagevalue}"{if $pagemodule eq $pagevalue} selected="selected"{/if}>{$pagetext}</option>
                    {/foreach}
                    {html_options options=$modules selected=$pagemodule}
                </select>
            </div>
            <div class="z-formrow">
                <label for="theme_pagetype">{gt text="Function type"}</label>
                <input id="theme_pagetype" type="text" name="pagetype" size="30" value="{$pagetype|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="theme_pagefunc">{gt text="Function name"}</label>
                <input id="theme_pagefunc" type="text" name="pagefunc" size="30" value="{$pagefunc|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="theme_pagecustomargs">{gt text="Function arguments"}</label>
                <input id="theme_pagecustomargs" type="text" name="pagecustomargs" size="30" value="{$pagecustomargs|safetext}" />
                <em class="z-formnote z-sub">{gt text="Notice: This is a list of arguments found in the page URL, separated by '/'."}</em>
            </div>
            <div class="z-formrow">
                <label for="theme_filename">{gt text="Configuration file"}</label>
                <select id="theme_filename" name="filename">
                    {html_options values=$existingconfigs output=$existingconfigs selected=$filename}
                </select>
            </div>
            <div class="z-formrow">
                <label for="theme_important">{gt text="Important"}</label>
                <input id="theme_important" type="checkbox" name="pageimportant" value="1"{if $pageimportant|default:0} checked="checked"{/if} />
                <em class="z-formnote z-sub">{gt text="Any match with this assignment will be consider over the following others."}</em>
            </div>
            <div class="z-buttons z-formbuttons">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>
{adminfooter}