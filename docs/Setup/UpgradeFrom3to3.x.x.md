---
currentMenu: upgrade3to3
---
# Upgrading Zikula 3.x.x to Zikula 3.x.x
This document is the upgrade process for **Zikula Core 3.x.x to 3.x.x**. If you are upgrading from a version **less** than
3.0.0 please use the proper [upgrade document](Upgrade.md). 

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
- Before uploading the new files, delete (or move) **all files** in your web root (typically `public_html` or `httpdocs`).
- Upload the new package and unpack the archive.
  - Please read the [INSTALL docs](INSTALL.md#upload) for detailed information on proper uploading.
  - Ensure `/var/cache` and `/var/log` directories are writable both by the web server and the command line user.
  - If you have `mod_suexec` installed for Apache the CLI will run into permission problems. (If you are not sure 
    check your phpinfo.) `mod_suexec` often is used in shared hosting environments. In this case, the CLI upgrader is not 
    recommended, please use the Web Upgrade.
- Copy old files to parallel locations:
    - Copy your previous installation's `/.env.local` to `/.env.local` in your new installation.
    - Copy your previous installation's `/config/services_custom.yaml` to `/config/services_custom.yaml` in your new installation.
        - When the upgrade to 3.1.0 is complete, the file will be deleted because the values are moved to config files.
    - Copy your custom theme to the `src/extensions` directory.
        - After upgrade, the site will default to the ZikulaDefaultTheme until you complete testing of your custom theme.
    - Copy extensions to the `src/extensions` directory.
        - **DO NOT copy the old Profile and Legal module** as new versions of these are provided.
    - Copy your backup contents of `/public/uploads` into `/public/uploads`
- Then **start the upgrade (do one or the other, CLI is recommended)**
  - Via Web: launch `https://yoursiteurl/` (you will be redirected to `/upgrade`) and follow any on-screen prompts.
  - Via CLI:
    - Access your main zikula directory then
        ```Shell
        php bin/console zikula:upgrade
        ```

    - Follow the prompts and complete that step. When you are finished, Open your browser and login!
  - Visit the extensions page and run each module upgrade one at a time.
