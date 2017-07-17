Routes and custom urls
======================

Changing module urls
--------------------

The urls of a module can be customized by the site admin. For this example we will use the following sample route:

`/theme/admin/config/`

The first part of this route (`/theme`) is called the prefix and is configurable from within the Extensions module.
Click on the wrench icon in the module's row and change the value of the **Module URL** to your preference. However,
there are restrictions on this value; you cannot select a value that is the same as any subdirectory within your
Zikula installation. So, at least the following names are not allowed: `app`, `config`, `docs`, `images`, `javascript`,
`lib`, `modules`, `plugins`, `style`, `system`, `themes`, `userdata`, `vendor`, `web`.

Adding custom routes
--------------------

The route module allows to add arbitrary rules to the system which are persisted in the database.
This can be useful for example to:

- define redirects for obsolete urls
- add short urls pointing to more complex ones

Note for managing custom routes it is recommended to read the [Routing docs](http://symfony.com/doc/current/routing.html)
of Symfony.
