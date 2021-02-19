HookBundle
==========

This is a read-only repository.

Resources
---------

  * [Report issues](https://github.com/zikula/core/issues) and
    [send Pull Requests](https://github.com/zikula/core/pulls)
    in the [main Zikula repository](https://github.com/zikula/core)
  * For more information visit [ziku.la](https://ziku.la/).
  * Please see our [documentation](https://docs.ziku.la).

What Are Hooks?
===============
 - Hooks are events that are dynamically attached to listeners at runtime.
 - Hooks are connected to listeners in a User Interface and stored in a DB table.
   - This UI allows the user to connect/disconnect and alter the priority of the
     connection between the event and the listener.
 - Hooks are generically contracted for workflow types.

How are HookEvents different from standard Events?
--------------------------------------------------
 - Events are connected to listeners using EventListeners or EventSubscribers from
   within the Symfony container and cannot be dynamically assigned at will via a UI.
 - Events are uniquely contractural and specific to a single workflow.

HookEvent Types
---------------
The following abstractHookEvents are provided by HookBundle. Additional
HookEvents can be easily created as well. Each HookEvent also has a companion
HookEventListener that is uniquely paired with that event. This pairing defines the
contract between Event and Listener.
 1. DisplayHookEvent -> DisplayHookEventListener
 2. FilterHookEvent -> FilterHookEventListener
 3. FormHookEvent -> FormHookEventListener

### DisplayHookEvent
A DisplayHookEvent is dispatched from within a twig template using the provided
twig function `{{ dispatchDisplayHookEvent('HookEventClassname', 'id') }}`
DisplayHookEventListeners must add `DisplayHookEventResponse` objects which add
content to the template at that location. (Example: display comments following a 
blog article).

### FilterHookEvent
A FilterHookEvent is dispatched from withing a twig template using the provided
twig filter `{{ text|dispatchFilterHookEvent('HookEventClassname') }}`
FilterHookEventListeners are provided the target string and this string may be
altered (or filtered) as needed. (Example: profanity removal).

### FormHookEvent
A FormHookEvent is dispatched twice within a Symfony Form workflow inside a
controller. First, after the instatiation of the form and then again after the form
has been submitted and validated. FormHookEventListeners are provided the target
Form and may alter the Form as needed, provide additional templates and then finally
process the unbound form data. (Example: adding profile fields to a user 
registration form, or adding a WYSIWYG editor to textfields).

Implementation
--------------
It is quite easy to implement both HookEvents and HookEventListeners. Simply extend
the abstract parent class, defining both `title` and `info` properties. It is 
important that the classes are in an auto-configured directory or are manually
tagged `zikula.hook_event` or `zikula.hook_event_listener` respectively.

Listeners require implementing logic needed to affect change as desired. In the
`DisplayHookEventListener` and `FilterHookEventListener`, this is done in the 
`execute` method. In `FormHookEventListener` two methods require logic: 
`preHandleExecute` for modifying the form before it handles the `Request` and 
`postSubmitExecute` after the form has been submitted. See all the properties and 
methods of each class as well as included demonstration code for more information.

Philosophy
----------
Hook Connections are intentionally _generic_ and agnostic of the larger application
or specific functions surrounding them. Listeners are unaware of the specific 
controller or template to which they are listening and the content or form to which
they may be connected. The listener does not change its behavior/response based
upon which controller or template from which the event is dispatched.

This is one aspect of Hooks that makes them unique from Events. If specific 
knowledge of the workflow or content is required, a custom event is the preferred 
solution. Instead, HookListeners respond in a generic way to unique HookEvents and
provide the same responses anytime that a HookEvent is dispatched. These Listeners 
can provide the same responses in any location within an application in this 
generic way.

The contract is based upon the abstract parent HookEvent class not upon the final 
concrete class. The final concrete class simply defines the unique name or 'id' so
that the Connections User Interface may identify the location to assign that 
connection. This is what is meant by generic contract: generic by HookEvent, and
the power is in the flexibility this standardization brings.
