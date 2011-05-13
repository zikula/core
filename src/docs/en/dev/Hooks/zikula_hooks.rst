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


Providers and Subscriber
------------------------
In this document we will use the terms "hook provider" (or simply "provider")
and "hook subscriber" (or simply "subscriber").

A provider is a module or a plugin that provides some functionality that can be
attached to another module's output. Providers receive notifications that a
hookable event that they are interested in has occurred, and in response
provides the additional UI components to display in the subscriber's UI (or for
certain hookable events, processes information gathered from the UI). An example
of a hook provider might be a module that allows users to comment on documents
throughout the web site, no matter what other module actually manages the
document.

A subscriber is a module that defines hookable events and sends notifications
that they have occurred. An example of a subscriber might be a module that
manages news stories, to which the comment hook provider example described
above might be hooked.


Areas
-----
This is an advanced feature for complex modules that define more than one
subject area. In general, simpler modules and plugins will only be a provider for or
subscriber to one area.

Areas allow subscribers and providers to group their features. This is to allow
modules to provide different groups of hooks for different subject matter. From
the provider side, it can provide different hook features based on the area. From
the subscriber side, it allows a module to apply specific hooks to one part of a
module, and different hooks (or no hooks at all) to other parts of the module.

Areas should all be unique, so please use this format:

    modulehook_area.mymodule.<areaname>

For example:

    modulehook_area.comments.general (the provider area)
    modulehook_area.news.articles    (the subscriber area)


Subscriber Modules
------------------
Modules that understand hooks (they can notify providers of an event, and can
display the providers' hook contents in their own UI) must make this known to
the Zikula framework in the modules's Version.php.  This is done by adding a
method called `setupHookBundles()` to Version.php, for example:

    protected function setupHookBundles()
    {
        $bundle = new Zikula_Version_HookSubscriberBundle($this->name, 'modulehook_area.news.articles', 'ui', $this->__('News Display Hooks'));
        $bundle->addType('ui.view', 'news.hook.articles.ui.view');
        $bundle->addType('ui.edit', 'news.hook.articles.ui.edit');
        // add other types as needed
        $this->registerHookSubscriberBundle($bundle);
    }

During installation of the module you must register the bundles with the
persistence layer using:

    HookUtil::registerSubscriberBundles($this->version->getHookSubscriberBundles());

A complex module might have several different areas where attaching Hooks may
be appropriate, or may require different Hooks for different areas.
This is why you might specify different areas in the API.  Each area would have to
have it's own set of unique event names.

The `addType()` method used in setting up hook bundles is how a subscriber
indicates what hookable events are available (understood) by the module.
The first parameter is the hook type (e.g., `'ui.view'`). The second parameter
is the event name that is triggered by *THIS* module (e.g., `'news.hook.articles.ui.view'`).
So if this module was a news module, then the second parameter is the unique
name of a hookable event that only *this* news module actually triggers.  Under
the hood, when a user attaches, say, a comment module (a hook provider), then
the hook handler of the comment module will be attached to the EventManager
using the event name supplied by the news module (the hook subscriber).
For example, `attach news.ui.view` to `comments.handler.ui.view` which is the name of a
callable handler registered by the hook provider (comment).

It is also necessary to add the following to the `getMetaData()` method of the
subscriber's Version.php to let Zikula know that the module understands hooks
and may subscribe to them:

    $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));

Zikula will add a configuration menu to the administration area of the module.  For
this reason you *must* have an admin controller in the module.


Provider Modules
----------------
Provider modules must make their available hook handlers known to Zikula.  You
must perform three tasks:

First add the hook_provider capability to the provider's `Version.php` in the
`getMetaData()` function:

    $meta['capabilities'] = array(HookUtil::PROVIDER_CAPABLE => array('enabled' => true));

Second, you must configure provider bundles in the `Version.php` by adding the
following method:

    protected function setupHookBundles()
    {
        $bundle = new Zikula_Version_HookProviderBundle($this->name, 'modulehook_area.ratings.rating', 'ui', $this->__('Ratings Hook Poviders'));
        $bundle->addHook('hookhandler.ratings.ui.view', 'ui.view', 'Ratings_Hooks', 'uiview', 'ratings.service');
        // add other hooks as needed
        $this->registerHookProviderBundle($bundle);

        //... repeat as many times as necessary
    }

