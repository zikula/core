---
currentMenu: dev-general
---
# Refactoring for 2.0

**Note:** this information is quite outdated but could still be useful when you need to migrate code from Zikula 1.x to 2.0. It could be easier to rewrite it for 3.x directly though.

## Purpose

This document contains explanations of how to implement various features in Zikula 2.0, especially if you are migrating from previous versions of Zikula. I wrote this guide as I worked through upgrading some of my modules. Currently, it is not in any order, just what I came across as I worked on upgrading my modules.

For this cookbook, I will call my example module FooModule and all examples will go off of that. You should replace FooModule with whatever your module is called. The namespace I will use is the one I use for all my modules, namely, Paustian. You will see this in the implementation of all the the example files.

## Module metadata

In previous Zikula versions, module metadata was stored in a version php file entitled `FooModuleVersion.php`. Inside this module you would implement a function, getMetaData. This function would fill an array with pertinent information about your module and return it. The function would be something like this:

```php
namespace Paustian\BookModule;

class BookModuleVersion extends \Zikula_AbstractVersion
{
    public function getMetaData()
    {
        $meta = [];
        $meta['name'] = __('Book');
        $meta['version'] = '3.0.0';
        $meta['displayname'] = __('Book');
        $meta['description'] = __('A module for displying a large structured document, creating figure descriptions for the book, and a glossary.');
        // this defines the module's url and should be in lowercase without space
        $meta['url'] = $this->__('book');
        $meta['core_min'] = '1.4.0'; // Fixed to 1.3.x range
        $meta['capabilities'] = [
            HookUtil::SUBSCRIBER_CAPABLE => ['enabled' => true],
            AbstractSearchable::SEARCHABLE => ['class' => 'Paustian\BookModule\Helper\SearchHelper']
        ];
        $meta['securityschema'] = ['PaustianBookModule::' => 'Book::Chapter'];
        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://http://www.bact.wisc.edu/faculty/paustian/';

        return $meta;
    }
}
```

In Zikula 2.0, this has been replaced by a .json file that is read in to get the metadata. For any module placed into the modules folder, if you go to the extensions page to read it in, Zikula scans the root folder of the module for a file entitled, `composer.json`. This is then parsed to obtain the metadata. It is a required file in 2.0. Below I place the same information that used to be in the module version php file into a `composer.json` file.

```json
{
    "name": "paustian/book-module",
    "version": "4.0",
    "description": "A module for displying a large structured document, creating figure descriptions for the book, and a glossary.",
    "type": "zikula-module",
    "license": "LGPL-3.0+",
    "authors": [
        {
            "name": "Timothy Paustian",
            "homepage": "http://www.microbiologytext.com/"
        }
    ],
    "autoload": {
        "psr-4": {
            "Paustian\\BookModule\\": ""
        }
    },
    "require": {
        "php": ">5.4.0"
    },
    "extra": {
        "zikula": {
            "url": "book",
            "class": "Paustian\\BookModule\\PaustianBookModule",
            "core-compatibility": ">=1.4.3",
            "displayname": "Book Module",
            "oldnames": [],
            "capabilities": {
                "admin": {"route": "paustianbookmodule_admin_edit"},
                "user": {"route": "zikulausersmodule_user_view"}
            },
            "securityschema": {
                "PaustianBookModule::": "Book::Chapter"
            }
        }
    }
}
```

As you can see it contains all the same information, just in another format. One major exception is the capabilities area. This is where you define the default routes that link to your module. This are used in the extension list to point at your module.

## Installation

