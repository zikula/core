---
currentMenu: developer-extension
---
# Best practices

This document describes best practices for module development. This applies to any module written in the new Symfony Bundle style.

## Module APIs

Module APIs are strictly and only for features you wish to expose at programming level to other modules. They are not for general helpers, for example as a helper in a controller.  Module APIs should generally not emit flash messages. Display of errors would be up to the controller. Use a logger instead for debugging purposes.

If an API fails a permission check, a PHP Exception should be emitted.

## Parameter Validation

As a rule of thumb, if a function/method/API receives an invalid argument (for example, a wrong type, or otherwise unusable argument), it should throw a PHP Exception, usually an `\InvalidArgumentException`.

If a function/method/API is able to continue execution after argument validation, then it should return something if appropriate or no return. If something unexpected happens, again it should throw a PHP Exception. Where possible use typehinting to reduce validation requirements.

## Exceptions

Exceptions must be thrown only at the exact point there the exception actually occurs. One may use a helper when so long as it returns the PHP Exception instance which can then be thrown, e.g.

```php
function createExceptionHelper()
{
    return new Exception();
}

if (false) {
    throw createExceptionHelper();
}
```

## Execution Flow

Zikula follows the HttpKernel workflow in all circumstances so no part of the code should perform a hard exit. Use PHP Exceptions to break flow or otherwise return a response object.

## Controller Behaviour

A controller must either return a `Response` or throw a PHP Exception. It should never stop the execution flow under any circumstances. 

## Javascript & jQuery

### Jquery plugins

These should always be wrapped in the following format:

```js
(function($) {
    ... here goes our code...
})(jQuery)
```

### Forward compatibility

Enclose all code in an anonymous function, so if you need to write a piece of JS in an HTML page use:

```js
<script>
    (function($) {
        $('element').hide();
    })(jQuery)
</script>
```

This means you **do not** have to use jQuery in `.noConflict()` mode. Within these constructs you MUST use native `$`

### Full example

The example below shows how one would use anonymous functions and take care of global variables (taken from http://jsfiddle.net/UErVh/).

This shows how local variables won't leek and if you need something global you have to make it explicit:

```
var myExistingGlobalNamespace = {}; // this could be defined somewhere else as for example: window.myExistingGlobalNamespace = {}

(function($) {
    function simple() {
        console.log('simple');
    };
    var otherSimple = function() {
        console.log('otherSimple');
    };
    window.myGlobalFunction = function() {
        console.log('myGlobalFunction');
    };
    myExistingGlobalNamespace.myMethod = function() {
        console.log('myMethod');
    };
})(jQuery)

try {
    simple(); // ReferenceError: simple is not defined
} catch (e) {
    console.log('this is undefined');
}

try {
    otherSimple(); // ReferenceError: otherSimple is not defined
} catch (e) {
    console.log('this is undefined');
}

myGlobalFunction(); // myGlobalFunction
myExistingGlobalNamespace.myMethod(); // myMethod
```
