---
currentMenu: dev-general
---
# Refactoring for 3.0

## Modules
### File Structure
The file structure of Zikula 3.0 is much different than the structure in 2.0. Intead of extensions being kept
in /modules or /themes, they are placed into /src/extensions. Much of the code that handles rendering pages
is outside the public folder that the webserver can see. To serve your html pages, you will need to navigate
to zikula_folder/src/ to serve pages. A virtual host or modrewrite may be needed on your production server. 

### Composer file

Add the following capability for defining the (default) admin icon:

```json
    …
    "extra": {
        "zikula": {
            …
                "icon": "fas fa-star"
            },
        },
    },

```
You can remove the old `admin.png` file afterwards.
In addtion, it is recommended that you increment your module version one full point (i.e. 3.0.0 -> 4.0.0). Upgrading to core 3.0 
is not backward compatible with core 2.0. Also, change the php version to >=7.2.5 (or higher if you module requires it) and the 
core-compatibility to >=3.0.0.
```{
  ...
  "version": "5.0.0",
  ...
  "require": {
    "php": ">7.2.5",
    "zikula/core-bundle": "3.*"
  },
  "extra": {
    "zikula": {
      ...
      "core-compatibility": ">=3.0.0",
      ...
      "icon": "fas fa-star",
      ...
    }
  }
}
  ```

### Interfaces

In general, interfaces and apis implement argument type-hinting in all methods. This can break an implementation of said
interfaces, etc. Extensions must update their implementation of any core/system interface to adhere to the new signature. For example, 
if you are upgrading a Block class, a member function line goes from:

```public function display($properties) : {```

to

```public function display(array $properties) :string {```

You can let php know to enforce strict type-hinting by putting ```declare(strict_types=1);``` at the top of each php file just after the ```<?php```
This is not required of other code, but strongly recommended.
### Service registration

