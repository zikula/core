Twig Tag: polyfill
==================

`{{ polyfill() }}`
(defaults to 'forms' feature)

or

`{{ polyfill(['es5', 'mediaelement', 'forms']) }}`

This Twig tag enables the afarkas `js-webshim` polyfill library in a template.

https://afarkas.github.io/webshim/demos/
https://github.com/aFarkas/webshim

Multiple usages of the tag on the same page will not duplicate calls to the javascript assets, but *will*
add whatever additional features are requested.

example:

```
    {{ polyfill() }}
    <h2>Page Title</h2>
    <p>Hello world!</p>

    <form>
        <input type="password" required="required" />
    </form>
```
