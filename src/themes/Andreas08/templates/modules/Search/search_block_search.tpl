<form id="theme_search" class="z-floatright" method="post" action="{modurl modname='Search' type='user' func='search'}">
    <div>
        <input class="theme_search_input" id="block_search_q" type="text" name="q" size="20" maxlength="255" value="Search keywords" onfocus="if(this.value=='Search keywords')this.value=''" />
        {if $vars.displaySearchBtn eq 1}
        <input class="z-button z-bt-small theme_search_button" type="submit" value="{gt text="Go" domain='zikula'}" />
        {/if}
        {if isset($vars.active) && is_array($vars.active)}
        {foreach item="dummy" key="actives" from=$vars.active}
        <input type="hidden" name="active[{$actives|safetext}]" value="1" />
        {/foreach}
        {/if}

        {searchvartofieldnames data=$modvars.Search prefix="modvar" assign="modvariables"}
        {foreach item="value" key="name" from=$modvariables}
        <input type="hidden" name="{$name|safetext}" value="{$value|safetext}" />
        {/foreach}
    </div>
</form>