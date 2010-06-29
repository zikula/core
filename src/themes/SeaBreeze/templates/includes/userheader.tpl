<div id="topmenu" class="sb-sub z-clearfix">
    <span class="sbleft">{userwelcome|ucwords}</span>
    <span class="sbright">&nbsp;</span>
</div>
<div id="header" class="z-clearfix">
    <h1 class="title"><a href="{homepage}">{sitename}</a></h1>
    <div id="navi" class="z-clearer">
        <ul id="nav">
            <li class="page_item">
                <a href="{homepage}">{gt text="Home"}</a>
            </li>
            {if $loggedin eq true}
            <li class="page_item">
                <a href="{modurl modname=Users}">{gt text='My account'}</a>
            </li>
            {/if}
            {modavailable modname="Search" assign="modavail"}
            {if $modavail}
            <li class="page_item">
                <a href="{modurl modname=Search}">{gt text="Search"}</a>
            </li>
            {/if}
            {modavailable modname="Dizkus" assign="modavail"}
            {if $modavail}
            <li class="page_item">
                <a href="{modurl modname=Dizkus}">{gt text="Forums"}</a>
            </li>
            {/if}
            {modavailable modname="Downloads" assign="modavail"}
            {if $modavail}
            <li class="page_item">
                <a href="{modurl modname=Downloads}">{gt text="Downloads"}</a>
            </li>
            {/if}
            {modavailable modname="News" assign="modavail"}
            {if $modavail}
            <li class="page_item">
                <a href="{modurl modname=News func=new}">{gt text="Submit article"}</a>
            </li>
            {/if}
            {modavailable modname="Reviews" assign="modavail"}
            {if $modavail}
            <li class="page_item">
                <a href="{modurl modname=Reviews}">{gt text="Reviews"}</a>
            </li>
            {/if}
            {modavailable modname="FAQ" assign="modavail"}
            {if $modavail}
            <li class="page_item">
                <a href="{modurl modname=FAQ}">{gt text="FAQ"}</a>
            </li>
            {/if}
        </ul>
    </div>
</div>
