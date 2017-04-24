Hook Definitions
================

In order for hooks to function, the `composer.json` file must define the extension's HookContainer class in the 
`capabilities` array like so:

```json
    "extra": {
        "zikula": {
            "capabilities": {
                "hook_subscriber": {"class": "Zikula\\BlocksModule\\Container\\HookContainer"},
                "hook_provider": {"class": "Zikula\\BlocksModule\\Container\\HookContainer"}
            }
        }
    }
```

Note that this **replaces** the 1.3.x/1.4.x convention of identifying the capabilities with
`"hook_subscriber": {'enabled": true}`.

Only define the key(s) that the module implements (e.g. `"hook_subscriber"` or `"hook_provider"` or both).

It is technically possible to define two different definition classes (one for subscriber and one for provider hooks).
If this is done, you must specify the hook type when instantiating the class from the Api (See HookContainer).

See `src/docs/Core-1.x/Hooks` and `src/docs/Core-1.x/UPGRADE-1.4.0.md` for more information on Hooks.
