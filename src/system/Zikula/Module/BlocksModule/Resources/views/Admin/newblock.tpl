{adminheader}
<h3>
    <span class="fa fa-plus"></span>
    {gt text='Create new block'}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulablocksmodule_admin_create'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='New block'}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_title">{gt text='Title'}</label>
                <div class="col-lg-9">
                    <input id="blocks_title" name="block[title]" value="{$block.title|default:''}" type="text" class="form-control" size="40" maxlength="255" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_description">{gt text='Description'}</label>
                <div class="col-lg-9">
                    <input id="blocks_description" name="block[description]" value="{$block.description|default:''}" type="text" class="form-control" size="40" maxlength="255" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_blockid">{gt text='Block'}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="blocks_blockid" name="block[blockid]"{if $block.blockid eq 'error'} class="form-error"{/if}>
                        <option value="" label="{gt text='Choose one'}">{gt text='Choose one'}</option>
                        {html_options options=$blockids selected=$block.blockid|default:''}
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_language">{gt text='Language'} </label>
                <div class="col-lg-9">
                    {html_select_locales id='blocks_language' class='form-control' name='block[language]' installed=true all=true selected=$block.language|default:''}
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text='Block placement filtering'}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_position">{gt text='Position(s)'}</label>
                <div class="col-lg-9">
                    <div>
                        {assign var='selectsize' value=$block_positions|@count}{if $selectsize gt 20}{assign var='selectsize' value=20}{/if}{if $selectsize lt 4}{assign var='selectsize' value=4}{/if}
                        <select class="form-control" id="blocks_position" name="block[positions][]" multiple="multiple" size="{$selectsize}">
                            {html_options options=$block_positions selected=$block.positions|default:0}
                        </select>
                    </div>
                </div>
            </div>
        </fieldset>
        {if $modvars.ZikulaBlocksModule.collapseable eq 1}
            <fieldset>
                <legend>{gt text='Collapsibility'}</legend>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="blocks_collapsable">{gt text='Collapsible'}</label>
                    <div class="col-lg-9">
                        <div id="blocks_collapsable">
                            <label for="blocks_collapsable_yes">{gt text='Yes'}</label>
                            <input id="blocks_collapsable_yes" name="block[collapsable]" type="radio" value="1"{if $block.collapsable|default:0 eq 1} checked="checked"{/if} />
                            <label for="blocks_collapsable_no">{gt text='No'}</label>
                            <input id="blocks_collapsable_no" name="block[collapsable]" type="radio" value="0"{if $block.collapsable|default:0 eq 0} checked="checked"{/if} />
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="blocks_defaultstate">{gt text='Default state'}</label>
                    <div class="col-lg-9">
                        <div id="blocks_defaultstate">
                            <label for="blocks_defaultstate_expanded">{gt text='Expanded'}</label>
                            <input id="blocks_defaultstate_expanded" name="block[defaultstate]" type="radio" value="1"{if $block.defaultstate|default:1 eq 1} checked="checked"{/if} />
                            <label for="blocks_defaultstate_collapsed">{gt text='Collapsed'}</label>
                            <input id="blocks_defaultstate_collapsed" name="block[defaultstate]" type="radio" value="0"{if $block.defaultstate|default:1 eq 0} checked="checked"{/if} />
                        </div>
                    </div>
                </div>
            </fieldset>
        {/if}

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
                <a class="btn btn-danger" href="{route name='zikulablocksmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
