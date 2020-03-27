---
currentMenu: home
---
# Zikula Core - Application Framework

Zikula Core is an Application Framework which extends Symfony 5.x and includes technologies
fostering a dynamic modular development paradigm and Twig-based theming system which allows for rapid
website and application development. See the [features section](#features-of-zikula) below for more information.

Zikula also features an [MDSD](https://en.wikipedia.org/wiki/Model-driven_engineering) tool for rapid prototyping
and module development called [ModuleStudio](https://modulestudio.de/en/) or MOST.

Zikula can quickly become a Content Management System utilizing community-driven modules.

## Some features of Zikula Core system

### Foundation

- Based on Symfony 5.x which provides stability, continuity and extensibility
- Uses Doctrine for persisting data
- Uses Twig as template engine
- Uses Mailer Component for email handling

### Extensions and bundles

- Modular development system
  - Modules are bundles which can be installed/uninstalled during runtime
- Hook system for connecting module features with each other
  - Hooks are a dynamic event dispatcher, allowing the administrator to choose which listeners respond to events.

### Themes and templating

- Twig-based theme engine for site-wide theming
  - Themes are bundles which can be installed/uninstalled during runtime
- Integration of common frontend technologies
  - Twitter Bootstrap
  - Font Awesome
  - jQuery

### Users and security

- Users and Groups management
  - OAuth integration
  - Extensible/customizable User authorization API
- Dynamic user rights/permissions management by group
- Included add-ons
  - OAuth module (allows login via Facebook, Github, Google or LinkedIn credentials)
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

## Further components

### ModuleStudio (MOST)

- Model-Driven Software Development tool
  - rapid prototyping
  - easy customization
  - quick updating
- Creates models describing your extensions
- Generates the Zikula module implementation
- Read more at the [project's website](https://modulestudio.de/en)

### Community Driven Modules

See [Extensions > Content](Extensions/Content.md) and [Extensions > Other](Extensions/Other.md).
