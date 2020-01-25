# BlockApi

classname: `\Zikula\BlocksModule\Api\BlockApi`.

The BlockApi helps with the management of Blocks.

The class makes the following methods available:

```php
/**
 * Get an unfiltered array of block entities that have been assigned to a block position.
 */
public function getBlocksByPosition(string $positionName): array;

/**
 * Create an instance of a the block Object given a 'bKey' string like AcmeFooModule:Acme\FooModule\Block\FooBlock
 * which is the common module name and the FullyQualifiedClassName of the block.
 */
public function createInstanceFromBKey(string $bKey): BlockHandlerInterface;

/**
 * Get an array of BlockTypes that are available by scanning the filesystem.
 * Optionally only retrieve the blocks of one module.
 *
 * @return array [[ModuleName:FqBlockClassName => ModuleDisplayName/BlockDisplayName]]
 */
public function getAvailableBlockTypes(ExtensionEntity $moduleEntity = null): array;

/**
 * Get an alphabetically sorted array of module names indexed by id that provide blocks.
 */
public function getModulesContainingBlocks(): array;
```

The class is fully tested.
