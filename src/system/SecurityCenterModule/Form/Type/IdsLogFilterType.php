<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * IDS Log filter form type class.
 */
class IdsLogFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder->setMethod('GET');

        $this->addFilterField($builder, $options, 'uid', $translator->__('User Name'));
        $this->addFilterField($builder, $options, 'name', $translator->__('Name'));
        $this->addFilterField($builder, $options, 'tag', $translator->__('Tag'));
        $this->addFilterField($builder, $options, 'value', $translator->__('Value'));
        $this->addFilterField($builder, $options, 'page', $translator->__('Page'));
        $this->addFilterField($builder, $options, 'ip', $translator->__('IP Address'));
        $this->addFilterField($builder, $options, 'impact', $translator->__('Impact'));
    }

    /**
     * Adds a choice field for filtering by a certain IDS log field.
     *
     * @param FormBuilderInterface $builder   The form builder
     * @param array                $options   Form type options
     * @param string               $fieldName Name of field to select
     * @param string               $label     Label for the form field
     */
    private function addFilterField($builder, array $options, $fieldName, $label)
    {
        $translator = $options['translator'];
        $repository = $options['repository'];

        $listEntries = $repository->getDistinctFieldValues($fieldName);

        $choices = [];
        foreach ($listEntries as $entry) {
            $choices[$entry] = $entry;
        }

        $builder->add($fieldName, 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $label,
            'attr' => [
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $translator->__('All'),
            'choices' => $choices,
            'choices_as_values' => true,
            'multiple' => false,
            'expanded' => false
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasecuritycentermodule_idslogfilter';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'repository' => null
        ]);
    }
}
