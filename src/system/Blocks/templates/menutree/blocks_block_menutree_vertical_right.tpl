<div class="menutree_vertical_right_container">
    {menutree data=$menutree_content id='menu'|cat:$blockinfo.bid class='menutree_vertical_right' ext=true}
    {if $menutree_editlinks}
    <p class="menutree_vertical_right_controls">
        <a href="{modurl modname=Blocks type=admin func=modify bid=$blockinfo.bid addurl=1}#menutree_tabs" title="{gt text='Add the current URL as new link in this block'}">{gt text='Add current URL'}</a><br />
        <a href="{modurl modname=Blocks type=admin func=modify bid=$blockinfo.bid fromblock=1}" title="{gt text='Edit this block'"}">{gt text='Edit this block'}</a>
    </p>
    {/if}
</div>
