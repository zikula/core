{admincategorymenu}
<div class="z-adminbox">
    <h1>{$currentmodule}</h1>
    {modulelinks modname=$currentmodule type='admin'}
</div>

<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname='core' src='exec.png' set='icons/large' __alt='Module Services'}</div>
    <h2>{gt text='Module Services'}</h2>

    <p class="z-informationmsg">{gt text='Module Services are functions provided by the core or other modules for this module.'}</p>

    {if count($sublinks) > 0}
    <ul style='list-style: none'>
        {foreach from=$sublinks item='sublink'}
        <li><a href='{$sublink.url|safetext}' class='z-icon-es-exec'>{$sublink.text|safetext}</a></li>
        {/foreach}
    </ul>
    {else}
    <p>{gt text="There aren't any modules services available."}</p>
    {/if}
</div>