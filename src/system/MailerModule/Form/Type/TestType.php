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
class TestType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var array
     */
    protected $dataValues;

    /**
     * TestType constructor.
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
        $this->dataValues['sitename'] = $variableApi->get('ZConfig', 'sitename_' . \ZLanguage::getLanguageCode(), $variableApi->get('ZConfig', 'sitename_en'));
        $this->dataValues['adminmail'] = $variableApi->get('ZConfig', 'adminmail');
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
            ->add('fromName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Sender\'s name'),
                'data' => $this->dataValues['sitename'],
                'disabled' => true
            ])
            ->add('fromAddress', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $this->__('Sender\'s e-mail address'),
                'data' => $this->dataValues['adminmail'],
                'disabled' => true
            ])
            ->add('toName', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Recipient\'s name'),
                'data' => '',
                'max_length' => 50
            ])
            ->add('toAddress', 'Symfony\Component\Form\Extension\Core\Type\EmailType', [
                'label' => $this->__('Recipient\'s e-mail address'),
                'data' => '',
                'max_length' => 50
            ])
            ->add('subject', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'label' => $this->__('Subject'),
                'data' => '',
                'max_length' => 50
            ])
            ->add('messageType', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $this->__('Message type'),
                'data' => ($this->dataValues['html'] ? 'html' : 'text'),
                'empty_data' => 'text',
                'choices' => [
                    'Plain-text message' => 'text',
                    'HTML-formatted message' => 'html',
                    'Multi-part message' => 'multipart'
                ],
                'choices_as_values' => true,
                'expanded' => false
            ])
            ->add('bodyHtml', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $this->__('HTML-formatted message'),
                'data' => '',
                'required' => false
            ])
            ->add('bodyText', 'Symfony\Component\Form\Extension\Core\Type\TextareaType', [
                'label' => $this->__('Plain-text message'),
                'data' => '',
                'required' => false
            ])
            ->add('test', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => $this->__('Send test email')])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', ['label' => $this->__('Cancel')])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulamailermodule_test';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
