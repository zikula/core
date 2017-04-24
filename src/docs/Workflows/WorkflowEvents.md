# Workflow Events

The Symfony Workflow component dispatches multiple events which you can use to inject custom behaviour.
*Note:* this document can be removed after it has been merged into the Symfony documentation (see [PR 7528](https://github.com/symfony/symfony-docs/pull/7528)).

## General events

The following events are dispatched for all workflows:

1. _workflow.guard_: occurs just before a transition is started and when testing which transitions are available. It allows you to define that the transition is not allowed by calling `$event->setBlocked(true);`.
2. _workflow.leave_: carries the marking with the initial places, occurs just after an object has left it's current state.
3. _workflow.transition_: carries the marking with the current places, occurs just before starting to transition to the new state.
4. _workflow.enter_: carries the marking with the new places, occurs just after the object has entered into the new state.

## Workflow-specific events

All the previous events are also triggered for each workflow individually, so you can react only to the events of a specific workflow:

1. _workflow.<workflow_name>.guard_
2. _workflow.<workflow_name>.leave_
3. _workflow.<workflow_name>.transition_
4. _workflow.<workflow_name>.enter_

## Transition or state-specific events

You can even listen to some specific transitions or states for a specific workflow:

1. _workflow.<workflow_name>.guard.<transition_name>_
2. _workflow.<workflow_name>.leave.<state_name>_
3. _workflow.<workflow_name>.transition.<transition_name>_
4. _workflow.<workflow_name>.enter.<state_name>_
