---
currentMenu: dev-extensions
---
# Provide help docs

In general extension developers should aim on making usage so easy that docs are not needed much. Most important explanations can be added directly to admin templates. While not being required a help can be provided in addition. It has been designed to make it very simple for extension authors: it is only a matter of writing some Markdown which can also arbitrarily extended and even translated.

As soon as these Markdown files exist they will be shown by Zikula automatically: in the extension's administration menu an additional item named `help` will appear.

## File locations and conventions

- An extension may provide Markdown files in the `Resources/docs/help/<language-code>` directory, for example `Resources/docs/help/de/` for German language.
- When looking for documentation English is used as a fallback. So if a file does not exist for the current language, the system also looks in `Resources/docs/help/en/`.
- It is recommended to create subfolders for different topics, images and other files.

## Other requirements

- The home page must be named `README.md`.
- You are responsible to create navigational links between multiple pages (use pure Markdown).

## Benefits of this approach

- Documentation is maintained directly with the component instead of elsewhere.
- Context sensitive: users find documentation right at the page where they need it.
- Easy to handle for different versions (documentation is updated together with the code).
- Easy to avoid that docs become outdated.
- Easy way to contribute as Markdown is simple.
- Easy to translate.
