{pageaddvar name='javascript' value='jquery,jquery-ui'}
{* TODO if you enable the following stylesheet it breaks the layout; thus, the styles in hooks.css should be updated accordingly *}
{*pageaddvar name='stylesheet' value='web/jquery-ui/themes/smoothness/jquery-ui.css'*}
{pageaddvar name='javascript' value='system/Zikula/Module/ExtensionsModule/Resources/public/js/hookui.js'}
{pageaddvar name='stylesheet' value='system/Zikula/Module/ExtensionsModule/Resources/public/css/hooks.css'}
{assign var='showBothPanels' value=false}
{if $isSubscriber and $isProvider and !empty($providerAreas) and $total_available_subscriber_areas gt 0}
    {assign var='showBothPanels' value=true}
{/if}
{if $isSubscriber}
    {pageaddvarblock}
    <script type="text/javascript">
        var subscriberAreas = new Array();

        {{if $isSubscriber && !empty($subscriberAreas)}}
            {{foreach item='sarea' from=$subscriberAreas}}
                {{assign var='sarea_md5' value=$sarea|md5}}
                subscriberAreas.push('sarea_{{$sarea_md5}}');
            {{/foreach}}
        {{/if}}

        ( function($) {
            $(document).ready(function() {
            {{if $showBothPanels}}
                $('#hookTabs a').click(function (e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
            {{/if}}
            initHookSubscriber();
            });
        })(jQuery);
    </script>
    {/pageaddvarblock}
{/if}

{admincategorymenu}
<div class="z-admin-content clearfix">
    {modgetinfo modname=$currentmodule info='displayname' assign='displayName'}
    {modgetimage modname=$currentmodule assign='image'}
    {moduleheader modname=$currentmodule type='admin' title=$displayName putimage=true image=$image}
    <h3>
        <span class="fa fa-paperclip"></span>
        {gt text='Hooks'}
    </h3>

{if $showBothPanels}
    <div role="tabpanel">
        <ul id="hookTabs" class="nav nav-tabs">
            <li role="presentation" class="active"><a href="#subscriberTab">{gt text='Subscription'}</a></li>
            <li role="presentation"><a href="#providerTab">{gt text='Provision'}</a></li>
        </ul>
    </div>
{/if}

{if $showBothPanels}
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="subscriberTab">
            <br />
            <div id="hookSubscriber" class="z-form clearfix">
                {include file='Admin/HookUi/subscriber.tpl'}
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="providerTab">
            <br />
            <div id="hookProvider" class="form-horizontal" role="form">
                {include file='Admin/HookUi/provider.tpl'}
            </div>
        </div>
    </div>
{else}
    {if $isSubscriber}
        <div id="hookSubscriber" class="z-form clearfix">
            {include file='Admin/HookUi/subscriber.tpl'}
        </div>
    {/if}

    {if $isProvider and !empty($providerAreas) and $total_available_subscriber_areas gt 0}
        <div id="hookProvider" class="form-horizontal" role="form">
            {include file='Admin/HookUi/provider.tpl'}
        </div>
    {/if}

    {if $total_available_subscriber_areas eq 0 && !$isSubscriber}
        <p class="alert alert-warning">{gt text='There are no subscribers available for %s.' tag1=$currentmodule}</p>
    {/if}
{/if}

{adminfooter}
