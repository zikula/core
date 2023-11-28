[![Build status](https://github.com/zikula/core/workflows/Build%20and%20test/badge.svg)](https://github.com/zikula/core/actions?query=workflow%3A"Build+and+test")
[![StyleCI](https://styleci.io/repos/781544/shield?branch=main)](https://styleci.io/repos/781544)
[![Scrutinizer](https://scrutinizer-ci.com/g/zikula/core/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/zikula/core/)

# Zikula Core - Application Framework

Zikula Core is an Application Framework which extends Symfony 6.x and includes technologies fostering a dynamic modular development paradigm which allows for rapid application development. See the [features](/docs/README.md) list for more information.

Zikula also features an [MDSD](https://en.wikipedia.org/wiki/Model-driven_engineering) tool for rapid prototyping and bundle development called [ModuleStudio](https://modulestudio.de/en/) or MOST.

## Current status: dormant

The further development of Zikula is more or less inactive. Changes are still being made from time to time, but a release is not expected any time soon.

For a long time, Zikula's philosophy was to fulfill all wishes. For several years, it therefore moved between the worlds of content management, community system, framework and so on. This has led to the system becoming bigger and bigger instead of really cutting out old habits. Unfortunately, this paralyzed the project so that capacities could not keep up with expectations.

## Zikula 3.x - the last of its kind

The releases of Zikula 3, which were based on Symfony 5, still bear witness to this old architecture. They can still be used, but they are no longer maintained. Bei Fragen schreibt gerne in die Github discussions.

## Zikula 4.0 - Focus on own strengths (Work in Progress)

The idea of Version 4 of Zikula is that it will fundamentally change the way Zikula works. Probably the most important change is that Zikula will no longer include Symfony and various third-party extensions, but will rather provide extensions for Symfony. Zikula bundles can then be included like any other extension using Composer and Flex. This unties some knots by making it easier to use the Symfony ecosystem instead of having to build solutions for all sorts of concerns in Zikula itself. This means a serious change: at the moment you only get Zikula completely or not at all. With core version 4, you can then start with a normal Symfony and add some Zikula bundles afterwards if you want to - just like you are used to with all kinds of other bundles. So things become much more compatible with each other.

The old core system was therefore greatly streamlined through deconstruction. For example, extensions are now only managed via Composer. UI-based settings, dynamic menus in the admin area, the block system, the outdated search module and many other things have also been removed. The hook system, to name another example, is also a thing of the past, as it basically just created a redundant additional layer to the Symfony event system. During this consolidation phase, things that already exist in the Symfony ecosystem are being dropped to avoid maintaining redundant components. This primarily includes various content management solutions.

For the future, we need to differentiate sharply and ask the question "make or buy": Where does it really make sense to invest our energy? Where is the added value of Zikula Core? How can we make even better use of the Symfony ecosystem without giving up our previous flexibility?  After the obvious things have been dropped, the remaining functions are sifted through and reorganized with the aim of creating a manageable number of independent (i.e. as decoupled as possible) bundles that can also be used individually.

We have also removed some other home-made items in favor of existing solutions. These include in particular [EasyAdminBundle](https://github.com/EasyCorp/EasyAdminBundle), [NucleosUserBundle](https://github.com/nucleos/NucleosUserBundle/) and [NucleosProfileBundle](https://github.com/nucleos/NucleosProfileBundle/). However, the integration of all these functions in detail has not yet been completed.

As mentioned above, development is currently progressing rather slowly. If you have ambitions to contribute to Zikula 4, please write to us.
