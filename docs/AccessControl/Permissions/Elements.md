---
currentMenu: permissions
---
# Fundamental elements of a permission rule

Each permission rule consists of the following parts:

Group
: The user group to which the rule applies.

Component
: The type(s) of content to which the rule applies.

Instance
: The concrete instance(s) of the component to which the rule applies.

Permission level
: The level of authorisation granted.

## Permission levels

Zikula uses the following nine permission levels:

No access (`ACCESS_NONE`)
: Users must not access the requested content and won't see affected instances at all.

Overview access (`ACCESS_OVERVIEW`)
: Users may see requested content, but have no access to it for reading its details.

Read access (`ACCESS_READ`)
: Users may see and read affected instances.

Comment access (`ACCESS_COMMENT`)
: Users may read requested content and they can add commenting additions to it.

Moderate access (`ACCESS_MODERATE`)
: Users may moderate affected instances in case some kind of a moderation exists.

Edit access (`ACCESS_EDIT`)
: Users may edit affected content instances. They may not add new instances though. For example a news system reviewer is allowed to edit articles to correct typos, but may not submit new content.

Add access (`ACCESS_ADD`)
: Users may add new content instances for the affected component types. Possibly they may also approve pending content instances.

Delete access (`ACCESS_DELETE`)
: Users may delete existing content instances of the affected component types.

Admin access (`ACCESS_ADMIN`)
: Users have full administrative rights for the affected content instances.

Important notes:

1. Higher permission levels include lower ones. For example delete access means that also moderate, edit and add access are available.
2. Any Zikula extension is free to decide how it interprets the permission levels. So what comment or moderate access specifically means can be different across multiple extensions and workflows.

## About components

The component specifies the type(s) of content to which a specific rule applies.

Any component consists of up to three levels that are separated by colons. The first level typically represents the extension or an extension's block within which the corresponding content types are processed.

## About instances

The instance part describes to which concrete instance(s) of the component a specific rule applies. If for example an extension allows creating recipes then each recipe record represents one instance of the corresponding permission component. Another example are blocks: it is possible to create several blocks of the same type, each of them is a dedicated instance of that block type.

The instance part of permission rules may also consists of up to three levels (again separated by colons), which allows nested contexts to be specified if required.
