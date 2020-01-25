# TranslatorTrait

The trait implemented by `\Zikula\Bundle\CoreBundle\Translator\TranslatorTrait` adds the following methods to your class:

- `trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string`
- `getTranslator(): TranslatorInterface`
- `setTranslator(TranslatorInterface $translator)`
 
In your constructor, you are required to call the `setTranslator()` method and set the `$translator` property.
Typically this will be set to the `'translator'` service.

See `\Zikula\Bundle\CoreBundle\AbstractExtensionInstaller` for usage example.
