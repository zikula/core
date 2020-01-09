<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * IDS Log filter form type class.
 */
class IdsLogFilterType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $this->addFilterField($builder, $options, 'uid', $this->trans('User Name'));
        $this->addFilterField($builder, $options, 'name', $this->trans('Name'));
        $this->addFilterField($builder, $options, 'tag', $this->trans('Tag'));
        $this->addFilterField($builder, $options, 'value', $this->trans('Value'));
        $this->addFilterField($builder, $options, 'page', $this->trans('Page'));
        $this->addFilterField($builder, $options, 'ip', $this->trans('IP Address'));
        $this->addFilterField($builder, $options, 'impact', $this->trans('Impact'));
    }

    /**
     * Adds a choice field for filtering by a certain IDS log field.
     */
    private function addFilterField(
        FormBuilderInterface $builder,
        array $options,
        string $fieldName,
        string $label
    ): void {
        $repository = $options['repository'];
        $listEntries = $repository->getDistinctFieldValues($fieldName);

        $choices = [];
        foreach ($listEntries as $entry) {
            $choices[$entry] = $entry;
        }

        $builder->add($fieldName, ChoiceType::class, [
            'label' => $label,
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => $this->trans('All'),
            'choices' => $choices,
            'multiple' => false,
            'expanded' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikulasecuritycentermodule_idslogfilter';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'repository' => null
        ]);
    }
}
