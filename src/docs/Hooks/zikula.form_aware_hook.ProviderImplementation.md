Provider Workflow for `form_aware_hook`
=======================================

recommend use of class constants for all hook names

when adding form children include all `constraints` required to validate each child.

response templates should include as little formatting as possible (e.g. no `fieldset`, etc.)

If add entire form:

    public function edit(FormAwareHook $hook)
    {
        $myForm = $this->formFactory->createNamedBuilder('fooName', FormType::class);
        $myForm->add('foo', TextType::class);
        $hook
            ->formAdd($myForm)
            ->addTemplate('@ZikulaFooHookModule/Hook/test.html.twig');
    }

and `test.html.twig`

    {% for element in form.fooName %}
        {{ form_row(element) }}
    {% endfor %}

then

    public function processEdit(FormAwareResponse $hook)
    {
        $data = $hook->getFormData('fooName);
    }

OR

If add only one field

    public function edit(FormAwareHook $hook)
    {
        $hook
            ->formAdd('test', TextType::class)
            ->addTemplate('@ZikulaFooHookModule/Hook/test.html.twig');
    }

and `test.html.twig`

    {{ form_row(form.test) }}

then

    public function processEdit(FormAwareResponse $hook)
    {
        $test = $hook->getFormData('test');
    }
