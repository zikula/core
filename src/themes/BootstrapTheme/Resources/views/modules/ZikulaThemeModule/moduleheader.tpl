{if $setpagetitle && $title}
    {pagesetvar name='title' value=$title}
{/if}
{if $insertstatusmsg}
    {insert name='getstatusmsg'}
{/if}

{if $userthemename|strtolower|strpos:"printer" !== false}
    {if isset($image) && $image}<img src="{$image|safetext}" alt="{$title|safetext}" />{/if}
    {if $title}<h2>{$title|safetext}</h2>{/if}

{elseif $type != 'admin'}
    <div class="{if $type == 'user'}z-modtitle{else}z-admin-content-modtitle{/if}">
        {if $image}<img src="{$image|safetext}" alt="{$title|safetext}" class="z-floatleft" />{/if}
        {if $title}<h2>{$title|safetext}</h2>{/if}
    </div>

    <div class="navbar navbar-inverse navbar-noborder">
        <div class="navbar-inner navbar-bgimages">
            <div class="container-fluid">
                <div class="navbar-header">
                    <button data-target="#userheader-div" data-toggle="collapse" type="button" class="navbar-toggle">
                        <span class="sr-only">{gt text='Navigation'}</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    {if isset($image) && $image}
                        <a class="navbar-brand" href="{if $titlelink}{$titlelink}{else}#{/if}" title="{$title}"><img class="img-responsive" alt="" src="{$image}" /></a>
                    {/if}
                </div>
                <div class="collapse navbar-collapse" id="userheader-div">
                    {modulelinks modname=$modname type=$type menuclass='nav navbar-nav'}
                </div>
            </div>
        </div>
    </div>

{else}
    {if $menufirst}{modulelinks modname=$modname type=$type}{/if}
    <div class="{if $type == 'user'}z-modtitle{else}z-admin-content-modtitle{/if}">
        {if $image}<img src="{$image|safetext}" alt="{$title|safetext}" class="z-floatleft" />{/if}
        {if $title}<h2>{$title|safetext}</h2>{/if}
    </div>
    {if !$menufirst}{modulelinks modname=$modname type=$type}{/if}
{/if}
