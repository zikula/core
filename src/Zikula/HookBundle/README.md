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
 - Hooks are generically contracted for workflow types.

How are HookEvents different from standard Events?
--------------------------------------------------
 - Events are connected to listeners using EventListeners or EventSubscribers from
   within the Symfony container and cannot be dynamically assigned at will via a UI.
 - Events are uniquely contractural and specific to a single workflow.

HookEvent Types
---------------
The following HookEvent types are provided by HookBundle. Additional generic-
contract HookEvents can be easily created and extended as well. Each HookEvent
type also has a companion HookEventListener that is uniquely paired with that event.
 1. DisplayHookEvent -> DisplayHookEventListener
 2. FilterHookEvent -> FilterHookEventListener
 3. FormHookEvent -> FormHookEventListener

### DisplayHookEvent
A DisplayHookEvent is dispatched from within a twig template using the provided
twig function `{{ dispatchDisplayHookEvent('HookEventClassname', 'id') }}`
DisplayHookEventListeners must add `DisplayHookEventResponse` objects which are
designed to add content to the template at that location. (Example: display
comments following a blog article).

### FilterHookEvent
A FilterHookEvent is dispatched from withing a twig tempalte using the provided
twig filter `{{ text|dispatchFilterHookEvent('HookEventClassname') }}`
FilterHookEventListeners are provided the target string and this string may be
altered (or filtered) as needed. (Example: profanity removal).

### FormHookEvent
A FormHookEvent is dispatched twice within a Symfony Form workflow. First, after
the creation of the form and then again after the form has been submitted and
validated. FormHookEventListeners are provided the target Form and may alter the
Form as needed, provide additional templates and then finally process the unbound
form data in addition to the main form. (Example: adding profile fields to a user
registration form, or adding a WYSIWYG editor to a textfield).

Implementation
--------------
It is quite easy to implement both HookEvents and HookEventListeners. Simply extend
the parent class, defining both `title` and `info` properties. Listeners require
implementing logic needed to affect change as desired. In the Display and Filter
HookEvents, this is done in the `execute` method. In the FormHookEventListener
two methods require logic: `preHandleExecute` for modifying the form before it is
used and `postSubmitExecute` after the form has been submitted. See all the
properties and methods of each class as well as included demonstration code for
more information.
