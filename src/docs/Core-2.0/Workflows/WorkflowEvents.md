Workflow Events
===============

The Symfony Workflow component dispatches multiple events which you can use to inject custom behaviour.
*Note:* this document can be removed after it has been merged into the Symfony documentation (see [https://github.com/symfony/symfony-docs/pull/7528](PR)).

General events
--------------

The following events are dispatched for all workflows:

1. _workflow.guard_: occurs before a transition is started. Allows you to prevent it by calling `$event->setBlocked(true);`.
2. _workflow.leave_: occurs when an object leaves it's current state.
3. _workflow.transition_: occurs when the transition to the new state is launched.
4. _workflow.enter_: occurs when the new state is just defined on the object.

Workflow-specific events
------------------------

All the events mentioned above are also triggered for each workflow specifically. This allows you to react only for the events of a specific workflow.

1. _workflow.<workflow_name>.guard_
2. _workflow.<workflow_name>.leave_
3. _workflow.<workflow_name>.transition_
4. _workflow.<workflow_name>.enter_

Transition- or state-specific events
------------------------------------

You can even listen to only specific transitions or states for a specific workflow:

1. _workflow.<workflow_name>.guard.<transition_name>_
2. _workflow.<workflow_name>.leave.<state_name>_
3. _workflow.<workflow_name>.transition.<transition_name>_
4. _workflow.<workflow_name>.enter.<state_name>_
