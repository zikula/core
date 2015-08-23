<ul>
{section name='line' loop=$content}
    <li><a href="{$content[line].link|safetext}">{$content[line].description|safetext} ({$content[line].number|safetext})</a></li>
{/section}
</ul>