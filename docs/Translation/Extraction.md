---
currentMenu: translation
---
# Translation extraction

To extract translations use the console command `translation:extract`.

To see all of it's option, do this:

```
php bin/console translation:extract -h
# or
php bin/console translation:extract --help
```

Example for Zikula core:

```
# extract for all configured locales
php bin/console translation:extract zikula
# extract only for English
php bin/console translation:extract zikula en
```

Note `zikula` is the name of our configuration.

Examples for a module or a theme:

```
php bin/console translation:extract -b AcmeFooModule extension
php bin/console translation:extract --bundle AcmeFooModule extension en
php bin/console translation:extract -b AcmeFooModule acmefoomodule
php bin/console translation:extract --bundle AcmeFooModule acmefoomodule en

# or with more memory:
php -dmemory_limit=2G bin/console translation:extract --bundle AcmeFooModule extension
php -dmemory_limit=2G bin/console translation:extract --bundle AcmeFooModule acmefoomodule en
```

You can always check the status of your translation using the `translation:status` command.
Check the available options using `-h` or `--help` like shown above.

## Translation annotations

To influence the extraction behaviour you can utilise some annotations from the `Translation\Extractor\Annotation` namespace.
Import them like any other php class:

```php
use Translation\Extractor\Annotation\Desc;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
```

### `@Desc`
The `@Desc` annotation allows specifying a default translation for a key.

Examples:

```php
$builder->add('title', 'text', [
    /** @Desc("Title:") */
    'label' => 'post.form.title',
]);

/** @Desc("We have changed the permalink because the post '%slug%' already exists.") */
$errors[] = $this->translator->trans(
    'post.form.permalink.error.exists', ['%slug%' => $slug]
);
```

### desc filter in Twig

There is also a `desc` filter for specifying a default translation for a key in Twig.

Use it like this:

```twig
{{ 'post.form.title'|trans|desc('Title:') }}
{{ 'welcome.message'|trans({'%userName%': 'John Smith'})|desc('Welcome %userName%!') }}
```

### `@Ignore`

The `@Ignore` annotation allows ignoring extracting translation subjects which are not a string, but a variable.
You can use it for example for `trans()` calls, form labels and form choices.

Examples:

```php
echo $this->translator->trans(/** @Ignore */$description);

$builder->add('modulecategory' . $module['name'], ChoiceType::class, [
    /** @Ignore */
    'label' => $module['displayname'],
    'empty_data' => null,
    'choices' => /** @Ignore */$options['categories']
]);
```

### `@Translate`

With the `/** @Translate */` you can explicitly add phrases to the dictionary. This helps to extract strings
which would have been skipped otherwise.

Examples:

```php
$placeholder = /** @Translate */'delivery.user.not_chosen';
```

It can be also used to force specific domain:

```php
$errorMessage = /** @Translate(domain="validators") */'error.user_email.not_unique';
```

### Combined example

If you have a form class which uses a help array with multiple help messages strings you need to prepare it like this:

```php
$builder->add('myField', [
    // ...
    /** @Ignore */
    'help' => [
        /** @Translate */'This is the first help message.',
        /** @Translate */'This is the second help message.'
    ]
]);
```

## Extracting from JavaScript files

**Note:** currently it is not possible to automatically extract translation messages from JavaScript files.
This functionality is being worked on [here](https://github.com/willdurand/BazingaJsTranslationBundle/pull/238).
