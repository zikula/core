{ajaxheader imageviewer="true" ui=true}
{gt text="Theme switcher" assign=title}
{pagesetvar name=title value=$title}
<h2>{$title}</h2>
{insert name="getstatusmsg"}
<p class="z-informationmsg">
    {gt text="Themes enable you to change the visual presentation of the site when you are logged-in."} {gt text="The current theme is '%s'." tag1=$currenttheme.displayname}
    {if $currenttheme.name neq $defaulttheme.name}
    {modurl modname='Theme' type='user' func='resettodefault' assign='resetdefaulturl'}
    {gt text='Your chosen theme is not the current site default. You can <a href="%1$s">reset</a> your chosen theme to site default of <a href="?theme=%2$s">%3$s</a>.' tag1=$resetdefaulturl|safetext tag2=$defaulttheme.name|safetext tag3=$defaulttheme.displayname|safetext}
    {/if}
</p>
<div style="text-align:center; margin:1em 0;"><img src="{$currentthemepic}" alt="{$currenttheme.displayname}" title="{$currenttheme.description|default:$currenttheme.displayname}" /></div>

<h3>{gt text="Themes list"}</h3>
<div id="themes_list" class="z-clearfix">
    {foreach from=$themes item=theme}
    <dl class="theme_item">
        <dt><strong>{$theme.displayname}</strong></dt>
        <dt>
            <a href="{$theme.largeImage}" title="{$theme.description|default:$theme.displayname}" rel="lightbox[themes]" >
                <img src="{$theme.previewImage}" alt="{$theme.displayname}" title="{$theme.description|default:$theme.displayname}" />
            </a>
        </dt>
        {homepage assign='homepageurl'}
        {if $modvars.ZConfig.shorturls eq 1 && $modvars.ZConfig.shorturlsstripentrypoint neq 1}
        {assign var='themeurl' value="`$homepageurl`/`$theme.name`"}
        {elseif $modvars.ZConfig.shorturls eq 1 && $modvars.ZConfig.shorturlsstripentrypoint eq 1}
        {assign var='themeurl' value="`$homepageurl``$theme.name`"}
        {else}
        {assign var='themeurl' value="`$homepageurl`?theme=`$theme.name`"}
        {/if}
        <dd><a class="z-icon-es-preview" href="{$themeurl|safetext}">{gt text="Preview theme"}</a></dd>
        <dd><a class="z-icon-es-ok" href="?newtheme={$theme.name}">{gt text="Use theme"}</a></dd>
    </dl>
    {/foreach}
</div>

<br />{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}


