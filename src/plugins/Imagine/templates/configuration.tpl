{pageaddvar name='javascript' value='plugins/Imagine/javascript/configuration.js'}

{$header}
<div class="z-admin-content-pagetitle">
    {icon type='gears' size='small'}
    <h3>{gt text='Imagine plugin settings'}</h3>
</div>

<form id="imagine-configuration" class="z-form" action="{modurl modname='Extensions' type='adminplugin' func='dispatch' _plugin='Imagine' _action='updateConfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='General settings'}</legend>

            <div class="z-formrow">
                <label for="thumb_dir">{gt text='Thumbnails storage directory'}</label>
                <input type="text" id="thumb_dir" name="thumb_dir" value="{$vars.thumb_dir|safetext}" />
                <p class="z-formnote z-sub">{gt text='This should be directory inside Zikula temp dir.'}</p>
                <p class="z-formnote z-sub">{gt text='Current storage full path is:'}<br />{$thumb_full_dir|safetext}</p>
            </div>

            <div class="z-formrow">
                <label for="thumb_auto_cleanup">{gt text='Cleanup automatically'}</label>
                <input type="checkbox" id="thumb_auto_cleanup" name="thumb_auto_cleanup"  value="1" {if $vars.thumb_auto_cleanup} checked="checked"{/if} />
                <p class="z-formnote z-sub">{gt text='When checked, thumbnail cleanup routine is automatically invoked once a day and unnecessary thumbnails are removed.'}</p>
            </div>

            <div class="z-formbuttons">
                <a class="z-action-icon z-icon-es-regenerate" href="{modurl modname='Extensions' type='adminplugin' func='dispatch' _plugin='Imagine' _action='cleanup'}" title="{gt text='Clear thumb'}">{gt text='Cleanup thumbnails now'}</a>
            </div>
        </fieldset>

        <fieldset class="presets">
            <legend>{gt text='Presets'}</legend>
            <p class="z-informationmsg">{gt text='Presets allow to define ready to use sets of thumbnail options.'}</p>

            {foreach item='preset' from=$vars.presets name='presetsLoop'}
                {assign var='index' value=$smarty.foreach.presetsLoop.index}
                <fieldset class="preset {$preset->getName()|safetext} preset-{$index}">
                    <legend>{$preset->getName()}</legend>

                    <div class="z-formrow preset-name">
                        <label for="presets-{$index}-name">{gt text='Preset name'} <span class="z-form-mandatory-flag">*</span></label>
                        <input type="text" id="presets-{$index}-name" name="presets[{$index}][name]" value="{$preset->getName()|safetext}" {if $preset->getName() == 'default'}readonly="readonly" class="z-form-readonly"{/if}/>
                        <p class="z-formnote z-sub">{gt text='Preset name can contain letters, numbers, underscores, periods, or dashes.'}</p>
                    </div>

                    <div class="z-formrow preset-width">
                        <label for="presets-{$index}-width">{gt text='Width'} <span class="z-form-mandatory-flag">*</span></label>
                        <div>
                            <input type="text" id="presets-{$index}-width" name="presets[{$index}][width]" size="4" value="{$preset.width|safetext}" /> {gt text='pixels'}
                        </div>
                    </div>

                    <div class="z-formrow preset-height">
                        <label for="presets-{$index}-height">{gt text='Height'} <span class="z-form-mandatory-flag">*</span></label>
                        <div>
                            <input type="text" id="presets-{$index}-height" name="presets[{$index}][height]" size="4" value="{$preset.height|safetext}" /> {gt text='pixels'}
                        </div>
                    </div>

                    <div class="z-formrow preset-mode">
                        <label for="presets-{$index}-mode">{gt text='Mode'}</label>
                        <select id="presets-{$index}-mode" name="presets[{$index}][mode]">
                        {foreach item='option' from=$options.mode}
                            {assign var='opt' value=$option|safetext}
                            <option value="{$opt}" label="{$opt}" {if $preset.mode == $option}selected="selected"{/if}>{$opt}</option>
                        {/foreach}
                        </select>
                        <p class="z-formnote z-sub">
                            {gt text='Thumbnail generation mode.'}<br />
                            {gt text='Inset mode - thumbnails are scale down to not exceed dimensions.'}<br />
                            {gt text='Outset mode - thumbnails are cut out to exactly fit dimmensions.'}
                        </p>
                    </div>

                    <div class="z-formrow preset-extension">
                        <label for="presets-{$index}-extension">{gt text='Extension'}</label>
                        <select id="presets-{$index}-extension" name="presets[{$index}][extension]">
                            <option value="" label="{gt text='Same as source image'}" {if !$preset.extension}selected="selected"{/if}>{gt text='Same as source image'}</option>
                            {foreach item='option' from=$options.extension}
                                {assign var='opt' value=$option|safetext}

                                <option value="{$opt}" label="{$opt}" {if $preset.extension == $option}selected="selected"{/if}>{$opt}</option>
                            {/foreach}
                        </select>
                    </div>

                    <div class="z-formbuttons">
                        <a class="z-action-icon z-icon-es-copy copy-preset" href="#" title="{gt text='Copy'}">{gt text='Copy'}</a>
                        <a class="z-action-icon z-icon-es-delete delete-preset{if $preset->getName() == 'default'} z-hide{/if}" href="#" title="{gt text='Delete'}">{gt text='Delete'}</a>
                    </div>
                </fieldset>
            {/foreach}
            <div class="z-formbuttons">
                <a class="z-action-icon z-icon-es-add add-preset" href="#" title="{gt text='Add new preset'}">{gt text='Add new preset'}</a>
            </div>
        </fieldset>

        <div class="z-buttons z-formbuttons">
        {button src=button_ok.png set=icons/extrasmall __alt='Save' __title='Save' __text='Save'}
            <a href="{modurl modname='Extensions' type='adminplugin' func='dispatch' _plugin='Imagine' _action='configure'}" title="{gt text='Cancel'}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>

{$footer}
