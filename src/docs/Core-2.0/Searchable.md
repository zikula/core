Searchable
==========

The Api/methodology for the search module has changed again for Core-2.0. All previous methods are deprecated (but still
fully functional) in favor of a dedicated class that is identified as a DependencyInjection tagged service, like so:

```
    acme_foo_module.helper.search_helper:
        class: Acme\FooModule\Helper\SearchHelper
        arguments:
          - '@zikula_permissions_module.api.permission'
          - '@twig'
          - '@session'
          - '@acme_foo_module.fooentity_repository'
        tags:
            - { name: zikula.searchable_module, bundleName: AcmeFooModule }
```

The class must implement `Zikula\SearchModule\SearchableInterface` Please see the interface itself for documentation 
of the required methods.

The `getResults()` method **MUST** return an array of `Zikula\SearchModule\Entity\SearchResultEntity`.

The `amendForm()` method accepts an argument that is an instance of `Zikula\SearchModule\Form\Type\AmendableModuleSearchType`
which already includes an `active` child object. Utilize this object to add additional child objects to the general 
search form object. 

The **UsersModule** has implemented the new Search method (`Zikula\UsersModule\Helper\SearchHelper`) and can be used as
a simple reference.
