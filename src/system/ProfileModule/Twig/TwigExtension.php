<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ProfileModule\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Zikula\ProfileModule\Entity\PropertyEntity;
use Zikula\UsersModule\Entity\UserAttributeEntity;

class TwigExtension extends AbstractExtension
{
    /**
     * @var string
     */
    protected $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('zikulaprofilemodule_formatPropertyForDisplay', [$this, 'formatPropertyForDisplay'])
        ];
    }

    public function formatPropertyForDisplay(
        PropertyEntity $property,
        UserAttributeEntity $attribute
    ): string {
        $value = $attribute->getValue();
        if (empty($value)) {
            return $value;
        }

        if ('Symfony\Component\Form\Extension\Core\Type\ChoiceType' !== $property['formType']) {
            return $value;
        }

        if (isset($property['formOptions']['multiple']) && true === $property['formOptions']['multiple']) {
            $values = json_decode($value, true);
            $labels = [];
            $choices = array_flip($property['formOptions']['choices']);
            foreach ($values as $choiceId) {
                $labels[] = $choices[$choiceId];
            }
            $value = implode(', ', $labels);
        }

        return $value;
    }
}