The code for installation is much simpler and easier to implement. You have to create your Entity class as clearly described in the [Symfony documentation](https://symfony.com/doc/current/doctrine.html). Once you have those, the implementation of the installation file is easy.

Here is an example file to use.

```php
namespace Paustian\BookModule;

use Zikula\Core\AbstractExtensionInstaller;

class BookModuleInstaller extends AbstractExtensionInstaller
{
    private $entities = array(
        'Paustian\BookModule\Entity\BookArticlesEntity',
        'Paustian\BookModule\Entity\BookChaptersEntity',
        'Paustian\BookModule\Entity\BookEntity',
        'Paustian\BookModule\Entity\BookFiguresEntity',
        'Paustian\BookModule\Entity\BookGlossEntity',
        'Paustian\BookModule\Entity\BookUserDataEntity',
    );

    public function install()
    {
        try {
            $this->schemaTool->create($this->entities);
        } catch (Exception $e) {
            return false;
        }
        $this->setVar('securebooks', false);

        return true;
    }
}
```

This function is called only once during the lifetime of your module to install the tables need by your application. The uninstall code is just as easy.

```php
     public function uninstall()
     {
        try {
            $this->schemaTool->drop($this->entities);
        } catch (\PDOException $e) {
            return false;
        }

        // Delete any module variables.
        $this->delVars();

        // Deletion successful*/
        return true;
    }
```

## Providing admin and user links to your module

A well-behaved module will have a number of links listing all the functions that the admin and users can use while using the module. These are normally present as drop-down menus anchored to a menubar. Zikula takes care of all the formatting, and you as the developer just have to provide a hierarchical array of links. Before 2.0 this was handled by having a function `getLinks()` in the `AdminApi.php` and `UserApi.php` classes. In 2.0, this is not allowed and a dedicated class extended from the `LinkContainer` class is required. Implementation of this is a little tricky because you have to make Zikula aware that your module provides this link service. There are two parts to this:

1. First, provide a services file in your Resources/config folder name services.yml that lists all the services your module implements. Here is the code for providing this service.

    ```yaml
     services:
        paustian_book_module.container.link_container:
            class: Paustian\BookModule\Container\LinkContainer
            arguments:
              - "@translator.default"
              - "@router"
              - "@zikula_permissions_module.api.permission"
            tags:
                - { name: zikula.link_container }
    ```

  Note that services must appear at the top of the file. The service listed points to your `LinkContainer` class. 

2. You have to implement this `LinkContainer` class. Inside it you list the links of your module. Here is an example:

    ```php
    namespace Paustian\BookModule\Container;

    use Symfony\Component\Routing\RouterInterface;
    use Zikula\Common\Translator\TranslatorInterface;
    use Zikula\Core\LinkContainer\LinkContainerInterface;
    use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

    class LinkContainer implements LinkContainerInterface
    {
        /**
         * @var TranslatorInterface
         */
        private $translator;

        /**
         * @var RouterInterface
         */
        private $router;

        /**
         * @var PermissionApiInterface
         */
        private $permissionApi;

        /**
         * constructor.
         *
         * @param TranslatorInterface $translator
         * @param RouterInterface $router
         * @param PermissionApiInterface $permissionApi
         **/
        public function __construct(
            TranslatorInterface $translator,
            RouterInterface $router,
            PermissionApiInterface $permissionApi
        )
        {
            $this->translator = $translator;
            $this->router = $router;
            $this->permissionApi = $permissionApi;
        }

        /**
         * get Links of any type for this extension
         * required by the interface
         *
         * @param string $type
         * @return array
         */
        public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
        {
            if (LinkContainerInterface::TYPE_ADMIN == $type) {
                return $this->getAdmin();
            }
            if (LinkContainerInterface::TYPE_ACCOUNT == $type) {
                return $this->getAccount();
            }
            if (LinkContainerInterface::TYPE_USER == $type) {
                return $this->getUser();
            }

            return [];
        }

        /**
         * get the Admin links for this extension
         *
         * @return array
         */
        private function getAdmin()
        {
            $links = [];
        
            if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {


                $submenulinks = [];
                $submenulinks[] = [
                    'url' => $this->router->generate('paustianbookmodule_admin_edit'),
                    'text' => $this->translator->__('Create New Book'),
                    ];

                $submenulinks[] = [
                    'url' => $this->router->generate('paustianbookmodule_admin_modify'),
                    'text' => $this->translator->__('Edit or Delete Book'),
                     ];

                $links[] = [
                    'url' => $this->router->generate('paustianbookmodule_admin_edit'),
                    'text' => $this->translator->__('Books'),
                    'icon' => 'book',
                    'links' => $submenulinks];

                /*more linke here*/

            }
            return $links;
        }

    //You can provide dummy functions if you do no have links for this type.

        private function getUser()
        {
            $links = [];

            return $links;
        }

        private function getAccount()
        {
            $links = [];

            return $links;
        }

        /**
         * set the BundleName as required by the interface
         *
         * @return string
         */
        public function getBundleName()
        {
            return 'PaustianBookModule';
        }
    }
    ```
