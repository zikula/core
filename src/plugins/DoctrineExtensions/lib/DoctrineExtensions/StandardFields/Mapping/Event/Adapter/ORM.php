<?php

namespace DoctrineExtensions\StandardFields\Mapping\Event\Adapter;

use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use DoctrineExtensions\StandardFields\Mapping\Event\StandardFieldsAdapter;

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