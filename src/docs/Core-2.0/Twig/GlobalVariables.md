Twig Template Global Variables
==============================

In addition to the global variables that are provided by Symfony, Zikula provides the following global variables.

pagevars
--------
The `pagevars` variable makes registered variables available in the template.

    {{ pagevars.homepath }}
    {{ pagevars.lang }}
    {{ pagevars.langdirection }}
    {{ pagevars.title }}
    {{ pagevars.meta.description }}
    {{ pagevars.meta.keywords }}
    {{ pagevars.meta.charset }}

When creating a template, variables can be created, modified or retrieved using the template tags:

    {{ pageSetVar('title', __('My Custom Page Title')) }}
    {{ pageGetVar('title', 'my default value') }}

Note: do NOT use `pagevars.homepath` for an asset path! The constructed url is **locale** sensitive and will include the
locale in the path!

themevars
---------

The `themevars` variable makes the theme's variables available in the template. These are assigned in the
`variables.yml` file for the theme and editable in the theme's config UI.

    {{ themevars.<variablename> }}

currentUser
-----------

The `currentUser` variable makes various other properties available in the template. Any property of the UserEntity
plus an additional `loggedIn` property (boolean).

    {{ currentUser.loggedIn }}
    {{ currentUser.uname }}
    {{ currentUser.uid }}
    {{ currentUser.email }}
    {{ currentUser.<propertyname> }}

Symfony Global Variables
------------------------

For more information about the global variables provided by symfony, please see
http://symfony.com/doc/current/book/templating.html#global-template-variables
