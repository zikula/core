<div class="z-formrow">
    <label for="themeswitcher_format">{gt text="Output format" domain='zikula'}</label>
    <select id="themeswitcher_format" name="format">
        <option value="1"{if $format eq 1} selected="selected"{/if}>{gt text="Dropdown list with preview images" domain='zikula'}</option>
        <option value="2"{if $format eq 2} selected="selected"{/if}>{gt text="Simple list" domain='zikula'}</option>
    </select>
</div>