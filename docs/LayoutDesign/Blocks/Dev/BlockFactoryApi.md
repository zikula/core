---
currentMenu: blocks
---
# BlockFactoryApi

Interface: `\Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface`.  
Class: `\Zikula\BlocksModule\Api\BlockFactoryApi`.

The BlockFactoryApi helps with the instantiation of blocks.

The class makes the following method available:

```php
/**
 * Factory method to create an instance of a block given its name and the providing module instance.
 * Given block class needs to implement Zikula\BlocksModule\BlockHandlerInterface.
 */
public function getInstance(string $blockClassName): BlockHandlerInterface;
```

The class is fully tested.
