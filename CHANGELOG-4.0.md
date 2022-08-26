# CHANGELOG - ZIKULA 4.0.x

## 4.0.0 (unreleased)

- BC Breaks:
  - [Admin] Remove old administration interface.
  - [Blocks] Remove blocks system/bundle and pending content.
  - [Core] Remove custom session handling.
  - [CoreInstaller] Remove custom installer bundle.
  - [Mailer] Remove Mailer bundle (keep test mail form, moving it to the Settings bundle).
  - [Extensions] Remove additional state-based extension layer in favor of Composer and Flex.
  - [Extensions] Remove bundle variables in favor of Symfony bundle configuration. Most configs options are managed using YAML files in `/config/packages` instead of forms.
  - [Groups] Remove group applications.
  - [Hook] Remove hooks support.
  - [Legal] Remove old cookie warning script. Use your preferred consent tool instead.
  - [Menu] Remove custom (database-driven) menus.
  - [PageLock] Remove page lock bundle.
  - [Routes] Remove custom routes system/bundle.
  - [Search] Remove search bundle.
  - [SecurityCenter] Remove security center bundle (PHPIDS in favor of your server's WAF, HTML Purifer in favor of [ExerciseHTMLPurifierBundle](https://github.com/Exercise/HTMLPurifierBundle) and [Symfony HtmlSanitizer](https://symfony.com/blog/new-in-symfony-6-1-htmlsanitizer-component)).
  - [Theme] Remove `AtomTheme`, `PrinterTheme`, `RssTheme`.
  - [Theme] Remove old `BootstrapTheme` in favor of `DefaultTheme`.
  - [Workflow] Remove graphical workflow editor.
  - [ZAuth] Remove deprecated `PasswordApi`.

- Fixes:
  - none yet

- Features:
  - [General] Minimum PHP version is now 8.1 instead of 7.2.5.
  - [General] Zikula uses Composer/Flex and native Symfony bundles instead of custom extension types.
  - [General] Use PHP 8 attributes as well as other features (like constructor property promotion) where appropriate.
  - [Admin] New interface based on `EasyAdminBundle` dashboards.
  - [Legal] Add `Legal` bundle to monorepo.
  - [Profile] Add `Profile` bundle to monorepo.
  - [Settings] Utilize rate limiter component for test email functionality.
  - [SortableColumns] Add `SortableColumns` component to monorepo.
  - [StaticContent] Add `StaticContent` bundle to monorepo.
  - [Wizard] Add `Wizard` component to monorepo.
