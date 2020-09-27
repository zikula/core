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
  - [config] Added standard Symfony bundle configurations for the following bundles (#4433):
    - CoreBundle, ZikulaRoutesModule, ZikulaSecurityCenterModule, ZikulaSettingsModule, ZikulaThemeModule
  - [extensions] Add StaticContent module to manage all static content (#4369).
  - [CoreBundle] Add `Zikula\Bundle\CoreBundle\Configurator` for writing config files to the filesystem (#4433).
  - [FormExtensionsBundle] Add bsCustomFileInput for direct file selection feedback (#4491).
  - [ZikulaDefaultTheme] Add new default theme (#4462).
    - This looks the same as ZikulaBootstrapTheme but improves the templates in a way that is not BC.

- Deprecated:
  - [General] Controller methods should not have an `Action` suffix in their names anymore.
  - [CoreBundle] `Zikula/CoreBundle/YamlDumper` is deprecated. Please use `Configurator` as needed.
  - [BlocksModule] Content-providing blocks (FincludeBlock, HtmlBlock, TextBlock, XsltBlock) use StaticContentModule instead.
  - [BootstrapTheme] The entire theme is deprecated. Please see DefaultTheme for replacement.
