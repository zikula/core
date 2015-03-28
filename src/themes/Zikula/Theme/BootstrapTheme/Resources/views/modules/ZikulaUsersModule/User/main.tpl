{gt text='My account' assign='templatetitle'}
{include file='User/menu.tpl'}

<ul class="list-group">
{foreach item='accountLink' from=$accountLinks}
    <li class="list-group-item">
        <a href="{$accountLink.url|safetext}">
            <div class="media">
                <div class="media-left">
                {if $modvars.ZikulaUsersModule.accountdisplaygraphics eq 1}
                    {if isset($accountLink.set) && !empty($accountLink.set)}
                        {assign var="iconset" value=$accountLink.set}
                    {else}
                        {assign var="iconset" value=null}
                    {/if}
                    {img src=$accountLink.icon modname=$accountLink.module set=$iconset class="media-object"}
                {/if}
                </div>
                <div class="media-body">
                    <h4><strong>{$accountLink.title|safetext}</strong></h4>
                </div>
            </div>
        </a>
    </li>
{/foreach}
</ul>
