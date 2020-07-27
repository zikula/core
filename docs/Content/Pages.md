---
currentMenu: content-pages
---
# Static Pages

### New in Core 3.1.0

Zikula now includes the StaticContentModule as a Value-Added extension in Core-3.1.0.
With this, basic **template-driven** static content is easily implemented in Zikula.
Simply create a template with the needed content in `/templates/p/` like `/templates/p/apple.html.twig`.
Then direct your browser to `/p/apple` and the template will be displayed.

Any standard HTML can be used as well as any template functions and global template variables. 

- `{{ siteName() }}`
- `{{ pagevars.title }}`
- `{{ currentUser.loggedIn }}`
- `{{ app.request.method }}`
- `{{ pageAddAsset('javascript', asset('/path/from/public/to/my/javascript.js')) }}`
- etc...

There is no way to add additional variables in these pages.

### Better pages

To display more advanced pages and non-static content please see

- [Pages](https://github.com/zikula-modules/Pages)
- [Content](https://github.com/zikula-modules/Content)
- Even more: [Extensions > Content](../Extensions/Content.md)
