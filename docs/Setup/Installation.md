---
currentMenu: install
---
# Zikula installation instructions

First please check [requirements](requirements.md).

This guide assumes you are going to install Zikula on a webserver which is fully operational. If you want to do the 
setup on a fresh root server there are additional steps to consider though. In this case maybe this guide can help: 
[Installation of Zikula on Ubuntu Server](InstallingZikulaOnUbuntu.md).

## Overview

1. [Install by uploading package](#upload)
2. [Install via Composer](#installing-using-composer-for-developers)
3. [Install with Vagrant](#vagrant-installation)
4. [Finish Installation](#finish-installation)

## Install by uploading package

### Upload

If you obtained Zikula Core from the CI server, then you can upload the entire archive (`.zip` or `.tgz` file)
to your server and then `unzip` them there. (This is faster and much more reliable than uploading many small
files via FTP). **Make sure to include the `-p` flag** if you extract from `.tgz` to keep the correct permissions
(for example: `tar -xpzvf Zikula_Core-3.0.0.build27.tar.gz`) change the filename to match the current download.
Copy all the files and directories to your server and point your webroot (typically `public_html` or `httpdocs`)
to the `public/` directory in the Zikula directory structure. Using symlinks or modifying the `.htaccess` file can
accomplish this. (see https://symfony.com/doc/current/setup/web_server_configuration.html for more information).

Another option can be to download the tar.gz directly from the github: e.g. 
`wget https://github.com/zikula/core/releases/download/3.0.0/3.0.tar.gz`. 
Again, change the filename/url to match the correct filename from the ci server.

Windows/FTP users: Take care about copying all files. If there are some files you are not able to transfer 
to the server check if your longest path length is longer than Windows/FTP-Software allows (more than 256 characters).

### Set file permissions (Critical)

If you installed from a `.zip` archive or uploaded the files via FTP, the permissions for some folders must be changed 
prior to installing so that your webserver's user has write access:

- `config`
- `config/dynamic`
- `var/cache`
- `var/log`
- `public`

You normally do so using `chmod 777 var/cache` and so on. (`.tgz` archives maintain
the permission settings as they were set correctly by the development team, if you unpacked it using the `-p` flag).

## Installing using Composer (for developers)

Zikula makes use of [Composer](https://getcomposer.org/) to manage and download all dependencies.
If cloning via GitHub, Composer must be run prior to installation. Run:

```
composer self-update
composer install
```

If you store Composer in the root of the Zikula Core checkout, please rename it from `composer.phar` to `composer`
to avoid your IDE reading the package contents.

## Vagrant installation

You can use vagrant to easily setup a complete Zikula development environment.
All you need to do is install [Vagrant](https://vagrantup.com) and
[VirtualBox](https://www.virtualbox.org/). Then run `vagrant up` inside the
cloned repository and wait for the machine to boot (first time booting might
take several minutes). Then head over to `localhost:8080` and install Zikula.
Database user, password and table are all set to `zikula`. PHPMyAdmin is
accessible from `localhost:8081`.

## Finish Installation

*Note 1:* One common issue when installing is that the `var/cache` and `var/log` directories must be writable both by the 
web server and the command line user. See Symfony's [Setting up or Fixing File Permissions](https://symfony.com/doc/current/setup/file_permissions.html) 
to see potential solutions to this problem when installing from the CLI.

*Note 2:* If you have `mod_suexec` installed for Apache the CLI will run into permission problems. (If you are not sure 
check your phpinfo.) `mod_suexec` often is used in shared hosting environments. In this case, the CLI installer is not 
recommended, please use the Web Installer. 

### Create the Database

Create a database on your server. Take note of the database **name** as well as the database **username** and
**password** which are possibly given by your provider. These will be needed during install. You can use an existing
database, but this is not recommended unless Zikula will be the only application using that database. In this case,
remove all existing tables from the existing database.

### Install by the Web installer or Command Line (not both!)

#### Web Installer

To begin the installer, simply visit the webroot directory with your browser, e.g. `http://www.example.com/`.
If you installed Zikula into a subdirectory 'foo' the URL would be `http://www.example.com/foo/`. You will be
automatically redirected to the installer.

#### Command Line Installer

Via CLI, access your main zikula directory and run this command:

```shell
php bin/console zikula:install:start
```

Follow the prompts and complete that step. When you are finished, you are directed to run the next command:

```shell
php bin/console zikula:install:finish
```

Open your browser and login!
