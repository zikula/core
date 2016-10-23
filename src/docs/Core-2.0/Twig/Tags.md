Twig Tags Provided by Zikula Core
=================================

The following twig tags are available in templates. These are in addition to the standard tags provided
by the Twig package itself. See [Twig Documentation](http://twig.sensiolabs.org/documentation) for more information.
Also see [standard Symfony functions](http://symfony.com/doc/current/reference/twig_reference.html) for additional
functions, filters, tags, tests and global variables.

Switch
------

    {% switch variable %}
        {% case val_1 %}
            code for val_1
            (notice - here's not break)
        {% case val_2 %}
            code for val_2
            {% break %}
        {% default %}
            code for default case
    {% endswitch %}
