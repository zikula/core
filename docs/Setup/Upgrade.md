---
currentMenu: upgrade
---
# Upgrading Zikula 1.x or 2.x to 3.x.x
This document is the upgrade process for **Zikula Core 1.x or 2.x to 3.x.x**. If you are upgrading from a version **greater** than
3.0.0 please use the proper [upgrade document](UpgradeFrom3to3.x.x.md). The minimum upgrade version is Zikula Core 1.4.3.
Please upgrade to at least this version before attempting to upgrade to Core-3.0.x.

1. [Test Environment](#test-environment)
2. [Before upgrading](#download)
3. [Upgrading](#upgrading)

## Backup your current files and database!
***Prior to any upgrade ensure that you have created a reliable backup of all files and the database.***

## Test Environment
The Zikula team strongly recommends having a duplicate testing environment of the live site in which all
changes including upgrades are tested on before application to the live site.

## Download
Download the current release from [GitHub releases](https://github.com/zikula/core/releases/)
All the dependencies and requirements are included in this package.

## Upgrading
- Make a note of your 'startpage' settings as they must be cleared in the upgrade process.
- If upgrading from Core-1.x:
    - All (1.x.x) blocks using MenuTree, ExtMenu or Menu will be DELETED during the upgrade as these are no longer available.
      You should consider deleting and replacing these with a MenuModule block before upgrading.
    - All (1.x.x) Hook connections will be deleted and need to be re-connected post-upgrade.
- Before uploading the new files, delete (or move) **all files** in your web root (typically `public_html` or `httpdocs`).
- Upload the new package and unpack the archive.
  - Please read the [INSTALL docs](INSTALL.md#upload) for detailed information on proper uploading.
  - Ensure that the `/var/cache` and `/var/log` directories must be writable both by the web server and the command line user.
    See Symfony's [Setting up or Fixing File Permissions](http://symfony.com/doc/current/setup/file_permissions.html) 
    to see potential solutions to this problem when installing from the CLI.
  - If you have `mod_suexec` installed for Apache the CLI will run into permission problems. (If you are not sure 
    check your phpinfo.) `mod_suexec` is often used in shared hosting environments. In this case, the CLI installer is not 
    recommended. Instead, please use the Web Installer.
- Please note, your _web root_ **must** now point to the `public/` directory. Adjust your symlinks or directory structure as needed.
  (see https://symfony.com/doc/current/setup/web_server_configuration.html for more information).

### Continue

- Copy old files to _new_ locations:
    - Copy your previous installation's `/app/config/custom_parameters.yml` to `/config/services_custom.yaml` in your new installation.
        - note the name change and the suffix changed from `.yml` to `.yaml`
        - When the upgrade to 3.1.0 is complete, the file will be deleted because the values are moved to config files.
    - Copy your _compatible_ custom theme to the `src/extensions` directory (There is no `/themes` directory any longer).
        - After upgrade, the site will default to the ZikulaBootstrapTheme until you complete testing of your custom theme.
    - Return _compatible_ modules (extensions) to the `src/extensions` directory.
        - **DO NOT copy the old Profile and Legal module** as new versions of these are provided, and their location may differ.
        - Check compatibility in the `/composer.json` file is: `"extra": { "zikula" : { "core-compatibility": ">=3.0.0"`
          (">=2.0.0" is _not_ compatible with 3.x) or ask in the Zikula slack channel.
    - Copy your backup contents of `/userdata` (1.x) or `/web/uploads` (2.x) into `/public/uploads`
- Then **start the upgrade (do one or the other, CLI is recommended)**
  - Via Web: launch `http://yoursiteurl/` (you will be redirected to `/upgrade`) and follow any on-screen prompts.
  - Via CLI:
    - Access your main zikula directory then run each of these commands:
        ```Shell
        php bin/console zikula:pre-upgrade
        php bin/console zikula:upgrade
        ```

    - Follow the prompts and complete that step. When you are finished, Open your browser and login!
  - Visit the extensions page and run each module upgrade one at a time.

### Post Upgrade

- MySQL databases will have been recoded from `utf8` to `utf8mb4`.
  - old copies of `app/config/config.yml` may have contained a reference to `doctrine.dbal.connections.default.charset` but this is removed.
- Double check FontAwesome icons in your menu module options. An attempt has been made to correct them, but manual correction may be required. You can lookup icon name changes between FontAwesome version 4 and 5 [at this page](https://fontawesome.com/how-to-use/on-the-web/setup/upgrading-from-version-4#name-changes).
- Blocks with properties/content that could not be unserialized have been deleted. The content of the block has been dumped to `/var/log/removedBlock#.txt` for recovery if desired.

