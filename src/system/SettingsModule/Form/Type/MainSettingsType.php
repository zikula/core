<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SettingsModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MainSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $spaceReplaceCallbackTransformer = new CallbackTransformer(
            function ($originalDescription) {
                return $originalDescription;
            },
            function ($submittedDescription) {
                return mb_ereg_replace(' ', '', $submittedDescription);
            }
        );
        $pageTitleLocalizationTransformer = new CallbackTransformer(
            function ($originalPageTitle) use ($options) {
                $originalPageTitle = empty($originalPageTitle) ? '%pagetitle%' : $originalPageTitle;
                $originalPageTitle = str_replace('%pagetitle%', $options['translator']->__('%pagetitle%'), $originalPageTitle);
                $originalPageTitle = str_replace('%sitename%', $options['translator']->__('%sitename%'), $originalPageTitle);
                $originalPageTitle = str_replace('%modulename%', $options['translator']->__('%modulename%'), $originalPageTitle);

                return $originalPageTitle;
            },
            function ($submittedPageTitle) use ($options) {
                $submittedPageTitle = str_replace($options['translator']->__('%pagetitle%'), '%pagetitle%', $submittedPageTitle);
                $submittedPageTitle = str_replace($options['translator']->__('%sitename%'), '%sitename%', $submittedPageTitle);
                $submittedPageTitle = str_replace($options['translator']->__('%modulename%'), '%modulename%', $submittedPageTitle);

                return $submittedPageTitle;
            }
        );

        $builder
            ->add(
                $builder->create('pagetitle', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('Page title structure'),
                    'required' => false
                ])
                ->addModelTransformer($pageTitleLocalizationTransformer)
            )
            ->add('adminmail', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $options['translator']->__('Admin\'s e-mail address'),
                'constraints' => new Email()
            ])
            ->add('siteoff', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Disable site'),
                'expanded' => true,
                'choices' => [
                    $options['translator']->__('Yes') => 1,
                    $options['translator']->__('No') => 0,
                ],
                'choices_as_values' => true
            ])
            ->add('siteoffreason', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $options['translator']->__('Reason for disabling site'),
                'required' => false
            ])
            ->add('startController', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Start Controller'),
                'required' => false
            ])
            ->add('startpage', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Start module'),
                'choices' => $options['modules'],
                'choices_as_values' => true,
                'placeholder' => $options['translator']->__('No start module (static frontpage)'),
                'required' => false
            ])
            ->add('starttype', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Start function type (required if module is set)'),
                'required' => false
            ])
            ->add('startfunc', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Start function (required if module is set)'),
                'required' => false
            ])
            ->add('startargs', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Start function arguments'),
                'required' => false
            ])
            ->add('entrypoint', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Site entry point (front controller)'),
                'constraints' => new Callback([
                    'callback' => function ($data, ExecutionContextInterface $context) use ($options) {
                        $falseEntryPoints = ['admin.php', 'ajax.php', 'user.php', 'mo2json.php', 'jcss.php'];
                        $entryPointExt = pathinfo($data, PATHINFO_EXTENSION);
                        if (in_array($data, $falseEntryPoints) || strtolower($entryPointExt) != 'php') {
                            $context->addViolation($options['translator']->__('Error! You entered an invalid entry point.'));
                        }
                        if (!file_exists($data)) {
                            $context->addViolation($options['translator']->__('Error! The file was not found in the Zikula root directory.'));
                        }
                    }
                ])
            ])
            ->add('shorturlsstripentrypoint', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Strip entry point (front controller) from URLs'),
                'required' => false
            ])
            ->add('useCompression', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Activate compression'),
                'required' => false
            ])
            ->add('profilemodule', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Module used for managing user profiles'),
                'choices' => $options['profileModules'],
                'choices_as_values' => true,
                'placeholder' => $options['translator']->__('No profile module'),
                'required' => false
            ])
            ->add('messagemodule', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Module used for private messaging'),
                'choices' => $options['messageModules'],
                'choices_as_values' => true,
                'placeholder' => $options['translator']->__('No message module'),
                'required' => false
            ])
            ->add('ajaxtimeout', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $options['translator']->__('Time-out for Ajax connections')
            ])
            ->add(
                $builder->create('permasearch', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('List to search for'),
                    'constraints' => new Callback([
                        'callback' => function ($data, ExecutionContextInterface $context) use ($options) {
                            if (mb_ereg(',$', $data)) {
                                $context->addViolation($options['translator']->__('Error! In your permalink settings, strings cannot be terminated with a comma.'));
                            }
                        }
                    ])
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add(
                $builder->create('permareplace', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('List to replace with')
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add('shorturls', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $options['translator']->__('Enable directory-based short URLs'),
                'required' => false
            ])
            ->add('shorturlsseparator', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $options['translator']->__('Separator for permalink titles')
            ])
            ->add('shorturlsdefaultmodule', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $options['translator']->__('Do not display module name in short URLs for'),
                'choices' => $options['modules']
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $options['translator']->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
        foreach ($options['languages'] as $languageCode => $language) {
            $builder
                ->add('sitename_' . $languageCode, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('Site name')
                ])
                ->add('slogan_' . $languageCode, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('Description line')
                ])
                ->add('defaultpagetitle_' . $languageCode, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('Default page title')
                ])
                ->add('defaultmetadescription_' . $languageCode, 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                    'label' => $options['translator']->__('Default meta description')
                ])
                ->add('metakeywords_' . $languageCode, 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                    'label' => $options['translator']->__('Default meta keywords')
                ])
            ;
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulasettingsmodule_mainsettings';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'modules' => [],
            'profileModules' => [],
            'messageModules' => [],
            'languages' => [],
            'constraints' => [
                new Callback(['callback' => [$this, 'validatePermalinkSettings']]),
                new Callback(['callback' => [$this, 'validateStartpageSettings']]),
            ]
        ]);
    }

    /**
     * Validate permalink settings.
     *
     * @param $data
     * @param ExecutionContextInterface $context
     */
    public function validatePermalinkSettings($data, ExecutionContextInterface $context)
    {
        if (mb_strlen($data['permasearch']) == 0) {
            $permasearchCount = 0;
        } else {
            $permasearchCount = (!mb_ereg(',', $data['permasearch']) && mb_strlen($data['permasearch'] > 0) ? 1 : count(explode(',', $data['permasearch'])));
        }

        if (mb_strlen($data['permareplace']) == 0) {
            $permareplaceCount = 0;
        } else {
            $permareplaceCount = (!mb_ereg(',', $data['permareplace']) && mb_strlen($data['permareplace'] > 0) ? 1 : count(explode(',', $data['permareplace'])));
        }

        if ($permareplaceCount !== $permasearchCount) {
            $context->addViolation(__('Error! In your permalink settings, the search list and the replacement list for permalink cleansing have a different number of comma-separated elements. If you have 3 elements in the search list then there must be 3 elements in the replacement list.'));
        }
    }

    /**
     * Validate startpage settings.
     *
     * @param $data
     * @param ExecutionContextInterface $context
     */
    public function validateStartpageSettings($data, ExecutionContextInterface $context)
    {
        if (!empty($data['startpage'])) {
            if (empty($data['starttype']) || empty($data['startfunc'])) {
                $context->addViolation(__('Error! When setting a startpage, starttype and startfunc are required fields.'));
            }
        }
    }
}
