---
currentMenu: permissions
---
# Permissions system

The permission system of Zikula is very flexible and powerful: it allows a very fine-grained control over who is allowed to do what. This power also contains a certain complexity though. In this section permission rules are explained. It also discusses how permissions management works and what developers need to consider.

## Permission rules are for user groups

First of all, permission rules are always applied to [user groups](../Groups/README.md). Many years ago permission rules were also possible for single users, but this this had been waived for performance reasons. So if it should ever become necessary to regulate things differently for a certain user, a separate user group must be created for this.

## Basics

- [Fundamental elements of a permission rule](Elements.md)
- [Further aspects for managing permissions](Management.md)

## Advanced topics

- [Using individual / additional components](IndividualRules.md)
- [Powerful permission rules with regular expressions](RegularExpressions.md)

## For developers

- [How to implement permissions](Dev/Implementation.md)
- [PermissionApi](Dev/PermissionApi.md)
- [PermissionCheck Annotation](PermissionCheckAnnotation.md)
- [CategoryPermissionApi](../Integration/Categories/Dev/CategoryPermissionApi.md)
