<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Form\Type\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\RoutesModule\Entity\Factory\RouteFactory;
use Zikula\RoutesModule\Helper\ListEntriesHelper;

/**
 * Route editing form type base class.
 */
abstract class AbstractRouteType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var RouteFactory
     */
    protected $entityFactory;

    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    /**
     * RouteType constructor.
     *
     * @param TranslatorInterface $translator    Translator service instance
     * @param RouteFactory        $entityFactory Entity factory service instance
     * @param ListEntriesHelper   $listHelper    ListEntriesHelper service instance
     */
    public function __construct(TranslatorInterface $translator, RouteFactory $entityFactory, ListEntriesHelper $listHelper)
    {
        $this->setTranslator($translator);
        $this->entityFactory = $entityFactory;
        $this->listHelper = $listHelper;
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function setTranslator(/*TranslatorInterface */$translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addEntityFields($builder, $options);
        $this->addReturnControlField($builder, $options);
        $this->addSubmitButtons($builder, $options);
    }

    /**
     * Adds basic entity fields.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addEntityFields(FormBuilderInterface $builder, array $options)
    {
        $listEntries = $this->listHelper->getEntries('route', 'routeType');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('routeType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
            'label' => $this->__('Route type') . ':',
            'empty_data' => 'additional',
            'attr' => [
                'class' => '',
                'title' => $this->__('Choose the route type')
            ],'placeholder' => '',
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => false,
            'expanded' => false
        ]);
        $builder->add('replacedRouteName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Replaced route name') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the replaced route name of the route')
            ],'required' => false,
            'max_length' => 255,
        ]);
        $builder->add('bundle', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Bundle') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the bundle of the route')
            ],'required' => true,
            'max_length' => 255,
        ]);
        $builder->add('controller', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Controller') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the controller of the route')
            ],'required' => true,
            'max_length' => 255,
        ]);
        $builder->add('action', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Action') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the action of the route')
            ],'required' => true,
            'max_length' => 255,
        ]);
        $builder->add('path', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Path') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the path of the route')
            ],'required' => true,
            'max_length' => 255,
        ]);
        $builder->add('host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Host') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the host of the route')
            ],'required' => false,
            'max_length' => 255,
        ]);
        $listEntries = $this->listHelper->getEntries('route', 'schemes');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('schemes', 'Zikula\RoutesModule\Form\Type\Field\MultiListType', [
            'label' => $this->__('Schemes') . ':',
            'empty_data' => 'http',
            'attr' => [
                'class' => '',
                'title' => $this->__('Choose the schemes')
            ],'placeholder' => '',
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => true,
            'expanded' => false
        ]);
        $listEntries = $this->listHelper->getEntries('route', 'methods');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('methods', 'Zikula\RoutesModule\Form\Type\Field\MultiListType', [
            'label' => $this->__('Methods') . ':',
            'empty_data' => 'GET',
            'attr' => [
                'class' => '',
                'title' => $this->__('Choose the methods')
            ],'placeholder' => '',
            'choices' => $choices,
            'choices_as_values' => true,
            'choice_attr' => $choiceAttributes,
            'multiple' => true,
            'expanded' => false
        ]);
        $builder->add('prependBundlePrefix', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
            'label' => $this->__('Prepend bundle prefix') . ':',
            'empty_data' => 'true',
            'attr' => [
                'class' => '',
                'title' => $this->__('prepend bundle prefix ?')
            ],'required' => true,
        ]);
        $builder->add('translatable', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
            'label' => $this->__('Translatable') . ':',
            'empty_data' => 'true',
            'attr' => [
                'class' => '',
                'title' => $this->__('translatable ?')
            ],'required' => true,
        ]);
        $builder->add('translationPrefix', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Translation prefix') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the translation prefix of the route')
            ],'required' => false,
            'max_length' => 255,
        ]);
        $builder->add('condition', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Condition') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the condition of the route')
            ],'required' => false,
            'max_length' => 255,
        ]);
        $builder->add('description', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Description') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the description of the route')
            ],'required' => false,
            'max_length' => 255,
        ]);
        $builder->add('sort', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
            'label' => $this->__('Sort') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => ' validate-digits',
                'title' => $this->__('Enter the sort of the route. Only digits are allowed.')
            ],'required' => false,
            'max_length' => 11,
            'scale' => 0
        ]);
        $builder->add('group', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
            'label' => $this->__('Group') . ':',
            'empty_data' => '',
            'attr' => [
                'class' => '',
                'title' => $this->__('Enter the group of the route')
            ],'required' => false,
            'max_length' => 255,
        ]);
    }

    /**
     * Adds the return control field.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addReturnControlField(FormBuilderInterface $builder, array $options)
    {
        $builder->add('repeatCreation', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
            'mapped' => false,
            'label' => $this->__('Create another item after save'),
            'required' => false
        ]);
    }

    /**
     * Adds submit buttons.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array                $options The options
     */
    public function addSubmitButtons(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['actions'] as $action) {
            $builder->add($action['id'], 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__(/** @ignore */$action['title']),
                'icon' => ($action['id'] == 'delete' ? 'fa-trash-o' : ''),
                'attr' => [
                    'class' => $action['buttonClass'],
                    'title' => $this->__($action['description'])
                ]
            ]);
        }
        $builder->add('reset', 'Symfony\Component\Form\Extension\Core\Type\ResetType', [
            'label' => $this->__('Reset'),
            'icon' => 'fa-refresh',
            'attr' => [
                'class' => 'btn btn-default',
                'formnovalidate' => 'formnovalidate'
            ]
        ]);
        $builder->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
            'label' => $this->__('Cancel'),
            'icon' => 'fa-times',
            'attr' => [
                'class' => 'btn btn-default',
                'formnovalidate' => 'formnovalidate'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikularoutesmodule_route';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                // define class for underlying data (required for embedding forms)
                'data_class' => 'Zikula\RoutesModule\Entity\RouteEntity',
                'empty_data' => function (FormInterface $form) {
                    return $this->entityFactory->createRoute();
                },
                'error_mapping' => [
                    'isSchemesValueAllowed' => 'schemes',
                    'isMethodsValueAllowed' => 'methods',
                ],
                'mode' => 'create',
                'actions' => [],
                'inlineUsage' => false
            ])
            ->setRequired(['mode', 'actions'])
            ->setAllowedTypes([
                'mode' => 'string',
                'actions' => 'array',
                'inlineUsage' => 'bool'
            ])
            ->setAllowedValues([
                'mode' => ['create', 'edit']
            ])
        ;
    }
}
