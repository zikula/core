<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\SecurityCenterModule\Constant;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('updatecheck', ChoiceType::class, [
                'label' => 'Check for updates',
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    'Yes' => 1,
                    'No' => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('updatefrequency', ChoiceType::class, [
                'label' => 'How often',
                'empty_data' => 7,
                'choices' => [
                    'Monthly' => 30,
                    'Weekly' => 7,
                    'Daily' => 1
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('seclevel', ChoiceType::class, [
                'label' => 'Security level',
                'empty_data' => 'Medium',
                'choices' => [
                    'High (user is logged-out after X minutes of inactivity)' => 'High',
                    "Medium (user is logged-out after X minutes of inactivity, unless 'Remember me' checkbox is activated during log-in)" => 'Medium',
                    'Low (user stays logged-in until he logs-out)' => 'Low'
                ],
                'expanded' => false,
                'multiple' => false,
                'help' => 'More information in <a href=\'%url%\' target="_blank">PHP documentation</a>.',
                'help_translation_parameters' => [
                    '%url%' => 'https://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime'
                ],
                'help_html' => true
            ])
            ->add('secmeddays', IntegerType::class, [
                'label' => 'Automatically log user out after',
                'empty_data' => 7,
                'attr' => [
                    'maxlength' => 3
                ],
                'input_group' => ['right' => 'days (if \'Remember me\' is activated)']
            ])
            ->add('secinactivemins', IntegerType::class, [
                'label' => 'Expire session after',
                'empty_data' => 20,
                'attr' => [
                    'maxlength' => 4
                ],
                'input_group' => ['right' => 'minutes of inactivity'],
                'help' => 'More information in <a href=\'%url%\' target="_blank">PHP documentation</a>.',
                'help_translation_parameters' => [
                    '%url%' => 'https://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime'
                ],
                'help_html' => true
            ])
            ->add('sessionstoretofile', ChoiceType::class, [
                'label' => 'Store sessions',
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    'File' => Constant::SESSION_STORAGE_FILE,
                    'Database (recommended)' => Constant::SESSION_STORAGE_DATABASE
                ],
                'expanded' => true,
                'multiple' => false,
                'alert' => ['Notice: If you change this setting, you will be logged-out immediately and will have to log back in again.' => 'info']
            ])
            ->add('sessionsavepath', TextType::class, [
                'label' => 'Path for saving session files',
                'empty_data' => '',
                'required' => false,
                'alert' => ["Notice: If you change 'Where to save sessions' to 'File' then you must enter a path in the 'Path for saving session files' box above. The path must be writeable. Leave value empty for default location '%kernel.cache_dir%/sessions'" => 'info'],
                'help' => 'More information in <a href=\'%url%\' target="_blank">PHP documentation</a>.',
                'help_translation_parameters' => [
                    '%url%' => 'https://www.php.net/manual/en/session.configuration.php#ini.session.save-path'
                ],
                'help_html' => true
            ])
            ->add('sessionname', TextType::class, [
                'label' => 'Session cookie name',
                'empty_data' => '_zsid',
                'alert' => ["Notice: If you change the 'Session cookie name' setting, all registered users who are currently logged-in will then be logged-out automatically, and they will have to log back in again." => 'warning']
            ])
            ->add('useids', ChoiceType::class, [
                'label' => 'Use PHPIDS',
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    'Yes' => 1,
                    'No' => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idssoftblock', ChoiceType::class, [
                'label' => 'Block action',
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    'Warn only' => 1,
                    'Block' => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsmail', ChoiceType::class, [
                'label' => 'Send email on block action',
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    'Yes' => 1,
                    'No' => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsfilter', ChoiceType::class, [
                'label' => 'Select filter rules to use',
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 'xml',
                'choices' => [
                    'XML' => 'xml',
                    'JSON' => 'json'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsrulepath', TextType::class, [
                'label' => 'IDS rule path',
                'empty_data' => '',
                'help' => 'Default: <code>Resources/config/phpids_zikula_default.xml</code>',
                'help_html' => true
            ])
            ->add('idsimpactthresholdone', IntegerType::class, [
                'label' => 'Minimum impact to log intrusion in the database',
                'empty_data' => 1
            ])
            ->add('idsimpactthresholdtwo', IntegerType::class, [
                'label' => 'Minimum impact to email the administrator',
                'empty_data' => 10
            ])
            ->add('idsimpactthresholdthree', IntegerType::class, [
                'label' => 'Minimum impact to block the request',
                'empty_data' => 25
            ])
            ->add('idsimpactthresholdfour', IntegerType::class, [
                'label' => 'Minimum impact to kick the user (destroy the session)',
                'empty_data' => 75
            ])
            ->add('idsimpactmode', ChoiceType::class, [
                'label' => 'Select the way the impact thresholds are used in Zikula',
                'empty_data' => 1,
                'choices' => [
                    'React on impact per request (uses the values from above)' => 1,
                    'React on impact sum per session [loose] (uses the values from above * 10)' => 2,
                    'React on impact sum per session [strict] (uses the values from above * 5)' => 3
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('idshtmlfields', TextareaType::class, [
                'label' => 'Define which fields contain HTML and need preparation',
                'empty_data' => 'POST.__wysiwyg',
                'required' => false,
                'help' => '(Place each value on a separate line.)'
            ])
            ->add('idsjsonfields', TextareaType::class, [
                'label' => 'Define which fields contain JSON data and should be treated as such',
                'empty_data' => 'POST.__jsondata',
                'required' => false,
                'help' => '(Place each value on a separate line.)'
            ])
            ->add('idsexceptions', TextareaType::class, [
                'label' => 'Define which fields should not be monitored',
                'empty_data' => implode("\n", ['GET.__utmz', 'GET.__utmc', 'REQUEST.linksorder', 'POST.linksorder', 'REQUEST.fullcontent', 'POST.fullcontent', 'REQUEST.summarycontent', 'POST.summarycontent', 'REQUEST.filter.page', 'POST.filter.page', 'REQUEST.filter.value', 'POST.filter.value']),
                'required' => false,
                'help' => '(Place each value on a separate line.)'
            ])
            ->add('idscachingtype', ChoiceType::class, [
                'label' => 'Caching method',
                'empty_data' => 'none',
                'choices' => [
                    'Session' => 'session',
                    'File' => 'file',
                    // 'Database' => 'database',
                    'Memcached' => 'memcached',
                    'None' => 'none'
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('idscachingexpiration', IntegerType::class, [
                'label' => 'Cache expiration time',
                'empty_data' => 600,
                'attr' => [
                    'maxlength' => 5
                ],
                'input_group' => ['right' => 'seconds']
            ])
            ->add('outputfilter', ChoiceType::class, [
                'label' => 'Select output filter',
                'empty_data' => 1,
                'choices' => [
                    'Use internal output filter only' => 0,
                    "Use 'HTML Purifier' + internal mechanism as output filter" => 1
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times'
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulasecuritycentermodule_config';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'security'
        ]);
    }
}