Third, on installation or upgrade of the module you must register the bundles with the
persistence layer. During installation use:

    HookUtil::registerProviderBundles($this->version->getHookProviderBundles());

This will register the hook event handlers. That is to say, it will
define the actual PHP class/method that will respond to hook events that are
triggered by subscriber modules.

A module may register either static class callable methods, like `Foo::Bar($event)` or
services (which are instantiated class objects).  If using services, they must be
instances of Zikula_AbstractEventHandler.  We use one API to register this.

Leaving `$serviceId = null`, will tell Zikula the callable is a static class method.
If you give a `$serviceId`, then this class will be instantiated and used.  This means
you can use the same `$serviceId` and have multiple methods inside if you wish.

The $name of the hook is the name of the handler - a common name.  This is NOT
an event name.

    $bundle->addHook($name, $type, $className, $method, $serviceId);

    // registering a static method handler.
    $bundle->addHook('hookhandler.ratings.ui.view', 'ui.view', 'Ratings_Hooks', 'uiview');

    // registering a service (preferred - class must be an instance of Zikula_HookHandler)
    $bundle->addHook('hookhandler.ratings.ui.view', 'ui.view', 'Ratings_Hooks', 'uiview', 'module.ratings_hooks');


Hook Types
----------
The following is a list of valid hook types.  Not all have to be used but in general,
a HookBundle should contain at least the ui.* and process.* handlers valid to complete
an action.

    ui.view         - Display hook for view/display templates.
    ui.edit         - Display hook for create/edit forms.
    ui.delete       - Display hook for delete dialogues (generally not used).

    validate.edit   - Used to validate input from an ui create/edit form.
    validate.delete - Used to validate input from an ui create/edit form (generally no used).

    process.edit    - Perform the final update actions for a ui create/edit form.
    process.delete  - Perform the final delete actions for a ui form.


Hook Events
-----------
In this section we will discuss the actual hookable event that is triggered by
a subscriber module.

The event encapsulates information about the hookable event. First, we need
the hook event name, e.g. `<module>.hook.<area>.ui.edit`

Next, we need the subject of the event. This will be the object or array of data.
For example if this was a blog post, then it would be the blog post object (if
using Doctrine or the array of the blog post). Please note you need this in all
cases except for `create` where there is no data yet, or if there is, it's an empty
object.  This goes for `delete` operations also.  This might not make sense at first
glance, but even `delete` operations normally come from a screen that has displayed
the post to be deleted, therefore, this object should already be available.

It might looks like this:

    new Zikula_ProcessHook('news.hook.articles.process.update', $id, $url)

The URL is an instance of Zikula_ModUrl() which describes the URL of how to
retrieve this particular object (the parent of the hook).


Implementing Hooks from the Subscriber Side
-------------------------------------------
Hooks are only for use with the UI, and with UI-related processing like validation.

Their main purpose it so one module can be attached to another at the UI layer,
like attaching the ability to rate blog posts. In this section we cover the
implementation of hooks from the subscriber's side. In our example, that
would mean the Blogging module.

We don't need to be concerned with attaching hooks to modules, that is handled
automatically by the administration UI.

Attaching display hooks is very simple.  Inside the template simply add

    {notifydisplayhooks eventname='news.hook.articles.ui.view' id=$id}

`$caller` will be added automatically unless you need to specify it, but the value is taken
from the Zikula_View instance so in general it's not needed.

The plugin will return all display hooks, sorted according to the administration
settings.  The return is an array of

    array(
        'providerarea1' => 'output1',
        'providerarea2' => 'output2'
    );

In the module controllers, you will need to implement the process and or validation
hook types.  This can be done as follows:

    $url = new Zikula_ModUrl(....); // describes how to retrieve this object
    $hook = new Zikula_Process('news.hook.articles.process.create', $id, $url);
    $this->notifyHooks->notify($hook);


HOOK RESPONSES FROM PROVIDERS
-----------------------------
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
        $hook->setResponse(new Zikula_Response_DisplayHook('modulehook_area.modname.area', $view, $template));
    }


GENERAL WORKFLOW OF HOOKS
-------------------------
The general workflow of hooks is as follows.

