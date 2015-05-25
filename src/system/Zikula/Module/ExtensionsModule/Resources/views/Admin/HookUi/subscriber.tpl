<div id="hookSubscriberAreas" class="pull-left z-w49">
    <fieldset>
        <legend>{gt text='Attached areas'}</legend>
        {foreach key='category' item='areas' from=$subscriberAreasAndCategories}
            <fieldset class="sareas_category">
                <legend>{$category|safetext}</legend>
                {foreach item='sarea' from=$areas}
                    {assign var='sarea_md5' value=$sarea|md5}
                    <div id="sarea_{$sarea_md5}" class="sarea_wrapper">
                        <input type="hidden" id="sarea_{$sarea_md5}_a" value="{$sarea}" />
                        <input type="hidden" id="sarea_{$sarea_md5}_c" value="{$subscriberAreasToCategories.$sarea}" />
                        <input type="hidden" id="sarea_{$sarea_md5}_i" value="{$sarea_md5}" />
                        <h4>{$subscriberAreasToTitles.$sarea}<span class="sub">({$sarea})</span></h4>
                        <ol id="sarea_{$sarea_md5}_list" class="z-itemlist">
                            {if isset($areasSorting.$category.$sarea)}
                                {foreach item='parea' from=$areasSorting.$category.$sarea}
                                    {assign var='parea_md5' value=$parea|md5}
                                    {assign var='attached_area_identifier' value="`$parea_md5`-`$sarea_md5`"}
                                    <li id="attachedarea_{$attached_area_identifier}" class="clearfix z-sortable {cycle name="attachedareaslist_`$sarea`" values='z-even,z-odd'} list-group-item ui-draggable" style="cursor: move; left: 0px; top: 0px; opacity: 1; position: relative;">
                                        <i class="fa fa-arrows"></i>
                                        <span>
                                            {$areasSortingTitles.$parea} <span class="sub">({$parea})</span>
                                            <a class="detachlink" title="{gt text='Detach'} {$areasSortingTitles.$parea}" href="javascript:void(0)" onclick="unbindProviderAreaFromSubscriberArea('{$sarea_md5}', '{$sarea}', '{$parea_md5}', '{$parea}');"><i class="fa fa-remove"></i></a>
                                        </span>
                                        <input type="hidden" id="attachedarea_{$attached_area_identifier}_a" value="{$parea}" />
                                        <input type="hidden" id="attachedarea_{$attached_area_identifier}_c" value="{$category}" />
                                        <input type="hidden" id="attachedarea_{$attached_area_identifier}_i" value="{$parea_md5}" />
                                    </li>
                                {/foreach}
                            {/if}
                            <li id="sarea_empty_{$sarea_md5}" class="clearfix sarea_empty{if isset($areasSorting.$category.$sarea)} hide{/if}">
                                <span class="z-itemcell">{gt text="There aren't any areas attached here.<br />Drag an area from the right and drop it here to attach it."}</span>
                            </li>
                        </ol>
                    </div>
                {/foreach}
            </fieldset>
        {foreachelse}
            <p class="alert alert-warning">{gt text='There are no subscribers available for %s.' tag1=$currentmodule}</p>
        {/foreach}
    </fieldset>
</div>
<div id="hookProviderAreas" class="pull-right z-w49">
    <fieldset>
        <legend>{gt text='Available areas'}</legend>
        {foreach item='hookprovider' from=$hookproviders}
            {if !empty($hookprovider.areas)}
                <div class="parea_wrapper">
                    <h4>{$hookprovider.displayname}</h4>
                    <div class="panel-content">
                        {foreach key='category' item='areas' from=$hookprovider.areasAndCategories}
                        <fieldset class="pareas_category">
                            <legend>{$category}</legend>
                            {assign var='draglist_identifier' value="`$hookprovider.name`_`$category`"}
                            {assign var='draglist_identifier_md5' value=$draglist_identifier|md5}
                            <ol id="availableareasdraglist_{$draglist_identifier_md5}" class="z-itemlist list-group">
                                {foreach item='parea' from=$areas}
                                    {assign var="parea_md5" value=$parea|md5}
                                    {assign var='available_area_identifier' value="`$parea_md5`-sarea_identifier"}
                                    <li id="availablearea_{$available_area_identifier}" class="{cycle name="availableareaslist_`$draglist_identifier`" values='z-even,z-odd'} z-draggable clearfix list-group-item">
                                        <i class="fa fa-long-arrow-left"></i>
                                        <span class="z-itemcell">
                                            {$hookprovider.areasToTitles.$parea} <span class="sub">({$parea})</span>
                                            <a class="detachlink hide" title="{gt text='Detach'} {$hookprovider.areasToTitles.$parea}" href="javascript:void(0)" onclick="unbindProviderAreaFromSubscriberArea('##id', '##name', '{$parea_md5}', '{$parea}');"><i class="fa fa-remove"></i></a></span>
                                        <input type="hidden" id="availablearea_{$available_area_identifier}_a" value="{$parea}" />
                                        <input type="hidden" id="availablearea_{$available_area_identifier}_c" value="{$hookprovider.areasToCategories.$parea}" />
                                        <input type="hidden" id="availablearea_{$available_area_identifier}_i" value="{$parea_md5}" />
                                    </li>
                                {/foreach}
                            </ol>
                        </fieldset>
                        {/foreach}
                    </div>
                </div>
            {/if}
        {foreachelse}
            <p class="alert alert-warning">{gt text='There are no providers available for %s.' tag1=$currentmodule}</p>
        {/foreach}
    </fieldset>
</div>
