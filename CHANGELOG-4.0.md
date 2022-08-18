# CHANGELOG - ZIKULA 4.0.x

## 4.0.0 (unreleased)

- BC Breaks:
  - [Blocks] Remove blocks system and pending content.
  - [CoreInstaller] Remove custom installer.
  - [Mailer] Remove mailer module (keep test mail form, moving it to the Settings module).
  - [Extensions] Remove additional state-based extension layer in favor of Composer and Flex.
  - [Groups] Remove group applications.
  - [Hook] Remove hooks support.
  - [Menu] Remove custom (database-driven) menus.
  - [PageLock] Remove page lock module.
  - [Routes] Remove custom (database-driven) routes.
  - [Search] Remove search module.
  - [SecurityCenter] Remove PHPIDS in favor of your server's WAF.
  - [Theme] Remove `AtomTheme`, `PrinterTheme`, `RssTheme`.
  - [Theme] Remove old `BootstrapTheme` in favor of `DefaultTheme`.
  - [Workflow] Remove graphical workflow editor.
  - [ZAuth] Remove deprecated `PasswordApi`.

- Fixes:
  - none yet

- Features:
  - [General] Minimum PHP version is now 8.1 instead of 7.2.5.
  - [General] Zikula uses Composer/Flex and bundles instead of modules.
  - [General] Use PHP 8 attributes as well as other features (like constructor property promotion) where appropriate.
  - [Legal] Add `Legal` module to monorepo.
  - [Profile] Add `Profile` module to monorepo.
  - [Settings] Utilize rate limiter component for test email functionality.
  - [SortableColumns] Add `SortableColumns` component to monorepo.
  - [StaticContent] Add `StaticContent` module to monorepo.
  - [Wizard] Add `Wizard` component to monorepo.
