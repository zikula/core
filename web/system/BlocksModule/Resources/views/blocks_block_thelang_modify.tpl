<div class="z-formrow">
    <label for="blocks_thelang_format">{gt text="Form of display" domain='zikula'}</label>
    <select id="blocks_thelang_format" name="format">
        <option value="1" {if $format eq 1} selected="selected"{/if}>{gt text="Flags" domain='zikula'}</option>
        <option value="2" {if $format eq 2} selected="selected"{/if}>{gt text="Dropdown menu" domain='zikula'"}</option>
        <option value="3"  {if $format eq 3} selected="selected"{/if}>{gt text="List" domain='zikula'}</option>
    </select>
</div>
<div class="z-formrow">
    <label for="blocks_thelang_fulltranslation">{gt text="Translate options" domain='zikula'}</label>
    <select id="blocks_thelang_fulltranslation" name="fulltranslation">
        <option value="1" {if $fulltranslation eq 1} selected="selected"{/if}>{gt text="No" domain='zikula'}</option>
        <option value="2" {if $fulltranslation eq 2} selected="selected"{/if}>{gt text="Yes" domain='zikula'"}</option>
    </select>
</div>


