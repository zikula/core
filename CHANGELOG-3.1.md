# CHANGELOG - ZIKULA 3.1.x

## 3.1.0 (unreleased)

- BC Breaks:
  - [config] Removed `config/dynamic/*.yaml` files (use standard package config files).
  - [config] Removed `config/services_custom.yaml` (use `services.yaml`).
  - [config] `zikula_asset_manager.combine` now defaults to `false` (#4419).
  - [dependency] The following symfony components are no longer included:
    - amazon-mailer, mailchimp-mailer, mailgun-mailer, postmark-mailer, sendgrid-mailer
  - [CoreBundle] Removed `Zikula\Bundle\CoreBundle\DynamicConfigDumper`.
  - [ThemeModule] Removed Require.js config (#4558).

- Fixes:
  - [composer] Correct Composer 2 compatibilty.
  - [CoreBundle] Added clearing of OPCache (if in use) to standard clearcache operation (#4507).
  - [ThemeModule] Asset combination now defaults to `false` on installation (#4419).
  - [ThemeModule] Corrected missing configurable value for `trimwhitespace` option (#4531).
  - [ThemeModule] Replaced `robloach/component-installer` with `oomphinc/composer-installers-extender` (#4558).
  - [UsersModule] Fix regression when sending mail to more than one user in one step.

- Features:
  - [dependency] Changed dependency from `symfony/symfony` to ALL the related `symfony/*` components (#4352, #4563).
  - [dependency] Added `symfony/flex` dependency and configured as needed for core-development (#4563).
  - [config] Added standard Symfony bundle configurations for the following bundles (#4433):
    - CoreBundle, ZikulaRoutesModule, ZikulaSecurityCenterModule, ZikulaSettingsModule, ZikulaThemeModule
  - [extensions] Add StaticContent module to manage all static content (#4369).
  - [CoreBundle] Add `Zikula\Bundle\CoreBundle\Configurator` for writing config files to the filesystem (#4433).
  - [FormExtensionsBundle] Add bsCustomFileInput for direct file selection feedback (#4491).
  - [BlocksModule] Add new block positions automatically on theme installation (#4228). 
  - [DefaultTheme] Add new default theme (#4462).
    - This looks the same as ZikulaBootstrapTheme but improves the templates in a way that is not BC.
  - [General] Implemented `Twig\Extension\RuntimeExtensionInterface` for all Twig extensions, allowing them to dynamically load (#4522).
  - [General] Added `addAnnotatedClassesToCompile` method to needed core classes to improve performance when activated.
  - [ThemeModule] Add `Symfony\WebpackEncoreBundle` (#4571).
    - Automatically adds webpack assets via a listener.

- Deprecated:
  - [General] Controller methods should not have an `Action` suffix in their names anymore.
  - [CoreBundle] `Zikula/CoreBundle/YamlDumper` is deprecated. Please use `Configurator` as needed.
  - [BlocksModule] Content-providing blocks (FincludeBlock, HtmlBlock, TextBlock, XsltBlock) use StaticContentModule instead.
  - [BootstrapTheme] The entire theme is deprecated. Please see DefaultTheme for replacement.
