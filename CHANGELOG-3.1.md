# CHANGELOG - ZIKULA 3.1.x

## 3.1.0 (unreleased)

- BC Breaks:
  - Removed `Zikula\Bundle\CoreBundle\DynamicConfigDumper`.
  - Removed `config/dynamic/*.yaml` files (use standard package config files).
  - Removed `config/services_custom.yaml` (use `services.yaml`).

- Fixes:
  - _first fix_

- Features:
  - Add StaticContent module to manage all static content (#4369).
  - Add `Zikula\Bundle\CoreBundle\Configurator` for writing config files to the filesystem (#4433).
  - Added standard Symfony bundle configurations for the following bundles (#4433):
    - CoreBundle
    - ZikulaRoutesModule
    - ZikulaSecurityCenterModule
    - ZikulaSettingsModule
    - ZikulaThemeModule

- Deprecated:
  - `Zikula/CoreBundle/YamlDumper` (use `Configurator` as needed).
