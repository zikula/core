{pageaddvar name="javascript" value="javascript/ajax/prototype.js,javascript/ajax/scriptaculous.js"}
{pageaddvar name="javascript" value="javascript/livepipe/livepipe.js,javascript/livepipe/contextmenu.js"}
{ajaxheader modname=Admin filename=admin_admin_ajax.js}

<script type="text/javascript">
    /* <![CDATA[ */
    var lblclickToEdit = "{{gt text='Right-click down arrows to edit tab name'}}";
    var lblEdit = "{{gt text='Edit category'}}";
    var lblDelete = "{{gt text='Delete category'}}";
    var lblMakeDefault = "{{gt text='Make default category'}}";
    var lblSaving = "{{gt text='Saving'}}";
    /* ]]> */
</script>

{include file='admin_admin_securityanalyzer.tpl'}
{include file='admin_admin_developernotices.tpl'}
{nocache}{include file='admin_admin_updatechecker.tpl'}{/nocache}
{insert name="getstatusmsg"}

<div class="admintabs-container" id="admintabs-container">
    <ul id="admintabs" class="z-clearfix">
        {foreach from=$menuoptions name=menuoption item=menuoption}
        <li id="admintab_{$menuoption.cid}" class="admintab {if $currentcat eq $menuoption.cid} active{/if}" style="z-index:0;">
            <a id="C{$menuoption.cid}" href="{$menuoption.url|safetext}" title="{$menuoption.description|safetext}">{$menuoption.title|safetext}</a>
            <span id="catcontext{$menuoption.cid}" class="z-admindrop">&nbsp;</span>

            <script type="text/javascript">
            /* <![CDATA[ */
                var context_catcontext{{$menuoption.cid}} = new Control.ContextMenu('catcontext{{$menuoption.cid}}',{
                    leftClick: true,
                    animation: false
                });

                {{foreach from=$menuoption.items item=item}}
                    context_catcontext{{$menuoption.cid}}.addItem({
                        label: '{{$item.menutext|safetext}}',
                        callback: function(){window.location = '{{$item.menutexturl}}';}
                    });
                {{/foreach}}

            /* ]]> */
            </script>

        </li>
        {/foreach}
        <li id="addcat">
            <a id="addcatlink" href="{modurl modname=Admin type=admin func=new}" title="{gt text='New module category'}" onclick='return Admin.Category.New(this);'>&nbsp;</a>
        </li>
    </ul>
    
    {helplink}
    {include file='admin_admin_ajaxAddCategory.tpl'}
</div>

<div class="z-hide" id="admintabs-none"></div>
