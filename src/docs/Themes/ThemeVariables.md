Theme Variables
===============

filepath: `/Resources/config/variables.yml`

The contents of this file can vary. At the very list, it can be a list of variables and values
that will be passed along to the theme engine as global variables within your theme, e.g.:

    {{ themevars.<variablename> }}

Additionally, dynamically created forms (using the symfony form engine) can be used by creating a yml definition like so:

    home:
      default_value: 3col_w_centerblock
      type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType'
      options:
        choices_as_values: true
        choices:
          'One column': 1col
          'Two columns': 2col
          'Two columns with centerblock': 2col_w_centerblock
          'Three columns': 3col
          'Three columns with centerblock': 3col_w_centerblock

These pseudo-forms are created an available to the site admin through the Theme Module UI. The selected value then
becomes available to templates as referenced above.
