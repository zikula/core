# Plural forms

Note: before reading this you should have read the [extraction chapter](Extraction.md) first.

## PHP files

PHP example using plural forms:

```php
// 4. With plural forms and advanced substitution
$this->_fn('User deleted!', '%n users deleted!', count($deletedUsers), ['%n' => count($deletedUsers)]);
/** @Desc("{count, plural,\n  one   {User deleted!}\n  other {# users deleted!}\n}") */
$this->getTranslator()->trans('plural_n.users.deleted', ['%count%' => count($deletedUsers)]);
```

## Twig template files

Here is an example using plural forms, advanced substitution and the `desc` filter:

```twig
Old: {% set amountOfUsers = _fn('%amount% registered user', '%amount% registered users', users|length, {'%amount%': users|length}) %}
New: {% set amountOfUsers = 'plural_n.registered.user'|trans({count: users|length})|desc('{count, plural,\n  one   {one registered user}\n  other {# registered users}\n}') %}
```

The `plural_n` portion of the translation key is simply a convention established to note that this key requires plural translation.

The translation of this would look something like:

```yaml
#messages+intl-icu.en.yaml
plural_n.registered.user: "{count, plural,\n  one   {one registered user}\n  other {# registered users}\n}"
```

## Note about ICU

More advanced translation like plurals and other substitutions require using the Symfony ICU MessageFormatter. See [How to Translate Messages using the ICU MessageFormat](https://symfony.com/doc/current/translation/message_format.html). This requires a specific name format on the translation file and other adjustments.