Please use `autowire` and `autoconfigure` as this will magically solve most issues.
Further information can be found [in Symfony docs](https://symfony.com/doc/current/service_container/3.3-di-changes.html#step-1-adding-defaults).

Module services should be registered by their classname (automatically as above) and not with old-fashioned
`service.class.dot.notation`.

Change `.yml` suffixes to `.yaml` (e.g. `routing.yaml`) and update `Extension.php` class.

### Blocks

BlockHandler classes must implement `Zikula\BlocksModule\BlockHandlerInterface` as in Core-2.0 but there is no longer
a need to tag these classes in your services file as they are auto-tagged. Also - as above, the classname should be
used as the service name.

### Extension menus

`Zikula\Core\LinkContainer\LinkContainerCollector` and `Zikula\Core\LinkContainer\LinkContainerInterface` have been
removed. Extension menus are now implemented using Knp Menu instead. See docs and system modules for further
information and examples.

## Sending mails

The `MailerApi` and `SwiftMailer` have been removed in favour of the Symfony Mailer component.

You will see the new interface is more intuitive. Start by injecting `Symfony\Component\Mailer\MailerInterface` and reading through the docs linked further below.

Here is a first example of how to migrate the code for sending mails:

```php
// OLD

use Swift_Message;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;

class MyService
{
    // ...

    public function send()
    {
        // ...
        $message = new Swift_Message();
        $message->setFrom([$adminMail => $siteName]);
        $message->setTo([$user->getEmail() => $user->getUname()]);
        $this->mailer->sendMessage($message, $title, $htmlBody, '', true);
    }
}

// NEW

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MyService
{
    // ...

    public function send()
    {
        // ...
        try {
            $email = (new Email())
                ->from(new Address($adminMail, $siteName))
                ->to(new Address($user->getEmail(), $user->getUname()))
                ->subject($title)
                ->html($htmlBody)
            ;
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            // ...
        }
}
```

For more information please refer to [Mailer docs](../../Configuration/Mailer/README.md).

## Translations

All custom Zikula translation mechanisms have been removed in favour of Symfony's native translation system.

For more information please refer to [Translation docs](../../Translation/README.md).

## Namespaces

Several namespaces and classes have been relocated.

- `Zikula\Bridge\HttpFoundation\` moved to `Zikula\Bundle\CoreBundle\HttpFoundation\Session\`.
- `Zikula\Bundle\CoreBundle\Bundle\AbstractCoreModule` moved into `Zikula\ExtensionsModule\`.
- `Zikula\Bundle\CoreBundle\Bundle\AbstractCoreTheme` moved into `Zikula\ExtensionsModule\`.
- `Zikula\Bundle\CoreBundle\Bundle\Bootstrap` moved and renamed to `Bundle\CoreBundle\Helper\PersistedBundleHelper`.
- `Zikula\Bundle\CoreBundle\Bundle\Helper\BootstrapHelper` moved and renamed to `Bundle\CoreBundle\Helper\BundlesSchemaHelper`.
- `Zikula\Bundle\CoreBundle\Bundle\MetaData` moved into `Zikula\Bundle\CoreBundle\Composer\`.
- `Zikula\Bundle\CoreBundle\Bundle\Scanner` moved into `Zikula\Bundle\CoreBundle\Composer\`.
- `Zikula\Common\Collection\` moved into `Zikula\Bundle\CoreBundle\Collection\`.
- `Zikula\Common\Content\` moved into `Zikula\ExtensionsModule\ModuleInterface\Content\`.
- `Zikula\Common\MultiHook\` moved into `Zikula\ExtensionsModule\ModuleInterface\MultiHook\`.
- `Zikula\Common\Translator\` moved into `Zikula\Bundle\CoreBundle\Translation\`.
- `Zikula\Common\ColumnExistsTrait` moved into `Zikula\Bundle\CoreBundle\Doctrine\`.
- `Zikula\Core\AbstractBundle` moved and renamed to `Zikula\ExtensionsModule\AbstractExtension`
- `Zikula\Core\AbstractExtensionInstaller` moved into `Zikula\ExtensionsModule\Installer\`.
- `Zikula\Core\AbstractModule` moved into `Zikula\ExtensionsModule\`.
- `Zikula\Core\Controller\` moved into `Zikula\Bundle\CoreBundle\Controller\`.
- `Zikula\Core\CoreEvents` was split into `Zikula\Bundle\CoreBundle\CoreEvents` and `Zikula\ExtensionsModule\ExtensionEvents`.
- `Zikula\Core\Doctrine\` moved into `Zikula\Bundle\CoreBundle\Doctrine\`.
- `Zikula\Core\Event\GenericEvent` moved into `Zikula\Bundle\CoreBundle\Event\`.
- `Zikula\Core\Event\ModuleStateEvent` moved into `Zikula\ExtensionsModule\Event\`.
- `Zikula\Core\ExtensionInstallerInterface` moved into `Zikula\ExtensionsModule\Installer\`.
- `Zikula\Core\InstallerInterface` moved into `Zikula\ExtensionsModule\Installer\`.
- `Zikula\Core\Response\` moved into `Zikula\Bundle\CoreBundle\Response\`.
- `Zikula\Core\RouteUrl` moved into `Zikula\Bundle\CoreBundle\`.
- `Zikula\Core\UrlInterface` moved into `Zikula\Bundle\CoreBundle\`.
- `Zikula\ThemeModule\AbstractTheme` moved into `Zikula\ExtensionsModule\`.

## Events

Several events have been changed which requires updates in corresponding listeners.

- Core bundles
  - `Zikula\Bundle\FormExtensionBundle\Event\FormTypeChoiceEvent` no longer extends `Symfony\Contracts\EventDispatcher\Event`.
    - Also, listeners should respond to the event _class_, the static property `NAME` is removed.
  - `Zikula\Bundle\HookBundle\Hook\Hook` (and all its subclasses) no longer extends `Symfony\Contracts\EventDispatcher\Event`.
- Blocks module
  - `get.pending_content` which was formerly in CoreBundle is removed in favor of `Zikula\BlocksModule\Event\PendingContentEvent`
    - `Zikula\Bundle\CoreBundle\Collection\Collectible\PendingContentCollectible` has changed its namespace to
      - `Zikula\BlocksModule\Collectible\PendingContentCollectible`
- Extensions module
  - `Zikula\ExtensionsModule\ExtensionEvents::REGENERATE_VETO` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionListPreReSyncEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::INSERT_VETO` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionEntityPreInsertEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::REMOVE_VETO` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionEntityPreRemoveEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::UPDATE_STATE` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPreStateChangeEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::EXTENSION_INSTALL` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPostInstallEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::EXTENSION_POSTINSTALL` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPostCacheRebuildEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::EXTENSION_UPGRADE` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPostUpgradeEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::EXTENSION_ENABLE` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPostEnabledEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::EXTENSION_DISABLE` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPostDisabledEvent`.
  - `Zikula\ExtensionsModule\ExtensionEvents::EXTENSION_REMOVE` is removed in favor of `Zikula\ExtensionsModule\Event\ExtensionPostRemoveEvent`.
  - `Zikula\ExtensionsModule\Event\ConnectionsMenuEvent` no longer extends `Symfony\Contracts\EventDispatcher\Event`.
- Groups module
  - `Zikula\GroupsModule\GroupEvents::GROUP_CREATE` is removed in favor of `Zikula\GroupsModule\Event\GroupPostCreatedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_UPDATE` is removed in favor of `Zikula\GroupsModule\Event\GroupPostUpdatedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_PRE_DELETE` is removed in favor of `Zikula\GroupsModule\Event\GroupPreDeletedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_DELETE` is removed in favor of `Zikula\GroupsModule\Event\GroupPostDeletedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_ADD_USER` is removed in favor of `Zikula\GroupsModule\Event\GroupPostUserAddedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_REMOVE_USER` is removed in favor of `Zikula\GroupsModule\Event\GroupPostUserRemovedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_APPLICATION_PROCESSED` is removed in favor of `Zikula\GroupsModule\Event\GroupApplicationPostProcessedEvent`
  - `Zikula\GroupsModule\GroupEvents::GROUP_GROUP_NEW_APPLICATIONCREATE` is removed in favor of `Zikula\GroupsModule\Event\GroupApplicationPostCreatedEvent`
- Menu module
  - `Zikula\MenuModule\Event\ConfigureMenuEvent` no longer extends `Symfony\Contracts\EventDispatcher\Event`.
    - Also, listeners should respond to the event _class_, the static property `POST_CONFIGURE` is removed.
- Routes module
  - `new.routes.avail` event is replaced by `Zikula\RoutesModule\Event\RoutesNewlyAvailableEvent`
- SecurityCenter module
  - `Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface::HTML_STRING_FILTER` is removed in favor of `Zikula\SecurityCenterModule\Event\FilterHtmlEvent`
- Theme module
  - `Zikula\ThemeModule::PRE_RENDER` is removed in favor of `Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent` (Same event, simply rename in Listener). 
  - `Zikula\ThemeModule::POST_RENDER` is removed in favor of `Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent` (Same event, simply rename in Listener).
  - `Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent` no longer extends `Symfony\Contracts\EventDispatcher\Event`.
  - `Zikula\ThemeModule\Bridge\Event\TwigPreRenderEvent` no longer extends `Symfony\Contracts\EventDispatcher\Event`.
- Users module
  - `Zikula\UsersModule\RegistrationEvents::REGISTRATION_STARTED` has been deleted.
  - `Zikula\UsersModule\RegistrationEvents::CREATE_REGISTRATION` is removed in favor of `Zikula\UsersModule\Event\RegistrationPostCreatedEvent`.
  - `Zikula\UsersModule\RegistrationEvents::DELETE_REGISTRATION` is removed in favor of `Zikula\UsersModule\Event\RegistrationPostDeletedEvent`.
  - `Zikula\UsersModule\RegistrationEvents::REGISTRATION_SUCCEEDED` is removed in favor of `Zikula\UsersModule\Event\RegistrationPostSuccessEvent`.
  - `Zikula\UsersModule\RegistrationEvents::FORCE_REGISTRATION_APPROVAL` is removed in favor of `Zikula\UsersModule\Event\RegistrationPostApprovedEvent`.
  - `Zikula\UsersModule\RegistrationEvents::UPDATE_REGISTRATION` is removed in favor of `Zikula\UsersModule\Event\RegistrationPostUpdatedEvent`.
  - `Zikula\UsersModule\RegistrationEvents::FULL_USER_CREATE_VETO` is removed in favor of `Zikula\UsersModule\Event\ActiveUserPreCreatedEvent`.
  - `Zikula\UsersModule\RegistrationEvents::REGISTRATION_FAILED` has been deleted.
  - `Zikula\UsersModule\UserEvents::DISPLAY_VIEW` is removed in favor of `Zikula\UsersModule\Event\UserAccountDisplayEvent`
  - `Zikula\UsersModule\UserEvents::CREATE_ACCOUNT` is removed in favor of `Zikula\UsersModule\Event\ActiveUserPostCreatedEvent`.
  - `Zikula\UsersModule\UserEvents::UPDATE_ACCOUNT` is removed in favor of `Zikula\UsersModule\Event\ActiveUserPostUpdatedEvent`.
  - `Zikula\UsersModule\UserEvents::DELETE_ACCOUNT` is removed in favor of `Zikula\UsersModule\Event\ActiveUserPostDeletedEvent`.
  - `Zikula\UsersModule\UserEvents::DELETE_VALIDATE` has been deleted.
  - `Zikula\UsersModule\UserEvents::DELETE_FORM` is removed in favor of `Zikula\UsersModule\Event\DeleteUserFormPostCreatedEvent`.
  - `Zikula\UsersModule\UserEvents::DELETE_PROCESS` is removed in favor of `Zikula\UsersModule\Event\DeleteUserFormPostValidatedEvent`.
  - `Zikula\UsersModule\UserEvents::EDIT_FORM` is removed in favor of `Zikula\UsersModule\Event\EditUserFormPostCreatedEvent`
    - The event class changed from `Zikula\UsersModule\Event\UserFormAwareEvent` to `EditUserFormPostCreatedEvent`
  - `Zikula\UsersModule\UserEvents::EDIT_FORM_HANDLE` is removed in favor of `Zikula\UsersModule\Event\EditUserFormPostValidatedEvent`
    - The event class changed from `Zikula\UsersModule\Event\UserFormDataEvent` to `EditUserFormPostValidatedEvent`
  - `Zikula\UsersModule\UserEvents::FORM_SEARCH` has been deleted.
  - `Zikula\UsersModule\UserEvents::FORM_SEARCH_PROCESS` has been deleted.
  - `Zikula\UsersModule\UserEvents::CONFIG_UPDATED` has been deleted.
  - `Zikula\UsersModule\AccessEvents::AUTHENTICATION_FORM` is removed in favor of `Zikula\UsersModule\Event\LoginFormPostCreatedEvent`
    - The event class changed from `Zikula\UsersModule\Event\UserFormAwareEvent` to `LoginFormPostCreatedEvent`
  - `Zikula\UsersModule\AccessEvents::AUTHENTICATION_FORM_HANDLE` is removed in favor of `Zikula\UsersModule\Event\LoginFormPostValidatedEvent`
    - The event class changed from `Zikula\UsersModule\Event\UserFormDataEvent` to `LoginFormPostValidatedEvent`
  - `Zikula\UsersModule\AccessEvents::LOGIN_STARTED` has been deleted.
  - `Zikula\UsersModule\AccessEvents::LOGIN_VETO` is removed in favor of `Zikula\UsersModule\Event\UserPreLoginSuccessEvent`
  - `Zikula\UsersModule\AccessEvents::LOGIN_SUCCESS` is removed in favor of `Zikula\UsersModule\Event\UserPostLoginSuccessEvent`
  - `Zikula\UsersModule\AccessEvents::LOGIN_FAILED` is removed in favor of `Zikula\UsersModule\Event\UserPostLoginFailureEvent`
  - `Zikula\UsersModule\AccessEvents::LOGOUT_SUCCESS` is removed in favor of `Zikula\UsersModule\Event\UserPostLogoutSuccessEvent`

## Twig

### Classes

Use namespaced classes because the non-namespaced classes have been removed.

For example:

| Old | New |
| --- | --- |
| `\Twig_Extension` | `Twig\Extension\AbstractExtension` |
| `\Twig_SimpleFunction` | `Twig\TwigFunction` |
| `\Twig_SimpleFilter` | `Twig\TwigFilter` |
| `\Twig_SimpleTest` | `Twig\TwigTest` |

and so on…

### Template paths

- Change all template names from e.g. `Bundle:Controller:Action.html.twig` to `@Bundle/Controller/Action.html.twig`.
- Modules and themes retain the `Module` or `Theme` suffix but bundles do not.
- The form theme `@ZikulaFormExtension/Form/bootstrap_3_zikula_admin_layout.html.twig`
  - is changed to `@ZikulaFormExtension/Form/bootstrap_4_zikula_admin_layout.html.twig`

### Templates

| Topic | Old | New | Further information |
| ---- | --- | --- | ------- |
| Filtering loops | `{% for item in items if item.active %}` | `{% for item in items\|filter(i => i.active) %}` | [blog post](https://symfony.com/blog/twig-adds-filter-map-and-reduce-features) with more examples |
| Filtering loops (alternative) | `{% for item in items if item.active %}` | `{% for item in items %}{% if item.active %}` | |
| apply tag | `{% filter upper %}…{% endfilter %}` | `{% apply upper %}…{% endapply %}` | [blog post](https://symfony.com/blog/twig-adds-filter-map-and-reduce-features#the-apply-tag) |
| spaceless filter | `{% spaceless %}…{% endspaceless %}` | `{% apply spaceless %}…{% endapply %}` | [blog post](https://symfony.com/blog/better-white-space-control-in-twig-templates#added-a-spaceless-filter) |
| Old array extension | `shuffle` filter | no equivalent |
| Old date extension | `time_diff` filter | no equivalent |
| Old i18n extension | `trans` filter | use the `trans` filter from Symfony | [trans](https://symfony.com/doc/current/reference/twig_reference.html#trans) reference |
| Old intl extension | `localizeddate` | use `format_date`, `format_datetime`, `format_time` | [format_date](https://twig.symfony.com/doc/3.x/filters/format_date.html) reference, [format_datetime](https://twig.symfony.com/format_datetime) reference, [format_time](https://twig.symfony.com/format_time) reference |
| Old intl extension | `{{ myNumber\|localizednumber }}` | `{{ myNumber\|format_number }}` | [format_number](https://twig.symfony.com/doc/3.x/filters/format_number.html) reference |
| Old intl extension | `{{ myAmount\|localizedcurrency('EUR') }}` | `{{ myAmount\|format_currency('EUR') }}` | [format_currency](https://twig.symfony.com/doc/3.x/filters/format_currency.html) reference |
| Old text extension | `{{ title\|truncate(200, true, '…') }}` | `{{ title\|u.truncate(200, '…') }}` | [u filter](https://twig.symfony.com/doc/3.x/filters/u.html) reference |
| Old text extension | `wordwrap` | `u` | [u filter](https://twig.symfony.com/doc/3.x/filters/u.html) reference |
| Country name | unavailable | `{{ myCountry\|country_name }}` | [country_name](https://twig.symfony.com/country_name) reference |
| Currency name | unavailable | `{{ myCurrency\|currency_name }}` | [currency_name](https://twig.symfony.com/currency_name) reference |
| Currency symbol | unavailable | `{{ 'EUR'\|currency_symbol }}` | [currency_symbol](https://twig.symfony.com/currency_symbol) reference |
| Language name | `{{ myLanguage\|languagename }}` | `{{ myLanguage\|language_name }}` | [language_name](https://twig.symfony.com/language_name) reference |
| Locale name | unavailable | `{{ myLocale\|locale_name }}` | [locale_name](https://twig.symfony.com/locale_name) reference |
| Timezone name | unavailable | `{{ myTimezone\|timezone_name }}` | [timezone_name](https://twig.symfony.com/timezone_name) reference |

### Admin panel integration

- Remove all calls of `adminHeader()` and `adminFooter()` from extension templates.
- Ensure that your admin theme calls them instead at a central place where appropriate.
