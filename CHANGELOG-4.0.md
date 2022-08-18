# CHANGELOG - ZIKULA 4.0.x

## 4.0.0 (unreleased)

- BC Breaks:
  - [CoreInstallerBundle] Remove custom installer.
  - [HookBundle] Remove hooks support.
  - [WorkflowBundle] Remove graphical workflow editor.
  - [Blocks] Remove blocks system and pending content.
  - [Mailer] Remove mailer module (keep test mail form, moving it to the Settings module).
  - [Extensions] Remove additional state-based extension layer in favor of Composer and Flex.
  - [Menu] Remove custom (database-driven) menus.
  - [PageLock] Remove page lock module.
  - [Routes] Remove custom (database-driven) routes.
  - [Search] Remove search module.
  - [SecurityCenter] Remove PHPIDS in favor of your server's WAF.
  - [Theme] Remove `AtomTheme`, `PrinterTheme`, `RssTheme`.
  - [Theme] Remove old `BootstrapTheme` in favor of `DefaultTheme`.
  - [ZAuth] Remove deprecated `PasswordApi`.

- Fixes:
  - none yet

- Features:
  - [Legal] Add `Legal` module to monorepo.
  - [Profile] Add `Profile` module to monorepo.
  - [Settings] Utilize rate limiter component for test email functionality.
  - [SortableColumns] Add `SortableColumns` component to monorepo.
  - [StaticContent] Add `StaticContent` module to monorepo.
  - [Wizard] Add `Wizard` component to monorepo.
