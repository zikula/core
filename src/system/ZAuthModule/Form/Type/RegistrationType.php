<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Validator\Constraints\ValidAntiSpamAnswer;

class RegistrationType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var array
     */
    private $modVars;

    /**
     * RegistrationType constructor.
     * @param TranslatorInterface $translator
     * @param VariableApi $variableApi
     */
    public function __construct(TranslatorInterface $translator, VariableApi $variableApi)
    {
        $this->translator = $translator;
        $this->modVars = $variableApi->getAll('ZikulaUsersModule');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', 'Zikula\UsersModule\Form\Type\UserType', [
                'translator' => $this->translator,
                'passwordReminderEnabled' => $options['passwordReminderEnabled'],
                'passwordReminderMandatory' => $options['passwordReminderMandatory']
            ])
            ->add('submit', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->translator->__('Save'),
                'icon' => 'fa-plus',
                'attr' => ['class' => 'btn btn-success']
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => ['class' => 'btn btn-danger']
            ])
            ->add('reset', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->translator->__('Reset'),
                'icon' => 'fa-refresh',
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;
        if (!$options['includeEmail']) {
            $builder->get('user')->remove('email');
        }
        if (!empty($options['antiSpamQuestion'])) {
            $builder->add('antispamanswer', 'Symfony\Component\Form\Extension\Core\Type\TextType', [
                'mapped' => false,
                'label' => $options['antiSpamQuestion'],
                'constraints' => new ValidAntiSpamAnswer(),
                'help' => $this->translator->__('Asking this question helps us prevent automated scripts from accessing private areas of the site.'),
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_registration';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'passwordReminderEnabled' => $this->modVars[UsersConstant::MODVAR_PASSWORD_REMINDER_ENABLED],
            'passwordReminderMandatory' => $this->modVars[UsersConstant::MODVAR_PASSWORD_REMINDER_MANDATORY],
            'antiSpamQuestion' => $this->modVars[UsersConstant::MODVAR_REGISTRATION_ANTISPAM_QUESTION],
            'includeEmail' => true,
        ]);
    }
}
