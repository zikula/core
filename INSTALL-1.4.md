Zikula Installation Instructions
================================

  1.  [Zikula](#zikula)
  2.  [Setup Consideration](#requirements)
  3.  [Upload and Prepare](#upload)
  4.  [New Installation](#install)
  5.  [Final Note](#final)


<a name="zikula"></a>
1. Zikula
---------

Zikula is an open source, open development application framework for dynamic
websites. The core system includes a complete API on which third party developers
can build. Zikula is built with other major open-source components like Symfony, Doctrine, Bootstrap
and jQuery among others. This means more power for the developer and more reliable code for
everyone involved.

Zikula can be extended with modules, plugins and themes which can add functionality to your
website. These are available at http://www.zikula.org/library

Zikula Core 1.4 maintains backward-compatibility with Zikula Core 1.3 and runs all 1.3.x-compatible
modules, plugins and themes without changes. If a module doesn't work with 1.4 but worked with 1.3, feel free
to open an issue at the GitHub Core Repository and we will try to fix any problems.


<a name="requirements"></a>
2. Server Consideration
-----------------------

Before upgrading Zikula it's important to ensure that the hosting server environment meets the requirements
of the new core release. Zikula Core 1.4 has the following requirements:

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.4.1         | 5.5          |

 - Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
   to 128 MB for the installation process.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
   the default setting for `AllowOverride` to `None`.
 - Additional (advanced) server considerations can be found on
   [the Symfony site](http://symfony.com/doc/current/cookbook/configuration/web_server_configuration.html)
 - Zikula also requires other php extensions and configurations. These are checked during the installation
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.


<a name="upload"></a>
3. Upload and Prepare
---------------------

###Upload

If you obtained the Zikula Core by cloning the repo at Github, you should see the `README.md` for further
instructions. This is **not recommended for non-developers**.

If you obtained Zikula Core from zikula.org or the CI server, then you can upload the entire archive (`.zip`
or `.tgz` file) to your server and then `unzip` them there. (This is faster and much more reliable than 
uploading files via FTP). **Make sure to include the `-p` flag if you extract from `.tgz` to keep the
correct permissions (`tar -xpzvf Zikula_Core-1.4.0.build123.tar.gz`) change the filename to match the current download.** 
Copy all the files and directories to your webroot (typically `public_html` or `httpdocs`).

Another option can be to download the tar.gz directly from the ci server: e.g. 
`wget http://ci.zikula.org/job/Zikula_Core-1.4.0/588/artifact/build/archive/Zikula_Core-1.4.0.build588.tar.gz`. 
Again, change the filename/url to match the correct filename from the ci server.

Windows/FTP users: Take care about copying all files. If there are some files you are not able to transfer 
to the server check if your longest path length is longer than Windows/FTP-Software allows (more than 256 characters).

###Set file permissions (Critical)

If you installed from a `.zip` archive or uploaded the files via FTP, the permissions for some folders must be changed 
prior to installing so that your webserver's user has write access:
- `app/config`
- `app/config/dynamic`
- `app/cache`
- `app/logs`
- `config`
- `userdata`

You normally do so using `chmod 777 app/cache` and so on. (`.tgz` archives maintain
the permission settings as they were set correctly by the development team, if you unpacked it using the `-p` flag).


<a name="install"></a>
4. New Installation
-------------------

###Create the Database

Create a database on your server. Take note of the database **name** as well as the database **username** and
**password** which are likely given by your provider. These will be needed during install. You can use an existing
database, but this is not recommended unless Zikula will be the only application using that database. In this case,
remove all existing tables from the existing database.

###Web Installer

To begin the installer, simply visit the root directory with your browser, e.g. `http://www.example.com/`.
If you installed Zikula into a subdirectory 'foo' the URL would be `http://www.example.com/foo/`. You will be
automatically redirected to the installer.

###Command Line Installer

*Note 1:* One common issue when installing is that the app/cache and app/logs directories must be writable both by the 
web server and the command line user. See Symfony's [Setting up or Fixing File Permissions](http://symfony.com/doc/2.8/setup/file_permissions.html) 
to see potential solutions to this problem when installing from the CLI.

*Note 2:* If you have `mod_suexec` installed for Apache the CLI will run into permission problems. (If you are not sure 
check your phpinfo.) `mod_suexec` often is used in shared hosting environments. In this case, the CLI installer is not 
recommended, please use the Web Installer. 

Via CLI, access your main zikula directory (`/src` if a Github clone) and run this command:
```Shell
$ php app/console zikula:install:start
```
Follow the prompts and complete that step. When you are finished, you are directed to run the next command:
```Shell
$ php app/console zikula:install:finish
```
Open your browser and login!


<a name="final"></a>
5. Final Note
-------------

Modifications to the Zikula core system code or database are not supported. Doing so can cause extensive
problems when upgrading the system in the future and therefore these *hacks* are not recommended. Zikula
has a flexible extensions system and configuration override system to allow customization and we recommend
you consult the developer documentation about this. If you feel like your hack is useful / a bug fix / a nice
feature, Please open an issue/pull-request at the GitHub Core Repository. We're glad to merge any improvements
of the core
