Translator Usage
================

##### Translator service:

The translator service can be obtained from container.
Service is pre-configured to automatically detect current locale, domain is by default set to 'zikula'.
Example from AbstractController obtaining translator and setting new domain.

    //access translator service
    $translator = $bundle->getContainer()->get('translator.default');
    
    // set domain 
    $translator->setDomain($bundle->getTranslationDomain());

##### AbstractController

Zikula Translator is automatically added in AbstractController and you can access it in your module controller using:
 
    $this->translator

Translation examples

    //Symfony native notation
    $translated = $this->translator->trans('Hello World');
    //Zikula translation method
    $translated = $this->translator->__('Page');
    ...
    //shortcut methods also available for native zikula methods e.g.:
    $translated = $this->__('Page');


##### Twig

For translations in Twig Zikula uses CoreGettext extensions apart from native Symfony Twig trans function.
http://symfony.com/doc/current/book/translation.html#translations-in-templates

    //Symfony native notation
    {% trans from "zikula" %}Error! That cannot be done.{% endtrans %}
    {% trans %}Error! That cannot be done'.{% endtrans %}

    //Zikula gettext notation
    {{ __('Done!') }}
    {{ __('Done!', 'zikula') }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}) }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}, 'zikula') }}
    {{ __f('Done! Saved the %s category.',{'%s':'test'}, 'zikula') }}
    {{ _fn('Done! Deleted %1$d user account.', 'Done! Deleted %1$d user accounts.', 1, {'%1$d' : 1}, 'zikula', 'pl')  }}
