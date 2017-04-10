BlockFactoryApi
===============

classname: \Zikula\BlocksModule\Api\BlockFactoryApi

service id = "zikula_blocks_module.api.block_factory"

The BlockFactoryApi helps with the instantiation of Blocks.

The class makes the following methods available:

    /**
     * Factory method to create an instance of a block given its name and the providing module instance.
     *  Supports either Zikula\BlocksModule\BlockHandlerInterface or
     *  Zikula_Controller_AbstractBlock (to be removed).
     *
     * @param $blockClassName
     * @param AbstractModule $moduleBundle
     * @return BlockHandlerInterface
     */
    public function getInstance($blockClassName, AbstractModule $moduleBundle = null);

The class is fully tested.
