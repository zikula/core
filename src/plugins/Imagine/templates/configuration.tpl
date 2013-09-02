{pageaddvar name='javascript' value='plugins/Imagine/javascript/configuration.js'}

{$header}
<div class="z-admin-content-pagetitle">
    {icon type='gears' size='small'}
    <h3>{gt text='Imagine plugin settings'}</h3>
</div>

<form id="imagine-configuration" class="form-horizontal" role="form" action="{modurl modname='ZikulaExtensionsModule' type='adminplugin' func='dispatch' _plugin='Imagine' _action='updateConfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='General settings'}</legend>

            <div class="form-group">
                <label class="col-lg-3 control-label" for="thumb_dir">{gt text='Thumbnails storage directory'}</label>
                <div class="col-lg-9">
                    <input type="text" id="thumb_dir" class="form-control" name="thumb_dir" value="{$vars.thumb_dir|safetext}" />
                    <p class="help-block z-sub">{gt text='This should be directory inside Zikula temp dir.'}</p>
                    <p class="help-block z-sub">{gt text='Current storage full path is:'}<br />{$thumb_full_dir|safetext}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label" for="thumb_auto_cleanup">{gt text='Cleanup automatically'}</label>
                <div class="col-lg-9">
                    <input type="checkbox" id="thumb_auto_cleanup" name="thumb_auto_cleanup"  value="1" {if $vars.thumb_auto_cleanup} checked="checked"{/if} />
                    <p class="help-block z-sub">{gt text='When checked, thumbnail cleanup routine is automatically invoked once a day and unnecessary thumbnails are removed.'}</p>
                </div>
            </div>

            <div class="form-group">  
                <div class="col-lg-offset-3 col-lg-9">
                    <a class="z-action-icon z-icon-es-regenerate" href="{modurl modname='ZikulaExtensionsModule' type='adminplugin' func='dispatch' _plugin='Imagine' _action='cleanup'}" title="{gt text='Clear thumb'}">{gt text='Cleanup thumbnails now'}</a>
                </div>
            </div>
        </fieldset>

        <p class="alert alert-info">{gt text='Presets allow to define ready to use sets of thumbnail options.'}</p>
            
        {foreach item='preset' from=$vars.presets name='presetsLoop'}
        {assign var='index' value=$smarty.foreach.presetsLoop.index}
        <fieldset class="preset {$preset->getName()|safetext} preset-{$index}">
            <legend>{$preset->getName()}</legend>

            <div class="form-group preset-name">
                <label class="col-lg-3 control-label" for="presets-{$index}-name">{gt text='Preset name'} <span class="z-form-mandatory-flag">*</span></label>
                <div class="col-lg-9">
                    <input type="text" id="presets-{$index}-name" class="form-control" name="presets[{$index}][name]" value="{$preset->getName()|safetext}" {if $preset->getName() == 'default'}readonly="readonly" class="z-form-readonly"{/if}/>
                    <p class="help-block z-sub">{gt text='Preset name can contain letters, numbers, underscores, periods, or dashes.'}</p>
                </div>
            </div>

            <div class="form-group preset-width">
                <label class="col-lg-3 control-label" for="presets-{$index}-width">{gt text='Width'}<span class="z-form-mandatory-flag">*</span></label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="text" id="presets-{$index}-width" class="form-control" name="presets[{$index}][width]" size="4" value="{$preset.width|safetext}" />
                        <span class="input-group-addon">{gt text='pixels'}</span>
                    </div>
                </div>
            </div>

            <div class="form-group preset-height">
                <label class="col-lg-3 control-label" for="presets-{$index}-height">{gt text='Height'} <span class="z-form-mandatory-flag">*</span></label>
                <div class="col-lg-9">
                    <div class="input-group">
                        <input type="text" id="presets-{$index}-height" class="form-control" name="presets[{$index}][height]" size="4" value="{$preset.height|safetext}" />
                        <span class="input-group-addon">{gt text='pixels'}</span>
                    </div>
                </div>
            </div>

            <div class="form-group preset-mode">
                <label class="col-lg-3 control-label" for="presets-{$index}-mode">{gt text='Mode'}</label>
                <div class="col-lg-9">
                    <select id="presets-{$index}-mode" class="form-control" name="presets[{$index}][mode]">
                    {foreach item='option' from=$options.mode}
                        {assign var='opt' value=$option|safetext}
                        <option value="{$opt}" label="{$opt}" {if $preset.mode == $option}selected="selected"{/if}>{$opt}</option>
                    {/foreach}
                    </select>
                    <p class="help-block z-sub">
                        {gt text='Thumbnail generation mode.'}<br />
                        {gt text='Inset mode - thumbnails are scale down to not exceed dimensions.'}<br />
                        {gt text='Outset mode - thumbnails are cut out to exactly fit dimmensions.'}
                    </p>
                </div>
            </div>

            <div class="form-group preset-extension">
                <label class="col-lg-3 control-label" for="presets-{$index}-extension">{gt text='Extension'}</label>
                <div class="col-lg-9">
                    <select id="presets-{$index}-extension" class="form-control" name="presets[{$index}][extension]">
                        <option value="" label="{gt text='Same as source image'}" {if !$preset.extension}selected="selected"{/if}>{gt text='Same as source image'}</option>
                        {foreach item='option' from=$options.extension}
                            {assign var='opt' value=$option|safetext}

                            <option value="{$opt}" label="{$opt}" {if $preset.extension == $option}selected="selected"{/if}>{$opt}</option>
                        {/foreach}
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-offset-3 col-lg-9">
                    <a class="copy-preset btn btn-default btn-sm" href="#" title="{gt text='Copy'}"><span class="glyphicon glyphicon-file"></span> {gt text='Copy'}</a>
                    <a class="delete-preset btn btn-default btn-sm{if $preset->getName() == 'default'} hide{/if}" href="#" title="{gt text='Delete'}"><span class="glyphicon glyphicon-trash"></span> {gt text='Delete'}</a>
                </div>
            </div>
        </fieldset>
        {/foreach}
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <a class="add-preset btn btn-default btn-sm" href="#" title="{gt text='Add new preset'}"><span class="glyphicon glyphicon-plus"></span> {gt text='Add new preset'}</a>
            </div>
        </div>


        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button src=button_ok.png set=icons/extrasmall __alt='Save' __title='Save' __text='Save'}
                <a class="btn btn-default" href="{modurl modname='ZikulaExtensionsModule' type='adminplugin' func='dispatch' _plugin='Imagine' _action='configure'}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>

{$footer}
