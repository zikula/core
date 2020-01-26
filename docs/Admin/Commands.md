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
Shortcut: `bin/console z:e:s AcmeFooModule`

It is also possible to fetch individual properties like 'status', or 'version' 
by doing something like:

`bin/console z:e:s AcmeFooModule --get=version`

All the properties of the ExtensionEntity are available.
