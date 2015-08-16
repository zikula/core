{admincategorymenu}
<div class="z-admin-content clearfix">
    {modgetinfo modname=$currentmodule info='displayname' assign='displayName'}
    {modgetimage modname=$currentmodule assign='image'}
    {moduleheader modname=$currentmodule type='admin' title=$displayName putimage=true image=$image}<h3>
    <h3>
        <span class="fa fa-paperclip"></span>
        {gt text='Module Services'}
    </h3>

<p class="alert alert-info">{gt text='Module Services are functions provided by the core or other modules for this module.'}</p>

{if count($sublinks) gt 0}
<ul class="list-unstyled">
    {foreach item='sublink' from=$sublinks}
    <li><a href="{$sublink.url|safetext}" class="fa fa-cog">{$sublink.text|safetext}</a></li>
    {/foreach}
</ul>
{else}
    <p>{gt text="There aren't any modules services available."}</p>
{/if}
{adminfooter}
