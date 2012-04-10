<form id="theme_search" method="post" action="{modurl modname="Search" type="user" func="search"}">
    <div>
        <input class="theme_search_input" id="block_search_q" type="text" name="q" size="20" maxlength="255" />
        {if $vars.displaySearchBtn eq 1}
        <input class="theme_search_button" type="submit" value="{gt text="Search" domain='zikula'}" />
        {/if}
        <div style="display: none;">
            {foreach from=$plugin_options key='plugin' item='plugin_option'}
            {$plugin_option}
            {/foreach}
        </div>
        {searchvartofieldnames data=$modvars.Search prefix="modvar" assign="modvariables"}
        {foreach item="value" key="name" from=$modvariables}
        <input type="hidden" name="{$name|safetext}" value="{$value|safetext}" />
        {/foreach}
    </div>
</form>

