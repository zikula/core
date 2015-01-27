<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th>{gt text='Registered component'}</th>
        <th>{gt text='Instance template'}</th>
    </tr>
    </thead>
    <tbody>
    {foreach key='component' item='instance' from=$schemas}
        <tr>
            <td>{$component|safetext}</td>
            <td>{$instance|safetext}</td>
        </tr>
    {/foreach}
    </tbody>
</table>