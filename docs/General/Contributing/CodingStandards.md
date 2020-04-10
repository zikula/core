---
currentMenu: contributing
---
# Coding Standards

When contributing to any Zikula Project, you must follow the these standards.  Zikula's standards follow `PSR-1`, `PSR-2` and `PSR-4` as a base with specific additions (not covered by the PSRs). These standards are recommended module development to maintain consistency across all code. Existing code can be easily reformatted in most IDEs or using the `PHP-CS-Fixer` utility.

Please configure your IDE/editor to comply with these standards by default to reduce accidental error.

## PHP Files

Immediately after the PHP opening tag, on a new line, there should be a file-level docblock.  Below is the template:

```php
/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
```

## Javascript Files

Non vendor javascript files may use a shortened header:

```js
// Copyright Zikula, licensed MIT.
```

## Arrays

Array keys should always be in lower case and may use an underscore if readability is a concern::

```php
['mykey' => 1];
['something_very_long' => 12345];
```

## Class and Interface Naming

Classes and interfaces MUST be namespaced. 

Abstract classes must contain the word ``Abstract`` prefixing the last element in the class name.
Interfaces must be suffixed with the word ``Interface``. Generally classes in subnamespaces should be suffixed with whatebver they are, for example ``Foo\Helper\MyHelper`` or ``Bar\Controller\UserController``.

## Class and interface Conventions

The following rules apply to all classes and interfaces:

- Declare constants first.
- Declare static variables after constants.
- Declare properties before methods (public, protected then private).
- The constructor (if there is one) should be the first method.
- Declare protected methods after public methods.
- Declare private methods after protected methods.
- All properties and methods should be type-hinted; docblocks may used to provide additional information.

## Single/Double Quotes

Reasons for double quotes are to evaluate variables or properties, or to avoid ugly escaping of single quotes::

```php
$message = 'John wants you to call him today';
$message = "Today's news is good for a change";
```

When concatenating strings, use double quotes where possible.  Here are some examples of this::

```php
$fullName = "$firstName $lastName";
$include = "$this->basePath/$filename.php";

// In this example, single quotes to avoid evaluating 
// $firstName as a variable.
$errorMsg = '$firstName should not contain numbers';

// {} required for method calls
$include = "{$this->getPath()}/$filename.php";

// {} required because of underscore
$path = "$fullPath/class/{$name}.php";

// Concatenation using periods because PHP does not
// evaluate constants inside quotation.  Using single
// quotes as there is nothing to evaluate.
$filePath = __DIR__ . '/myfile.php';

// As above with double quotes because there are variables 
// to evaluate.
$filePath = __DIR__ . "/data_dir/$filename.php";
```

## Negative conditional statements

If using 'not' logic, the exclamation mark should immediately precede the condition:

```php
if (!$foo) {
    // code
}

if (!($a == $b)) {
    // code
}
  
return !$this->flag; // invert a value
```

## Method Naming Conventions

Class methods should have standards naming that is linguistically meaningful and consistent::

    getFoo()  - getter for foo.
    getFoos() - get all foo collection.
    setFoo()  - setter for foo.
    setFoos() - set foo collection.
    hasFoo()  - has a Foo (in the collection).
    isBar()  - is a Bar?
    isEmpty() - Is whatever empty.
    clearFoo() - Flush/clear.
    flushFoo() - Flush/clear.
    registerFoo() - Set specific key in collection.
    unregisterFoo() - Unset specific key in collection.
    addFoo() - Add to a stack.
    countFoo() - How many in collection.

## Docblocks

All PHP files should have a documentation block with the standard header [ref].

All properties must have a docblock which includes the ``@var`` annotation:

```php
/**
 * Argument storage.
 *
 * @var array
 */
private $arguments = array();
```

All functions (except lambda functions) and class methods should have type hints for their arguments and return types. Docblocks may be used to provide additional information:

```php
/**
 * Single line summary here.
 *
 * Extra details can go here in a paragraph or paragraphs as
 * necessary.
 *
 * @throws \InvalidArgumentException If ID is invalid.
 */
public function myMethod(SomeType $foo, string $title, int $number): MyReturnType
```

## Author Annotations

`@author` annotations are not permitted. There are specific reason for this which is worth mentioning for the record. The nature of open source means that contributors will come and go. As such `@author` tags can create a false sense of ownership over portions of the code-base. `@author` annotations can prevent others from contributing to certain files because they may feel it's "someone domain".

Next comes the "when should I add my own `@author` My Name?" - When should you be entitled to add your name? If you merely touch the file, or if you fix a typo, or a bug - how extensive do the changes have to be? It starts getting rather complicated.

Open source is about contribution, not about name recognition, so for these reasons, the `@author` annotations are forbidden.

## API Annotations

`@api` - This annotation is used in function and method docblocks to declare it stable, public facing and intended for use by developers.

The reason for this annotation is that there is no way to know definitively from the visibility declarations the intention of an API or whether the API is considered stable because even if a method is declared public, it may be intended to be internal to the framework.

The `@api` annotation removes any ambiguity by expressly declaring what is meant to be public and stable so developers can rely on the interface. Conversely, this also implies that everything without this annotation should be considered internal or unstable.

This annotation must be used frugally and with careful consideration.

## New and Deprecated APIs

Please mark deprecated APIs with `@deprecated since x.x.x`. If there is alternative, add `@see Foo::Bar() instead`.

If a new API is added, you may mark it as `@since x.x.x`.

- `PSR-1`: <http://www.php-fig.org/psr/psr-1/>
- `PSR-2`: <http://www.php-fig.org/psr/psr-2/>
- `PSR-4`: <http://www.php-fig.org/psr/psr-4/>
- `PHP-CS-Fixer`: <https://github.com/fabpot/PHP-CS-Fixer>
