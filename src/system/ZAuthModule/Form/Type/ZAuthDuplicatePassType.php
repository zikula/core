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

namespace Zikula\ZAuthModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ZAuthModule\Validator\Constraints\ValidPassword;
use Zikula\ZAuthModule\ZAuthConstant;

class ZAuthDuplicatePassType extends AbstractType
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    public function __construct(VariableApiInterface $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pass', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => [
                        'class' => 'pwstrength',
                        'data-uname-id' => $options['dataUnameId'],
                        'minlength' => $options['minimumPasswordLength'],
                        'pattern' => '.{' . $options['minimumPasswordLength'] . ',}'
                    ],
                    'required' => false,
                    'label' => 'Create new password',
                    'input_group' => ['left' => '<i class="fas fa-asterisk"></i>'],
                    'help' => 'Minimum password length: %amount% characters.',
                    'help_translation_parameters' => [
                        '%amount%' => $options['minimumPasswordLength']
                    ]
                ],
                'second_options' => [
                    'required' => false,
                    'label' => 'Repeat new password',
                    'input_group' => ['left' => '<i class="fas fa-asterisk"></i>']
                ],
                'invalid_message' => 'The passwords must match!',
                'constraints' => [
                    new ValidPassword()
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulazauthmodule_zauthcustompass';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'inherit_data' => true,
            'dataUnameId' => '',
            'minimumPasswordLength' => $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_PASSWORD_MINIMUM_LENGTH, ZAuthConstant::PASSWORD_MINIMUM_LENGTH),
        ]);
    }
}
