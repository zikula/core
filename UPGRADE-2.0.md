Upgrading Zikula
================

  1. [Test Environment](#testenv)
  2. [Before upgrading](#download)
  3. [Upgrading](#upgrading)


<a name="testenv"></a>

Test Environment
----------------

The Zikula team strongly recommends having a duplicate testing environment of the live site in which all
changes including upgrades are tested on before application to the live site.


<a name="download"></a>

Download
--------

***Prior to any upgrade ensure that you have created a reliable backup of all files and the database.***

Download the current release from [GitHub releases](https://github.com/zikula/core/releases/)
All the dependencies and requirements are included in this package.


<a name="upgrading"></a>

Upgrading
---------

The minimum upgrade version is Zikula Core 1.4.3. Please upgrade to at least this version before attempting to upgrade
to Core-2.0.x.

The following process should be followed for all upgrades even small point releases (e.g. `2.0.x`).

  - **Backup all your files and database.**

  - Make a note of your 'startpage' settings as they must be cleared in the upgrade process.
  - All blocks using MenuTree, ExtMenu or Menu will be DELETED during the upgrade as these are no longer available in Core-2.0.
    You should consider deleting and replacing these with a MenuModule block before upgrading.
  - Before uploading the new files, delete **all files** in your web root (typically `public_html` or `httpdocs`).
  - Upload the new package and unpack the archive.
    - Please read the [INSTALL docs](INSTALL-2.0.md#upload) for detailed information on proper uploading.
    - Note 1: One common issue when installing is that the bin/cache and bin/logs directories must be writable both by the 
      web server and the command line user. See Symfony's [Setting up or Fixing File Permissions](http://symfony.com/doc/current/setup/file_permissions.html) 
      to see potential solutions to this problem when installing from the CLI.
    - Note 2: If you have `mod_suexec` installed for Apache the CLI will run into permission problems. (If you are not sure 
      check your phpinfo.) `mod_suexec` often is used in shared hosting environments. In this case, the CLI installer is not 
      recommended, please use the Web Installer.
  - Copy your previous installation's `app/config/custom_parameters.yml` to the same location in your new installation.
    - **Change the value of `datadir` to `web/uploads`** if upgrading from Core-1.x.
    - Do not change any other values.

#### Continue:

  - Copy your custom theme to your new installation. The folders of your theme should be in the exact same place as your
    backup.
  - Return compatible modules `/modules` directory.
    - **DO NOT copy the old Profile and Legal module** as new versions of these are provided, and their location may differ.
  - Copy your backup contents of `/userdata` into `/web/uploads`

  - **Upgrade: (do one or the other)**
    - Via Web: launch `http://yoursiteurl/` (you will be redirected to `/upgrade`) and follow any on-screen prompts.
    - Via CLI:
      - Access your main zikula directory (`/src` if a Github clone) and run this command:

         ```Shell
         $ php bin/console zikula:upgrade
         ```

      - Follow the prompts and complete that step. When you are finished, Open your browser and login!
    - Visit the extensions page and run each module upgrade one at a time.
