<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Doctrine\StandardFields\Mapping\Event\Adapter;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Zikula\Core\Doctrine\StandardFields\Mapping\Event\StandardFieldsAdapter;

/**
 * Doctrine event adapter for ORM adapted
 * for StandardFields behavior
 */
final class ORM extends BaseAdapterORM implements StandardFieldsAdapter
{
    /**
* @inheritDoc
     */
    public function getUserIdValue(ClassMetadata $meta, $field)
    {
        return \SessionUtil::getVar('uid', 0);
    }
}
