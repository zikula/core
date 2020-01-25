# Provider workflow for `form_aware_hook`

Recommend use of class constants for all hook names.

when adding form children include all `constraints` required to validate each child.

Response templates should include as little formatting as possible (e.g. no `fieldset`, etc.).

If add entire form:

```php
public function edit(FormAwareHook $hook)
{
    $myForm = $this->formFactory->createNamedBuilder('fooName', FormType::class);
    $myForm->add('foo', TextType::class);
    $hook
        ->formAdd($myForm)
        ->addTemplate('@ZikulaFooHookModule/Hook/test.html.twig')
    ;
}
```

and `test.html.twig`:

```twig
{% for element in form.fooName %}
    {{ form_row(element) }}
{% endfor %}
```

then

```php
public function processEdit(FormAwareResponse $hook)
{
    $data = $hook->getFormData('fooName');
}
```

OR

If add only one field:

```php
public function edit(FormAwareHook $hook)
{
    $hook
        ->formAdd('test', TextType::class)
        ->addTemplate('@ZikulaFooHookModule/Hook/test.html.twig')
    ;
}
```

and `test.html.twig`:

```twig
{{ form_row(form.test) }}
```

then

```php
public function processEdit(FormAwareResponse $hook)
{
    $test = $hook->getFormData('test');
}
```