#### Displaying an item ####

When viewing an item of some sort, we want to allow other modules to attach some form of
content to the display view.  We simply notify `ui.view` hooks with the item being displayed
(the subject), the id and the module name as arguments.

In the template we simply use something like this, using the `ui.view` hook type.

    {notifydisplayhooks eventname='<module>.hook.<area>.ui.view' id=$id}


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

#### Creating a new item ####

When when we create an item, essentially, we visit an edit page with no id in the request.
From this we know that the action is not an edit, but a 'create new'.  We can determine
if it's a brand new form or a submitted form by reading the form submit property.
Accordingly, we can notify the system of the hook events.

When displaying a new empty form, we simply trigger a `ui.edit` in the template with
{notifydisplayhooks} using a null id.

    {notifydisplayhooks eventname='<module>.hook.<area>.ui.edit' id=null}

When we come to validate a new create form, this means we have received a submit command
in the form.  We can then validate our form and then trigger a `validate.edit` hook with

    $hook = new Zikula_ValidationHook('...validate.edit', new Zikula_Collection_HookValidationProviders());
    $this->notifyHooks($hook);
    $validators = $hook->getValidators();

The validator collection can then be tested for the presence of validation errors or not
with `$validators->hasErrors()`.  Together with the form submit the method can decide
if it's safe to commit the data to the database or, if the form needs to be redisplayed with
validation errors.

If it's ok simply commit the form data, then trigger a `process.edit` Zikula_ProcessHook with

    new Zikula_ProcessHook($name, $id, $url);

The URL should be an instance of Zikula_ModUrl which describes how to get the newly created object.
For this reason you must determine the ID of the object before you issue a Zikula_ProcessHook.

If the data is not ok, then simply redisplay the template.  The triggered hook event will pick up
the validation problems automatically as the validation of each handler will persist in
the `Zikula_HookHandler` instances unless using an outdated workflow where the validation method
redirects to display methods, in which case you will have to do validation again.

`ui.edit` hooks are displayed in the template with

    {notifydisplayhooks eventname='<module>.hook.<area>.ui.edit' id=$id}

#### Editing an existing item ####

When when we edit an item, we visit an edit page with an id in the request and the
controller will retrieve the item to be edited from the database.

We can determine if we should validate and commit the item or just display the item for
editing by reading the form submit property.
Accordingly, we can notify the system of the hook events.

When displaying an edit form, we simply trigger a `ui.edit` hook with with

     {notifydisplayhooks eventname='<module>.hook.<area>.ui.edit' id=$id}

When we come to validate an edit form, this means we have received a submit command
in the form.  We can then validate our form and then trigger a `validate.edit` event with

    $hook = new Zikula_ValidationHook('...validate.edit', new Zikula_Collection_HookValidationProviders());
    $this->notifyHooks($hook);
    $validators = $hook->getValidators();

The validator collection can then be tested for the presence of validation errors or not
with `$validators->hasErrors()`.  Together with the form submit the method can decide
if it's safe to commit the data to the database or, if the form needs to be redisplayed with
validation errors.

If it's ok simply commit the form data, then trigger a `process.edit` event with

    new Zikula_ProcessHook($name, $id, $url);

If the data is not ok, then simply redisplay the template.  The triggered event will pick up
the validation problems automatically as the validation of each handler will persist in
the `Zikula_HookHandler` instances unless using an outdated workflow where the validation method
redirects to display methods, in which case you will have to do validation again.

`ui.edit` hooks are displayed in the template with

    {notifydisplayhooks eventname='<module>.hook.<area>.ui.edit' id=$id}

#### Deleting an item ####
There are many different approaches that can be taken to deleting an item. For example we
can add a delete button to an edit form.  We usually would have a confirmation screen
or we might just use a javascript confirmation.  Generally, we would not want to add
anything extra to a delete confirmation page, but we certainly need to process a delete
action.  Ultimately when a controller (that makes use of hooks) deletes an item, it
must notify the attached modules to prevent orphaned records.  This is done simply by
triggering a hookable event with

    new Zikula_ProcessHook($name, $id, $url);

`ui.delete` hooks are displayed in the template with

    {notifydisplayhooks eventname='<module>.hook.<area>.ui.delete' id=$id}


