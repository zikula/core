<div data-role="controlgroup" data-type="horizontal">
    {foreach from=$menuoptions name='menuoption' item='menuoption'}
        <a data-theme="b" id="C{$menuoption.cid}" href="{$menuoption.url|safetext}" title="{$menuoption.description|safetext}" data-role="button">{$menuoption.title|safetext}</a>
    {/foreach}
</div>

<div class="hide" id="admintabs-none"></div>