{include file='theme_admin_menu.tpl'}
<div class="z-admincontainer">
    {include file="theme_admin_modifymenu.tpl"}
    {gt text="Edit page configuration assignment" assign=templatetitle}
    <div class="z-adminpageicon">{img modname=core src=xedit.png set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <form class="z-form" action="{modurl modname="Theme" type="admin" func="updatepageconfigurationassignment"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module=Theme}" />
            <input type="hidden" name="themename" value="{$themename|safetext}" />
            <fieldset>
                <div class="z-formrow">
                    <label for="theme_pagemodule">{gt text="Module"}</label>
                    <select id="theme_pagemodule" name="pagemodule">
                        <option value="">&nbsp;</option>
                        <option value="*home" {if $pagemodule eq '*home'} selected="selected"{/if}>{gt text="Home page"}</option>
                        <option value="*admin" {if $pagemodule eq '*admin'} selected="selected"{/if}>{gt text="Admin panel pages"}</option>
                        <option value="master" {if $pagemodule eq 'master'} selected="selected"{/if}>{gt text="Master"}</option>
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
                    <input id="theme_filename" type="text" name="filename" size="30" value="{$filename|safetext}" />
                </div>
                <div class="z-buttons z-formbuttons">
                    {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save"}
                    <a href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>