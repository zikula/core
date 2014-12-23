{* Purpose of this template: Display a popup selector for Forms and Content integration *}
{assign var='baseID' value='route'}
<div id="{$baseID}Preview" style="float: right; width: 300px; border: 1px dotted #a3a3a3; padding: .2em .5em; margin-right: 1em">
    <p><strong>{gt text='Route information'}</strong></p>
    {img id='ajax_indicator' modname='core' set='ajax' src='indicator_circle.gif' alt='' class='hidden'}
    <div id="{$baseID}PreviewContainer">&nbsp;</div>
</div>
<br />
<br />
{assign var='leftSide' value=' style="float: left; width: 10em"'}
{assign var='rightSide' value=' style="float: left"'}
{assign var='break' value=' style="clear: left"'}
<p>
    <label for="{$baseID}Id"{$leftSide}>{gt text='Route'}:</label>
    <select id="{$baseID}Id" name="id"{$rightSide}>
        {foreach item='route' from=$items}
            <option value="{$route.id}"{if $selectedId eq $route.id} selected="selected"{/if}>{$route->getTitleFromDisplayPattern()}</option>
        {foreachelse}
            <option value="0">{gt text='No entries found.'}</option>
        {/foreach}
    </select>
    <br{$break} />
</p>
<p>
    <label for="{$baseID}Sort"{$leftSide}>{gt text='Sort by'}:</label>
    <select id="{$baseID}Sort" name="sort"{$rightSide}>
        <option value="id"{if $sort eq 'id'} selected="selected"{/if}>{gt text='Id'}</option>
        <option value="workflowState"{if $sort eq 'workflowState'} selected="selected"{/if}>{gt text='Workflow state'}</option>
        <option value="name"{if $sort eq 'name'} selected="selected"{/if}>{gt text='Name'}</option>
        <option value="bundle"{if $sort eq 'bundle'} selected="selected"{/if}>{gt text='Bundle'}</option>
        <option value="controller"{if $sort eq 'controller'} selected="selected"{/if}>{gt text='Controller'}</option>
        <option value="action"{if $sort eq 'action'} selected="selected"{/if}>{gt text='Action'}</option>
        <option value="path"{if $sort eq 'path'} selected="selected"{/if}>{gt text='Path'}</option>
        <option value="host"{if $sort eq 'host'} selected="selected"{/if}>{gt text='Host'}</option>
        <option value="schemes"{if $sort eq 'schemes'} selected="selected"{/if}>{gt text='Schemes'}</option>
        <option value="methods"{if $sort eq 'methods'} selected="selected"{/if}>{gt text='Methods'}</option>
        <option value="defaults"{if $sort eq 'defaults'} selected="selected"{/if}>{gt text='Defaults'}</option>
        <option value="requirements"{if $sort eq 'requirements'} selected="selected"{/if}>{gt text='Requirements'}</option>
        <option value="options"{if $sort eq 'options'} selected="selected"{/if}>{gt text='Options'}</option>
        <option value="condition"{if $sort eq 'condition'} selected="selected"{/if}>{gt text='Condition'}</option>
        <option value="description"{if $sort eq 'description'} selected="selected"{/if}>{gt text='Description'}</option>
        <option value="userRoute"{if $sort eq 'userRoute'} selected="selected"{/if}>{gt text='User route'}</option>
        <option value="sort"{if $sort eq 'sort'} selected="selected"{/if}>{gt text='Sort'}</option>
        <option value="group"{if $sort eq 'group'} selected="selected"{/if}>{gt text='Group'}</option>
        <option value="createdDate"{if $sort eq 'createdDate'} selected="selected"{/if}>{gt text='Creation date'}</option>
        <option value="createdUserId"{if $sort eq 'createdUserId'} selected="selected"{/if}>{gt text='Creator'}</option>
        <option value="updatedDate"{if $sort eq 'updatedDate'} selected="selected"{/if}>{gt text='Update date'}</option>
    </select>
    <select id="{$baseID}SortDir" name="sortdir" class="form-control">
        <option value="asc"{if $sortdir eq 'asc'} selected="selected"{/if}>{gt text='ascending'}</option>
        <option value="desc"{if $sortdir eq 'desc'} selected="selected"{/if}>{gt text='descending'}</option>
    </select>
    <br{$break} />
</p>
<p>
    <label for="{$baseID}SearchTerm"{$leftSide}>{gt text='Search for'}:</label>
    <input type="text" id="{$baseID}SearchTerm" name="q" class="form-control"{$rightSide} />
    <input type="button" id="zikulaRoutesModuleSearchGo" name="gosearch" value="{gt text='Filter'}" class="btn btn-default" />
    <br{$break} />
</p>
<br />
<br />

<script type="text/javascript">
/* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            zikulaRoutesModule.itemSelector.onLoad('{{$baseID}}', {{$selectedId|default:0}});
        });
    })(jQuery);
/* ]]> */
</script>
