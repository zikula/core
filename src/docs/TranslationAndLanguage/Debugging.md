Debugging Translations
======================

Symfony comes with `bin/console debug:translation` command line tool to debug translations.
**This tool work only with Symfony and Zikula Core-2.0 translation paths.**

Example output for more information please check http://symfony.com/doc/current/book/translation.html#debugging-translations

	%> php bin/console debug:translation pl KaikmediaPagesModule
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


## Important notes
From Symfony translator documentation
> Each time you create a new translation resource (or install a bundle that includes a translation resource), be sure to
clear your cache so that Symfony can discover the new translation resources.
