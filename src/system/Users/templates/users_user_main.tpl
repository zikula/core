{gt text='My account' assign='templatetitle'}
{include file='users_user_menu.tpl'}

{foreach item='accountlink' from=$accountlinks}
<div class="z-accountlink" style="width:{math equation='100/x' x=$zcore.Users.accountitemsperrow format='%.0d'}%;">
    {if $zcore.Users.accountdisplaygraphics eq 1}
        {if isset($accountlink.set) && !empty($accountlink.set)}
            {assign var="iconset" value=$accountlink.set}
	{else}
            {assign var="iconset" value=null}
	{/if}
        <a href="{$accountlink.url|safetext}">{img src=$accountlink.icon modname=$accountlink.module set=$iconset}</a>
        <br />
    {/if}
    <a href="{$accountlink.url|safetext}">{$accountlink.title|safetext}</a>
</div>
{/foreach}
<br style="clear: left" />
