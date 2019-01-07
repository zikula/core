Dynamic Form Fields
===================

The `FormExtensionBundle` offers helpers for handling dynamic form fields (*properties*). This can be helpful for several applications, like contact form builders or survey modules. The default Profile module uses this functionality to handle definition and management of user profile data.

Dynamic selection of field types and corresponding options
----------------------------------------------------------

The `Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicFieldType` class provides a form type which consists of two parts. First a choice field which allows to select a field type using a dropdown list. Upon selection further field-specific form fields for the field options are loaded using ajax and dynamically added/replaced in the form. To use this add something like `$builder->add('fieldInfo', DynamicFieldType::class, ['label' => false]);` to your form type's `buildForm` method.

Sometimes you want to amend or extend the field type list. For example profile modules may want to add an avatar field type. Similarly other custom types may be relevant for other applications. For this purpose you can listen for an event provided by the `Zikula\Bundle\FormExtensionBundle\Event\FormTypeChoiceEvent` class. Implementation inside your listener might look similar to this example:

```php
    public function formTypeChoices(FormTypeChoiceEvent $event)
    {
        $groupName = $this->translator->__('Other Fields');

        $choices = $event->getChoices();
        if (!isset($choices[$groupName])) {
            $choices[$groupName] = [];
        }
        $choices[$groupName][] = AvatarType::class;
    }
```

Management and storage of field definitions
-------------------------------------------

Each module should be able to decide how and where to persist the property data. The `FormExtensionsBundle` needs to receive different information though. Hence, there are two interfaces used for proper communication:

1. `Zikula\Bundle\FormExtensionBundle\DynamicFieldsContainerInterface`
   - Represents a form object (“data_class”) containing dynamic fields.
   - Provides a list of field specifications by implementing a `getDynamicFieldsSpecification()` method.
2. `Zikula\Bundle\FormExtensionBundle\DynamicFieldInterface`
   - Represents a single field specification.
   - Provides detail information for the form handling with the following methods:
       - `getName()` (returns name of form field)
       - `getPrefix()` (returns optional prefix of form field)
       - `getLabels()` (returns a list of labels per locale)
       - `getLabel($locale = '', $default = 'en')` (returns label for a specific locale)
       - `getFormType()` (returns the FqCN of the form class (e.g. `return IntegerType::class;`))
       - `getFormOptions()` (returns an array of form options)
       - `getWeight()` (returns a weighting number for sorting fields; this is currently not utilised, but reserved for future usage)
       - `getGroupNames()` (returns a list of group names per locale; may optionally be used for dividing fields into several fieldsets)

This way any module has complete freedom about loading and saving the data (database, YAML file, web service, and so on). At the same time the dynamic fields information are ensured to be provided properly.

Loading and usage of field definitions
--------------------------------------

Another form type implemented by the `Zikula\Bundle\FormExtensionBundle\Form\Type\InlineFormDefinitionType` class allows for central inclusion of the dynamic sub part of a form (e.g. profile fields). So a module can just use one form type for adding the defined fields for a given data object form.

Example:

```php
    $formBuilder->add('dynamicFields', InlineFormDefinitionType::class, [
        'dynamicFieldsContainer' => $this->propertyRepository,
        'prefix' => $this->prefix,
        'translator' => $this->translator,
        'label' => false,
        'inherit_data' => true
    ]);
```
