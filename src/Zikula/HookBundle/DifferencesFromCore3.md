How are Core 4 HookEvents different from Core 3 Hooks?
======================================================

1. HookEvents are not required to be placed in an extension (Theme, Module).
   - meaning they can live in the `App` namespace for example.
2. Hook types/categories/areas are all eliminated
   - the concept of generic contracts are enforced through inheritance
3. Hooks now use more standardized terminology (HookEvent & HookEventListener)
   instead of Subscriber and Provider (which were reversed from Symfony and therefore confusing).
4. The admin User Interface is MUCH simpler.
5. HookEvents use the Symfony EventDispatcher instead of a customized HookDispatcher.
6. Zikula-specific dependencies have been removed so the bundle becomes useful within the Symfony ecosystem.
7. The persistent object (Connection) is simplified to only three properties: hookEventClass, listenerClass, priority.

This new hook system is completely incompatible with the old Hooks system. In Zikula Core 3.1 the new system is
introduced as a Forward-Compatibility layer. Thus allowing *either* system to be used, but they cannot connect to
each other. E.g. a new HookEvent cannot trigger nor connect to an old Provider.

The old system is deprecated and will all be removed in Core 4.0.0.
