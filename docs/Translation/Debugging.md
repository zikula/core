---
currentMenu: translation
---
# Debugging translations

Symfony comes with `bin/console debug:translation` command line tool to debug translations.

Example output:

```
%> php bin/console debug:translation pl --domain=mydomain
+----------+-------------+----------------------+
| State(s) | Id          | Message Preview (pl) |
+----------+-------------+----------------------+
| o        | Pages       | Strony               |
| o        | Page        | Strona               |
| o        | pages       | strony               |
| o        | page        | strona               |
| o        | read more   | czytaj więcej        |
| o        | title       | tytuł                |
| o        | description | opis                 |
+----------+-------------+----------------------+

Legend:
    x Missing message
    o Unused message
    = Same as the fallback message
```

For more information please check [Debugging Translations](https://symfony.com/doc/current/translation.html#debugging-translations).

## Important notes

From Symfony translator documentation:

> Each time you create a new translation resource (or install a bundle that includes a translation resource), be sure to
clear your cache so that Symfony can discover the new translation resources.
