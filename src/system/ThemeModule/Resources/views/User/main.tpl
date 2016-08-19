{* @deprecated at Core-2.0 - do not convert to twig *}
{pageaddvar name='javascript' value='web/bootstrap-media-lightbox/bootstrap-media-lightbox.min.js'}
{pageaddvar name='stylesheet' value='web/bootstrap-media-lightbox/bootstrap-media-lightbox.css'}
{gt text='Theme switcher' assign='title'}
{pagesetvar name='title' value=$title}
<h2>{$title}</h2>
{insert name='getstatusmsg'}
<p class="alert alert-info">
    {gt text='Themes enable you to change the visual presentation of the site when you are logged-in.'} {gt text="The current theme is '%s'." tag1=$currenttheme.displayname}
    {if $currenttheme.name ne $defaulttheme.name}
        {route name='zikulathememodule_user_resettodefault' assign='resetdefaulturl'}
        {gt text='Your chosen theme is not the current site default. You can <a href="%1$s">reset</a> your chosen theme to site default of <a href="?theme=%2$s">%3$s</a>.' tag1=$resetdefaulturl|safetext tag2=$defaulttheme.name|safetext tag3=$defaulttheme.displayname|safetext}
    {/if}
</p>
<div class="text-center">
    <img class="img-thumbnail themes-list" src="{$currentthemepic}" alt="{$currenttheme.displayname}" title="{$currenttheme.description|default:$currenttheme.displayname}" />
</div>

<h3>{gt text='Themes list'}</h3>

{foreach item='theme' from=$themes}
<dl class="img-thumbnail themes-list">
    <dt><strong>{$theme.displayname}</strong></dt>
    <dt>
        <a href="{$theme.largeImage}" title="{$theme.description|default:$theme.displayname}" class="lightbox" >
            <img  src="{$theme.previewImage}" alt="{$theme.displayname}" title="{$theme.description|default:$theme.displayname}" />
        </a>
    </dt>
    {homepage assign='homepageurl'}
    {assign var='themeurl' value="`$homepageurl`?theme=`$theme.name`"}
    <dd><a href="{$themeurl|safetext}"><span class="fa fa-eye"></span> {gt text='Preview theme'}</a></dd>
    <dd><a href="?newtheme={$theme.name}"><span class="fa fa-check"></span>{gt text='Use theme'}</a></dd>
</dl>
{/foreach}

<br />{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulathememodule_user_index'}
