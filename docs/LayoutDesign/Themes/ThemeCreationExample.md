---
currentMenu: themes
---
# Creating a theme based on ZikulaBootstrapTheme

If you want to create your own theme, the starting point of reusing the ZikulaBoostrapTheme is a good idea. This 
example is based on the ZikulaBoostrapTheme of Zikula 1.4.5

- First create a new vendor folder inside the extensions folder. It should start with a capital letter. For our example we should start with `Company`.
- In this folder we will now create a theme folder. Let us name it `PaulaTheme`. So we now have the following: `/src/extensions/Company/PaulaTheme`.
- Copy all the files and folder of the folder ZikulaBootstrapTheme into the folder `PaulaTheme`.
- Rename `Company/PaulaTheme/ZikulaBootstrapTheme.php` to `Company/PaulaTheme/CompanyPaulaTheme.php`.
- Change the content as follows:

```php
namespace Company\PaulaTheme;

use Zikula\ExtensionsModule\AbstractTheme;

class CompanyPaulaTheme extends AbstractTheme
{
}
```

- Next the `composer.json` file must be changed. It is also located inside the theme root.
  It should look like:

```json
{
    "name": "company/paula-theme",
    "version": "1.0.0",
    "description": "Our fancy Bootstrap based theme",
    "type": "zikula-theme",
    "license": "LGPL-3.0+",
    "authors": [
        {
            "name": "Company",
            "homepage": "http://company.tld/"
        }
    ],
    "autoload": {
        "psr-4": { "Company\\PaulaTheme\\": "" }
    },
    "require": {
        "php": ">=5.4.1"
    },
    "extra": {
        "zikula" : {
            "core-compatibility": ">=1.4.5",
            "class": "Company\\PaulaTheme\\CompanyPaulaTheme",
            "displayname": "Paula",
            "capabilities": {
                "user": {
                    "theme": true
                },
                "admin": {
                    "theme": true,
                    "icon": "fab fa-bootstrap"
                }
            }
        }
    }
}
```

- In `home.html.twig`, `master.html.twig` and `admin.html.twig` you have to change all instance of `@ZikulaBoostrapTheme`
  to `@CompanyPaulaTheme`. They are located in `/src/extensions/Company/PaulaTheme/Resources/views`
- Do the same in `header.html.twig` which is located in `/src/extensions/Company/PaulaTheme/Resources/views/include`
- Next change `{{ knp_menu_render('ZikulaBootstrapTheme:AdminMenu:menu') }}` with 
  `{{ knp_menu_render('CompanyPaulaTheme:AdminMenu:menu') }}`
- There is a file named `AdminMenu` in the `/Menu` directory. Inside this file you will find the namespace. You have to adjust
  this accordingly: `namespace Company\PaulaTheme\Menu;`
- at the end you have to adjust the bootstrap css file. It is located inside `config/theme.yaml`.
  Normally it looks like `bootstrapPath: "@ZikulaBootstrapTheme:css/cerulean.min.css"`.
  It should get the right path: `bootstrapPath: "@CompanyPaulaTheme:css/cerulean.min.css`

Now you should be able to activate your theme.
