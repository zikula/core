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

namespace Zikula\ProfileBundle\Twig;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Zikula\ProfileBundle\Entity\Property;
use Zikula\UsersBundle\Entity\UserAttribute;

class TwigExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('zikulaprofilebundle_formatPropertyForDisplay', [$this, 'formatPropertyForDisplay'])
        ];
    }

    public function formatPropertyForDisplay(
        Property $property,
        UserAttribute $attribute
    ): string {
        $value = $attribute->getValue();
        if (empty($value)) {
            return $value;
        }

        if (ChoiceType::class !== $property['formType']) {
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
