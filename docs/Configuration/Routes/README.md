---
currentMenu: routes-management
---
# Routes and custom urls

## Changing extension urls

The urls of a module can be customized by the site admin. For this example we will use the following sample route:

`/groups/admin/list`

The first part of this route (`/groups`) is called the prefix and is configurable from within the Extensions module.
Click on the wrench icon in the extension's row and change the value of the **URL** to your preference. However,
there are restrictions on this value; you cannot select a value that is the same as any subdirectory within the
`public` folder of your Zikula installation.

## Adding custom routes

The route module allows to add arbitrary rules to the system which are persisted in the database.
This can be useful for example to:

- define redirects for obsolete urls
- add short urls pointing to more complex ones

Note for managing custom routes it is recommended to read the [Routing docs](https://symfony.com/doc/current/routing.html)
of Symfony.
