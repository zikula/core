Zikula Core Roadmap
-------------------

Zikula manages its releases through a time-based model and follows the [Semantic Versioning](http://semver.org/) strategy. 
A new Zikula patch version comes out about once per month. The Core-1.5 branch and the Core-2.0 branch
are synced so that patch releases for both occur simultaneously.

Current Stable Version
----------------------

2.0.x (inc. Symfony 3.x)

Development Version
-------------------

2.1.0 - Currently Scheduled for August 2018 but this is not a fixed release yet

Long Term Support Versions
--------------------------

1.5.x - LTS (inc. Symfony 2.8.x LTS)
  - Bug Fix support ending **31 August 2018**
  - Security Fix support ending **31 August 2019** (EOL)
  - Technical Support (without commits) ending **31 August 2020**
  - Paid support beyond the posted dates _may_ be available. Please contact the team for information.


Our Backward Compatibility Promise
----------------------------------

Ensuring smooth upgrades of your projects is our first priority.
That's why we promise you backward compatibility (BC) for all minor Zikula releases. You probably recognize this
strategy as [Semantic Versioning](http://semver.org/). In short, Semantic Versioning means that only major releases
(such as 3.0, 4.0 etc.) are allowed to break backward compatibility. Minor releases (such as 2.1, 2.2 etc.) may
introduce new features, but must do so without breaking the existing API of that release branch.

CAUTION
-------
This promise was introduced with Zikula Core-1.5 and 2.0 and does not apply to previous versions of Zikula.
However, backward compatibility comes in many different flavors. In fact, almost every change that we make to the Core
can potentially break an application. For example, if we add a new method to a class, this will break an application which
extended this class and added the same method, but with a different method signature.

Also, not every BC break has the same impact on application code. While some BC breaks require you to make significant
changes to your classes or your architecture, others are fixed as easily as changing the name of a method.

Zikula includes the Symfony framework which contains the same [Promise](https://symfony.com/doc/current/contributing/code/bc.html).
The Zikula team works to maintain this exact same promise while including Value Added Code to the Symfony Framework.
Any BC break introduced in the Symfony Framework (however inadvertently) will also be included in Zikula Core. Please see
the above referenced Symfony link for details on working with Symfony (and Zikula) Code to ensure smooth upgrades between
minor Zikula versions.


_Much of the language above has been heavily copied from the Symfony project. All credit to the Symfony authors, with gratitude._

