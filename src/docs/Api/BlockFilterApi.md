BlockFilterApi
==============

classname: \Zikula\BlocksModule\Api\BlockFilterApi

service id = "zikula_blocks_module.api.block_filter"

The BlockFilterApi helps determine if Blocks should be displayed or not.

The class makes the following methods available:

    /**
     * Determine if the block is displayable based on the filter criteria.
     *
     * @param BlockEntity $blockEntity
     * @return boolean
     */
    public function isDisplayable(BlockEntity $blockEntity);

    /**
     * Get all the attributes of the request + 'query param'.
     *
     * @return array
     */
    public function getFilterAttributeChoices();

The class is fully tested.
