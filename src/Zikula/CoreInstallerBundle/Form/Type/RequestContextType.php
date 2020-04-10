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

namespace Zikula\Bundle\CoreInstallerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class RequestContextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('router:request_context:host', TextType::class, [
                'label' => 'The root domain where you install Zikula, e.g. "example.com". Do not include subdirectories.',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'data' => 'localhost',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('router:request_context:scheme', ChoiceType::class, [
                'label' => 'Please enter the scheme of where you install Zikula, can be either "http" or "https"',
                'label_attr' => [
                    'class' => 'col-md-3'
                ],
                'choices' => [
                    'http' => 'http',
                    'https' => 'https'
                ],
                'data' => 'http'
            ])
            ->add('router:request_context:base_url', TextType::class, [
                'label' => 'Please enter the url path of the directory where you install Zikula, leave empty if you install it at the top level. Example: /my/sub-dir',
                'label_attr' => [
                    'class' => 'col-md-3'
                ]
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'router_request_context';
    }
}
