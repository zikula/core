<div id="z-adminiconlist" class="z-clearfix">
    {assign var="count" value="0"}
    {foreach from=$adminlinks item=adminlink}
    {math equation="$count+1" assign="count"}
    <div id="A{$adminlink.id}" class="z-adminiconcontainer z-center draggable" style="width:{math equation='100/x' x=$modvars.modulesperrow format='%.0f'}%;">
        {if $modvars.admingraphic eq 1}
        <a class="z-adminicon" title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">
            <img src="{$adminlink.adminicon}" title="{$adminlink.menutexttitle|safetext}" alt="{$adminlink.menutext|safetext}" />
        </a>
        <br />
        {/if}
        <span class="z-adminlinkheader">
            {img modname='Admin' src='mouse.png' set='icons/extrasmall' __alt='Drag and drop into a new module category' __title='Drag and drop into a new module category' id="dragicon`$adminlink.id`" class='z-dragicon'}
            <a class="z-adminmodtitle" title="{$adminlink.menutexttitle}" href="{$adminlink.menutexturl|safetext}">{$adminlink.menutext|safetext}</a>
        </span>

        <script type="text/javascript">
            new Draggable("A{{$adminlink.id}}", {
                revert: true,
                handle: "dragicon{{$adminlink.id}}",
                zindex: 2200 // must be higher than the active minitab and all other admin icons
            });
        </script>
    </div>

    {if $count eq $modvars.modulesperrow}
    {assign var="count" value="0"}
    <br class="z-clearer" />
    {/if}

    {/foreach}
</div>
