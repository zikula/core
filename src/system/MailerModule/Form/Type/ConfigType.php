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

namespace Zikula\MailerModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var array
     */
    protected $dataValues;

    /**
     * ConfigType constructor.
     *
     * @param TranslatorInterface $translator   Translator service instance.
     * @param DynamicConfigDumper $configDumper DynamicConfigDumper service instance.
     * @param VariableApi         $variableApi  VariableApi service instance.
     */
    public function __construct(TranslatorInterface $translator, DynamicConfigDumper $configDumper, VariableApi $variableApi)
    {
        $this->setTranslator($translator);

        $params = $configDumper->getConfiguration('swiftmailer');
        $modVars = $variableApi->getAll('ZikulaMailerModule');
        $this->dataValues = array_merge($params, $modVars);
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance.
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
        $builder
            ->add('transport', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Mail transport'),
                'data' => $this->dataValues['transport'],
                'empty_data' => 'mail',
                'choices' => [
                    $this->__('Internal PHP `mail()` function') => 'mail',
                    $this->__('Sendmail message transfer agent') => 'sendmail',
                    $this->__('Google gmail') => 'gmail',
                    $this->__('SMTP mail transfer protocol') => 'smtp',
                    $this->__('Development/debug mode (Do not send any email)') => 'test'/*'null'*/
                ],
                'choices_as_values' => true
            ])
            ->add('charset', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Character set'),
                'data' => $this->dataValues['charset'],
                'empty_data' => \ZLanguage::getEncoding(),
                'max_length' => 20,
                'help' => $this->__f("Default: '%s'", ['%s' => \ZLanguage::getEncoding()])
            ])
            ->add('encoding', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Encoding'),
                'data' => $this->dataValues['encoding'],
                'empty_data' => '8bit',
                'choices' => [
                    '8bit' => '8bit',
                    '7bit' => '7bit',
                    'binary' => 'binary',
                    'base64' => 'base64',
                    'quoted-printable' => 'quoted-printable'
                ],
                'choices_as_values' => true,
                'help' => $this->__f("Default: '%s'", ['%s' => '8bit'])
            ])
            ->add('html', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('HTML-formatted messages'),
                'data' => (bool) $this->dataValues['html'],
                'required' => false
            ])
            ->add('wordwrap', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $this->__('Word wrap'),
                'data' => $this->dataValues['wordwrap'],
                'empty_data' => 50,
                'scale' => 0,
                'max_length' => 3,
                'help' => $this->__f("Default: '%s'", ['%s' => '50'])
            ])
            ->add('enableLogging', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Enable logging of sent mail'),
                'data' => (bool) $this->dataValues['enableLogging'],
                'required' => false
            ])
            ->add('host', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('SMTP host server'),
                'data' => $this->dataValues['host'],
                'empty_data' => 'localhost',
                'max_length' => 255,
                'required' => false,
                'help' => $this->__f("Default: '%s'", ['%s' => 'localhost'])
            ])
            ->add('port', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $this->__('SMTP port'),
                'data' => $this->dataValues['port'],
                'empty_data' => 25,
                'scale' => 0,
                'max_length' => 5,
                'required' => false,
                'help' => $this->__f("Default: '%s'", ['%s' => '25'])
            ])
            ->add('encryption', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('SMTP encryption method'),
                'data' => $this->dataValues['encryption'],
                'empty_data' => '',
                'choices' => [
                    $this->__('None') => '',
                    'SSL' => 'ssl',
                    'TLS' => 'tls'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('auth_mode', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('SMTP authentication type'),
                'data' => $this->dataValues['auth_mode'],
                'empty_data' => '',
                'choices' => [
                    $this->__('None') => '',
                    'Plain' => 'plain',
                    'Login' => 'login',
                    'Cram-MD5' => 'cram-md5'
                ],
                'choices_as_values' => true,
                'required' => false
            ])
            ->add('username', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('SMTP user name'),
                'data' => $this->dataValues['username'],
                'empty_data' => '',
                'max_length' => 50,
                'required' => false
            ])
            ->add('password', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $this->__('SMTP password'),
                'data' => $this->dataValues['password'],
                'empty_data' => '',
                'max_length' => 50,
                'always_empty' => false,
                'required' => false
            ])
            ->add('usernameGmail', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Gmail user name'),
                'data' => $this->dataValues['usernameGmail'],
                'empty_data' => '',
                'max_length' => 50,
                'required' => false
            ])
            ->add('passwordGmail', 'Symfony\Component\Form\Extension\Core\Type\PasswordType', [
                'label' => $this->__('Gmail password'),
                'data' => $this->dataValues['passwordGmail'],
                'empty_data' => '',
                'max_length' => 50,
                'always_empty' => false,
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => $this->__('Save')])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => $this->__('Cancel')])
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
}
