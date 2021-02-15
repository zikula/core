<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Bundle\FormExtensionBundle\Form\Type\ControllerType;
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

        $builder->remove('bundle');
        $builder->remove('controller');
        $builder->remove('action');
        $builder->add('routeController', ControllerType::class, [
            'label' => 'Controller:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the controller of the route'
            ],
            'required' => true,
            'parameterTypes' => [],
        ]);

        $builder->add('path', TextType::class, [
            'label' => 'Path:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the path of the route'
            ],
            'required' => true,
            'help' => 'The path must start with a "/" and can be a regular expression. Example: "/login"',
            'input_group' => ['left' => '<span id="pathPrefix"></span>']
        ]);

        $builder->add('host', TextType::class, [
            'label' => 'Host:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the host of the route'
            ],
            'required' => false,
            'help' => 'Advanced setting, see <a href=\'%url%\' target="_blank">Symfony documentation</a>.',
            'help_translation_parameters' => [
                '%url%' => 'https://symfony.com/doc/current/routing/hostname_pattern.html'
            ],
            'help_html' => true
        ]);

        $builder->add('condition', TextType::class, [
            'label' => 'Condition:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the condition of the route'
            ],
            'required' => false,
            'help' => 'Advanced setting, see <a href=\'%url%\' target="_blank">Symfony documentation</a>.',
            'help_translation_parameters' => [
                '%url%' => 'https://symfony.com/doc/current/routing/conditions.html'
            ],
            'help_html' => true
        ]);

        $builder->add('description', TextType::class, [
            'label' => 'Description:',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => 'Enter the description of the route'
            ],
            'required' => false,
            'help' => 'Insert a brief description of the route, to explain why you created it. It is only shown in the admin interface.'
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
