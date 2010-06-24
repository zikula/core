<table class="z-admintable">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Value"}</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr class="{cycle values="z-odd,z-even"}">
            <td><input name="attribute_name[]" id="new_attribute_name" value="" /></td>
            <td><input name="attribute_value[]" id="new_attribute_value" value="" size="50" /></td>
            <td><input type="image" onclick="return Category.Attributes.Add(this)" title="{gt text="Add"}" src="images/icons/extrasmall/edit_add.gif"/></td>
        </tr>
        {foreach from=$attributes item=value key=name}
       <tr class="{cycle values="z-odd,z-even"}">
            <td><input name="attribute_name[]" value="{$name}" /></td>
            <td><input name="attribute_value[]" value="{$value}" size="50" /></td>
            <td><input type="image" onclick="return Category.Attributes.Remove(this)" title="{gt text="Delete"}" src="images/icons/extrasmall/edit_remove.gif"/></td>
        </tr>
        {/foreach}
    </tbody>
</table>
