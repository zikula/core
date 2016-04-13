Form Helpers
============

Using Symfony forms, there is often a need for various customizations in the template. Some of these are simplified
in Zikula.


Form Theming
------------

Zikula provides two form themes that can be used together or separately

    {% form_theme form with [
        'ZikulaFormExtensionBundle:Form:bootstrap_3_zikula_admin_layout.html.twig',
        'ZikulaFormExtensionBundle:Form:form_div_layout.html.twig'
    ] %}

The `ZikulaFormExtensionBundle:Form:bootstrap_3_zikula_admin_layout.html.twig` theme automatically 'bootstrapifies' the
form so all form elements use the bootstrap css stylesheet. This is an extension of Symfony's own bootstrap theme.
Additional customizations are added to format the form in a standard Zikula 'admin' style form.


Input Additions
---------------

When using `ZikulaFormExtensionBundle:Form:bootstrap_3_zikula_admin_layout.html.twig` you can specify 'help' text and
'input_group' parameters for each form element:

    ->add('foo', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
        'help' => 'Foo help text.',
        'input_group' => ['left' => '<i class="fa fa-rocket"></i>', 'right' => 'some text']
    ])


When using `ZikulaFormExtensionBundle:Form:form_div_layout.html.twig` you can specify an 'icon' parameter to button form elements. 

    ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
        'label' => $options['translator']->__('Save'),
        'icon' => 'fa-check',
        'attr' => ['class' => 'btn btn-success']
    ])


In addition, the `help`, `input_group` and `icon` parameters can be set in the template if desired.
