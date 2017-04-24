TranslatorTrait
===============

name: \Zikula\Common\Translator\TranslatorTrait

Adds the following methods to your class:

 - \__($msg, $domain = null, $locale = null)
 - _n($m1, $m2, $n, $domain = null, $locale = null)
 - \__f($msg, $param, $domain = null, $locale = null)
 - _fn($m1, $m2, $n, $param, $domain = null, $locale = null)
 - getTranslator()
 
_note: if you are reading this unrendered, the slash characters above are not part of the method name. they are escape
characters for the renderer._

You are required to implement a public method `setTranslator($translator)`. 
In your constructor, you are required to call the `setTranslator()` method and set the `$translator` property.
Typically this will be set to the `'translator'` service.

See \Zikula\Core\AbstractExtensionInstaller for usage example.
