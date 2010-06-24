{gt text='My account' assign='templatetitle'}
{include file='users_user_menu.tpl'}

{foreach item='accountlink' from=$accountlinks}
<div class="z-accountlink" style="width:{math equation='100/x' x=$pncore.Users.accountitemsperrow format='%.0d'}%;">
    {if $pncore.Users.accountdisplaygraphics eq 1}
    <a href="{$accountlink.url|safetext}">{img src=$accountlink.icon modname=$accountlink.module set=$accountlink.set|default:null}</a>
    <br />
    {/if}
    <a href="{$accountlink.url|safetext}">{$accountlink.title|safetext}</a>
</div>
{/foreach}
<br style="clear: left" />
