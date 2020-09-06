# CHANGELOG - ZIKULA 3.1.x

## 3.1.0 (unreleased)

- BC Breaks:
  - [CoreBundle] Removed `Zikula\Bundle\CoreBundle\DynamicConfigDumper`.
  - [config] Removed `config/dynamic/*.yaml` files (use standard package config files).
  - [config] Removed `config/services_custom.yaml` (use `services.yaml`).
  - [config] `zikula_asset_manager.combine` now defaults to `false` (#4419).

- Fixes:
  - [ThemeModule] Asset combination now defaults to `false` on installation (#4419).

- Features:
  - [CoreBundle] Add `Zikula\Bundle\CoreBundle\Configurator` for writing config files to the filesystem (#4433).
  - [extensions] Add StaticContent module to manage all static content (#4369).
  - [config] Added standard Symfony bundle configurations for the following bundles (#4433):
    - [CoreBundle, ZikulaRoutesModule, ZikulaSecurityCenterModule, ZikulaSettingsModule, ZikulaThemeModule]
  - [ZikulaDefaultTheme] Add new default theme (#4462).
    - This looks the same as ZikulaBootstrapTheme but improves the templates in a way that is not BC.

- Deprecated:
  - [CoreBundle] `Zikula/CoreBundle/YamlDumper` use `Configurator` as needed.
  - [BlocksModule] Content-providing blocks (FincludeBlock, HtmlBlock, TextBlock, XsltBlock) use StaticContentModule instead.
  - [BootstrapTheme] The entire theme is deprecated. Please see DefaultTheme for replacement.
