##in PHP files:

use Rector (see comments below)
replace method ->__(message) with ->trans(message)
replace method ->__f(message, ['key' => $var]) with ->trans(message, ['key' => $var])
`_n()` and `_fn()` will need to be manually refactored

replace interface Zikula\Common\Translator\TranslatorInterface
    with Symfony\Contracts\Translation\TranslatorInterface
    
alter use of TranslatorTrait
    - remove old methods
    - add `trans` method with automatic domain setting (maybe from bundle instead of Translator?)
    - also auto-set the locale?

look at \Zikula\Core\Controller\AbstractController use of translation (e.g. `decorateTranslator`) is this functional?
 can we override `$this->trans` calls with module domain automatically?

Maybe still use our own Translator and override `trans` method with our custom domains (use decoration)
  https://stackoverflow.com/questions/39470596/replacing-the-translator-service-in-symfony-3

Maybe a listener to add something to every template to set the domain?
    `{% trans_default_domain "custom_domain" %}`

##in .twig files:

PHPStorm (java-type) regex - replace: (check 'regex' and 'file mask: *.twig')
  - remove the escaped curly braces for php-type regex (`\{` v `{`)

\{\{\s+__\(('|")(.*?)\1\)\s+\}\}
replace with 
\{% trans %\}$2\{% endtrans %\}


\{\{\s+__f\(('|")(.*?)\1,\s(\{.*?\})\)\s+\}\}
replace with
\{% trans with $3 %\}$2\{% endtrans %\}

_n() and _fn() should be manually refactored


##Extraction

e.g. command: `bin/console translation:update --force en ZikulaBlocksModule`
 - note setting the prefix to "" (`--prefix=""`) might be useful in creating the first English Translation
 - yaml seems a lot easier than xliff
 - can set the domain with `--domain=mySpecialDomain`


##problems with rector Rector\Renaming\Rector\MethodCall\RenameMethodCallRector
replacing more methods than only `__` and `__f` in (at least):

5) src/lib/Zikula/Bundle/CoreBundle/Twig/Extension/GettextExtension.php
16) src/lib/Zikula/Bundle/FormExtensionBundle/Form/Type/InlineFormDefinitionType.php
17) src/lib/Zikula/Bundle/HookBundle/Hook/AbstractHookListener.php
20) src/lib/Zikula/Common/Content/AbstractContentType.php
21) src/lib/Zikula/Common/Translator/IdentityTranslator.php
22) src/lib/Zikula/Common/Translator/Translator.php
23) src/lib/Zikula/Core/AbstractExtensionInstaller.php
24) src/lib/Zikula/Core/Controller/AbstractController.php
26) src/modules/zikula/legal-module/Listener/UsersUiListener.php
38) src/system/BlocksModule/AbstractBlockHandler.php

##Actual usage in tempalte for plural+

    {% trans with {'%count%': node.children.count} %}n.direct1.child{% endtrans %}<br>
    {% trans count node.children.count with {'%count%': node.children.count} %}n.direct2.child{% endtrans %}<br>
    {% trans count node.children.count with {'%children%': node.children.count} %}(%children%).direct3.child{% endtrans %}<br>
    {% trans count node.children.count %}n.direct4.child{% endtrans %}<br>

with translations:

    n.direct1.child: "{count, plural,\n  one   {one direct child}\n  other {# direct children}\n}"
    n.direct2.child: "{count, plural,\n  one   {one direct child}\n  other {# direct children}\n}"
    n.direct3.child: "{count, plural,\n  one   {one direct child}\n  other {{children} direct children}\n}"
    n.direct4.child: "{count, plural,\n  one   {one direct child}\n  other {# direct children}\n}"

assuming count=2 produces

    2 direct children
    2 direct children
    __(%children%).direct3.child
    2 direct children

So the fourth option `{% trans count node.children.count %}` seems to be the most efficient
