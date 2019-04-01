<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
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
     */
    public function isDisplayable(BlockEntity $blockEntity): bool;

    /**
     * Get all the attributes of the request + 'query param'.
     */
    public function getFilterAttributeChoices(): array;
}
