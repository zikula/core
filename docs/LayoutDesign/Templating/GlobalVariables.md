---
currentMenu: templating
---
# Twig template global variables

In addition to the global variables that are provided by Symfony, Zikula provides the following global variables.

## pagevars

The `pagevars` variable makes registered variables available in the template.

```twig
{{ pagevars.homepath }}
{{ pagevars.title }}
{{ pagevars.meta.description }}
{{ pagevars.meta.charset }}
```

When creating a template, variables can be created, modified or retrieved using the template tags:

```twig
{{ pageSetVar('title', 'My Custom Page Title'|trans ) }}
{{ pageGetVar('title', 'my default value') }}
```

Note: do NOT use `pagevars.homepath` for an asset path! The constructed url is **locale** sensitive and will include the
locale in the path!

## themevars

The `themevars` variable makes the theme's variables available in the template. These are assigned in the
`variables.yaml` file for the theme and editable in the theme's config UI.

```twig
{{ themevars.<variablename> }}
```

## currentUser

The `currentUser` variable makes various other properties available in the template. Any property of the UserEntity
plus an additional `loggedIn` property (boolean).

```twig
{{ currentUser.loggedIn }}
{{ currentUser.uname }}
{{ currentUser.uid }}
{{ currentUser.email }}
{{ currentUser.<propertyname> }}
```

## Symfony global variables

For more information about the global variables provided by symfony, please see [this doc](https://symfony.com/doc/current/templates.html#the-app-global-variable).
