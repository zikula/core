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
use Zikula\Common\Translator\TranslatorInterface;
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

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setMethod('GET');

        $this->addFilterField($builder, $options, 'uid', $this->__('User Name'));
        $this->addFilterField($builder, $options, 'name', $this->__('Name'));
        $this->addFilterField($builder, $options, 'tag', $this->__('Tag'));
        $this->addFilterField($builder, $options, 'value', $this->__('Value'));
        $this->addFilterField($builder, $options, 'page', $this->__('Page'));
        $this->addFilterField($builder, $options, 'ip', $this->__('IP Address'));
        $this->addFilterField($builder, $options, 'impact', $this->__('Impact'));
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
                'class' => 'input-sm'
            ],
            'required' => false,
            'placeholder' => $this->__('All'),
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
