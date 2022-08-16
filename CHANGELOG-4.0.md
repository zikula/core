# CHANGELOG - ZIKULA 4.0.x

## 4.0.0 (unreleased)

- BC Breaks:
  - [CoreInstallerBundle] Remove custom installer.
  - [HookBundle] Remove hooks support.
  - [PageLock] Remove page lock module.
  - [WorkflowBundle] Remove graphical workflow editor.
  - [Blocks] Remove pending content.
  - [Menu] Remove custom (database-driven) menus.
  - [Search] Remove search module.
  - [SecurityCenter] Remove PHPIDS in favor of your server's WAF.
  - [Theme] Remove `AtomTheme`, `PrinterTheme`, `RssTheme`.
  - [Theme] Remove old `BootstrapTheme` in favor of `DefaultTheme`.
  - [ZAuth] Remove deprecated `PasswordApi`.

- Fixes:
  - none yet

- Features:
  - [Mailer] Utilize rate limiter component for test email functionality.
