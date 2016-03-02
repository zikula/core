Zikula Hooks
============

This document describes how HookManager is implemented in Zikula.  It outlines
the conventions and expected implementation patterns with code examples.


What is a hook?
---------------

A hook another kind of event that occurs in execution life-cycle and should really be called
'hookable events'.  Unlike generic events, display hookable events are used to create a
dynamic relation to an object.  It may be 1:1 or 1:n.

In the case of 1:n relations, they attach to an object's view.  In the case of 1:1 relations
they intercept the objects entire lifecycle (create, validate, edit, view and delete).

Hook relations are entirely configurable by the site administrator and not controlled by
the programmer.

Example uses of 1:n relations: adding comments to a blog article view, or maybe ratings.
Example uses of 1:1 relations: generally to gather extra information on a form for example
adding an age verification field to a form.

There are some more esoteric uses of hookable events which may not add any relation to
forms but instead allow the creation of a blog article ping.

The other kind of hookable events are hook filters which allow the site administrator
to selectively filter content.


General Naming Convention
-------------------------

By convention we are use lower case letter and number, with full-stop and underscore
characters only.  The full-stop is used to separate out logically separate items,
a kind of namespacing, while the underscore is used in place of a space character
to punctuate logically connected items.


Area Naming
-----------

Areas should all be unique, so please use this format:

    <type>.<name>.<category>.<areaname>

Type can be 'subscriber' or 'provider'

For example:

    provider.ratings.ui_hooks.rating (the provider area in 'ui_hooks' category)
    subscriber.blog.ui_hooks.articles  (the subscriber area in 'ui_hooks' category)

    provider.ratings.filter_hooks.rating (the provider area in 'filter_hooks' category)
    subscriber.blog.filter_hooks.articles  (the subscriber area in 'filter_hooks' category)


Hook Types
----------
The following is a list of valid hook types.

### 'ui_hooks' category

    display_view    - Display hook for view/display templates.

    form_edit       - Display hook for create/edit forms.
    form_delete     - Display hook for delete dialogues.

    validate_edit   - Used to validate input from a create/edit form.
    validate_delete - Used to validate input from a delete form.

    process_edit    - Perform the final update actions for a create/edit form.
    process_delete  - Perform the final delete actions for a delete form.


### 'filter_hooks' category

    filter          - Filter's content in a template.


Subscriber Capability
---------------------

Modules that may subscriber to hook providers must advertise this capability
in the Version.php using

    $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));

Zikula will add a configuration menu to the administration area of the module.  For
this reason you *must* have an admin controller in the module.


Subscriber Bundles
------------------

Modules that are subscriber hook capable must advertise their areas and events
using "Subscriber Bundles".  This is done in the Version.php

    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.blog.ui_hooks.articles', 'ui_hooks', $this->__('blog Article UI Hooks'));
        $bundle->addEvent('display_view', 'blog.ui_hooks.articles.display_view');
        $bundle->addEvent('form_edit', 'blog.ui_hooks.articles.form_edit');
        $bundle->addEvent('form_delete', 'blog.ui_hooks.articles.form_delete');
        $bundle->addEvent('validate_edit', 'blog.ui_hooks.articles.validate_edit');
        $bundle->addEvent('validate_delete', 'blog.ui_hooks.articles.validate_delete');
        $bundle->addEvent('process_edit', 'blog.ui_hooks.articles.process_edit');
        $bundle->addEvent('process_delete', 'blog.ui_hooks.articles.process_delete');
        $this->registerHookSubscriberBundle($bundle);

        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.blog.filter_hooks.articles', 'filter_hooks', $this->__('blog Article Filter Hooks'));
        $bundle->addEvent('filter', 'blog.filter_hooks.articles.filter');
        $this->registerHookSubscriberBundle($bundle);
    }

During installation of the module you must register the bundles with the
persistence layer using:

    HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

The `addEvent()` method used in setting up hook bundles is how a subscriber
indicates what hookable events are available (understood) by the module.
The first parameter is the hook type (e.g., `'display_view'`). The second parameter
is the event name that is triggered by *THIS* module (e.g., `'blog.ui_hooks.articles.display_view'`).

So if this module was a blog module, then the second parameter is the unique
name of a hookable event that only *this* blog module actually triggers.  Under
the hood, when a user attaches, say, a comment module (a hook provider), then
the hook handler of the comment module will be attached to the HookManager
using the event name supplied by the blog module (the hook subscriber).


Provider Capability
-------------------

Provider modules must make their available hook handlers known to Zikula provider
capability to the provider's `Version.php` in the `getMetaData()` method:

    $meta['capabilities'] = array(HookUtil::PROVIDER_CAPABLE => array('enabled' => true));

Zikula will add a configuration menu to the administration area of the module.  For
this reason you *must* have an admin controller in the module.


Provider Bundles
----------------

You must configure the Version.php with the provider bundle information.  This tells HookManager
what areas are supported and describes the hook handlers.

    protected function setupHookBundles()
    {
        $bundle = new Zikula_HookManager_ProviderBundle($this->name, 'provider.ui_hooks.ratings.rating', 'ui_hooks', $this->__('Ratings Hook Poviders'));
        $bundle->addServiceHandler('display_view', 'Ratings_HookHandler', 'displayView', 'ratings.rating');
        // add other hook handlers as needed

        $this->registerHookProviderBundle($bundle);
    }

During installation of the module you must register the bundles with the
persistence layer using:

    HookUtil::registerProviderBundles($this->version->getHookProviderBundles());

You may register static class methods as hook handlers or services.  Use the appropriate
method `bundle->addServiceHandler()` and `bundle->addStaticHandler()` as required.

