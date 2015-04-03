<div class="form-group">
    <label class="col-sm-3 control-label" for="blocks_finclude_filename">{gt text='File name (including relative path from Zikula root directory)' domain='zikula'}</label>
    <div class="col-sm-9">
        <input id="blocks_finclude_filename" class="form-control" type="text" name="filo" size="30" maxlength="255" value="{$filo|safetext}" />
    </div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label" for="blocks_finclude_filetype">{gt text='File type' domain='zikula'}</label>
    <div class="col-sm-9">
        <select class="form-control" name="typo">
            <option value="0"{if $typo eq 0} selected="selected"{/if}>{gt text='HTML' domain='zikula'}</option>
            <option value="1"{if $typo eq 1} selected="selected"{/if}>{gt text='Text' domain='zikula'}</option>
            <option value="2"{if $typo eq 2} selected="selected"{/if}>{gt text='PHP' domain='zikula'}</option>
        </select>
    </div>
</div>
