{$menu}
<div id="z-admincontainer" class="z-admin-content">
    <h2>{$category.name|safetext}</h2>
    <div class="z-admincategorydescription">{$category.description|safetext}</div>

        {if !empty($adminlinks)}
        <ul id="modulelist">
            {foreach from=$adminlinks name=adminlink item=adminlink}

            <li data-modid="{$adminlink.id}" class="draggable">
                {* module icon *}
                {if $modvars.ZikulaAdminModule.admingraphic eq 1}
                <a title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">
                    <img src="{$adminlink.adminicon}" title="{$adminlink.menutext|safetext}" alt="{$adminlink.menutext|safetext}" />
                </a>
                {/if}
                
                <div>
                    {* movable icon *}
                    <span title="{gt text="Drag and drop into a new module category"}" class="tooltips icon icon-move modulelist-drag"></span> 
                    
                    {* module title *}
                    <a title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">{$adminlink.menutext|safetext}</a>

                    {assign var="modlinks" value=false}
                    {modapifunc modname=$adminlink.modname type="admin" func="getlinks" assign="modlinks"}
                    {if $modlinks}
                    <span class="dropdown">
                        <a class="dropdown-toggle caret" data-toggle="dropdown" href="#" title="{gt text="Functions"}"></a>
                        <ul class="dropdown-menu" role="menu">
                        {foreach from=$modlinks item="item"}
                            <li><a role="menuitem" href="{$item.url}">{$item.text}</a></li>
                        {/foreach}
                        </ul>
                    </span>   
                        
                    {/if}
                    {* module description *}
                    <p>
                        <span title="{gt text="Sort module"}" class="tooltips icon icon-move modulelist-sort"></span> 
                        {$adminlink.menutexttitle|safetext}
                    </p>
                    
                </div>
            </li>

            {/foreach}
        </ul>
        
        <div class="clearfix"></div>

        {else}
        <p class="bold text-center">{gt text="There are currently no modules in this category."}</p>
        {/if}
    
</div>

<div class="z-admin-coreversion">Zikula {$coredata.version_num}</div>


