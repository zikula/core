Zikula Core - Application Framework
===================================

Zikula Core is a web based application framework, fully extensible by
Modules, plugins and themes.

For more information visit http://zikula.org/


Installing
----------

Run `composer self-update && composer update`. Composer can
be downloaded from http://getcomposer.org/

.. note::

  If you store Composer in the root of the Zikula Core checkout, please
  rename it from `composer.phar` to `composer` to avoid your IDE reading
  the package contents.


Contributing
------------

Pull requests are welcome, please see https://github.com/zikula/core/wiki/Contributing

Pull requests should use the following description template. Use [WIP] for work in progress.

```
| Q             | A
| ------------- | ---
| Bug fix?      | [yes|no]
| New feature?  | [yes|no]
| BC breaks?    | [yes|no]
| Deprecations? | [yes|no]
| Tests pass?   | [yes|no]
| Fixed tickets | [comma separated list of tickets fixed by the PR]
| License       | MIT
| Doc PR        | [The reference to the documentation PR if any]
```

If there are any todos, please use this template:

```
- [ ] fix the tests as they have not been updated yet
- [ ] submit changes to the documentation
- [ ] document the BC breaks
```

