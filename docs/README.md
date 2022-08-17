---
currentMenu: home
---
# Zikula Core - Application Framework

Zikula Core is an Application Framework which extends Symfony 5.x and includes technologies
fostering a dynamic modular development paradigm and Twig-based theming system which allows for rapid
website and application development. See the [features section](#features-of-zikula) below for more information.

Zikula also features an [MDSD](https://en.wikipedia.org/wiki/Model-driven_engineering) tool for rapid prototyping
and module development called [ModuleStudio](https://modulestudio.de/en/) or MOST.

## Some features of Zikula Core system

### Foundation

- Based on Symfony 5.x which provides stability, continuity and extensibility
- Uses Doctrine for persisting data
- Uses Twig as template engine
- Uses Mailer Component for mail handling

### Themes and templating

- Twig-based theme engine for site-wide theming
- Integration of common frontend technologies
  - Twitter Bootstrap
  - Font Awesome

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
- Menu system based on KnpMenu

### Developer gems

- Centralized category assignments by entity
- Several distinct APIs for feature utilization
- Imagine image manipulation library integration
- CLI based module skeleton generator

## Further components

### ModuleStudio (MOST)

- Model-Driven Software Development tool
  - rapid prototyping
  - easy customization
  - quick updating
- Creates models describing your extensions
- Generates the Symfony bundle implementation
- Read more at the [project's website](https://modulestudio.de/en)

### Relevant modules

[Legal](https://github.com/zikula-modules/Legal)
: Provides website TOS/AUP, privacy policy, and other legal documents.
: Included in Core distribution.

[Profile](https://github.com/zikula-modules/Profile)
: User profile extension and avatar management.
: Included in Core distribution.
: See [User profiles](../AccessControl/Users/index.md#user-profiles) for further details.

[OAuth](https://github.com/zikula/OAuth)
: OAuth authentication provider.
: Included in Core distribution.
: See [Authentication / OAuth](../AccessControl/Authentication/index.md#oauth) for further details.

[StaticContent](https://github.com/zikula-modules/StaticContent)
: Display static content simply.
: Included in Core distribution.
