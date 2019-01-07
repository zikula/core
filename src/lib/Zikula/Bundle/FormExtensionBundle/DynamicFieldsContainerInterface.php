<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle;

/**
 * Represents a form object (“data_class”) containing dynamic fields.
 */
interface DynamicFieldsContainerInterface
{
    /**
     * Returns a list of field specifications.
     * @return DynamicFieldInterface[]
     */
    public function getDynamicFieldsSpecification();
}
