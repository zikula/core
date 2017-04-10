BlockApi
========

classname: \Zikula\BlocksModule\Api\BlockApi

service id = "zikula_blocks_module.api.block"

The BlockApi helps with the management of Blocks.

The class makes the following methods available:

    /**
     * Get an unfiltered array of block entities that have been assigned to a block position.
     * @param $positionName
     * @return array
     */
    public function getBlocksByPosition($positionName);

    /**
     * Create an instance of a the block Object given a 'bKey' string like AcmeFooModule:Acme\FooModule\Block\FooBlock
     *   which is the Common ModuleName and the FullyQualifiedClassName of the block.
     * @param string $bKey
     * @return BlockHandlerInterface
     */
    public function createInstanceFromBKey($bKey);

    /**
     * Get an array of BlockTypes that are available by scanning the filesystem.
     * Optionally only retrieve the blocks of one module.
     *
     * @param ExtensionEntity $moduleEntity
     * @return array [[ModuleName:FqBlockClassName => ModuleDisplayName/BlockDisplayName]]
     */
    public function getAvailableBlockTypes(ExtensionEntity $moduleEntity = null);

    /**
     * Get an alphabetically sorted array of module names indexed by id that provide blocks.
     *
     * @return array
     */
    public function getModulesContainingBlocks();

    /**
     * Get the block directory for a module given an instance of the module
     *
     * @param AbstractModule|null $moduleInstance
     * @return array
     */
    public function getModuleBlockPath(AbstractModule $moduleInstance = null);

The class is fully tested.
