---
currentMenu: dev-extensions
---
# Extension Maker

> "Thank the Maker!" - C3P0

The CoreBundle provides a CLI function which takes advantage of the Symfony MakerBundle ecosystem.
This easy to use function will quickly generate the skeleton of a fully functional and installable
Zikula Extension (Module or Theme). To use it, you must have a fully functional Zikula installation (this should
be done locally of course). Put your installation into `dev` mode by altering the `APP_ENV` value in the `.env.local`
file and change it from `prod` to `dev`. Then decide on a 'namespace' - this will be how your extension is named.
For example, if you are generating a Blog module and your name is Fabien, you could select `Fabien/Blog`. The first
part is called the _vendor_ (in this case 'Fabien') and the second part is going to be the name of the extension.
The full extension name combines these into FabienBlogModule.

Now call the function with your namespace and choose and extension type (Module or Theme). In this case, we are
generating a _Module_.

```shell
php bin/console make:zikula-extension Fabien/Blog Module
```

The Extension is fully generated in the `src/extensions` directory.
When that is complete, you will be reminded to execute two more commands:

```shell
php bin/console cache:clear
```

```shell
php bin/console zikula:extension:install FabienBlogModule
```

After that is complete, you can take full advantage of the rest of the Symfony/MakerBundle commands to create the
rest of your extension. For a full list of Makers, execute this command:

```shell
php bin/console list make
```

Use the Makers to create Controllers, Commands, Crud operations, Forms and so much more. And of course, if you prefer,
you can always manually create anything you like. You are not restricted by the the makers!

## make:set-namespace

You can use Symfony's MakerBundle to create new classes and files for any existing Extension, but to do so, you must
set the config up properly to use the namespace of the Extension you are working on. To do so, execute this command:

```shell
php bin/console make:set-namespace Acme/FooTheme
```

Please note that the namespace in this command differs slightly than the generator above because it includes
the fully generated namespace with the suffix.

## note:

For CLI the CLI commands, the arguments including slashes can be either `/` or `\\` (actually meaning one forward slash).
The command will correct the type used in the various files.
