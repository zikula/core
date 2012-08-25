{$menu}
<div id="z-admincontainer" class="z-admin-content">
    <h2>{$category.name|safetext}</h2>
    <div class="z-admincategorydescription">{$category.description|safetext}</div>

    {if !empty($adminlinks)}

        <ul data-role="listview" data-inset="true">
        {foreach from=$adminlinks name=adminlink item=adminlink}
            
            <li style="height:54px;padding:4px">
             <a title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">

                    {if $modvars.Admin.admingraphic eq 1}
                        <img class="z-adminfloat" src="{$adminlink.adminicon}" title="{$adminlink.menutext|safetext}" alt="{$adminlink.menutext|safetext}" />
                    {/if}
                    <h3 style="margin-top:-7px">{$adminlink.menutext|safetext}</h3>
                    <p>{$adminlink.menutexttitle|safetext}</p>
                    </a>

            </li>

        {/foreach}

    {else}
    <p class="z-bold z-center">{gt text="There are currently no modules in this category."}</p>
    {/if}
</div>

<div class="z-admin-coreversion z-right">Zikula {$coredata.version_num}</div>

