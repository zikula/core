<div class="post">
    <a id="arrow-{$info.sid}" class="post-arrow" href="javascript: return false;"></a>
    <div class="calendar">
        <div class="cal-month month-08">{$info.from|dateformat:'%b'}</div>
        <div class="cal-date">{$info.from|dateformat:'%d'}</div>
    </div>

    {if $modvars.ZConfig.shorturls}
    <a class="h2" href="{modurl modname='News' type='user' func='display' sid=$info.sid from=$info.from urltitle=$info.urltitle}">{$info.title|safehtml}<</a>
    {else}
    <a class="h2" href="{modurl modname='News' type='user' func='display' sid=$info.sid}">{$info.title|safehtml}</a>
    {/if}

    <div class="post-author">
        <span class="lead">{gt text="By"}:</span>
        {$info.contributor}
        <br>
        <span class="lead">{gt text="Categories"}:</span>
        {foreach name='categorylinks' from=$preformat.categories item='categorylink'}
            {$categorylink}{if $smarty.foreach.categorylinks.last neq true},&nbsp;{/if}
        {/foreach}
        <br>
    </div>

    <div class="clearer"></div>

</div>
