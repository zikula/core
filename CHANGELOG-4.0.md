# CHANGELOG - ZIKULA 4.0.x

## 4.0.0 (unreleased)

- BC Breaks:
  - Administration
    - [Admin] Remove old administration interface in favor of EasyAdminBundle.
    - [Core] Remove custom session handling.
    - [CoreInstaller] Remove custom installer bundle.
    - [Mailer] Remove Mailer bundle (keep test mail form, moving it to the Settings bundle).
    - [Extensions] Remove additional state-based extension layer in favor of Composer and Flex.
    - [Extensions] Remove bundle variables in favor of Symfony bundle configuration. Most configs options are managed using YAML files in `/config/packages` instead of forms.
    - [SecurityCenter] Remove security center bundle (PHPIDS in favor of your server's WAF, HTML Purifer in favor of [ExerciseHTMLPurifierBundle](https://github.com/Exercise/HTMLPurifierBundle) and [Symfony HtmlSanitizer](https://symfony.com/blog/new-in-symfony-6-1-htmlsanitizer-component)).
  - Layout
    - [Blocks] Remove blocks system/bundle and pending content.
    - [FormExtensions] Remove dynamic form related stuff in favour of [DynamicFormBundle](https://github.com/zikula/DynamicFormBundle) and move remaining things into `ThemeBundle`.
    - [Theme] Remove `AtomTheme`, `PrinterTheme`, `RssTheme`, `BootstrapTheme`, `DefaultTheme`.
    - [Theme] Remove theme engine in favor of lightweight themed dashboards (see below).
  - User and authentication
    - [Groups] Remove group bundle in favor of nucleos and EAB vendors.
    - [Permissions] Remove custom permission system in favor of Symfony security. Default roles are `ROLE_USER`, `ROLE_EDITOR`, `ROLE_ADMIN`.
    - [Users] Base on `NucleosUserBundle` and `NucleosProfileBundle`.
    - [ZAuth] Remove deprecated `PasswordApi`.
  - Remove content management
    - [Categories] Remove categories bundle.
    - [Hook] Remove hooks support.
    - [Menu] Remove custom (database-driven) menus.
    - [PageLock] Remove page lock bundle.
    - [Routes] Remove custom routes system/bundle.
    - [Search] Remove search bundle.
    - [StaticContent] Remove static content bundle.
    - [Workflow] Remove graphical workflow editor and move dynamic workflow recognition into `CoreBundle`.

- Fixes:
  - none yet

- Features:
  - [General] Minimum PHP version is now 8.2.0 instead of 7.2.5.
  - [General] Zikula uses Composer/Flex and native Symfony bundles instead of custom extension types.
  - [General] Use PHP 8 attributes as well as other features (like constructor property promotion) where appropriate.
  - [General] Use Symfony security and `NucleosUserBundle` + `NucleosProfileBundle` for user and auth related concerns.
  - [General] Use Symfony UX Translator instead of `BazingaJsTranslationBundle`
  - [Admin] New interface based on `EasyAdminBundle` dashboards.
  - [Settings] Utilize rate limiter component for test email functionality.
  - [Theme] Introduce themed dashboards extending `EasyAdminBundle` dashboard functionality.
