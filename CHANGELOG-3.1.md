# CHANGELOG - ZIKULA 3.1.x

## 3.1.0 (2021-12-21)

- BC Breaks:
  - [config] Removed `config/dynamic/*.yaml` files (use standard package config files).
  - [config] Removed `config/services_custom.yaml` (use `services.yaml`).
  - [config] `zikula_asset_manager.combine` now defaults to `false` (#4419).
  - [dependency] The following symfony components are no longer included:
    - amazon-mailer, mailchimp-mailer, mailgun-mailer, postmark-mailer, sendgrid-mailer
  - [CoreBundle] Removed `Zikula\Bundle\CoreBundle\DynamicConfigDumper`.
  - [Routes] Controller actions are now named without the old `Action` suffix.
  - [Theme] Removed Require.js config (#4558).

- Fixes:
  - [composer] Correct Composer 2 compatibilty.
  - [translations] Fix non-working extraction of translation with `@Translate` annotation (#4694).
  - [CoreBundle] Added clearing of OPCache (if in use) to standard clearcache operation (#4507).
  - [CoreInstallerBundle] Use DBAL for cross-database determination of existing tables (#4688).
  - [Admin] Add missing numeric casts to admin module setting usages (#4709).
  - [Extensions] Fixed non-working extension modification actions (#4768).
  - [Groups] Fix some non-working translations (#4694).
  - [Groups] Rename database tables for improved PostgreSQL compatibilty (#4762).
  - [Menu] Fixed handling of menu items without URI in custom request voter.
  - [Search] Add missing query string to search results pagination.
  - [Theme] Asset combination now defaults to `false` on installation (#4419).
  - [Theme] Corrected missing configurable value for `trimwhitespace` option (#4531).
  - [Theme] Replaced `robloach/component-installer` with `oomphinc/composer-installers-extender` (#4558).
  - [Users] Fixed regression when sending mail to more than one user in one step.
  - [Users] Fixed broken mass deletion (#4597).
  - [Users] Added redirect for cancel button on registration form (#4595).
  - [ZAuth] Fix wrong `DateTime` value (#4657).
  - [ZAuth] Fix some non-working translations (#4694).

- Features:
  - [dependency] Changed dependency from `symfony/symfony` to ALL the related `symfony/*` components (#4352, #4563).
  - [dependency] Added `symfony/flex` dependency and configured as needed for core-development (#4563).
  - [config] Added standard Symfony bundle configurations for the following bundles (#4433):
    - CoreBundle, ZikulaRoutesModule, ZikulaSecurityCenterModule, ZikulaSettingsModule, ZikulaThemeModule
  - [extensions] Add StaticContent module to manage all static content (#4369).
  - [CoreBundle] Add `Zikula\Bundle\CoreBundle\Configurator` for writing config files to the filesystem (#4433).
  - [CoreBundle] Improved pagination display avoiding large amount of page links (#4547).
  - [FormExtensionsBundle] Add bsCustomFileInput for direct file selection feedback (#4491).
  - [HookBundle] Added Forward-Compatibility layer of new HookEvent concept (#4593).
  - [BlocksModule] Add new block positions automatically on theme installation (#4228). 
  - [DefaultTheme] Add new default theme (#4462).
    - This looks the same as ZikulaBootstrapTheme but improves the templates in a way that is not BC.
  - [General] Implemented `Twig\Extension\RuntimeExtensionInterface` for all Twig extensions, allowing them to dynamically load (#4522).
  - [General] Added `addAnnotatedClassesToCompile` method to needed core classes to improve performance when activated.
  - [Routes] Dropdown for choosing bundle/controller/action combination (#4517).
  - [Theme] Add `Symfony\WebpackEncoreBundle` (#4571).
    - Automatically adds webpack assets via a listener.
  - [Users/ZAuth] Default authentication method is changed to "native either" (#4351).
  - [ZAuth] Utilize rate limiter component for lost username / lost password functionalities.

- Deprecated:
  - [General] Controller methods should not have an `Action` suffix in their names anymore.
  - [CoreBundle] `Zikula/CoreBundle/YamlDumper` is deprecated. Please use `Configurator` as needed.
  - [HookBundle] The old hook concept is deprecated. Use new HookEvent concept described in README.
  - [BlocksModule] Content-providing blocks (FincludeBlock, HtmlBlock, TextBlock, XsltBlock) use StaticContentModule instead.
  - [BootstrapTheme] The entire theme is deprecated. Please see DefaultTheme for replacement.
