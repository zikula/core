Twig Tags Provided by Zikula Core
=================================

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
