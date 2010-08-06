{pageaddvar name="javascript" value="prototype"}
{pageaddvar name="javascript" value="system/Blocks/javascript/usermenutree.js"}
{pageaddvar name="stylesheet" value="system/Blocks/style/menutree/tree.css"}

<div class="usermenutree">
    {usermenutree data=$menutree_content id='usermenutree'|cat:$blockinfo.bid class='usermenutree' nodeprefix='b'|cat:$blockinfo.bid|cat:'n'}
    {if $menutree_editlinks}
    <p class="menutreecontrols">
        <a href="{modurl modname=Blocks type=admin func=modify bid=$blockinfo.bid addurl=1}#menutree_tabs" title="{gt text='Add the current URL as new link in this block'}">{gt text='Add current URL'}</a><br />
        <a href="{modurl modname=Blocks type=admin func=modify bid=$blockinfo.bid fromblock=1}" title="{gt text='Edit this block'}">{gt text='Edit this block'}</a>
    </p>
    {/if}
</div>
