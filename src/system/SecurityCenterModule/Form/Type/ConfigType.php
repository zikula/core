<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('updatecheck', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Check for updates'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('updatefrequency', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('How often'),
                'empty_data' => 7,
                'choices' => [
                    $translator->__('Monthly') => 30,
                    $translator->__('Weekly') => 7,
                    $translator->__('Daily') => 1
                ],
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('keyexpiry', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__("Time limit for authorisation keys ('authkeys') in seconds (default: 0)"),
                'empty_data' => 0,
                'scale' => 0,
                'max_length' => 4
            ])
            ->add('sessionauthkeyua', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__("Bind authkey to user agent ('UserAgent')"),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('secure_domain', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Secure host name'),
                'empty_data' => '',
                'max_length' => 100,
                'required' => false,
                'alert' => [$translator->__("Notice: If you use a different host name for HTTPS secure sessions and you insert an address in the 'Secure host name' box, make sure you include a trailing slash at the end of the address.") => 'info']
            ])
            ->add('signcookies', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Sign cookies'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('signingkey', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Signing key'),
                'empty_data' => sha1(mt_rand(0, time())),
                'max_length' => 100,
                'required' => false
            ])
            ->add('seclevel', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Security level'),
                'empty_data' => 'Medium',
                'choices' => [
                    $translator->__('High (user is logged-out after X minutes of inactivity)') => 'High',
                    $translator->__("Medium (user is logged-out after X minutes of inactivity, unless 'Remember me' checkbox is activated during log-in)") => 'Medium',
                    $translator->__('Low (user stays logged-in until he logs-out)') => 'Low'
                ],
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false,
                'help' => $translator->__('More information: http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime')
            ])
            ->add('secmeddays', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Automatically log user out after'),
                'empty_data' => 7,
                'scale' => 0,
                'max_length' => 3,
                'input_group' => ['right' => '<em>' . $translator->__("days (if 'Remember me' is activated)") . '</em>']
            ])
            ->add('secinactivemins', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Expire session after'),
                'empty_data' => 20,
                'scale' => 0,
                'max_length' => 4,
                'input_group' => ['right' => '<em>' . $translator->__('minutes of inactivity') . '</em>'],
                'help' => $translator->__('More information: http://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime')
            ])
            ->add('sessionstoretofile', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Store sessions'),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('File') => 1,
                    $translator->__('Database (recommended)') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
                'alert' => [$translator->__('Notice: If you change this setting, you will be logged-out immediately and will have to log back in again.') => 'info']
            ])
            ->add('sessionsavepath', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Path for saving session files'),
                'empty_data' => '',
                'required' => false,
                'alert' => [$translator->__("Notice: If you change 'Where to save sessions' to 'File' then you must enter a path in the 'Path for saving session files' box above. The path must be writeable.") => 'info'],
                'help' => $translator->__('More information: http://www.php.net/manual/en/session.configuration.php#ini.session.save-path')
            ])
            ->add('gc_probability', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Garbage collection probability'),
                'empty_data' => 100,
                'scale' => 0,
                'max_length' => 5,
                'input_group' => ['right' => $translator->__('/10000')],
                'help' => $translator->__('More information: http://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability')
            ])
            ->add('sessioncsrftokenonetime', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('CSRF Token'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Per session') => 1,
                    $translator->__('One time use') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
                'alert' => [$translator->__('One time CSRF protection may affect the browser back button but is more secure.') => 'info']
            ])
            ->add('anonymoussessions', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Use sessions for anonymous guests'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('sessionrandregenerate', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Periodically regenerate session ID'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('sessionregenerate', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Regenerate session ID during log-in and log-out'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('sessionregeneratefreq', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Regeneration probability'),
                'empty_data' => 10,
                'scale' => 0,
                'max_length' => 3,
                'input_group' => ['right' => $translator->__('% (0 to disable)')]
            ])
            ->add('sessionipcheck', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('IP checks on session (may cause problems for AOL users)'),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('sessionname', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Session cookie name'),
                'empty_data' => '_zsid',
                'alert' => [$translator->__("Notice: If you change the 'Session cookie name' setting, all registered users who are currently logged-in will then be logged-out automatically, and they will have to log back in again.") => 'warning']
            ])
            ->add('useids', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Use PHPIDS'),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idssoftblock', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Block action'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Warn only') => 1,
                    $translator->__('Block') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsmail', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Send email on block action'),
                'empty_data' => 0,
                'choices' => [
                    $translator->__('Yes') => 1,
                    $translator->__('No') => 0
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsfilter', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Select filter rules to use'),
                'empty_data' => 'xml',
                'choices' => [
                    $translator->__('XML') => 'xml',
                    $translator->__('JSON') => 'json'
                ],
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsrulepath', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('IDS Rule Path'),
                'empty_data' => '',
                'help' => $translator->__('Default: Resources/config/phpids_zikula_default.xml')
            ])
            ->add('idsimpactthresholdone', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Minimum impact to log intrusion in the database'),
                'empty_data' => 1,
                'scale' => 0
            ])
            ->add('idsimpactthresholdtwo', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Minimum impact to email the administrator'),
                'empty_data' => 10,
                'scale' => 0
            ])
            ->add('idsimpactthresholdthree', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Minimum impact to block the request'),
                'empty_data' => 25,
                'scale' => 0
            ])
            ->add('idsimpactthresholdfour', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Minimum impact to kick the user (destroy the session)'),
                'empty_data' => 75,
                'scale' => 0
            ])
            ->add('idsimpactmode', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Select the way the impact thresholds are used in Zikula'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('React on impact per request (uses the values from above)') => 1,
                    $translator->__('React on impact sum per session [loose] (uses the values from above * 10)') => 2,
                    $translator->__('React on impact sum per session [strict] (uses the values from above * 5)') => 3
                ],
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('idshtmlfields', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Define which fields contain HTML and need preparation'),
                'empty_data' => 'POST.__wysiwyg',
                'required' => false,
                'help' => $translator->__('(Place each value on a separate line.)')
            ])
            ->add('idsjsonfields', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Define which fields contain JSON data and should be treated as such'),
                'empty_data' => 'POST.__jsondata',
                'required' => false,
                'help' => $translator->__('(Place each value on a separate line.)')
            ])
            ->add('idsexceptions', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $translator->__('Define which fields should not be monitored'),
                'empty_data' => implode("\n", ['GET.__utmz', 'GET.__utmc', 'REQUEST.linksorder', 'POST.linksorder', 'REQUEST.fullcontent', 'POST.fullcontent', 'REQUEST.summarycontent', 'POST.summarycontent', 'REQUEST.filter.page', 'POST.filter.page', 'REQUEST.filter.value', 'POST.filter.value']),
                'required' => false,
                'help' => $translator->__('(Place each value on a separate line.)')
            ])
            ->add('outputfilter', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Select output filter'),
                'empty_data' => 1,
                'choices' => [
                    $translator->__('Use internal output filter only') => 0,
                    $translator->__("Use 'HTML Purifier' + internal mechanism as output filter") => 1
                ],
                'choices_as_values' => true,
                'expanded' => false,
                'multiple' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasecuritycentermodule_config';
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
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
