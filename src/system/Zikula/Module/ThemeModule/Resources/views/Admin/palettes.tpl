{adminheader}
{include file="Admin/modifymenu.tpl"}

<h4>{gt text="Colour palettes"}</h4>

{pageaddvar name="javascript" value="javascript/picky_color/picky_color.js"}
{pageaddvar name="stylesheet" value="javascript/picky_color/picky_color.css"}
<form class="form-horizontal" role="form" id="theme_modify_palette" action="{modurl modname="Theme" type="admin" func="updatepalettes"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />

        {if $palettes}
        {foreach from=$palettes item=palette key=palettename}
        <fieldset>
            <legend>{$palettename|safetext}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label"><strong>{gt text="Name"}</strong></label>
                <div class="col-lg-9">
                <span><strong>{gt text="Value"}</strong></span>
            </div>
            {foreach from=$palette item=color key=name}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="palettes[{$palettename}][{$name}]">{$name|safetext}</label>
                <div class="col-lg-9">
                <input id="palettes[{$palettename}][{$name}]" class="colorpicker" name="palettes[{$palettename}][{$name}]" type="text" class="form-control" value="{$color|safetext}" maxlength="7" size="7" />
            </div>
            <script type="text/javascript" charset="utf-8">
                /* <![CDATA[ */

            var {{$palettename}}{{$name}}Picky = new PickyColor({
                field: 'palettes[{{$palettename}}][{{$name}}]',
                color: '{{$color|safetext}}',
                colorWell: 'palettes[{{$palettename}}][{{$name}}]',
                closeText: "{{gt text='Close'}}",
            })

            /* ]]> */
        </script>
        {/foreach}
    </fieldset>
    {/foreach}
    {/if}

    <fieldset>
        <legend>{gt text="Create new palette"}</legend>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="theme_palettename"><strong>{gt text="Name"}</strong></label>
            <div class="col-lg-9">
            <input id="theme_palettename" name="palettename" size="30" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="bgcolorField">bgcolor</label>
            <div class="col-lg-9">
            <input id="bgcolorField" name="bgcolor" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color1Field">color1</label>
            <div class="col-lg-9">
            <input id="color1Field" name="color1" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color2Field">color2</label>
            <div class="col-lg-9">
            <input id="color2Field" name="color2" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color3Field">color3</label>
            <div class="col-lg-9">
            <input id="color3Field" name="color3" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color4Field">color4</label>
            <div class="col-lg-9">
            <input id="color4Field" name="color4" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color5Field">color5</label>
            <div class="col-lg-9">
            <input id="color5Field" name="color5" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color6Field">color6</label>
            <div class="col-lg-9">
            <input id="color6Field" name="color6" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color7Field">color7</label>
            <div class="col-lg-9">
            <input id="color7Field" name="color7" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="color8Field">color8</label>
            <div class="col-lg-9">
            <input id="color8Field" name="color8" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="sepcolorField">sepcolor</label>
            <div class="col-lg-9">
            <input id="sepcolorField" name="sepcolor" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="text1Field">text1</label>
            <div class="col-lg-9">
            <input id="text1Field" name="text1" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="text2Field">text2</label>
            <div class="col-lg-9">
            <input id="text2Field" name="text2" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="linkField">link</label>
            <div class="col-lg-9">
            <input id="linkField" name="link" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="vlinkField">vlink</label>
            <div class="col-lg-9">
            <input id="vlinkField" name="vlink" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="hoverField">hover</label>
            <div class="col-lg-9">
            <input id="hoverField" name="hover" class="colorpicker" type="text" class="form-control" size="7" />
        </div>
    </div>
    </fieldset>
    <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a class="btn btn-default" href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img class=theme_colorpicker_image modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
        </div>
</div>
</form>
{adminfooter}

<script type="text/javascript" charset="utf-8">
    /* <![CDATA[ */
    var lblClose = "{{gt text='Close'}}";

    var bgcolorPicky = new PickyColor({
        field: 'bgcolorField',
        colorWell: 'bgcolorField',
        closeText: lblClose,
    })

    var color1Picky = new PickyColor({
        field: 'color1Field',
        colorWell: 'color1Field',
        closeText: lblClose,
    })

    var color2Picky = new PickyColor({
        field: 'color2Field',
        colorWell: 'color2Field',
        closeText: lblClose,
    })

    var color3Picky = new PickyColor({
        field: 'color3Field',
        colorWell: 'color3Field',
        closeText: lblClose,
    })

    var color4Picky = new PickyColor({
        field: 'color4Field',
        colorWell: 'color4Field',
        closeText: lblClose,
    })

    var color5Picky = new PickyColor({
        field: 'color5Field',
        colorWell: 'color5Field',
        closeText: lblClose,
    })

    var color6Picky = new PickyColor({
        field: 'color6Field',
        colorWell: 'color6Field',
        closeText: lblClose,
    })

    var color7Picky = new PickyColor({
        field: 'color7Field',
        colorWell: 'color7Field',
        closeText: lblClose,
    })

    var color8Picky = new PickyColor({
        field: 'color8Field',
        colorWell: 'color8Field',
        closeText: lblClose,
    })

    var sepcolorPicky = new PickyColor({
        field: 'sepcolorField',
        colorWell: 'sepcolorField',
        closeText: lblClose,
    })

    var text1Picky = new PickyColor({
        field: 'text1Field',
        colorWell: 'text1Field',
        closeText: lblClose,
    })

    var text2Picky = new PickyColor({
        field: 'text2Field',
        colorWell: 'text2Field',
        closeText: lblClose,
    })

    var linkPicky = new PickyColor({
        field: 'linkField',
        colorWell: 'linkField',
        closeText: lblClose,
    })

    var vlinkPicky = new PickyColor({
        field: 'vlinkField',
        colorWell: 'vlinkField',
        closeText: lblClose,
    })

    var hoverPicky = new PickyColor({
        field: 'hoverField',
        colorWell: 'hoverField',
        closeText: lblClose,
    })

    /* ]]> */
</script>