{if $format eq 1}
    <div style="text-align:center">
        <div>{gt text='Preferred language' domain='zikula'}</div>
        {section name='lang' loop=$urls}
        {if $urls[lang].flag neq ''}
        <a href="{$urls[lang].url|safetext}">
            <img src="{$urls[lang].flag|safetext}" title="{$urls[lang].name}" alt="{$urls[lang].name|safetext}" />
        </a>
        {/if}
        {/section}
    </div>

{elseif $format eq 2}
    {assign var='formid' value='thelang_'|cat:$bid}
    {pageaddvarblock}
        <script type="text/javascript">
        <!--//
        function blocks_block_thelang_changeaction() {
            document.getElementById('{{$formid}}').action = document.getElementById('languageblock_changelang').value;
            document.getElementById('{{$formid}}').submit();
        }
        //-->
        </script>
    {/pageaddvarblock}
    <form id="{$formid}" method="post" action="">
    <div style="text-align:left">
        <div><label for="languageblock_changelang">{gt text='Preferred language' domain='zikula'}</label></div>
        <select id="languageblock_changelang" onchange="blocks_block_thelang_changeaction()">
        {section name='lang' loop=$urls}
        {if $urls[lang].code eq $currentlanguage}
            <option value="{$urls[lang].url|safetext}" selected="selected">{$urls[lang].name|safetext}</option>
        {else}
            <option value="{$urls[lang].url|safetext}">{$urls[lang].name|safetext}</option>
        {/if}
        {/section}
        </select>
    </div>
    </form>

{else}
    <div>{gt text='Preferred language' domain='zikula'}</div>
    <ul>
    {section name='lang' loop=$urls}
        <li><a href="{$urls[lang].url|safetext}">{$urls[lang].name|safetext}</a></li>
    {/section}
    </ul>
{/if}

