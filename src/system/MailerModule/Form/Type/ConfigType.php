<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MailerModule\Form\Type;

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
            ->add('transport', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Mail transport'),
                'empty_data' => 'mail',
                'choices' => [
                    $translator->__('Internal PHP `mail()` function') => 'mail',
                    $translator->__('Sendmail message transfer agent') => 'sendmail',
                    $translator->__('Google gmail') => 'gmail',
                    $translator->__('SMTP mail transfer protocol') => 'smtp',
                    $translator->__('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
                ],
                'choices_as_values' => true
            ])
            ->add('charset', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Character set'),
                'empty_data' => \ZLanguage::getEncoding(),
                'max_length' => 20,
                'help' => $translator->__f("Default: '%s'", ['%s' => \ZLanguage::getEncoding()])
            ])
            ->add('encoding', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Encoding'),
                'empty_data' => '8bit',
                'choices' => [
                    '8bit' => '8bit',
                    '7bit' => '7bit',
                    'binary' => 'binary',
                    'base64' => 'base64',
                    'quoted-printable' => 'quoted-printable'
                ],
                'choices_as_values' => true,
                'help' => $translator->__f("Default: '%s'", ['%s' => '8bit'])
            ])
            ->add('html', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('HTML-formatted messages'),
                'required' => false
            ])
            ->add('wordwrap', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Word wrap'),
                'empty_data' => 50,
                'scale' => 0,
                'max_length' => 3,
                'help' => $translator->__f("Default: '%s'", ['%s' => '50'])
            ])
            ->add('enableLogging', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Enable logging of sent mail'),
                'required' => false
            ])
            ->add('host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('SMTP host server'),
                'empty_data' => 'localhost',
                'max_length' => 255,
                'required' => false,
                'help' => $translator->__f("Default: '%s'", ['%s' => 'localhost'])
            ])
            ->add('port', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('SMTP port'),
                'empty_data' => 25,
                'scale' => 0,
                'max_length' => 5,
                'required' => false,
                'help' => $translator->__f("Default: '%s'", ['%s' => '25'])
            ])
            ->add('encryption', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('SMTP encryption method'),
                'empty_data' => '',
                'choices' => [
                    $translator->__('None') => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('auth_mode', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('SMTP authentication type'),
                'empty_data' => '',
                'choices' => [
                    $translator->__('None') => '',
                    'Plain' => 'plain',
                    'Login' => 'login',
                    'Cram-MD5' => 'cram-md5'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('SMTP user name'),
                'empty_data' => '',
                'max_length' => 50,
                'required' => false
            ])
            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $translator->__('SMTP password'),
                'empty_data' => '',
                'max_length' => 50,
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $translator->__('Gmail user name'),
                'empty_data' => '',
                'max_length' => 50,
                'required' => false
            ])
            ->add('passwordGmail', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $translator->__('Gmail password'),
                'empty_data' => '',
                'max_length' => 50,
                'always_empty' => false,
                'required' => false
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
        return 'zikulamailermodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null
        ]);
    }
}
