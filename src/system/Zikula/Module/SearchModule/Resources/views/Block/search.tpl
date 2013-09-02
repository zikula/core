<form class="z-form z-linear" method="post" action="{modurl modname="Search" type="user" func="search"}">
    <div>
        <fieldset>
            <legend>{gt text="Search keyword"}</legend>
            <div class="form-group">
                <input id="block_search_q" type="search" name="q" size="20" maxlength="255" results="10" autosave="Search" />
            </div>
            {if $vars.displaySearchBtn eq 1}
            <div class="z-buttons">
                <input class="z-bt-ok z-bt-small" type="submit" value="{gt text="Search now" domain='zikula'}" />
            </div>
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
        </fieldset>
    </div>
</form>
