---
currentMenu: install
---
# Production

In an ideal situation, you would test your installation of Zikula in a local (developmet) environment before
moving the entire site into "production". This process is called _deployment_. 

Symfony provides documentation for this process [here](https://symfony.com/doc/current/deployment.html). 

Before deploying your site, you should **remove all unused Zikula extensions**. 

Another recommended step is to run `composer dump-autoload -a` (authoritative mode). This will allow all supported
Zikula core classes to load faster. (see composer's [autoloader optimization](https://getcomposer.org/doc/articles/autoloader-optimization.md)).
It will also pre-cache all annotation classes in supported classes which will also speed up loading.

Beware however, as classes that are generated at runtime will not autoload and this [could cause issues](https://getcomposer.org/doc/articles/autoloader-optimization.md#trade-offs-2).
