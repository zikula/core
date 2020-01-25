# Searchable

The Api/methodology for the search module requires a dedicated class which must implement `Zikula\SearchModule\SearchableInterface`.
Please see the interface itself for documentation of the required methods.

The `getResults()` method **MUST** return an array of `Zikula\SearchModule\Entity\SearchResultEntity`.

The `amendForm()` method accepts an argument that is an instance of `Zikula\SearchModule\Form\Type\AmendableModuleSearchType`
which already includes an `active` child object. Utilize this object to add additional child objects to the general 
search form object. 

The **UsersModule** has implemented the new Search method (`Zikula\UsersModule\Helper\SearchHelper`) and can be used as
a simple reference.
