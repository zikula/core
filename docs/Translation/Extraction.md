---
currentMenu: translation
---
# Translation extraction

To extract translations use the console command `translation:extract`.

To see all of it's option, do this:

```shell
php bin/console translation:extract -h
# or
php bin/console translation:extract --help
```

Example for Zikula core:

```shell
# extract for all configured locales
php bin/console translation:extract zikula
# extract only for English
php bin/console translation:extract zikula en
```

Note `zikula` is the name of our configuration.

Examples for a module or a theme:

```shell
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

Note there is also a similar `translation:update` command that is from Symfony itself.
We use `translation:extract` from php-translation though because this allows more sophisticated
configuration options and processing, like handling multiple bundles, translation annotations etc.

## About default values

Note that new translations are added with `null` as translation value. While this works like a charm in PHP and Twig,
it will cause problems when being used in JavaScript because then `null` appears as string.

So if you use English translations as translation keys, ensure that you add corresponding values at least for those
entries that are used in JavaScript files also for the English translation.

You can also use an additional command `zikula:translation:keytovalue` to fix default values:

```shell
php -dmemory_limit=2G bin/console zikula:translation:keytovalue
php -dmemory_limit=2G bin/console zikula:translation:keytovalue --bundle AcmeFooModule
```

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
    // …
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
