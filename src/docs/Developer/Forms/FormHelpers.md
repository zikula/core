# Form helpers

Using Symfony forms, there is often a need for various customizations in the template. Some of these are simplified
in Zikula.

## Form theming

Zikula provides two form themes that can be used together or separately

```twig
{% form_theme form with [
    '@ZikulaFormExtension/Form/bootstrap_4_zikula_admin_layout.html.twig',
    '@ZikulaFormExtension/Form/form_div_layout.html.twig'
] %}
```

The `@ZikulaFormExtension/Form/bootstrap_4_zikula_admin_layout.html.twig` theme automatically 'bootstrapifies' the
form so all elements use the Bootstrap 4 form styles. This is an extension of Symfony's own bootstrap theme.
Additional customizations are added to format the form in a standard Zikula 'admin' style form.

## Input additions

When using `@ZikulaFormExtension/Form/bootstrap_4_zikula_admin_layout.html.twig` you can specify 'help' text, 
'alert' text and 'input_group' parameters for each form element. 

- Help texts are rendered as bootstrap `form-text text-muted` class (see [Bootstrap forms](https://getbootstrap.com/docs/4.4/components/forms/)).
- Alert texts are rendered as a [Bootstrap alert](https://getbootstrap.com/docs/4.4/components/alerts/).
- Input groups are rendered as [Bootstrap input-group](https://getbootstrap.com/docs/4.4/components/input-group/)

Help text can be a simple text value. Input groups must be an array with the position as key and the content as value.

```php
->add('foo', TextType::class, [
    'help' => 'Foo help text.',
    'input_group' => ['left' => '<i class="fas fa-rocket"></i>', 'right' => 'some text']
])
```

It is also possible to have multiple help text elements using an array:

```php
->add('foo', TextType::class, [
    'help' => ['Foo help text.', 'Bar another text.']
])
```

You can use HTML inside help messages if you enable the `help_html` option:

```php
->add('foo', TextType::class, [
    'help' => '<a target="_blank" href="...">Look up your ZIP code.</a>',
    'help_html' => true
])
```

Alert texts must be an array with the keys as the text and the value as the type:

```php
->add('foo', TextType::class, [
    'alert' => ['Foo alert text.' => 'warning', 'Bar alert text.' => 'danger']
])
```

When using `@ZikulaFormExtension/Form/form_div_layout.html.twig` you can specify an 'icon' parameter to button form elements. 

```php
->add('save', SubmitType::class, [
    'label' => $options['translator']->__('Save'),
    'icon' => 'fa-check',
    'attr' => ['class' => 'btn btn-success']
])
```

In addition, the `help`, `alert`, `input_group` and `icon` parameters can be set in the template if desired.
