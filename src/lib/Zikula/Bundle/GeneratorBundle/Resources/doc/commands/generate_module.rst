Generating a New Module Skeleton
================================

Usage
-----

The ``generate:module`` generates a new module structure and automatically
activates it in the application.

By default the command is run in the interactive mode and asks questions to
determine the module name, location, configuration format and default
structure:

.. code-block:: bash

    php app/console generate:module

To deactivate the interactive mode, use the `--no-interaction` option but don't
forget to pass all needed options:

.. code-block:: bash

    php app/console generate:module --namespace=Acme/Bundle/BlogModule --no-interaction

Available Options
-----------------

* ``--namespace``: The namespace of the module to create. The namespace should
  begin with a "vendor" name like your company name, your project name, or
  your client name, followed by one or more optional category sub-namespaces,
  and it should end with the module name itself (which must have Module as a
  suffix):

  .. code-block:: bash

        php app/console generate:module --namespace=Acme/Bundle/BlogModule

* ``--module-name``: The optional module name. It must be a string ending with
  the ``Module`` suffix:

    .. code-block:: bash

        php app/console generate:module --module-name=AcmeBlogModule

* ``--dir``: The directory in which to store the module. By convention, the
  command detects and uses the applications's ``src/`` folder:

    .. code-block:: bash

        php app/console generate:module --dir=/var/www/myproject/src

* ``--format``: (**annotation**) [values: yml, xml, php or annotation]
  Determine the format to use for the generated configuration files like
  routing. By default, the command uses the ``annotation`` format. Choosing
  the ``annotation`` format expects the ``SensioFrameworkExtraBundle`` is
  already installed:

    .. code-block:: bash

        php app/console generate:module --format=annotation

* ``--structure``: (**no**) [values: yes|no] Whether or not to generate a
  complete default directory structure including empty public folders for
  documentation, web assets and translations dictionaries:

    .. code-block:: bash

        php app/console generate:module --structure=yes
