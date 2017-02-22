<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineExtensions\StandardFields\Mapping\Event\Adapter;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use DoctrineExtensions\StandardFields\Mapping\Event\StandardFieldsAdapter;
use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;

/**
 * Doctrine event adapter for ORM adapted
 * for StandardFields behavior
 *
 * @deprecated remove in Core-2.0
 */
final class ORM extends BaseAdapterORM implements StandardFieldsAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getUserIdValue(ClassMetadata $meta, $field)
    {
        @trigger_error('StandardFields extension is deprecated, please use Blameable and Timestampable instead.', E_USER_DEPRECATED);

        return \SessionUtil::getVar('uid', 0);
    }
}
