<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Value"}</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr class="{cycle values="z-odd,z-even"}">
            <td><input type="text" name="attribute_name[]" id="new_attribute_name" value="" /></td>
            <td><input type="text" name="attribute_value[]" id="new_attribute_value" value="" size="50" /></td>
            <td><input type="image" id="category_attributes_add" title="{gt text="Add"}" src="images/icons/extrasmall/edit_add.png"/></td>
        </tr>
        {foreach from=$attributes item=value key=name}
        <tr class="{cycle values="z-odd,z-even"}">
            <td><input type="text" name="attribute_name[]" value="{$name}" /></td>
            <td><input type="text" name="attribute_value[]" value="{$value}" size="50" /></td>
            <td><input type="image" class="category_attributes_remove" title="{gt text="Delete"}" src="images/icons/extrasmall/edit_remove.png"/></td>
        </tr>
        {/foreach}
    </tbody>
</table>
