<div class="navcontainer">
    <ul class="navlist">
        {foreach item='accountlink' from=$accountlinks}
        <li><a href="{$accountlink.url|safetext}">{$accountlink.title|safetext}</a></li>
        {/foreach}
    </ul>
</div>
