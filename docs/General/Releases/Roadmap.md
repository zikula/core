---
currentMenu: roadmap-releases
---
# Zikula Core Roadmap

Zikula manages its releases through a time-based model and follows the [Semantic Versioning](https://semver.org/) strategy. 

## Current Stable Version

3.0.x (inc. Symfony 5.x)

## Development Version

3.* - Currently being worked on.

## Long Term Support versions

3.* - exact version undetermined at this time

- Paid support beyond the posted dates _may_ be available. Please contact the team for information.

## Our backward compatibility promise

Ensuring smooth upgrades of your projects is our first priority.
That's why we promise you backward compatibility (BC) for all minor Zikula releases. You probably recognize this
strategy as [Semantic Versioning](https://semver.org/). In short, Semantic Versioning means that only major releases
(such as 3.0, 4.0 etc.) are allowed to break backward compatibility. Minor releases (such as 2.1, 2.2 etc.) may
introduce new features, but must do so without breaking the existing API of that release branch.

This promise was introduced with Zikula Core-1.5 and 2.0 and does not apply to previous versions of Zikula.

However, backward compatibility comes in many different flavors. In fact, almost every change that we make to the Core
can potentially break an application. For example, if we add a new method to a class, this will break an application which
extended this class and added the same method, but with a different method signature.

Also, not every BC break has the same impact on application code. While some BC breaks require you to make significant
changes to your classes or your architecture, others are fixed by changing the name of a method.

Zikula includes the Symfony framework which contains the same [Promise](https://symfony.com/doc/current/contributing/code/bc.html).
The Zikula team works to maintain this exact same promise while including Value Added Code to the Symfony Framework.
Any BC break introduced in the Symfony Framework (however inadvertently) will also be included in Zikula Core. Please see
the above referenced Symfony link for details on working with Symfony (and Zikula) Code to ensure smooth upgrades between
minor Zikula versions.

_Much of the language above has been heavily copied from the Symfony project. All credit to the Symfony authors, with gratitude._

## Overview of recent versions

This section shows the important major steps in the Zikula roadmap.

### Core 3.0

Released: 25 June, 2020

Standards:

- [x] Symfony 5.x
- [x] Twig 3.x
- [x] Bootstrap 4.4.1
- [x] FontAwesome 5.12.0
- [x] jQuery 3.4.1
- [x] require PHP >= 7.2.5

New features:

- View the [Changelog](https://github.com/zikula/core/blob/master/CHANGELOG-3.0.md)

### Core 2.0

EOL November 2019 - last release was [2.0.15](https://github.com/zikula/core/releases/tag/2.0.15).

Standards:

- [x] removal of all 1.x Backward-compatibility
  - [x] remove Prototype/Scriptaculous
  - [x] remove Doctrine 1
  - [x] remove Smarty
  - [x] remove DBUtil/DBObject
  - [x] remove FormLib
- [x] Symfony 3.3
- [x] jQuery 2.1 (jQueryUI 1.12)
- [x] Bootstrap 3
- [x] FontAwesome 4.7
- [x] require PHP >= 5.5.9
- [x] move all extensions to PSR-4, remove support for PSR-0 ([#2424](https://github.com/zikula/core/issues/2424))
- [x] remove icon images and replace with font-awesome icons

New features:

- [x] fully implement Twig ([#1753](https://github.com/zikula/core/issues/1753))
  - [x] implement Twig theming system
  - [x] fully implement gettext translation in twig templates 
- [x] fully implement Symfony Forms ([#2034](https://github.com/zikula/core/issues/2034))
- [x] replace static Util classes with services accessible from container.

### Core 1.5 LTS

EOL December 2018 - last release was [1.5.9](https://github.com/zikula/core/releases/tag/1.5.9).

- Maintains BC with 1.3 and 1.4 as much as possible.
- Maintains feature parity with Core-2.0.x as much as possible.
- Full integration of Symfony 2.8.x

### Core 1.4

EOL February 2017 - last release was [1.4.6](https://github.com/zikula/core/releases/tag/1.4.6).

Standards:

- [x] maintain BC with 1.3.x

New features:

- [x] Basic integration of Symfony
  - [x] Symfony routing
  - [x] Symfony events
  - [x] Full Doctrine integration
  - [x] Gettext Translation integration
- [x] Namespaced extensions (psr-0 & psr-4)
- [x] Integration of Composer and use of dependencies
- [x] add hidden panel admin interface

### Core 1.3

EOL January 2017 - last release was [1.3.12](https://github.com/zikula/core/releases/tag/1.3.12).
