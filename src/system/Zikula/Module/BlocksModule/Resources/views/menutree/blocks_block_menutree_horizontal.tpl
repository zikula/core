<div class="menutree_horizontal_container">
    {menutree data=$menutree_content id='menu'|cat:$blockinfo.bid class='menutree_horizontal clearfix' ext=true}
    {if $menutree_editlinks}
    <ul class="menutree_horizontal_controls">
        <li><a class="icon icon-add" href="{modurl modname=Blocks type=admin func=modify bid=$blockinfo.bid addurl=1}#menutree_tabs" title="{gt text='Add the current URL as new link in this block'}"></a></li>
        <li><a class="icon icon-pencil" href="{modurl modname=Blocks type=admin func=modify bid=$blockinfo.bid fromblock=1}" title="{gt text='Edit this block'}"></a></li>
    </ul>
    {/if}
</div>
