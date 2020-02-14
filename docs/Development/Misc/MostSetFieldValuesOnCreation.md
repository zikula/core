---
currentMenu: dev-misc
---
# ModuleStudio module: automatically set field values on creation

## Requirement

Automated assignation of values to hidden form fields.

### Case example

Creating a part number consisting of the 'current user ID' & timestamp splitted by dash.

Example: `2-1533134203`

A URL parameter like `&set_partnumber=xyz` is not an option in this case as the user potentially could manipulate the field value, even if the form field is 'read only' or 'hidden'.

Assuming that a user is only logged in once at a time, the combination of userID & timestamp leads to a unique part number.

### Solution

This approach is valid for modules that have been built with [ModuleStudio](https://modulestudio.de/).

1. In `Resources/config/entityFactory.yml` find the definition of your entity intialiser and then add below it's arguments:

    ```yaml
    calls:
    - [setCurrentUserApi, ['@zikula_users_module.current_user']]
    ```

2. Next edit `Entity/Factory/EntityInitialiser.php` and replace

    ```php
    /**
    * Entity initialiser class used to dynamically apply default values to newly created entities.
    */
    class EntityInitialiser extends AbstractEntityInitialiser
    {
        // feel free to customise the initialiser
    }
    ```

    by

    ```php
    use ITThiele\JumbleSeekerModule\Entity\FooEntity;
    use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;

    /**
    * Entity initialiser class used to dynamically apply default values to newly created entities.
    */
    class EntityInitialiser extends AbstractEntityInitialiser
    {
        /**
        * @var CurrentUserApiInterface
        */
        protected $currentUserApi;

        public function setCurrentUserApi(CurrentUserApiInterface $currentUserApi)
        {
            $this->currentUserApi = $currentUserApi;
        }

        /**
        * @inheritDoc
        */
        public function initFoo(FooEntity $entity)
        {
            $entity = parent::initFoo($entity);

            $userId = $this->currentUserApi->get('uid');
            $entity->setTableColumn($userId . '-' . time());

            return $entity;
        }
    }
    ```

  - Replace `Foo` with the name of your Entity.
  - Replace `TableColumn` with the name of the column you want to fill automatically.

3. Inside MOST look at the application properties of your model and do the following changes to prevent the generator from overriding your changes:

  - add `Resources/config/entityFactory.yml` to `markFiles`
  - add `Entity/Factory/EntityInitialiser.php` to `skipFiles`

4. Also inside MOST look at the properties of the `TableColumn` field and set `visible=false`.

5. Regenerate and copy/move the new files to your existing installation.

6. Test if it works (the TableColumn field should not be part of the edit form anymore, but it's value should be set automatically without any GET or POST parameters).
