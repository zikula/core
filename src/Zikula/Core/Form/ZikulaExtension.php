<?php

namespace Zikula\Core\Form;

use Symfony\Component\Form\AbstractExtension;

/**
 * Zikula symfony2 forms extendsion.
 */
class ZikulaExtension extends AbstractExtension
{
    protected function loadTypes()
    {
        return array(
            new Type\CategoriesType()
        );
    }
}

