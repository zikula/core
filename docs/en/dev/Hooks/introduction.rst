INTRODUCTION
============

In this introduction we're going to look at the concept and rationale behind
HookManager.

A hook is basically a user-configurable event between a 'subscriber' and a
'provider' managed by HookManager.  So this means there are two sides parties
which are 'connected together'.  The mediator of this connection is a some
kind of hook event object.

By 'user-configurable' we mean that the user many connect provider hooks to
subscribers and control the order in which those providers are notified.

Hooks are generally associated with the view in MVC, modifying the view in
some way or another. This allows providers to modify content from a subscriber.
An example would be to add a comments form to a blog post or to selectively
apply filters to the body of the blog post.

This is a rather gross look at what HookManager does, and it is necessary to
look more deeply into the mechanisms to fully understand.


Hook Areas
----------

As we have seen, hooks consist of so-called 'providers' and 'subscribers'
however, it would be more accurate to talk about 'provider hook areas' and
'subscriber hook areas'.

A 'subscriber area' is a single distinct area usually on a form.  We'll
talk about 'usually' in a minute.  The reason for subscriber areas is that
it gives the user precise control over where a provider is attached to.

We talk of 'provider areas' again as being a distinct provider that can
be connected or attached to a distinct subscriber area.  The order of
provider areas can be varied to control the order they appear in the
subscriber area.


Hook Categories
---------------

Areas are classified into categories.  This is to ensure that only compatible
hooks can be connected together.


Hook Bundles
------------

The next concept is the 'bundle'.  To manipulate one subscriber area may require
several separate related steps.  For example, the usual workflow of a new/edit
form would be to display new/edit form in the view. To receive the input in the
controller and validate the input.  If valid the data would be committed to
the persistence layer, and if not valid, the form would be redisplayed in the
view with the validation errors.

It stands to reason then, that if a hook provider seeks to add some additional
fields on a form, that those fields would also require validation.  It would not
make sense to accept the valid subscriber's form only, and disregard the
validity of the hooks fields.

Bundles allow us to attach a group of provider handlers to listen to these
distinct events, create, edit, validate, process (insert/update/delete to
persistence).  This allow us to maintain some integrety and give an expected
result to the user who ultimately decides to connect providers to subscribers.


Owners
------

The last grouping of hook areas is into owner, i.e. between two applications,
the provider and the subscriber.  This will typically be the application name.
This allows us to build a user interface and group areas and connections
per application to give an easy user interface.


Coupling
--------

The most important thing about hooks is their generic quality.  However, we
need to qualify what we mean by 'generic' because your mileage may vary, so
we must qualify what is meant.

In the a previous section we specifcally talked about compatibility between
subscriber and providers.  This implies that there must be a contract between
the subscribing and providing sides: when we talk about coupling, it is between
the subscriber and provider area implementations.  The the contract is defined
defined by category standard and the individual hook types _within_ that
category.

This does not mean that the applications on either side have a contract with
each other, that would be a complete misunderstanding.  The only coupling
is what the subscriber and provider expect from each other in the context
of the hook category and specific hook being notified. The key words are
"what is expected within that category".  This means that both sides are
coupled by what the category defines.  That is what makes the hooks
implementation 'generic' for that category.  It means that any application
that understands that particular category, can talk with each-other.

You cannot have two subscribers connected to the same provider and expect the
provider to behave differently based on which subscriber it's communicating
with.

Ultimately, the power of HookManager is that the system in itself does not
have any limitations other than what is imposed when creating the standards
and contracts of category (of 'hook area').

HookManager itself merely connects providers and subscribers and facilitates
communication between then via an "hook event interface".  It is up to the
creator of the category, how the object is hook event object implemented
beyond the required interface. This is what allows us to set up specific
contracts for a given hook category.


Hooks vs Events
---------------

It is worth nothing some philosophical differences between HookManager and
EventManager.  Firstly, HookManager is coupled to the EventManager and
ultimately, uses the EventManager to notify events.

As you know, EventManager provides both a generic event object, and, an
EventInterface to allow more 'contracted' events.  The generic event object
is really for convenience but could be probably a source of confusion at
the same time.

Ultimately, every event that is triggered is different, but it is clearer
if using specific event object than one event object for everything.

new UserLoginEvent($user) is more clear to the listeners than new Event($user).
It's more clear what the event object is about and the event object may
provide a clearer interface - instead of some random "args" or miscellaneous
$subject, we're able to create a proper interface more OO style.

The point is that regardless of whether you use a generic event object or
a specific one, there is always an expectation or contract created by
the one who triggers the event.  That affects how the listener must interact
and behave.  We normally consider this tied to the event name.  Each different
event name requires a different set of interactions because event is different.

Hook events on the other hand may have unique event names, but they must all
behave the same way according to their category and type.  That means if you
have a category called 'filter' and a type called 'ui.filter', then regardless
of the hook event name (which is always unqiue), the contract is exactly the
same for that category + type.  This is what allows any subscriber and provider
to be connected together that have a matching category. This is what is meant
by generic communication: generic by category, and the power is in the
flexibility this standardisation brings.


Mechanism
---------

So in simple terms, HookManager is a complex interpretation of the observer
notification pattern.  That is, events are triggered and handlers may
listen to these events.  The event is encapsulated an object which implements
a HookInterface.

The key difference to a standard event notifier is that the hook manager allows
specific control over which handlers listen to which events and in what order.
This can ultimate be controlled by a UI or other mechanism.