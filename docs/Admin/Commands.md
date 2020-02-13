---
currentMenu: admin-overview
---
# Zikula CLI Commands

## Extension management

### zikula:extension:install

Quickly installs a zikula extension from the CLI.

Usage: `bin/console zikula:extension:install AcmeFooModule`
Shortcut: `bin/console z:e:i AcmeFooModule`

Options:

- `--ignore_deps` Force install the extension ignoring all dependencies

### zikula:extension:uninstall

Quickly uninstalls a zikula extension from the CLI.

Usage: `bin/console zikula:extension:uninstall AcmeFooModule`
Shortcut: `bin/console z:e:un AcmeFooModule`

### zikula:extension:upgrade

Quickly upgrades a zikula extension from the CLI.

Usage: `bin/console zikula:extension:upgrade AcmeFooModule`
Shortcut: `bin/console z:e:up AcmeFooModule`

### zikula:extension:status

Provides some basic information about the module and its status.

Usage: `bin/console zikula:extension:status AcmeFooModule`
Shortcut: `bin/console z:e:st AcmeFooModule`

It is also possible to fetch individual properties like 'status', or 'version' 
by doing something like:

`bin/console z:e:s AcmeFooModule --get=version`

All the properties of the ExtensionEntity are available.

### zikula:extension:sync

Sync extensions in the filesystem to the `bundles` and `extensions` database tables from the CLI.

This command defaults to only syncing the `extensions` directory. See below to also sync Core extensions.
"Sync" means to scan `composer.json` files and persist information there (e.g. version, etc.) as well as *autoload* 
paths to the `bundles` and `extensions` database tables. This is sometimes needed in development when iterating the 
extension's configuration. It is **not** required when using the commands above to install, etc.

Usage: `bin/console zikula:extension:sync`
Shortcut: `bin/console z:e:sy`

Options:

- `--include_core` Force the system extensions to re-sync as well. Core data is _only_ stored in the `extensions` table.

Usage: `bin/console z:e:sy --include_core`
