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

namespace Zikula\UsersModule\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\GroupsModule\Entity\GroupEntity;
use Zikula\UsersModule\Constant;

class AdminModifyUserType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('activated', ChoiceType::class, [
                'choices' => [
                    $this->trans('Active') => Constant::ACTIVATED_ACTIVE,
                    $this->trans('Inactive') => Constant::ACTIVATED_INACTIVE,
                    $this->trans('Pending') => Constant::ACTIVATED_PENDING_REG
                ],
                'label' => $this->trans('User status')
            ])
            ->add('groups', EntityType::class, [
                'class' => GroupEntity::class,
                'choice_label' => 'name',
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->trans('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->trans('Cancel'),
                'icon' => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_adminmodifyuser';
    }
}
