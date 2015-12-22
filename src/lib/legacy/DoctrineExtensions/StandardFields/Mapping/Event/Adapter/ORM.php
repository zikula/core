<?php

namespace DoctrineExtensions\StandardFields\Mapping\Event\Adapter;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use DoctrineExtensions\StandardFields\Mapping\Event\StandardFieldsAdapter;
use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;

/**
 * Doctrine event adapter for ORM adapted
 * for StandardFields behavior
 */
final class ORM extends BaseAdapterORM implements StandardFieldsAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getUserIdValue(ClassMetadata $meta, $field)
    {
        return \SessionUtil::getVar('uid', 0);
    }
}
