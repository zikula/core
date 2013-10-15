{$menu}
<div id="z-admincontainer" class="z-admin-content">
    <h2>{$category.name|safetext}</h2>
    <div class="z-admincategorydescription">{$category.description|safetext}</div>

    {assign var='popups' value=false}
    {if !empty($adminlinks)}
        <ul data-role="listview" data-inset="true">
        {foreach from=$adminlinks name=adminlink item=adminlink}
            {assign var="modlinks" value=false}
            {modapifunc modname=$adminlink.modname type="admin" func="getlinks" assign="modlinks"}

            <li style="height:54px;padding:4px" {if $modlinks}data-icon="gear"{/if}>
                <a title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">
                    {if $modvars.ZikulaAdminModule.admingraphic eq 1}
                        <img src="{$adminlink.adminicon}" title="{$adminlink.menutext|safetext}" alt="{$adminlink.menutext|safetext}" />
                    {/if}
                    <h3 style="margin-top:-7px">{$adminlink.menutext|safetext}</h3>
                    <p>{$adminlink.menutexttitle|safetext}</p>

                    {if $modlinks}
                        <a href="#zikulaadminmodule-{$adminlink.modname}-popup" data-rel="popup" data-position-to="window" data-transition="pop">{gt text='Module links'}</a>
                        {capture assign='popups'}
                            {$popups}
                            <div data-role="popup" id="zikulaadminmodule-{$adminlink.modname}-popup">
                                <ul data-role="listview">
                                    <li data-role="list-divider">{$adminlink.menutext}</li>
                                    {foreach from=$modlinks item="item"}
                                        <li><a href="{$item.url|safehtml}">{$item.text}</a></li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/capture}
                    {/if}
                </a>
            </li>
        {/foreach}
        </ul>
    {else}
        <p class="bold text-center">{gt text="There are currently no modules in this category."}</p>
    {/if}
</div>

<div class="z-admin-coreversion text-right">Zikula {$coredata.version_num}</div>

{$popups}