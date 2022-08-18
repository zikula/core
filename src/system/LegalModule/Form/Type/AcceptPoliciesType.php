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

namespace Zikula\LegalModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Translation\Extractor\Annotation\Ignore;
use Translation\Extractor\Annotation\Translate;
use Zikula\LegalModule\Constant;

class AcceptPoliciesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $login = $builder->getData()['login'];

        $builder
            ->add('uid', HiddenType::class)
            ->add('login', HiddenType::class)
            ->add('acceptedpolicies_policies', CheckboxType::class, [
                'data' => true,
                'help' => 'Check this box to indicate your acceptance of this site\'s policies.',
                'label' => 'Policies',
                'label_attr' => ['class' => 'switch-custom'],
                'constraints' => [
                    new IsTrue(['message' => 'you must accept this site\'s policies'])
                ]
            ])
            ->add('submit', SubmitType::class, [
                /** @Ignore */
                'label' => $login ? /** @Translate */ 'Save and continue logging in' : /** @Translate */ 'Save',
                'icon' => 'fa-check',
                'attr' => ['class' => 'btn-success']
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return Constant::FORM_BLOCK_PREFIX;
    }
}
