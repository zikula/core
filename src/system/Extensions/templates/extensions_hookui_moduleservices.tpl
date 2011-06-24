{admincategorymenu}
<div class="z-adminbox">
    <div class="z-admin-moduleheader">
        <h2>{$currentmodule}</h2>
    </div>
    {modulelinks modname=$currentmodule type='admin'}
</div>

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="hook" size="small"}</div>
    <h3>{gt text='Module Services'}</h3>

    <p class="z-informationmsg">{gt text='Module Services are functions provided by the core or other modules for this module.'}</p>

    {if count($sublinks) > 0}
    <ul style='list-style: none'>
        {foreach from=$sublinks item='sublink'}
        <li><a href='{$sublink.url|safetext}' class='z-icon-es-gears'>{$sublink.text|safetext}</a></li>
        {/foreach}
    </ul>
    {else}
    <p>{gt text="There aren't any modules services available."}</p>
    {/if}
</div>
