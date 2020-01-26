# Performance optimisation

1. [Introduction](#introduction)
2. [Use DQL Joins](#use-dql-joins)
3. [Use the Twig service](#use-the-twig-service)
4. [Use namespace notation for templates](#use-namespace-notation-for-templates)

## Introduction

This document collects some developer hints for improving performance of your code.

## Use DQL Joins

Doctrine fetches related items lazily by default. While this saves performance in cases you do not need them,
this behaviour becomes worse if you access a few relationships though. The reason is that several single queries
are slower than one combined query.

So if you know that you need access to some relationships add corresponding DQL joins to your query builder.
Read more about this [here](https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/reference/dql-doctrine-query-language.html#joins).

## Use the Twig service

Since Zikula always uses Twig templates you should use the `twig` service instead of the `templating` abstraction.
The latter is just overhead we do not need.  
Read more about this [here](https://symfony.com/blog/new-in-symfony-2-7-twig-as-a-first-class-citizen).

## Use namespace notation for templates

You must always use namespaced pathes for your templates which is faster than the normal notation because Twig
does not need to convert it when resolving/reading the template file.

So for example use `@AcmeFooModule/Person/index.html.twig` instead of `AcmeFooModule:Person:index.html.twig`.
