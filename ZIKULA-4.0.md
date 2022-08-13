# Zikula 4.0 - Focus on own strengths

Version 4 of Zikula will fundamentally change the way Zikula works. Probably the most important change is that Zikula will no longer include Symfony and various third-party extensions, but will provide extensions for Symfony. Zikula bundles can then be included like any other extension using Composer and Flex. This unties some knots by making it easier to use the Symfony ecosystem instead of having to build solutions for all sorts of concerns in Zikula itself.

There will still be an official distribution to work with the full Zikula - which will be nothing more than a Symfony with all the core Zikula bundles. But it will also be possible to use e.g. only the theme layer without other Zikula components - or to integrate only the user system and the permissions of Zikula. This means a serious change: at the moment you only get Zikula completely or not at all. With core version 4, you can then start with a normal Symfony and add some Zikula bundles afterwards if you want to - just like you are used to with all kinds of other bundles. So things become much more compatible with each other.

Subsequently, that now means a deconstruction to slim down the old core system to decouple things and get away from having to ship everything directly. For example, the additional administration layer for extensions will be completely omitted. Other things actually just provide a frontend for functions that Symfony can do anyway. Also dynamic menus in the admin area click together or the block system are probably going to be removed. Things like a search function can be implemented with existing Symfony solutions much more efficient and modern than the dusty Search module of Zikula allows. The Hook system, to give another example, has basically created a redundant additional layer to the Symfony event system.

We need to make a differentiation here: Where does it really make sense to invest our energy? Where is the added value of Zikula Core? How can we leverage the Symfony ecosystem even more without giving up our current flexibility? During this consolidation phase, things that already exist in the Symfony ecosystem will be dropped to avoid maintaining redundant components. This includes various solutions around content management in particular. After the obvious things have been stomped out, the remaining functions will be sifted through and rearranged, with the goal of creating a manageable number of independent (i.e., as strongly decoupled as possible from each other) bundles that can also be used individually.