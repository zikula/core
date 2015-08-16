{$menu}
<div id="z-admincontainer" class="z-admin-content">
    <h2>{$category.name|safetext}</h2>
    <div class="z-admincategorydescription">{$category.description|safetext}</div>

    {if !empty($adminlinks)}
        <ul id="modulelist">
            {foreach from=$adminlinks name='adminlink' item='adminlink'}
            <li data-modid="{$adminlink.id}" class="draggable">
                {* module icon *}
                {if $modvars.ZikulaAdminModule.admingraphic eq 1}
                <a title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}"><img src="{$adminlink.adminicon}" title="{$adminlink.menutext|safetext}" alt="{$adminlink.menutext|safetext}" /></a>
                {/if}
                <div>
                    {* movable icon *}
                    <span title="{gt text='Drag and drop into a new module category'}" class="tooltips fa fa-arrows admintabs-lock"></span>

                    {* module title *}
                    <a title="{$adminlink.menutexttitle|safetext}" href="{$adminlink.menutexturl|safetext}">{$adminlink.menutext|safetext}</a>

                    {assign var="modlinks" value=false}
                    {modulelinks modname=$adminlink.modname type='admin' assign='modlinks' returnAsArray=true}
                    {if $modlinks}
                        <div class="dropdown" style="display: inline">
                            <a class="caret" data-toggle="dropdown" href="#" title="{gt text='Functions'}"></a>
                            <ul class="dropdown-menu" role="menu">
                                {foreach item='item' from=$modlinks}
                                    <li><a href="{$item.url|safetext}">{$item.text}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                    {* module description *}
                    <p>{$adminlink.menutexttitle|safetext}</p>
                </div>
            </li>
            {/foreach}
        </ul>
        <div class="clearfix"></div>
    {else}
        <p class="bold text-center">{gt text='There are currently no modules in this category.'}</p>
    {/if}
    {adminfooter}
