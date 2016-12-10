<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineExtensions\StandardFields\Mapping\Event;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Gedmo\Mapping\Event\AdapterInterface;

/**
 * Doctrine event adapter interface
 * for StandardFields behavior
 *
 * @deprecated remove in Core-2.0
 */
interface StandardFieldsAdapter extends AdapterInterface
{
    /**
     * Get the user id value
     *
     * @param ClassMetadata $meta
     * @param string        $field
     *
     * @return mixed
     */
    public function getUserIdValue(ClassMetadata $meta, $field);
}
