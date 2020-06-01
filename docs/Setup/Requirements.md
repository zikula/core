---
currentMenu: requirements
---
# Requirements

- Zikula Core requires PHP >= 7.2.5 (same as Symfony 5).
- Additional server considerations can be found on [the Symfony site](https://symfony.com/doc/current/setup.html#technical-requirements).
- Zikula requires more memory than typical to install. You should set your memory limit in `php.ini`
  to 128 MB for the installation process.
- Zikula requires that `date.timezone` be set in the `php.ini` configuration file (or `.htaccess`).
- Zikula requires `AllowOverride All` and the `mod_rewrite` module (be aware the Apache 2.3.9+ has changed
  the default setting for `AllowOverride` to `None`).
- Zikula also requires other PHP extensions and configurations. These are checked during the installation
  process and if there are problems, you will be notified. If you discover errors, check with your hosting
  provider on how to rectify these issues. Typically, they will require changing the `php.ini` file or
  possibly reconfiguring the PHP installation by your provider.
