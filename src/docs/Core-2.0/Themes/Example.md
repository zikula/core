If you want to create an own theme the starting point of reusing the ZikulaBoostrapTheme would be a good idea. This example is based on the ZikulaBoostrapTheme of Zikula 1.4.5

* First you have to create a new vendor folder inside the themes folder. It should start with a capital letter. For our example we should start with ``Company``
* In this folder we will now create a theme folder. Let us name it ``Paula``. So wee do have now the following: ``themes/Company/Paula``.
* We now copy all the files and folder of the folder ZikulaBoostrapTheme into the folder Paula.
* in Paula you will find a file named ``ZikulaBootstrapTheme.php``. This we will rename to ``CompanyPaulaTheme.php``
* There we have to change the content as following:
````
namespace Company\PaulaTheme;

use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreTheme;

class CompanyPaulaTheme extends AbstractCoreTheme
````
* next the ``composer.json`` file mus be changed. It is also located inside the theme root. It should looks like:
````
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
                "user": true,
                "admin": true
            }
        }
    }
}
````
* the ``theme/Company/Paula/Resources/config/overrides.yml`` needs to be updated for the paths. Otherwise the theme would use the overrides of the original ZikulaBootstrapTheme
* in ``home.html.twig``, ``master.html.twig`` and ``admin.html.twig`` you have to exchange all ``@ZikulaBoostrapTheme`` with ``@CompanyPaulaTheme``. They are located in ``theme/Company/Paula/Resources/views``
* same you have to do in ``header.html.twig`` which is located in ``theme/Company/Paula/Resources/views/include``
* at the end you have to exchange the ``{{ knp_menu_render('ZikulaBootstrapTheme:AdminMenu:menu') }}`` with ``{{ knp_menu_render('CompanyPaulaTheme:AdminMenu:menu') }}``

Now you should be able to activate your theme.
