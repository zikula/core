<div>
    <ul>
        {section name='admincategories' loop=$admincategories}
        {if $admincategories[admincategories].url ne ''}
            {assign var='adminmodules' value=$admincategories[admincategories].modules}
            <li><a href="{$admincategories[admincategories].url|safetext}">{$admincategories[admincategories].title|safetext}</a></li>
            <li style="list-style: none;">
                <ul>
                    {section name='adminmodules' loop=$adminmodules}
                        <li><a href="{$adminmodules[adminmodules].menutexturl|safetext}">{$adminmodules[adminmodules].menutexttitle|safetext}</a></li>
                    {/section}
                </ul>
            </li>
        {/if}
        {/section}
    </ul>
</div>