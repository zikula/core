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

namespace Zikula\LegalBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcceptPoliciesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $loginRequired = $options['loginRequired'];

        $builder
            ->add('userId', HiddenType::class, ['mapped' => false])
            ->add('loginRequired', HiddenType::class, ['mapped' => false])
            ->add('submit', SubmitType::class, [
                'label' => $loginRequired ? 'Save and continue logging in' : 'Save',
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn-success'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['userId' => '', 'loginRequired' => false])
            ->setAllowedTypes('userId', 'int')
            ->setAllowedTypes('loginRequired', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'zikulalegalbundle_policy';
    }
}
