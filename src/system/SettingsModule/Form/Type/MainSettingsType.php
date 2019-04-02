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

namespace Zikula\SettingsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Main settings form type.
 */
class MainSettingsType extends AbstractType
{
    use TranslatorTrait;

    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $spaceReplaceCallbackTransformer = new CallbackTransformer(
            static function($originalDescription) {
                return $originalDescription;
            },
            static function($submittedDescription) {
                return mb_ereg_replace(' ', '', $submittedDescription);
            }
        );
        $pageTitleLocalizationTransformer = new CallbackTransformer(
            function($originalPageTitle) {
                $originalPageTitle = empty($originalPageTitle) ? '%pagetitle%' : $originalPageTitle;
                $originalPageTitle = str_replace(
                    ['%pagetitle%', '%sitename%', '%modulename%'],
                    [$this->__('%pagetitle%'), $this->__('%sitename%'), $this->__('%modulename%')],
                    $originalPageTitle
                );

                return $originalPageTitle;
            },
            function($submittedPageTitle) {
                $submittedPageTitle = str_replace(
                    [$this->__('%pagetitle%'), $this->__('%sitename%'), $this->__('%modulename%')],//
                    ['%pagetitle%', '%sitename%', '%modulename%'],
                    $submittedPageTitle
                );

                return $submittedPageTitle;
            }
        );

        $builder
            ->add(
                $builder->create('pagetitle', TextType::class, [
                    'label' => $this->__('Page title structure'),
                    'required' => false,
                    'help' => $this->__('Possible tags: %pagetitle%, %sitename%, %modulename%')
                ])
                ->addModelTransformer($pageTitleLocalizationTransformer)
            )
            ->add('adminmail', EmailType::class, [
                'label' => $this->__('Admin\'s e-mail address'),
                'constraints' => new Email()
            ])
            ->add('siteoff', ChoiceType::class, [
                'label' => $this->__('Disable site'),
                'expanded' => true,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0,
                ],
            ])
            ->add('siteoffreason', TextareaType::class, [
                'label' => $this->__('Reason for disabling site'),
                'required' => false
            ])
            ->add('startController', TextType::class, [
                'label' => $this->__('Start Controller'),
                'required' => false,
                'help' => $this->__('MyModuleName:Controller:method'),
                'constraints' => [
                    new Regex('/\w+:\w+:\w+/')
                ]
            ])
            ->add('startargs', TextType::class, [
                'label' => $this->__('Start function arguments'),
                'required' => false,
                'help' => $this->__('Separate with & for example:') . ' <code>foo=2&bar=5</code>'
            ])
            ->add('useCompression', CheckboxType::class, [
                'label' => $this->__('Activate compression'),
                'required' => false
            ])
            ->add('profilemodule', ChoiceType::class, [
                'label' => $this->__('Module used for managing user profiles'),
                'choices' => $options['profileModules'],
                'placeholder' => $this->__('No profile module'),
                'required' => false
            ])
            ->add('messagemodule', ChoiceType::class, [
                'label' => $this->__('Module used for private messaging'),
                'choices' => $options['messageModules'],
                'placeholder' => $this->__('No message module'),
                'required' => false
            ])
            ->add('ajaxtimeout', IntegerType::class, [
                'label' => $this->__('Time-out for Ajax connections'),
                'input_group' => ['right' => $this->__('seconds')]
            ])
            ->add(
                $builder->create('permasearch', TextType::class, [
                    'label' => $this->__('List to search for'),
                    'constraints' => new Callback([
                        'callback' => function($data, ExecutionContextInterface $context) {
                            if (mb_ereg(',$', $data)) {
                                $context->addViolation($this->__('Error! In your permalink settings, strings cannot be terminated with a comma.'));
                            }
                        }
                    ])
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add(
                $builder->create('permareplace', TextType::class, [
                    'label' => $this->__('List to replace with')
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add('save', SubmitType::class, [
                'label' => $this->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
        foreach ($options['languages'] as $language => $languageCode) {
            $builder
                ->add('sitename_' . $languageCode, TextType::class, [
                    'label' => $this->__('Site name')
                ])
                ->add('slogan_' . $languageCode, TextType::class, [
                    'label' => $this->__('Description line')
                ])
                ->add('defaultpagetitle_' . $languageCode, TextType::class, [
                    'label' => $this->__('Default page title')
                ])
                ->add('defaultmetadescription_' . $languageCode, TextType::class, [
                    'label' => $this->__('Default meta description')
                ])
            ;
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulasettingsmodule_mainsettings';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'profileModules' => [],
            'messageModules' => [],
            'languages' => [],
            'constraints' => [
                new Callback(['callback' => [$this, 'validatePermalinkSettings']])
            ]
        ]);
    }

    public function validatePermalinkSettings(array $data, ExecutionContextInterface $context): void
    {
        if ('' === $data['permasearch']) {
            $permasearchCount = 0;
        } else {
            $permasearchCount = (!mb_ereg(',', $data['permasearch']) && '' !== $data['permasearch']) ? 1 : mb_substr_count($data['permasearch'], ',') + 1;
        }

        if ('' === $data['permareplace']) {
            $permareplaceCount = 0;
        } else {
            $permareplaceCount = (!mb_ereg(',', $data['permareplace']) && '' !== $data['permareplace']) ? 1 : mb_substr_count($data['permareplace'], ',') + 1;
        }

        if ($permareplaceCount !== $permasearchCount) {
            $context->addViolation($this->__('Error! In your permalink settings, the search list and the replacement list for permalink cleansing have a different number of comma-separated elements. If you have 3 elements in the search list then there must be 3 elements in the replacement list.'));
        }
    }
}