Service handlers must be an instance of Zikula_Hook_AbstractHandler.  This is preferred
when the hook workflow requires some kind of runtime persistence for validation. When doing
so you should specify the same service ID for all handlers in the bundle - this will ensure
the same object is used throughout the runtime session which thus provides the persistence
for the duration of the request cycle.

Examples:
    // registering a static method handler.
    $bundle->addStaticHandler('display_view', 'Ratings_Hooks', 'displayView', 'ratings.rating');

    // registering a service (preferred - class must be an instance of Zikula_Hook_AbstractHandler)
    $bundle->addServiceHandler('display_.view', 'Ratings_Hooks', 'displayView', 'ratings.rating');


Hook Events
-----------

In this section we will discuss the actual hookable event that is triggered by
a subscriber module.  The hook event object encapsulates information about the
hookable event. First, we need the hook event name, e.g. `<module>.<category>.<area>.<type>`

### Naming Convention

Hook events should be named as follows:

    `<module>.<category>.<area>.<type>`

### Event Trigger

To create a hookable event, create an appropriate hook object and then notify that
hook using HookManager's notify() method.


Subscriber Implementation
-------------------------

Hooks are only for use with the UI, and with UI-related processing like validation.

Their main purpose it so one module can be attached to another at the UI layer,
like attaching the ability to rate blog posts. In this section we cover the
implementation of hooks from the subscriber's side. In our example, that
would mean the Blogging module.

We don't need to be concerned with attaching hooks to modules, that is handled
automatically by the administration UI and is under the control of the administrator.

Attaching display hooks is very simple.  Inside the template simply add

    {notifydisplayhooks eventname='blog.ui_hooks.articles.display_view' id=$id}

The plugin will return all display hooks handler that respondes, sorted according to
the administration settings.  The return is an array of

    array(
        'providerarea1' => `Zikula_Response_DisplayHook`, // object
        'providerarea2' => `Zikula_Response_DisplayHook`, // object
    );

In the module controllers, you will need to implement the process and or validation
hook types.  This can be done as follows (example of a process hook).

    $url = new Zikula_ModUrl(....); // describes how to retrieve this object by URL metadata
    $hook = new \Zikula\Bundle\HookBundle\Hook\ProcessHook($id, $url);
    $this->notifyHooks()->dispatch('blog.ui_hooks.articles.process_edit', $hook);


Provider Display Hooks
----------------------

A hook handler should respond to a hookable event with a `Zikula_Response_DisplayHook`
instance in the following manner.

    // example of a static handler (static handlers are *not* the preferred handlers
    // for edit/validate handlers which should be Zikula_Hook_AbstractHandler instances instead)
    public static function hookHandler(Zikula_DisplayHook $event)
    {
        $template = 'template_name.tpl'; // the name of the module's template
        $view = Zikula_View::getInstance($module);

        // do stuff...

        // add this response to the event stack
        $hook->setResponse(new Zikula_Response_DisplayHook('subscriber.ui_hooks.modname.area', $view, $template));
    }


WORKFLOW OF HOOKS
-----------------

The general workflow of hooks is as follows.

### Displaying an item

When viewing an item of some sort, we want to allow other modules to attach some form of
content to the display view.  We simply notify `display_view` hooks with the item being displayed
(the subject), the id and the module name as arguments.

In the template we simply use something like this, using the `display_view` hook type.

    {notifydisplayhooks eventname='<module>.ui_hooks.<area>.display_view' id=$id}


Introduction to new/edit/delete types
-------------------------------------

The next two hook types, 'creating new items and editing existing items' are considered to be
all part of the same workflow.  There is little point duplicating the bulk of code required
to process create and edit, therefore we should combine them into a single controller and view.
This is because whether creating a new item, or editing an existing item, we're using
essentially the same form.  In 'create' the form starts out empty, and in 'edit' the form
is populated by a database query.  We know if we should validate and commit any input data
if the form was submitted or not.  And lastly, when we process the form on submit, again,
it's the same process that is used to update, the only difference is we might use an
SQL INSERT over an SQL UPDATE.  This is why we can use one controller method and view
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

    {notifydisplayhooks eventname='<module>.ui_hooks.<area>.form_edit' id=null}

When we come to validate a new create form, this means we have received a submit command
in the form.  We can then validate our form and then trigger a `validate_edit` hook with

    $hook = new \Zikula\Bundle\HookBundle\Hook\ValidationHook(new Zikula_Hook_ValidationProviders());
    $this->dispatchHooks('...validate_edit', $hook);
    $validators = $hook->getValidators();

The validator collection can then be tested for the presence of validation errors or not
with `$validators->hasErrors()`.  Together with the form submit the method can decide
if it's safe to commit the data to the database or, if the form needs to be redisplayed with
validation errors.

If it's ok simply commit the form data, then trigger a `process_edit` Zikula_ProcessHook with

    new \Zikula\Bundle\HookBundle\Hook\ProcessHook($name, $id, $url);

The URL should be an instance of Zikula_ModUrl which describes how to get the newly created object.
For this reason you must determine the ID of the object before you issue a Zikula_ProcessHook.

If the data is not ok, then simply redisplay the template.  The triggered hook event will pick up
the validation problems automatically as the validation of each handler will persist in
the `Zikula_HookHandler` instances unless using an outdated workflow where the validation method
redirects to display methods, in which case you will have to do validation again.

`form_edit` hooks are displayed in the template with

    {notifydisplayhooks eventname='<module>.ui_hooks.<area>.form_edit' id=$id}


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

