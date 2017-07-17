Features of Zikula
==================

### Foundation

 - Based on Symfony 3.x which provides stability, continuity and extensibility
 - Uses Doctrine for persisting data
 - Uses Twig as template engine
 - Uses SwiftMailer for email handling

### Extensions and bundles

 - Modular development system
   - Modules are bundles which can be installed/uninstalled during runtime
 - Hook system for connecting module features with each other
   - Hooks are a dynamic event dispatcher, allowing the administrator to choose which listeners respond to events.

### Themes and templating

 - Twig-based theme engine for site-wide theming
   - Themes are bundles which can be installed/uninstalled during runtime
 - Integration of de-facto frontend technologies
   - Twitter Bootstrap
   - Font Awesome
   - jQuery

### Users and security

 - Users and Groups management
   - OAuth integration
   - Extensible/customizable User authorization API
 - Dynamic user rights/permissions management by group
 - Included add-ons
   - OAuth module (allows login via Facebook, Github, Google or LinkedIn Credentials)
   - Profile module (user profile information)
   - Legal module (TOS, Age Check, etc)

### Administration

 - Centralized site administration interface
 - Multi-language & translation support
 - Centralized category management
 - Dynamic and flexible content block creation
 - Centralized search functionality
 - Menu system based on KnpMenu

### Developer gems

 - Centralized category assignments by entity
 - Several distinct APIs for feature utilization
 - Imagine image manipulation library integration
 - CLI based module skeleton generator
 - Multi-Sites capability (one core-base, multiple custom DB)


### ModuleStudio (MOST)

 - Model-Driven Software Development tool
    - rapid prototyping
    - easy customization
    - quick updating
 - Creates models describing your extensions
 - Generates the Zikula module implementation
 - Read more at the [project's website](http://modulestudio.de/en)

### Community Driven Modules

 - [Pages](https://github.com/zikula-modules/Pages) (Basic content pages)
 - [Scribite](https://github.com/zikula-modules/Scribite) (WYSIWYG JS Editor integration)
 - [Dizkus](https://github.com/zikula-modules/DizkusModule) (Forum)
 - [MediaModule](https://github.com/cmfcmf/MediaModule) (Media management)
 - [Formicula](https://github.com/zikula-ev/Formicula) (Contact form generator)
 - [Piwik](https://github.com/Guite/Piwik) (Piwik integration)

##### Legacy Modules not yet converted but intended to be done

 - [News](https://github.com/zikula-modules/News) (Blog system)
 - [Content](https://github.com/zikula-modules/Content) (Advanced content pages)
 - [Multisites](https://github.com/zikula-modules/Multisites) (Multisites system)
 - [PostCalendar](https://github.com/craigh/PostCalendar) (Calendar and event system)
 - [Tag](https://github.com/craigh/Tag) (Tagging hook)
 - [EZComments](https://github.com/zikula-modules/EZComments) (Comments hook)
 - Captcha (Anti-spam hook)
 - Several others...
