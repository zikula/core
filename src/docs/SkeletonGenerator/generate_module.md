Generating a New Module Skeleton
================================

Usage
-----

The `zikula:generate:module` generates a new module structure.

By default the command is run in the interactive mode and asks questions to
determine the namespace, module name, location and configuration format:

    php bin/console zikula:generate:module

To deactivate the interactive mode, use the `--no-interaction` option but don't
forget to pass all needed options:

    php bin/console zikula:generate:module --namespace=Acme/BlogModule --no-interaction

Available Options
-----------------

* `--namespace`: The namespace of the module to create. The namespace should
  begin with a "vendor" name like your company name, your project name, or
  your client name, followed by one or more optional category sub-namespaces,
  and it should end with the module name itself (which must have Module as a
  suffix):

    `php bin/console zikula:generate:module --namespace=Acme/BlogModule`

* `--module-name`: The optional module name. It must be a string ending with
  the `Module` suffix:

    `php bin/console zikula:generate:module --module-name=AcmeBlogModule`

* `--dir`: The directory in which to store the module. By convention, the
  command detects and uses the applications's `src/modules/` folder:

    `php bin/console zikula:generate:module --dir=/var/www/myproject/src`

* `--format`: (**annotation**) [values: yml, xml, php or annotation]
  Determine the format to use for the generated configuration files like
  routing. By default, the command uses the `annotation` format. Choosing
  the `annotation` format expects the `SensioFrameworkExtraBundle` is
  already installed:

    `php bin/console zikula:generate:module --format=annotation`
