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

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;

class LocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', ChoiceType::class, [
                'label' => 'Select your default language',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'choices' => /** @Ignore */ $options['choices'],
                'choice_loader' => null,
                'data' => $options['choice']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'locale';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_loader' => null,
            'choices' => ['English' => 'en'],
            'choice' => 'en'
        ]);
    }
}
