{adminheader}
{include file="theme_admin_modifymenu.tpl"}

<h4>{gt text="Colour palettes"}</h4>

{pageaddvar name="javascript" value="javascript/picky_color/picky_color.js"}
{pageaddvar name="stylesheet" value="javascript/picky_color/picky_color.css"}
<form class="z-form" id="theme_modify_palette" action="{modurl modname="Theme" type="admin" func="updatepalettes"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />

        {if $palettes}
        {foreach from=$palettes item=palette key=palettename}
        <fieldset>
            <legend>{$palettename|safetext}</legend>
            <div class="z-formrow">
                <label><strong>{gt text="Name"}</strong></label>
                <span><strong>{gt text="Value"}</strong></span>
            </div>
            {foreach from=$palette item=color key=name}
            <div class="z-formrow">
                <label for="palettes[{$palettename}][{$name}]">{$name|safetext}</label>
                <input id="palettes[{$palettename}][{$name}]" class="colorpicker" name="palettes[{$palettename}][{$name}]" type="text" value="{$color|safetext}" maxlength="7" size="7" />
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
        <div class="z-formrow">
            <label for="theme_palettename"><strong>{gt text="Name"}</strong></label>
            <input id="theme_palettename" name="palettename" size="30" />
        </div>
        <div class="z-formrow">
            <label for="bgcolorField">bgcolor</label>
            <input id="bgcolorField" name="bgcolor" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color1Field">color1</label>
            <input id="color1Field" name="color1" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color2Field">color2</label>
            <input id="color2Field" name="color2" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color3Field">color3</label>
            <input id="color3Field" name="color3" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color4Field">color4</label>
            <input id="color4Field" name="color4" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color5Field">color5</label>
            <input id="color5Field" name="color5" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color6Field">color6</label>
            <input id="color6Field" name="color6" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color7Field">color7</label>
            <input id="color7Field" name="color7" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="color8Field">color8</label>
            <input id="color8Field" name="color8" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="sepcolorField">sepcolor</label>
            <input id="sepcolorField" name="sepcolor" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="text1Field">text1</label>
            <input id="text1Field" name="text1" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="text2Field">text2</label>
            <input id="text2Field" name="text2" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="linkField">link</label>
            <input id="linkField" name="link" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="vlinkField">vlink</label>
            <input id="vlinkField" name="vlink" class="colorpicker" type="text" size="7" />
        </div>
        <div class="z-formrow">
            <label for="hoverField">hover</label>
            <input id="hoverField" name="hover" class="colorpicker" type="text" size="7" />
        </div>
    </fieldset>
    <div class="z-buttons z-formbuttons">
        {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
        <a href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img class=theme_colorpicker_image modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
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