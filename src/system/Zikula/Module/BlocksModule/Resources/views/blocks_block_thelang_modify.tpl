<div class="form-group">
    <label class="col-lg-3 control-label" for="blocks_thelang_format">{gt text="Form of display" domain='zikula'}</label>
    <div class="col-lg-9">
    <select id="blocks_thelang_format" name="format">
        <option value="1" {if $format eq 1} selected="selected"{/if}>{gt text="Flags" domain='zikula'}</option>
        <option value="2" {if $format eq 2} selected="selected"{/if}>{gt text="Dropdown menu" domain='zikula'"}</option>
        <option value="3"  {if $format eq 3} selected="selected"{/if}>{gt text="List" domain='zikula'}</option>
    </select>
</div>
</div>
<div class="form-group">
    <label class="col-lg-3 control-label" for="blocks_thelang_fulltranslation">{gt text="Translate options" domain='zikula'}</label>
    <div class="col-lg-9">
    <select id="blocks_thelang_fulltranslation" name="fulltranslation">
        <option value="1" {if $fulltranslation eq 1} selected="selected"{/if}>{gt text="No" domain='zikula'}</option>
        <option value="2" {if $fulltranslation eq 2} selected="selected"{/if}>{gt text="Yes" domain='zikula'"}</option>
    </select>
</div>


