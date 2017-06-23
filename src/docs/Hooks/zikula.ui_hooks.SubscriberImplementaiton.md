WORKFLOW OF ui_hooks HOOKS
---------------------------

Introduction to new/edit/delete types Subscriber Implementation
---------------------------------------------------------------

The next two hook types, 'creating new items' and 'editing existing items' are considered to be
all part of the same workflow.  There is little point duplicating the bulk of code required
to process create and edit, therefore we should combine them into a single controller and view.
This is because whether creating a new item, or editing an existing item, we're using
essentially the same form.  In 'create' the form starts out empty, and in 'edit' the form
is populated by a database query.  We know if we should validate and commit any input data
if the form was submitted or not.  And lastly, when we process the form on submit, again,
it's the same process that is used to update, the only difference is we might use an
`persist($entity)` as well as `flush()`.  This is why we can use one controller method and view
for both create and edit actions.

For this reason also, there is no need for separate display and processing methods.  For example
`edit()` to display edit form, and `update()` to validate and update the record, followed by a
redirect simply do not make sense when it can be done easily in one controller method.


### Creating a new item

When we create an item, essentially, we visit an edit page with no id in the request.
From this we know that the action is not an edit, but a 'create new'.  We can determine
if it's a brand new form or a submitted form by reading the form submit property.
Accordingly, we can notify the system of the hook events.

When displaying a new empty form, we simply trigger a `form_edit` in the template with
{notifydisplayhooks} using a null id.

    {{ notifyDisplayHooks('foo.ui_hooks.form_edit', null) }}

The function will return all display hook handlers that responde, sorted according to
the administration settings.  The return is an array of

    [
        'providerarea1' => `\Zikula\Bundle\HookBundle\Hook\DisplayHook`,
        'providerarea2' => `\Zikula\Bundle\HookBundle\Hook\DisplayHook`,
    ];

When we come to validate a new create form, this means we have received a submit command
in the form.  We can then validate our form and then trigger a `validate_edit` hook with

    $hook = new \Zikula\Bundle\HookBundle\Hook\ValidationHook(new \Zikula\Bundle\HookBundle\Hook\ValidationProviders());
    $this->dispatchHooks('...validate_edit', $hook);
    $validators = $hook->getValidators();

The validator collection can then be tested for the presence of validation errors or not
with `$validators->hasErrors()`.  Together with the form submit the method can decide
if it's safe to commit the data to the database or, if the form needs to be redisplayed with
validation errors.

If it's ok simply commit the form data, then trigger a `process_edit` Zikula_ProcessHook with

    new \Zikula\Bundle\HookBundle\Hook\ProcessHook($name, $id, $url);

The URL should be an instance of Zikula_ModUrl which describes how to get the newly created object.
For this reason you must determine the ID of the object before you issue a `\Zikula\Bundle\HookBundle\Hook\ProcessHook`.

If the data is not ok, then redisplay the template.

`form_edit` hooks are displayed in the template with

    {{ notifyDisplayHooks('foo.ui_hooks.form_edit', id) }}


### Editing an existing item

When when we edit an item, we visit an edit page with an id in the request and the
controller will retrieve the item to be edited from the database.

We can determine if we should validate and commit the item or just display the item for
editing by reading the form submit property.
Accordingly, we can notify the system of the hook events.

When displaying an edit form, we simply trigger a `form_edit` hook with with

     {notifydisplayhooks eventname='<module>.ui_hooks.<area>.form_edit' id=$id}

When we come to validate an edit form, this means we have received a submit command
in the form.  We can then validate our form and then trigger a `validate_edit` event with

    $hook = new \Zikula\Bundle\HookBundle\Hook\ValidationHook(new Zikula_Hook_ValidationProviders());
    $this->DispatchHooks('...validate_edit', $hook);
    $validators = $hook->getValidators();

The validator collection can then be tested for the presence of validation errors or not
with `$validators->hasErrors()`.  Together with the form submit the method can decide
if it's safe to commit the data to the database or, if the form needs to be redisplayed with
validation errors.

If it's ok simply commit the form data, then trigger a `process_edit` event with

    new \Zikula\Bundle\HookBundle\Hook\ProcessHook($name, $id, $url);

If the data is not ok, then simply redisplay the template.  The triggered event will pick up
the validation problems automatically as the validation of each handler will persist in
the `Zikula_Hook_AbstractHandler` instances unless using an outdated workflow where the validation method
redirects to display methods, in which case you will have to do validation again.

`form_edit` hooks are displayed in the template with

    {notifydisplayhooks eventname='<module>.ui_hooks.<area>.form_edit' id=$id}


### Deleting an item

There are many different approaches that can be taken to deleting an item. For example we
can add a delete button to an edit form.  We usually would have a confirmation screen
or we might just use a javascript confirmation.  Generally, we would not want to add
anything extra to a delete confirmation page, but we certainly need to process a delete
action.  Ultimately when a controller (that makes use of hooks) deletes an item, it
must notify the attached modules to prevent orphaned records.  This is done simply by
triggering a hookable event with

    new \Zikula\Bundle\HookBundle\Hook\ProcessHook($name, $id, $url);

`form_delete` hooks are displayed in the template with

    {notifydisplayhooks eventname='<module>.ui_hook.<area>.form_delete' id=$id}
