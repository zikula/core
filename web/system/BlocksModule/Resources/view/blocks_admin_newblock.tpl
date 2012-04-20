{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{gt text="Create new block"}</h3>
</div>

<form class="z-form" action="{modurl modname="Blocks" type="admin" func="create"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="New block"}</legend>
            <div class="z-formrow">
                <label for="blocks_title">{gt text="Title"}</label>
                <input id="blocks_title" name="block[title]" value="{$block.title|default:''}" type="text" size="40" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="blocks_description">{gt text="Description"}</label>
                <input id="blocks_description" name="block[description]" value="{$block.description|default:''}" type="text" size="40" maxlength="255" />
            </div>
            <div class="z-formrow">
                <label for="blocks_blockid">{gt text="Block"}</label>
                <select id="blocks_blockid" name="block[blockid]"{if $block.blockid eq 'error'} class="z-form-error"{/if}>
                    <option value="" label="{gt text='Choose one'}">{gt text='Choose one'}</option>
                    {html_options options=$blockids selected=$block.blockid|default:''}
                </select>
            </div>
            <div class="z-formrow">
                <label for="blocks_language">{gt text="Language"} </label>
                {html_select_locales id=blocks_language name="block[language]" installed=true all=true selected=$block.language|default:''}
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Block placement filtering"}</legend>
            <div class="z-formrow">
                <label for="blocks_position">{gt text="Position(s)"}</label>
                <div>
                    <select id="blocks_position" name="block[positions][]" multiple="multiple">
                        {html_options options=$block_positions selected=$block.positions}
                    </select>
                </div>
            </div>
        </fieldset>
        {if $modvars.Blocks.collapseable eq 1}
        <fieldset>
            <legend>{gt text="Collapsibility"}</legend>
            <div class="z-formrow">
                <label for="blocks_collapsable">{gt text="Collapsible"}</label>
                <div id="blocks_collapsable">
                    <label for="blocks_collapsable_yes">{gt text="Yes"}</label>
                    <input id="blocks_collapsable_yes" name="block[collapsable]" type="radio" value="1"{if $block.collapsable|default:0 eq 1} checked="checked"{/if} />
                    <label for="blocks_collapsable_no">{gt text="No"}</label>
                    <input id="blocks_collapsable_no" name="block[collapsable]" type="radio" value="0"{if $block.collapsable|default:0 eq 0} checked="checked"{/if} />
                </div>
            </div>
            <div class="z-formrow">
                <label for="blocks_defaultstate">{gt text="Default state"}</label>
                <div id="blocks_defaultstate">
                    <label for="blocks_defaultstate_expanded">{gt text="Expanded"}</label>
                    <input id="blocks_defaultstate_expanded" name="block[defaultstate]" type="radio" value="1"{if $block.defaultstate|default:1 eq 1} checked="checked"{/if} />
                    <label for="blocks_defaultstate_collapsed">{gt text="Collapsed"}</label>
                    <input id="blocks_defaultstate_collapsed" name="block[defaultstate]" type="radio" value="0"{if $block.defaultstate|default:1 eq 0} checked="checked"{/if} />
                </div>
            </div>
        </fieldset>
        {/if}

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname='Blocks' type='admin' func='view'}" title="{gt text="Cancel"}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}
