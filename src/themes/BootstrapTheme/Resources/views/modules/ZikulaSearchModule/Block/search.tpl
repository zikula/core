<form class="z-form z-linear" method="post" action="{modurl modname="Search" type="user" func="search"}">
    <div>
        <div class="input-group">
            <input id="block_search_q" name="q" type="text" class="form-control">
              <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
              </span>
        </div><!-- /input-group -->
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
