<?php

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
     * {@inheritDoc}
     */
    public function getUserIdValue(ClassMetadata $meta, $field)
    {
        return \SessionUtil::getVar('uid', 0);
    }
}
