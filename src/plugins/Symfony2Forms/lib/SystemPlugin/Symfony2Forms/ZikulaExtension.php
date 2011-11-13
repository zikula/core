<?php

namespace SystemPlugin\Symfony2Forms;

use Symfony\Component\Form\AbstractExtension;

/**
 *
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

