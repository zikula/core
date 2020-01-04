##in PHP files:

replace method ->__(message) with ->trans(message)
replace method ->__f(message, ['key' => $var]) with ->trans(message, ['key' => $var])
`_n()` and `_fn()` will need to be manually refactored

replace interface Zikula\Common\Translator\TranslatorInterface
    with Symfony\Contracts\Translation\TranslatorInterface
    
remove use of TranslatorTrait
    - remove `setTranslator()` method
    - add `$translator` private property
    - set `$translator` property in constructor
    - OR... modify TranslatorTrait and remove old method but leave the rest and not remove it


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


##problems
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
