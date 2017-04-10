<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Api\ApiInterface;

use Zikula\BlocksModule\Entity\BlockEntity;

/**
 * Class BlockFilterApiInterface
 */
interface BlockFilterApiInterface
{
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
}
