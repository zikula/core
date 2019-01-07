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
 * Represents a single dynamic field specification.
 * Provides detail information for the form handling.
 */
interface DynamicFieldInterface
{
    /**
     * Returns name of form field.
     * @return string
     */
    public function getName();

    /**
     * Returns optional prefix of form field.
     * @return string
     */
    public function getPrefix();

    /**
     * Returns a list of labels per locale.
     * @return array
     */
    public function getLabels();

    /**
     * Returns label for a specific locale.
     * @return string
     */
    public function getLabel($locale = '', $default = 'en');

    /**
     * Returns the FqCN of the form class (e.g. return IntegerType::class;)
     * @return string
     */
    public function getFormType();

    /**
     * Returns an array of form options.
     * @return array
     */
    public function getFormOptions();

    /**
     * Returns a weighting number for sorting fields.
     * This is currently not utilised, but reserved for future usage.
     * @return integer
     */
    public function getWeight();

    /**
     * Returns a list of group names per locale.
     * May optionally be used for dividing fields into several fieldsets.
     * @return array
     */
    public function getGroupNames();
}
