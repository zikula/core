{$menu}
<div id="z-admincontainer" class="z-admin-content">
    <h2>{$category.name|safetext}</h2>
    <div class="z-admincategorydescription">{$category.description|safetext}</div>

    {if !empty($adminlinks)}

        <div id="z-adminiconlist">
            {assign var="moduleid" value="0"}
            {foreach from=$adminlinks name=adminlink item=adminlink}
            {math equation="$moduleid+1" assign="moduleid"}

            {if $smarty.foreach.adminlink.first}<div class="z-adminiconrow z-clearfix" id="modules">{/if}
                <div id="module_{$adminlink.id}" class="z-adminiconcontainer draggable" style="width:{math equation='100/x' x=$modvars.Admin.modulesperrow format='%.0f'}%;z-index:{math equation="2200-$moduleid"};">
                    {if $modvars.Admin.admingraphic eq 1}
                    <a class="z-adminicon z-adminfloat" title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">
                        <img class="z-adminfloat" src="{$adminlink.adminicon}" title="{$adminlink.menutext|safetext}" alt="{$adminlink.menutext|safetext}" />
                    </a>
                    {/if}
                    <div class="z-adminlinkheader">
                        {img modname='Admin' src='mouse.png' __alt='Drag and drop into a new module category' __title='Drag and drop into a new module category' id="dragicon`$adminlink.id`" class='z-dragicon'}
                        <a class="z-adminmodtitle" title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">{$adminlink.menutext|safetext}</a>

                        {assign var="modlinks" value=false}
                        {modapifunc modname=$adminlink.modname type="admin" func="getlinks" assign="modlinks"}
                        {if $modlinks}
                        <span class="z-pointericon module-context" title="Functions">&nbsp;</span>
                        {/if}
                        <input type="hidden" name="modlinks-{$adminlink.id}" class="modlinks" id="modlinks-{$adminlink.id}" value="{$modlinks|@json_encode|escape}" />

                    </div>

                    {math equation="170-x*30" x=$modvars.Admin.modulesperrow format="%.0f" assign=trunLen}
                    <div class="z-menutexttitle">{$adminlink.menutexttitle|safetext|truncate:$trunLen:"&hellip;":false}</div>

                </div>
        {if $smarty.foreach.adminlink.last}</div>{/if}

        {/foreach}
        </div>

    {else}
    <p class="z-bold z-center">{gt text="There are currently no modules in this category."}</p>
    {/if}
</div>

<div class="z-admin-coreversion z-right">Zikula {$coredata.version_num}</div>