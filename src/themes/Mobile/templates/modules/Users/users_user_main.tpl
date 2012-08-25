{gt text='My account' assign='templatetitle'}
{include file='users_user_menu.tpl'}

<ul data-role="listview" data-inset="true">
{foreach item='accountLink' from=$accountLinks}
    <li style="height:54px;padding:4px">
        <a href="{$accountLink.url|safetext}">
            {if $modvars.Users.accountdisplaygraphics eq 1}
                {if isset($accountLink.set) && !empty($accountLink.set)}
                    {assign var="iconset" value=$accountLink.set}
                {else}
                    {assign var="iconset" value=null}
                {/if}
                {img src=$accountLink.icon modname=$accountLink.module set=$iconset width=48 height=48 }
            {/if}
            
    	    <h3>{$accountLink.title|safetext}</h3>
        </a>
    </li>					
{/foreach}
</ul>
