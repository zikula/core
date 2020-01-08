<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\ExtensionsModule\Constant as ExtensionConstant;
use Zikula\ExtensionsModule\Entity\ExtensionEntity;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\RoutesModule\Form\Type\Base\AbstractRouteType;

/**
 * Route editing form type implementation class.
 */
class RouteType extends AbstractRouteType
{
    /**
     * @var ExtensionRepositoryInterface
     */
    private $extensionRepository;

    public function addEntityFields(FormBuilderInterface $builder, array $options = []): void
    {
        parent::addEntityFields($builder, $options);

        // note we just read fields which already had been added in the parent class
        // FormBuilder just overrides the field allowing us easier customisation

        $moduleChoices = [];
        $moduleChoiceAttributes = [];
        /** @var ExtensionEntity[] $modules */
        $modules = $this->extensionRepository->findBy(['state' => ExtensionConstant::STATE_ACTIVE]);
        foreach ($modules as $module) {
            $displayName = $module->getDisplayName();
            $moduleChoices[$displayName] = $module->getName();
            $moduleChoiceAttributes[$displayName] = ['title' => $displayName];
        }
        ksort($moduleChoices);

        $builder->add('bundle', ChoiceType::class, [
            'label' => $this->trans('Bundle') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->trans('Enter the bundle of the route')
            ],
            'required' => true,
            'choices' => $moduleChoices,
            'choice_attr' => $moduleChoiceAttributes,
            'multiple' => false,
            'expanded' => false
        ]);

        $builder->add('controller', TextType::class, [
            'label' => $this->trans('Controller') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the controller of the route')
            ],
            'required' => true,
            'help' => $this->trans('Insert the name of the controller, which was called "type" in earlier versions of Zikula.')
        ]);

        $builder->add('action', TextType::class, [
            'label' => $this->trans('Action') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the action of the route')
            ],
            'required' => true,
            'help' => $this->trans('Insert the name of the action, which was called "func" in earlier versions of Zikula.')
        ]);

        $builder->add('path', TextType::class, [
            'label' => $this->trans('Path') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the path of the route')
            ],
            'required' => true,
            'help' => $this->trans('The path must start with a "/" and can be a regular expression. Example: "/login"'),
            'input_group' => ['left' => '<span id="pathPrefix"></span>']
        ]);

        $builder->add('host', TextType::class, [
            'label' => $this->trans('Host') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the host of the route')
            ],
            'required' => false,
            'help' => $this->trans('Advanced setting, see %s', ['%s' => 'https://symfony.com/doc/current/routing/hostname_pattern.html'])
        ]);

        $builder->add('condition', TextType::class, [
            'label' => $this->trans('Condition') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the condition of the route')
            ],
            'required' => false,
            'help' => $this->trans('Advanced setting, see %s', ['%s' => 'https://symfony.com/doc/current/routing/conditions.html'])
        ]);

        $builder->add('description', TextType::class, [
            'label' => $this->trans('Description') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the description of the route')
            ],
            'required' => false,
            'help' => $this->trans('Insert a brief description of the route, to explain why you created it. It is only shown in the admin interface.')
        ]);
    }

    /**
     * @required
     */
    public function setExtensionRepository(ExtensionRepositoryInterface $extensionRepository): void
    {
        $this->extensionRepository = $extensionRepository;
    }
}
