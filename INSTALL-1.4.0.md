ZIKULA INSTALLATION INSTRUCTIONS
================================

CONTENTS
========

  1.  [Zikula](#zikula)
  2.  [Setup Consideration](#requirements)
  3.  [Upload and Prepare](#upload)
  4.  [New Installation](#install)
  5.  [Final Note](#final)

<a name="zikula" />
1. Zikula
=========

Zikula is an open source, open development application framework for dynamic
websites. The core system includes a complete API on which third party developers
can build. Zikula is built with other major open-source components like Symfony, Doctrine, Bootstrap
and jQuery among others. This means more power for the developer and more reliable code for
everyone involved.

Zikula can be extended with modules, plugins and themes which can add functionality to your
website. These are available at http://www.zikula.org/library

Zikula Core 1.4.0 maintains backward-compatibility with Zikula Core 1.3.0 and runs all 1.3.x-compatible
modules, plugins and themes without changes.


<a name="requirements" />
2. Server Consideration
=======================

Before upgrading Zikula it's important to ensure that the hosting server environment meets the requirements
of the new core release. Zikula Core 1.4.0 has the following requirements:

|               | Minimum       | Recommended  |
| ------------- |:-------------:| :-----------:|
| PHP           | 5.3.3         | 5.5          |

 - Please note that PHP versions less than `5.3.8` and `5.3.16` are known to be buggy and will not work.
 - Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
 - Zikula also requires other php extensions and configurations. These are checked during the installation
   process and if there are problems, you will be notified. If you discover errors, check with your hosting
   provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
   possibly reconfiguring the php installation by your provider.


<a name="upload">
3. Upload and Prepare
=====================

###Upload

If you obtained the Zikula Core by cloning the repo at Github, you should see the `README.md` for further
instructions. This is **not recommended for non-developers**.

If you obtained Zikula Core from zikula.org or the CI server, then you can upload the entire archive (`.zip`
or `.tgz` file) to your server and then `unzip` them there. (This is faster and much more reliable). In the
end, however, you will only need the contents of the `/src` archive; copy all these files and directories to
your webroot (typically `public_html` or `httpdocs`).

###Set file permissions (Critical)

If you installed from a `.zip` archive, the permissions for the `app/cache` and `app/logs` must be reset so
these directories are writable. `chmod 777 app/cache` and `chmod 777 app/logs`. (`.tgz` archives maintain
the permission settings as they were set correctly by the development team).


<a name="install">
4. New Installation
===================

###Create the Database

Create a database on your server and note the **name**, **username** and **password**.
These will be needed during install. You can use an existing database, but this is not recommended unless Zikula
will be the only application using that database. In this case, remove all existing tables from the existing database.

###Web Installer

To begin the installer, simply visit `/install` in the root directory with your browser,
e.g. `http://www.example.com/install`. If you installed Zikula into a subdirectory 'foo' the URL would
be `http://www.example.com/foo/install`


<a name="final">
5. Final Note
=============

Modifications to the Zikula core system code or database are not supported. Doing so can cause extensive
problems when upgrading the system in the future and therefore these *hacks* are not recommended. Zikula
has a flexible extensions system and configuration override system to allow customization and we recommend
you consult the developer documentation about this.