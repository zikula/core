{gt text='Search keywords' domain='zikula' assign='searchtxt'}
<form id="theme_search" class="pull-right" method="post" action="{modurl modname='ZikulaSearchModule' type='user' func='search'}">
    <div>
        <input id="block_search_q" type="search" name="q" size="20" maxlength="255" class="theme_search_input" onfocus="if(this.value=='{$searchtxt}')this.value=''" value="{$searchtxt|safetext}" />
        {if $vars.displaySearchBtn eq 1}
        <input class="theme_search_button" type="submit" value="{gt text="Go" domain='zikula'}" />
        {/if}
        <div style="display: none;">
            {foreach from=$plugin_options key='plugin' item='plugin_option'}
            {$plugin_option}
            {/foreach}
        </div>
        {searchvartofieldnames data=$modvars.ZikulaSearchModule prefix="modvar" assign="modvariables"}
        {foreach item="value" key="name" from=$modvariables}
        <input type="hidden" name="{$name|safetext}" value="{$value|safetext}" />
        {/foreach}
    </div>
</form>