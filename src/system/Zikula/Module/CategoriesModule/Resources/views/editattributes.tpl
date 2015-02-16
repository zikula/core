<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text='Name'}</th>
            <th>{gt text='Value'}</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="text" class="form-control input-sm" name="attribute_name[]" id="new_attribute_name" value="" /></td>
            <td><input type="text" class="form-control input-sm" name="attribute_value[]" id="new_attribute_value" value="" size="50" /></td>
            <td><a href="#" id="category_attributes_add" title="{gt text='Add'}"><i class="fa fa-plus-square fa-lg text-success"></i></a></td>
        </tr>
        {foreach key='name' item='value' from=$attributes}
        <tr>
            <td><input type="text" class="form-control input-sm" name="attribute_name[]" value="{$name}" /></td>
            <td><input type="text" class="form-control input-sm" name="attribute_value[]" value="{$value}" size="50" /></td>
            <td><a href="#" class="category_attributes_remove" title="{gt text='Delete'}"><i class="fa fa-minus-square fa-lg text-danger"></i></a></td>
        </tr>
        {/foreach}
    </tbody>
</table>
