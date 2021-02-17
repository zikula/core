How are Core 4 HookEvents different from Core 3 Hooks?
======================================================

1. HookEvents are not required to be placed in an extension (Theme, Module).
   - meaning they can live in the `App` namespace.
2. Hooks are now much more generic. They are more accurately, a ‘dynamic event-dispatcher’
3. We have remvoed all zikula-specific dependencies so it becomes useful within the symfony ecosystem
4. Hooks now uses more standardized terminology (hookEvent & hookListener)
   instead of Subscriber and Provider (which were reversed from Symfony and therefore confusing)
5. Hooks now simplifies persistent object to hookEventClass, listenerClass, priority
6. Hook types/categories/areas are all eliminated
   - the concept of generic contracts are enforced through inheritance
8. HookEvents use the standard EventDispatcher instead of customized HookDispatcher
9. MUCH simpler User Interface
