{include file='theme_admin_menu.tpl'}
<div class="z-admincontainer">
    {include file="theme_admin_modifymenu.tpl"}
    {gt text="Page configuration assignments" assign=templatetitle}
    <div class="z-adminpageicon">{img modname=core src=xedit.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Name"}</th>
                <th>{gt text="Configuration file"}</th>
                <th class="z-right">{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$pageconfigurations item=filesection key=name}
            <tr class="{cycle name=pageconfigurations values=z-odd,z-even}">
                <td>{$name|safetext}</td>
                <td>{$filesection.file|safetext}</td>
                <td class="z-right">
                    <a href="{modurl modname=Theme type=admin func=modifypageconfigurationassignment themename=$themename pcname=$name|urlencode}">{img modname=core src=xedit.gif set=icons/extrasmall __alt="Edit" __title="Edit"}</a>
                    <a href="{modurl modname=Theme type=admin func=deletepageconfigurationassignment themename=$themename pcname=$name|urlencode}">{img modname=core src=trashcan_empty.gif set=icons/extrasmall __alt="Delete" __title="Delete"}</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    <h3>{gt text="Page configurations"}</h3>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Configuration file"}</th>
                <th>{gt text="Configuration file found"}</th>
                <th class="z-right">{gt text="Actions"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$pageconfigs item=fileexists key=filename}
            <tr class="{cycle name=pageconfigs values=z-odd,z-even}">
                <td>{$filename|safetext}</td>
                <td>{$fileexists|yesno}</td>
                <td class="z-right"><a href="{modurl modname=Theme type=admin func=modifypageconfigtemplates themename=$themename filename=$filename}">{img modname=core src=xedit.gif set=icons/extrasmall __alt="Edit" __title="Edit"}</a></td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    <p class="z-informationmsg">{gt text="Notice: Any configuration files that Zikula cannot find must be created in 'themes/%s/templates/config'." tag1=$themename}</p>
    <h3>{gt text="Create new page configuration assignment"}</h3>
    <form class="z-form" action="{modurl modname="Theme" type="admin" func="updatepageconfigurationassignment"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module=Theme}" />
            <input type="hidden" name="themename" value="{$themename|safetext}" />
            <fieldset>
                <div class="z-formrow">
                    <label for="theme_pagemodule">{gt text="Module"}</label>
                    <select id="theme_pagemodule" name="pagemodule">
                        <option value="">&nbsp;</option>
                        <option value="*home">{gt text="Home page"}</option>
                        <option value="*admin">{gt text="Admin panel pages"}</option>
                        <option value="master">{gt text="Master"}</option>
                        {html_options options=$modules}
                    </select>
                </div>
                <div class="z-formrow">
                    <label for="theme_pagetype">{gt text="Function type"}</label>
                    <input id="theme_pagetype" type="text" name="pagetype" size="30" />
                </div>
                <div class="z-formrow">
                    <label for="theme_pagefunc">{gt text="Function name"}</label>
                    <input id="theme_pagefunc" type="text" name="pagefunc" size="30" />
                </div>
                <div class="z-formrow">
                    <label for="theme_pagecustomargs">{gt text="Function arguments"}</label>
                    <input id="theme_pagecustomargs" type="text" name="pagecustomargs" size="30" />
                    <em class="z-formnote z-sub">{gt text="Notice: This is a list of arguments found in the page URL, separated by '/'."}</em>
                </div>
                <div class="z-formrow">
                    <label for="theme_filename">{gt text="Configuration file"}</label>
                    <input id="theme_filename" type="text" name="filename" size="30" />
                </div>
                <div class="z-buttons z-formbuttons">
                    {button src=button_ok.gif set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                    <a href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.gif set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
