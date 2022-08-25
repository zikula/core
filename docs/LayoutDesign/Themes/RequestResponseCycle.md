---
currentMenu: themes
---
# Request / Response cycle

The theme engine works by intercepting the Response sent by the bundle controller
(the controller method is the 'primary actor'). It takes this Response and "wraps"
the theme around it and filters the resulting html to add required page assets
and variables and then sends the resulting Response to the browser.

```
Request -> Controller -> CapturedResponse -> Filter -> ThemedResponse
```

In this altered Symfony Request/Response cycle, the theme can be altered by the controller method through attribute.
The attribute only accepts defined values. See [ThemeAttribute](Dev/ThemeAttribute.md) for more information.
