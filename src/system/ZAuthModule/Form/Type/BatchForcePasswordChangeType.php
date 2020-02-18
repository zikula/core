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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\GroupsModule\Entity\RepositoryInterface\GroupRepositoryInterface;

class BatchForcePasswordChangeType extends AbstractType
{

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', ChoiceType::class, [
                'label' => 'Group to modify',
                'required' => true,
                'choices' => $this->getChoices(),
                'help' => 'Old passwords are pre-Core3 passwords which are less secure. <strong>The current user will not be affected</strong>.',
                'help_html' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'icon' => 'fas fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fas fa-times'
            ])
        ;
    }

    private function getChoices(): array
    {
        $choices = [
            'Users with old passwords (recommended)' => 'old',
            'All users' => 'all'
        ];
        /** @var \Zikula\GroupsModule\Entity\GroupEntity[] $groups */
        $groups = $this->groupRepository->getGroups();
        foreach ($groups as $group) {
            $choices[$group->getName() . ' group'] = $group->getGid();
        }

        return $choices;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_batchtogglepass';
    }
}
