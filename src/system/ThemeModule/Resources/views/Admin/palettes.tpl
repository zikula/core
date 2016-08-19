{adminheader}
{include file='Admin/modifymenu.tpl'}

<h4>{gt text='Colour palettes'}</h4>

{pageaddvar name="javascript" value="web/jquery-minicolors/jquery.minicolors.min.js"}
{pageaddvar name="stylesheet" value="web/jquery-minicolors/jquery.minicolors.css"}

<form class="form-horizontal" role="form" id="theme_modify_palette" action="{route name='zikulathememodule_admin_updatepalettes'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
    </div>

    {if $palettes}
        {foreach key='palettename' item='palette' from=$palettes}
            <fieldset>
                <legend>{$palettename|safetext}</legend>
                {foreach from=$palette item=color key=name}
                    <div class="form-group">
                        <label class="col-sm-3 control-label" for="palettes[{$palettename}][{$name}]">{$name|safetext}</label>
                        <div class="col-sm-9">
                            <input id="palettes[{$palettename}][{$name}]" class="form-control minicolors-input" name="palettes[{$palettename}][{$name}]" type="text" value="{$color|safetext}" maxlength="7" size="7" style="width:140px" />
                        </div>
                    </div>
                {/foreach}
            </fieldset>
        {/foreach}
    {/if}

    <fieldset>
        <legend>{gt text='Create new palette'}</legend>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="theme_palettename"><strong>{gt text="Name"}</strong></label>
            <div class="col-xs-9">
                <input id="theme_palettename" name="palettename" class="form-control" size="30" />
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="bgcolorField">bgcolor</label>
            <div class="col-xs-9">
                <input id="bgcolorField" name="bgcolor" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color1Field">color1</label>
            <div class="col-xs-9">
                <input id="color1Field" name="color1" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color2Field">color2</label>
            <div class="col-xs-9">
                <input id="color2Field" name="color2" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color3Field">color3</label>
            <div class="col-xs-9">
                <input id="color3Field" name="color3" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color4Field">color4</label>
            <div class="col-xs-9">
                <input id="color4Field" name="color4" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color5Field">color5</label>
            <div class="col-xs-9">
                <input id="color5Field" name="color5" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color6Field">color6</label>
            <div class="col-xs-9">
                <input id="color6Field" name="color6" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color7Field">color7</label>
            <div class="col-xs-9">
                <input id="color7Field" name="color7" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="color8Field">color8</label>
            <div class="col-xs-9">
                <input id="color8Field" name="color8" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="sepcolorField">sepcolor</label>
            <div class="col-xs-9">
                <input id="sepcolorField" name="sepcolor" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="text1Field">text1</label>
            <div class="col-xs-9">
                <input id="text1Field" name="text1" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="text2Field">text2</label>
            <div class="col-xs-9">
                <input id="text2Field" name="text2" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="linkField">link</label>
            <div class="col-xs-9">
                <input id="linkField" name="link" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="vlinkField">vlink</label>
            <div class="col-xs-9">
                <input id="vlinkField" name="vlink" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-3 control-label" for="hoverField">hover</label>
            <div class="col-xs-9">
                <input id="hoverField" name="hover" class="form-control minicolors-input" type="text" size="7" style="width:140px"/>
            </div>
        </div>
    </fieldset>

    <div class="form-group">
        <div class="col-xs-offset-3 col-xs-9">
            <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
            <a class="btn btn-danger" href="{route name='zikulathememodule_admin_pageconfigurations' themename=$themename}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}

<script type="text/javascript" charset="utf-8">
    jQuery('.minicolors-input').minicolors({theme: 'bootstrap'});
</script>
