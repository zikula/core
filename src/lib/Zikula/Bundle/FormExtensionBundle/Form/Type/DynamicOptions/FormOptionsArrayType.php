<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\FormExtensionBundle\Form\Type\DynamicOptions;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\IdentityTranslator;
use Zikula\Common\Translator\TranslatorInterface;

class FormOptionsArrayType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->translator = $options['translator'];

        $builder
            ->add('required', CheckboxType::class, [
                'label' => $this->translator->__('Required'),
                'required' => false,
            ])
            ->add('help', TextType::class, [
                'label' => $this->translator->__('Help text'),
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulaformextensionbundle_formoptionsarray';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => new IdentityTranslator()
        ]);
    }
}
