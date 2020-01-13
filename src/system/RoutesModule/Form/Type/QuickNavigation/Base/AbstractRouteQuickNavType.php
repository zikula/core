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

namespace Zikula\RoutesModule\Form\Type\QuickNavigation\Base;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Translation\Extractor\Annotation\Ignore;
use Zikula\RoutesModule\Form\Type\Field\MultiListType;
use Zikula\RoutesModule\Helper\ListEntriesHelper;

/**
 * Route quick navigation form type base class.
 */
abstract class AbstractRouteQuickNavType extends AbstractType
{
    /**
     * @var ListEntriesHelper
     */
    protected $listHelper;

    public function __construct(
        ListEntriesHelper $listHelper
    ) {
        $this->listHelper = $listHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('GET')
            ->add('all', HiddenType::class)
            ->add('own', HiddenType::class)
            ->add('tpl', HiddenType::class)
        ;

        $this->addListFields($builder, $options);
        $this->addSearchField($builder, $options);
        $this->addSortingFields($builder, $options);
        $this->addAmountField($builder, $options);
        $this->addBooleanFields($builder, $options);
        $builder->add('updateview', SubmitType::class, [
            'label' => 'OK',
            'attr' => [
                'class' => 'btn btn-default btn-sm'
            ]
        ]);
    }

    /**
     * Adds list fields.
     */
    public function addListFields(FormBuilderInterface $builder, array $options = []): void
    {
        $listEntries = $this->listHelper->getEntries('route', 'workflowState');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('workflowState', ChoiceType::class, [
            'label' => 'State',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => $choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => false,
            'expanded' => false
        ]);
        $listEntries = $this->listHelper->getEntries('route', 'schemes');
        $choices = [];
        $choiceAttributes = [];
        foreach ($listEntries as $entry) {
            $choices[$entry['text']] = $entry['value'];
            $choiceAttributes[$entry['text']] = ['title' => $entry['title']];
        }
        $builder->add('schemes', MultiListType::class, [
            'label' => 'Schemes',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => $choices,
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
        $builder->add('methods', MultiListType::class, [
            'label' => 'Methods',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => $choices,
            'choice_attr' => $choiceAttributes,
            'multiple' => true,
            'expanded' => false
        ]);
    }

    /**
     * Adds a search field.
     */
    public function addSearchField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('q', SearchType::class, [
            'label' => 'Search',
            'attr' => [
                'maxlength' => 255,
                'class' => 'form-control-sm'
            ],
            'required' => false
        ]);
    }


    /**
     * Adds sorting fields.
     */
    public function addSortingFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder
            ->add('sort', ChoiceType::class, [
                'label' => 'Sort by',
                'attr' => [
                    'class' => 'form-control-sm'
                ],
                'choices' =>             [
                    'Bundle' => 'bundle',
                    'Controller' => 'controller',
                    'Action' => 'action',
                    'Path' => 'path',
                    'Host' => 'host',
                    'Schemes' => 'schemes',
                    'Methods' => 'methods',
                    'Prepend bundle prefix' => 'prependBundlePrefix',
                    'Translatable' => 'translatable',
                    'Translation prefix' => 'translationPrefix',
                    'Condition' => 'condition',
                    'Description' => 'description',
                    'Sort' => 'sort',
                    'Creation date' => 'createdDate',
                    'Creator' => 'createdBy',
                    'Update date' => 'updatedDate',
                    'Updater' => 'updatedBy'
                ],
                'required' => true,
                'expanded' => false
            ])
            ->add('sortdir', ChoiceType::class, [
                'label' => 'Sort direction',
                'empty_data' => 'asc',
                'attr' => [
                    'class' => 'form-control-sm'
                ],
                'choices' => [
                    'Ascending' => 'asc',
                    'Descending' => 'desc'
                ],
                'required' => true,
                'expanded' => false
            ])
        ;
    }

    /**
     * Adds a page size field.
     */
    public function addAmountField(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('num', ChoiceType::class, [
            'label' => 'Page size',
            'empty_data' => 20,
            'attr' => [
                'class' => 'form-control-sm text-right'
            ],
            /** @Ignore */
            'choices' => [
                5 => 5,
                10 => 10,
                15 => 15,
                20 => 20,
                30 => 30,
                50 => 50,
                100 => 100
            ],
            'required' => false,
            'expanded' => false
        ]);
    }

    /**
     * Adds boolean fields.
     */
    public function addBooleanFields(FormBuilderInterface $builder, array $options = []): void
    {
        $builder->add('prependBundlePrefix', ChoiceType::class, [
            'label' => 'Prepend bundle prefix',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
        $builder->add('translatable', ChoiceType::class, [
            'label' => 'Translatable',
            'attr' => [
                'class' => 'form-control-sm'
            ],
            'required' => false,
            'placeholder' => 'All',
            'choices' => [
                'No' => 'no',
                'Yes' => 'yes'
            ]
        ]);
    }

    public function getBlockPrefix()
    {
        return 'zikularoutesmodule_routequicknav';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false
        ]);
    }
}
