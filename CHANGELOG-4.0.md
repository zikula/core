# CHANGELOG - ZIKULA 4.0.x

## 4.0.0 (unreleased)

- BC Breaks:
  - [CoreInstallerBundle] Remove custom installer.
  - [HookBundle] Remove hooks support.
  - [PageLock] Remove page lock module.
  - [WorkflowBundle] Remove graphical workflow editor.
  - [Search] Remove search module.
  - [Theme] Remove `AtomTheme`, `PrinterTheme`, `RssTheme`.
  - [Theme] Remove old `BootstrapTheme` in favor of `DefaultTheme`.
  - [ZAuth] Remove deprecated `PasswordApi`.

- Fixes:
  - none yet

- Features:
  - [Mailer] Utilize rate limiter component for test email functionality.
