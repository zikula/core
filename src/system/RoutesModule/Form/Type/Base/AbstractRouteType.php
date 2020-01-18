<?php

/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\RoutesModule\Entity\Factory\EntityFactory;
use Zikula\RoutesModule\Form\Type\Field\ArrayType;
use Zikula\RoutesModule\Form\Type\Field\MultiListType;
use Zikula\RoutesModule\Entity\RouteEntity;
use Zikula\RoutesModule\Helper\ListEntriesHelper;
use Zikula\RoutesModule\Traits\ModerationFormFieldsTrait;

/**
 * Route editing form type base class.
 */
abstract class AbstractRouteType extends AbstractType
{
    use TranslatorTrait;
    use ModerationFormFieldsTrait;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    public function __construct(
        TranslatorInterface $translator,
        EntityFactory $entityFactory,
        ListEntriesHelper $listHelper
    ) {
        $this->setTranslator($translator);
        $this->entityFactory = $entityFactory;
        $this->listHelper = $listHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder, $options);
        $this->addModerationFields($builder, $options);
        $this->addSubmitButtons($builder, $options);
    }

    /**
     * Adds basic entity fields.
     */
    public function addEntityFields(FormBuilderInterface $builder, array $options = []): void
    {
        
        $builder->add('bundle', TextType::class, [
            'label' => $this->trans('Bundle') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the bundle of the route.')
            ],
            'required' => true,
        ]);
        
        $builder->add('controller', TextType::class, [
            'label' => $this->trans('Controller') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the controller of the route.')
            ],
            'required' => true,
        ]);
        
        $builder->add('action', TextType::class, [
            'label' => $this->trans('Action') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the action of the route.')
            ],
            'required' => true,
        ]);
        
        $builder->add('path', TextType::class, [
            'label' => $this->trans('Path') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the path of the route.')
            ],
            'required' => true,
        ]);
        
        $builder->add('host', TextType::class, [
            'label' => $this->trans('Host') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the host of the route.')
            ],
            'required' => false,
        ]);
        
        $listEntries = $this->listHelper->getEntries('route', 'schemes');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('schemes', MultiListType::class, [
            'label' => $this->trans('Schemes') . ':',
            'label_attr' => [
                'class' => 'checkbox-custom'
            ],
            'empty_data' => [],
            'attr' => [
                'class' => '',
                'title' => $this->trans('Choose the schemes.')
            ],
            'required' => true,
            'choices' => $choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => true,
            'expanded' => true
        ]);
        
        $listEntries = $this->listHelper->getEntries('route', 'methods');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('methods', MultiListType::class, [
            'label' => $this->trans('Methods') . ':',
            'label_attr' => [
                'class' => 'checkbox-custom'
            ],
            'empty_data' => [],
            'attr' => [
                'class' => '',
                'title' => $this->trans('Choose the methods.')
            ],
            'required' => true,
            'choices' => $choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => true,
            'expanded' => true
        ]);
        
        $builder->add('prependBundlePrefix', CheckboxType::class, [
            'label' => $this->trans('Prepend bundle prefix') . ':',
            'label_attr' => [
                'class' => 'switch-custom'
            ],
            'attr' => [
                'class' => '',
                'title' => $this->trans('prepend bundle prefix ?')
            ],
            'required' => false,
        ]);
        
        $builder->add('translatable', CheckboxType::class, [
            'label' => $this->trans('Translatable') . ':',
            'label_attr' => [
                'class' => 'switch-custom'
            ],
            'attr' => [
                'class' => '',
                'title' => $this->trans('translatable ?')
            ],
            'required' => false,
        ]);
        
        $builder->add('translationPrefix', TextType::class, [
            'label' => $this->trans('Translation prefix') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the translation prefix of the route.')
            ],
            'required' => false,
        ]);
        
        $builder->add('defaults', ArrayType::class, [
            'label' => $this->trans('Defaults') . ':',
            'help' => $this->trans('Enter one entry per line.'),
            'empty_data' => [],
            'attr' => [
                'class' => '',
                'title' => $this->trans('Enter the defaults of the route.')
            ],
            'required' => false,
        ]);
        
        $builder->add('requirements', ArrayType::class, [
            'label' => $this->trans('Requirements') . ':',
            'help' => $this->trans('Enter one entry per line.'),
            'empty_data' => [],
            'attr' => [
                'class' => '',
                'title' => $this->trans('Enter the requirements of the route.')
            ],
            'required' => false,
        ]);
        
        $builder->add('options', ArrayType::class, [
            'label' => $this->trans('Options') . ':',
            'help' => $this->trans('Enter one entry per line.'),
            'empty_data' => [],
            'attr' => [
                'class' => '',
                'title' => $this->trans('Enter the options of the route.')
            ],
            'required' => false,
        ]);
        
        $builder->add('condition', TextType::class, [
            'label' => $this->trans('Condition') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the condition of the route.')
            ],
            'required' => false,
        ]);
        
        $builder->add('description', TextType::class, [
            'label' => $this->trans('Description') . ':',
            'empty_data' => '',
            'attr' => [
                'maxlength' => 255,
                'class' => '',
                'title' => $this->trans('Enter the description of the route.')
            ],
            'required' => false,
        ]);
        
        $builder->add('sort', IntegerType::class, [
            'label' => $this->trans('Sort') . ':',
            'empty_data' => 0,
            'attr' => [
                'maxlength' => 11,
                'class' => '',
                'title' => $this->trans('Enter the sort of the route.') . ' ' . $this->trans('Only digits are allowed.')
            ],
            'required' => false,
        ]);
    }

    /**
     * Adds submit buttons.
     */
    public function addSubmitButtons(FormBuilderInterface $builder, array $options = []): void
    {
        foreach ($options['actions'] as $action) {
            $builder->add($action['id'], SubmitType::class, [
                'label' => $action['title'],
                'icon' => 'delete' === $action['id'] ? 'fa-trash-alt' : '',
                'attr' => [
                    'class' => $action['buttonClass']
                ]
            ]);
            if ('create' === $options['mode'] && 'submit' === $action['id']) {
                // add additional button to submit item and return to create form
                $builder->add('submitrepeat', SubmitType::class, [
                    'label' => $this->trans('Submit and repeat'),
                    'icon' => 'fa-repeat',
                    'attr' => [
                        'class' => $action['buttonClass']
                    ]
                ]);
            }
        }
        $builder->add('reset', ResetType::class, [
            'label' => $this->trans('Reset'),
            'icon' => 'fa-sync',
            'attr' => [
                'formnovalidate' => 'formnovalidate'
            ]
        ]);
        $builder->add('cancel', SubmitType::class, [
            'label' => $this->trans('Cancel'),
            'validate' => false,
            'icon' => 'fa-times'
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikularoutesmodule_route';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                // define class for underlying data (required for embedding forms)
                'data_class' => RouteEntity::class,
                'empty_data' => function (FormInterface $form) {
                    return $this->entityFactory->createRoute();
                },
                'error_mapping' => [
                    'isSchemesValueAllowed' => 'schemes',
                    'isMethodsValueAllowed' => 'methods',
                ],
                'mode' => 'create',
                'actions' => [],
                'has_moderate_permission' => false,
                'allow_moderation_specific_creator' => false,
                'allow_moderation_specific_creation_date' => false,
            ])
            ->setRequired(['mode', 'actions'])
            ->setAllowedTypes('mode', 'string')
            ->setAllowedTypes('actions', 'array')
            ->setAllowedTypes('has_moderate_permission', 'bool')
            ->setAllowedTypes('allow_moderation_specific_creator', 'bool')
            ->setAllowedTypes('allow_moderation_specific_creation_date', 'bool')
            ->setAllowedValues('mode', ['create', 'edit'])
        ;
    }
}
