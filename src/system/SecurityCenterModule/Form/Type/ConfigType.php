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

namespace Zikula\SecurityCenterModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\SecurityCenterModule\Constant;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
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
        $builder
            ->add('updatecheck', ChoiceType::class, [
                'label' => $this->__('Check for updates'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('updatefrequency', ChoiceType::class, [
                'label' => $this->__('How often'),
                'empty_data' => 7,
                'choices' => [
                    $this->__('Monthly') => 30,
                    $this->__('Weekly') => 7,
                    $this->__('Daily') => 1
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('keyexpiry', IntegerType::class, [
                'label' => $this->__("Time limit for authorisation keys ('authkeys') in seconds (default: 0)"),
                'empty_data' => 0,
                'attr' => [
                    'maxlength' => 4
                ]
            ])
            ->add('sessionauthkeyua', ChoiceType::class, [
                'label' => $this->__("Bind authkey to user agent ('UserAgent')"),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('secure_domain', TextType::class, [
                'label' => $this->__('Secure host name'),
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 100
                ],
                'required' => false,
                'alert' => [$this->__("Notice: If you use a different host name for HTTPS secure sessions and you insert an address in the 'Secure host name' box, make sure you include a trailing slash at the end of the address.") => 'info']
            ])
            ->add('signcookies', ChoiceType::class, [
                'label' => $this->__('Sign cookies'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('signingkey', TextType::class, [
                'label' => $this->__('Signing key'),
                'empty_data' => sha1((string)random_int(0, time())),
                'attr' => [
                    'maxlength' => 100
                ],
                'required' => false
            ])
            ->add('seclevel', ChoiceType::class, [
                'label' => $this->__('Security level'),
                'empty_data' => 'Medium',
                'choices' => [
                    $this->__('High (user is logged-out after X minutes of inactivity)') => 'High',
                    $this->__("Medium (user is logged-out after X minutes of inactivity, unless 'Remember me' checkbox is activated during log-in)") => 'Medium',
                    $this->__('Low (user stays logged-in until he logs-out)') => 'Low'
                ],
                'expanded' => false,
                'multiple' => false,
                'help' => $this->__('More information: http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime')
            ])
            ->add('secmeddays', IntegerType::class, [
                'label' => $this->__('Automatically log user out after'),
                'empty_data' => 7,
                'attr' => [
                    'maxlength' => 3
                ],
                'input_group' => ['right' => '<em>' . $this->__("days (if 'Remember me' is activated)") . '</em>']
            ])
            ->add('secinactivemins', IntegerType::class, [
                'label' => $this->__('Expire session after'),
                'empty_data' => 20,
                'attr' => [
                    'maxlength' => 4
                ],
                'input_group' => ['right' => '<em>' . $this->__('minutes of inactivity') . '</em>'],
                'help' => $this->__f('More information in <a href="%url%" target="_blank">PHP documentation</a>.', ['%url%' => 'https://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime']),
                'help_html' => true
            ])
            ->add('sessionstoretofile', ChoiceType::class, [
                'label' => $this->__('Store sessions'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    $this->__('File') => Constant::SESSION_STORAGE_FILE,
                    $this->__('Database (recommended)') => Constant::SESSION_STORAGE_DATABASE
                ],
                'expanded' => true,
                'multiple' => false,
                'alert' => [$this->__('Notice: If you change this setting, you will be logged-out immediately and will have to log back in again.') => 'info']
            ])
            ->add('sessionsavepath', TextType::class, [
                'label' => $this->__('Path for saving session files'),
                'empty_data' => '',
                'required' => false,
                'alert' => [$this->__("Notice: If you change 'Where to save sessions' to 'File' then you must enter a path in the 'Path for saving session files' box above. The path must be writeable. Leave value empty for default location '%kernel.cache_dir%/sessions'") => 'info'],
                'help' => $this->__f('More information in <a href="%url%" target="_blank">PHP documentation</a>.', ['%url%' => 'https://www.php.net/manual/en/session.configuration.php#ini.session.save-path']),
                'help_html' => true
            ])
            ->add('gc_probability', IntegerType::class, [
                'label' => $this->__('Garbage collection probability'),
                'empty_data' => 100,
                'attr' => [
                    'maxlength' => 5
                ],
                'input_group' => ['right' => $this->__('/10000')],
                'help' => $this->__f('More information in <a href="%url%" target="_blank">PHP documentation</a>.', ['%url%' => 'https://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability']),
                'help_html' => true
            ])
            ->add('sessioncsrftokenonetime', ChoiceType::class, [
                'label' => $this->__('CSRF Token'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    $this->__('Per session') => 1,
                    $this->__('One time use') => 0
                ],
                'expanded' => true,
                'multiple' => false,
                'alert' => [$this->__('One time CSRF protection may affect the browser back button but is more secure.') => 'info']
            ])
            ->add('sessionrandregenerate', ChoiceType::class, [
                'label' => $this->__('Periodically regenerate session ID'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('sessionregenerate', ChoiceType::class, [
                'label' => $this->__('Regenerate session ID during log-in and log-out'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('sessionregeneratefreq', IntegerType::class, [
                'label' => $this->__('Regeneration probability'),
                'empty_data' => 10,
                'attr' => [
                    'maxlength' => 3
                ],
                'input_group' => ['right' => $this->__('% (0 to disable)')]
            ])
            ->add('sessionname', TextType::class, [
                'label' => $this->__('Session cookie name'),
                'empty_data' => '_zsid',
                'alert' => [$this->__("Notice: If you change the 'Session cookie name' setting, all registered users who are currently logged-in will then be logged-out automatically, and they will have to log back in again.") => 'warning']
            ])
            ->add('useids', ChoiceType::class, [
                'label' => $this->__('Use PHPIDS'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idssoftblock', ChoiceType::class, [
                'label' => $this->__('Block action'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 1,
                'choices' => [
                    $this->__('Warn only') => 1,
                    $this->__('Block') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsmail', ChoiceType::class, [
                'label' => $this->__('Send email on block action'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 0,
                'choices' => [
                    $this->__('Yes') => 1,
                    $this->__('No') => 0
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsfilter', ChoiceType::class, [
                'label' => $this->__('Select filter rules to use'),
                'label_attr' => ['class' => 'radio-custom'],
                'empty_data' => 'xml',
                'choices' => [
                    $this->__('XML') => 'xml',
                    $this->__('JSON') => 'json'
                ],
                'expanded' => true,
                'multiple' => false
            ])
            ->add('idsrulepath', TextType::class, [
                'label' => $this->__('IDS Rule Path'),
                'empty_data' => '',
                'help' => $this->__('Default: <code>Resources/config/phpids_zikula_default.xml</code>'),
                'help_html' => true
            ])
            ->add('idsimpactthresholdone', IntegerType::class, [
                'label' => $this->__('Minimum impact to log intrusion in the database'),
                'empty_data' => 1
            ])
            ->add('idsimpactthresholdtwo', IntegerType::class, [
                'label' => $this->__('Minimum impact to email the administrator'),
                'empty_data' => 10
            ])
            ->add('idsimpactthresholdthree', IntegerType::class, [
                'label' => $this->__('Minimum impact to block the request'),
                'empty_data' => 25
            ])
            ->add('idsimpactthresholdfour', IntegerType::class, [
                'label' => $this->__('Minimum impact to kick the user (destroy the session)'),
                'empty_data' => 75
            ])
            ->add('idsimpactmode', ChoiceType::class, [
                'label' => $this->__('Select the way the impact thresholds are used in Zikula'),
                'empty_data' => 1,
                'choices' => [
                    $this->__('React on impact per request (uses the values from above)') => 1,
                    $this->__('React on impact sum per session [loose] (uses the values from above * 10)') => 2,
                    $this->__('React on impact sum per session [strict] (uses the values from above * 5)') => 3
                ],
                'expanded' => false,
                'multiple' => false
            ])
            ->add('idshtmlfields', TextareaType::class, [
                'label' => $this->__('Define which fields contain HTML and need preparation'),
                'empty_data' => 'POST.__wysiwyg',
                'required' => false,
                'help' => $this->__('(Place each value on a separate line.)')
            ])
            ->add('idsjsonfields', TextareaType::class, [
                'label' => $this->__('Define which fields contain JSON data and should be treated as such'),
                'empty_data' => 'POST.__jsondata',
                'required' => false,
                'help' => $this->__('(Place each value on a separate line.)')
            ])
            ->add('idsexceptions', TextareaType::class, [
                'label' => $this->__('Define which fields should not be monitored'),
                'empty_data' => implode("\n", ['GET.__utmz', 'GET.__utmc', 'REQUEST.linksorder', 'POST.linksorder', 'REQUEST.fullcontent', 'POST.fullcontent', 'REQUEST.summarycontent', 'POST.summarycontent', 'REQUEST.filter.page', 'POST.filter.page', 'REQUEST.filter.value', 'POST.filter.value']),
                'required' => false,
                'help' => $this->__('(Place each value on a separate line.)')
            ])
            ->add('outputfilter', ChoiceType::class, [
                'label' => $this->__('Select output filter'),
                'empty_data' => 1,
                'choices' => [
                    $this->__('Use internal output filter only') => 0,
                    $this->__("Use 'HTML Purifier' + internal mechanism as output filter") => 1
                ],
                'expanded' => false,
                'multiple' => false
            ])
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
    }

    public function getBlockPrefix()
    {
        return 'zikulasecuritycentermodule_config';
    }
}
