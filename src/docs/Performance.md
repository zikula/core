Performance optimisation
========================
  1. [Introduction](#introduction)
  2. [Use DQL Joins](#usedqljoins)
  3. [Use the Twig service](#usethetwigservice)
  4. [Use namespace notation for templates](#usenamespacenotation)


<a name="introduction"></a>
Introduction
------------
This document collects some developer hints for improving performance of your code.

<a name="usedqljoins"></a>
Use DQL Joins
-------------
Doctrine fetches related items lazily by default. While this saves performance in cases you do not need them,
this behaviour becomes worse if you access a few relationships though. The reason is that several single queries
are slower than one combined query.
So if you know that you need access to some relationships add corresponding DQL joins to your query builder.
Read more about this [here](http://doctrine-orm.readthedocs.org/en/latest/reference/dql-doctrine-query-language.html#joins).

<a name="usethetwigservice"></a>
Use the Twig service
--------------------
Since Zikula always uses Twig templates you should use the `twig` service instead of the `templating` abstraction.
The latter is just overhead we do not need.
Read more about this [here](http://symfony.com/blog/new-in-symfony-2-7-twig-as-a-first-class-citizen).

<a name="usenamespacenotation"></a>
Use namespace notation for templates
------------------------------------
You should always use namespaced pathes for your templates which is faster than the normal notation because Twig
does not need to convert it when resolving/reading the template file.
Read more about this [here](http://symfony.com/doc/current/cookbook/templating/namespaced_paths.html).
