{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text="Settings"}
</h3>

<form class="form-horizontal" role="form" action="{route name="zikulasearchmodule_admin_updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="search_itemsperpage">{gt text="Items per page"}</label>
                <div class="col-lg-9">
                    <input id="search_itemsperpage" type="text" class="form-control" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="search_limitsummary">{gt text="Number of characters to display in item summaries"}</label>
                <div class="col-lg-9">
                    <input id="search_limitsummary" type="text" class="form-control" name="limitsummary" size="5" value="{$limitsummary|safetext}" />
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Disable search plug-ins"}</legend>
            {foreach from=$plugins item=plugin}
            {if isset($plugin.title)}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="search_disable{$plugin.title|safetext}">{modgetinfo info=displayname modname=$plugin.title}</label>
                <div class="col-lg-9">
                    <input id="search_disable{$plugin.title|safetext}" type="checkbox" name="disable[{$plugin.title|safetext}]" value="1"
                    {if $plugin.disabled} checked="checked"{/if} />
                </div>
            </div>
            {/if}
            {/foreach}
        </fieldset>
        <fieldset>
            <legend>OpenSearch</legend>
            <div class="alert alert-info">
                {gt text='%1$s OpenSearch %2$s makes it possible for your site\'s users to use your site\'s search function as a search engine.' tag1='<a href="http://en.wikipedia.org/wiki/OpenSearch">' tag2='</a>'}
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="search_opensearch_enabled">{gt text='Enable OpenSearch'}</label>
                <div class="col-lg-9">
                    <input id="search_opensearch_enabled" type="checkbox" name="opensearch_enabled" {if $opensearch_enabled|default:false}checked="checked" {/if}/>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="search_opensearch_adult_content">{gt text='This page contains adult content'}</label>
                <div class="col-lg-9">
                    <input id="search_opensearch_adult_content" type="checkbox" name="opensearch_adult_content" {if $opensearch_adult_content|default:false}checked="checked" {/if}/>
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{route name='zikulasearchmodule_admin_index'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
