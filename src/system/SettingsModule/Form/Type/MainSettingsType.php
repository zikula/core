<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Main settings form type.
 */
class MainSettingsType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->translator = $options['translator'];

        $spaceReplaceCallbackTransformer = new CallbackTransformer(
            function ($originalDescription) {
                return $originalDescription;
            },
            function ($submittedDescription) {
                return mb_ereg_replace(' ', '', $submittedDescription);
            }
        );
        $pageTitleLocalizationTransformer = new CallbackTransformer(
            function ($originalPageTitle) {
                $originalPageTitle = empty($originalPageTitle) ? '%pagetitle%' : $originalPageTitle;
                $originalPageTitle = str_replace('%pagetitle%', $this->translator->__('%pagetitle%'), $originalPageTitle);
                $originalPageTitle = str_replace('%sitename%', $this->translator->__('%sitename%'), $originalPageTitle);
                $originalPageTitle = str_replace('%modulename%', $this->translator->__('%modulename%'), $originalPageTitle);

                return $originalPageTitle;
            },
            function ($submittedPageTitle) {
                $submittedPageTitle = str_replace($this->translator->__('%pagetitle%'), '%pagetitle%', $submittedPageTitle);
                $submittedPageTitle = str_replace($this->translator->__('%sitename%'), '%sitename%', $submittedPageTitle);
                $submittedPageTitle = str_replace($this->translator->__('%modulename%'), '%modulename%', $submittedPageTitle);

                return $submittedPageTitle;
            }
        );

        $builder
            ->add(
                $builder->create('pagetitle', TextType::class, [
                    'label' => $this->translator->__('Page title structure'),
                    'required' => false,
                    'help' => $this->translator->__('Possible tags: %pagetitle%, %sitename%, %modulename%')
                ])
                ->addModelTransformer($pageTitleLocalizationTransformer)
            )
            ->add('adminmail', EmailType::class, [
                'label' => $this->translator->__('Admin\'s e-mail address'),
                'constraints' => new Email()
            ])
            ->add('siteoff', ChoiceType::class, [
                'label' => $this->translator->__('Disable site'),
                'expanded' => true,
                'choices' => [
                    $this->translator->__('Yes') => 1,
                    $this->translator->__('No') => 0,
                ],
                'choices_as_values' => true
            ])
            ->add('siteoffreason', TextareaType::class, [
                'label' => $this->translator->__('Reason for disabling site'),
                'required' => false
            ])
            ->add('startController', TextType::class, [
                'label' => $this->translator->__('Start Controller'),
                'required' => false
            ])
            ->add('startpage', ChoiceType::class, [
                'label' => $this->translator->__('Start module'),
                'choices' => $options['modules'],
                'choices_as_values' => true,
                'placeholder' => $this->translator->__('No start module (static frontpage)'),
                'required' => false,
                'help' => $this->translator->__("('index.php' points to this)")
            ])
            ->add('starttype', TextType::class, [
                'label' => $this->translator->__('Start function type (required if module is set)'),
                'required' => false
            ])
            ->add('startfunc', TextType::class, [
                'label' => $this->translator->__('Start function (required if module is set)'),
                'required' => false
            ])
            ->add('startargs', TextType::class, [
                'label' => $this->translator->__('Start function arguments'),
                'required' => false,
                'help' => $this->translator->__('Separate with & for example:') . ' <code>foo=2&bar=5</code>'
            ])
            ->add('entrypoint', TextType::class, [
                'label' => $this->translator->__('Site entry point (front controller)'),
                'constraints' => new Callback([
                    'callback' => function ($data, ExecutionContextInterface $context) {
                        $falseEntryPoints = ['admin.php', 'ajax.php', 'user.php', 'mo2json.php', 'jcss.php'];
                        $entryPointExt = pathinfo($data, PATHINFO_EXTENSION);
                        if (in_array($data, $falseEntryPoints) || strtolower($entryPointExt) != 'php') {
                            $context->addViolation($this->translator->__('Error! You entered an invalid entry point.'));
                        }
                        if (!file_exists($data)) {
                            $context->addViolation($this->translator->__('Error! The file was not found in the Zikula root directory.'));
                        }
                    }
                ])
            ])
            ->add('shorturlsstripentrypoint', CheckboxType::class, [
                'label' => $this->translator->__('Strip entry point (front controller) from URLs'),
                'required' => false
            ])
            ->add('useCompression', CheckboxType::class, [
                'label' => $this->translator->__('Activate compression'),
                'required' => false
            ])
            ->add('profilemodule', ChoiceType::class, [
                'label' => $this->translator->__('Module used for managing user profiles'),
                'choices' => $options['profileModules'],
                'placeholder' => $this->translator->__('No profile module'),
                'required' => false
            ])
            ->add('messagemodule', ChoiceType::class, [
                'label' => $this->translator->__('Module used for private messaging'),
                'choices' => $options['messageModules'],
                'placeholder' => $this->translator->__('No message module'),
                'required' => false
            ])
            ->add('ajaxtimeout', IntegerType::class, [
                'label' => $this->translator->__('Time-out for Ajax connections')
            ])
            ->add(
                $builder->create('permasearch', TextType::class, [
                    'label' => $this->translator->__('List to search for'),
                    'constraints' => new Callback([
                        'callback' => function ($data, ExecutionContextInterface $context) {
                            if (mb_ereg(',$', $data)) {
                                $context->addViolation($this->translator->__('Error! In your permalink settings, strings cannot be terminated with a comma.'));
                            }
                        }
                    ])
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add(
                $builder->create('permareplace', TextType::class, [
                    'label' => $this->translator->__('List to replace with')
                ])
                ->addModelTransformer($spaceReplaceCallbackTransformer)
            )
            ->add('shorturls', CheckboxType::class, [
                'label' => $this->translator->__('Enable directory-based short URLs'),
                'required' => false
            ])
            ->add('shorturlsseparator', TextType::class, [
                'label' => $this->translator->__('Separator for permalink titles')
            ])
            ->add('shorturlsdefaultmodule', ChoiceType::class, [
                'label' => $this->translator->__('Do not display module name in short URLs for'),
                'choices' => $options['modules'],
                'choices_as_values' => true,
            ])
            ->add('save', SubmitType::class, [
                'label' => $this->translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => $this->translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
        foreach ($options['languages'] as $language => $languageCode) {
            $builder
                ->add('sitename_' . $languageCode, TextType::class, [
                    'label' => $this->translator->__('Site name')
                ])
                ->add('slogan_' . $languageCode, TextType::class, [
                    'label' => $this->translator->__('Description line')
                ])
                ->add('defaultpagetitle_' . $languageCode, TextType::class, [
                    'label' => $this->translator->__('Default page title')
                ])
                ->add('defaultmetadescription_' . $languageCode, TextType::class, [
                    'label' => $this->translator->__('Default meta description')
                ])
                ->add('metakeywords_' . $languageCode, TextareaType::class, [
                    'label' => $this->translator->__('Default meta keywords')
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasettingsmodule_mainsettings';
    }

    /**
     * {@inheritdoc}
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
                new Callback(['callback' => [$this, 'validateStartpageSettings']])
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
            $context->addViolation($this->translator->__('Error! In your permalink settings, the search list and the replacement list for permalink cleansing have a different number of comma-separated elements. If you have 3 elements in the search list then there must be 3 elements in the replacement list.'));
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
                $context->addViolation($this->translator->__('Error! When setting a startpage, starttype and startfunc are required fields.'));
            }
        }
    }
}
