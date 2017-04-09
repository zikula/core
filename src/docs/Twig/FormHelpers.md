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

When using `ZikulaFormExtensionBundle:Form:bootstrap_3_zikula_admin_layout.html.twig` you can specify 'help' text, 
'alert' text and 'input_group' parameters for each form element. 
 - Help texts are rendered as bootstrap `help-block small` class (see [bootstrap forms](http://getbootstrap.com/css/#forms)).
 - Alert texts are rendered as a [bootstrap alert](http://getbootstrap.com/components/#alerts).
 - Input groups are rendered as [bootstrap input-group](http://getbootstrap.com/components/#input-groups)

Help text can be a simple text value. Input groups must be an array with the position as key and the content as value.

    ->add('foo', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
        'help' => 'Foo help text.',
        'input_group' => ['left' => '<i class="fa fa-rocket"></i>', 'right' => 'some text']
    ])

It is also possible to have multiple help text elements using an array:

    ->add('foo', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
        'help' => ['Foo help text.', 'Bar another text.']
    ])

Alert texts must be an array with the keys as the text and the value as the type:

    ->add('foo', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
        'alert' => ['Foo alert text.' => 'warning', 'Bar alert text.' => 'danger']
    ])

When using `ZikulaFormExtensionBundle:Form:form_div_layout.html.twig` you can specify an 'icon' parameter to button form elements. 

    ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
        'label' => $options['translator']->__('Save'),
        'icon' => 'fa-check',
        'attr' => ['class' => 'btn btn-success']
    ])


In addition, the `help`, `alert`, `input_group` and `icon` parameters can be set in the template if desired.
