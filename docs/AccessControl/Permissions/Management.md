---
currentMenu: permissions
---
# Further aspects for managing permissions

## Why the order matters

An important thing to know is that the order of rules is important. As all rules are part of an ordered table the permissions system as a whole can be imagined as a hierarchy: the system evaluates permissions from top to bottom whereby the first match for a given request is used. Background is the performance, so that not with each check for access rights unnecessarily many rules must be evaluated.

This is especially important to remember with regards to that any user may be a member of multiple user groups. As a rule of thumb it is a good approach to have rules for groups with higher permissions above rules for other groups which should have less abilities.

If for example a moderators group should be able to add posts and other users may only read posts, then the rule for moderators should come before the rule for users to ensure that moderators get their permission also if they are part of the users group additionally.

## Looking up components and instances

The permissions administration page allows to open an overview of all registered components along with a template for possible instances by clicking on the *"Permission rules information"* link. Note this overview includes all currently available components, depending on which extensions are installed and activated.

## Testing permission rules

Whenever there is an uncertainty about whether a specific permission is correctly configured or not it is possible to test permissions for any user name. Below the permission rules table there is a form which shows if a user has a given permission level for a given component and instance or not.

## Keeping the overview

A permission rule has two more fields that are only for assisting the site administrator and have not been mentioned yet above:

- An optional comment which can store explanations for the rule that are displayed as a tooltip when hovering over the corresponding table row.
- A Bootstrap colour class which can be used for building visual groups to mark multiple related rules.
