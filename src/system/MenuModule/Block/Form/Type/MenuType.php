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

namespace Zikula\MenuModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

class MenuType extends AbstractType
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
            ->add('name', TextType::class, [
                'label' => $this->__('Menu name'),
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('options', TextType::class, [
                'required' => false,
                'invalid_message' => $this->__('Could not json_decode the string you entered.'),
                'alert' => [$this->__('This must be a json_encoded string of option key-value pairs.') => 'warning']
            ])
        ;
        $builder->get('options')
            ->addModelTransformer(new CallbackTransformer(
                static function($text) {
                    return $text;
                },
                static function($text) {
                    if (empty($text)) {
                        return '{}';
                    }
                    $json = str_replace("'", '"', $text);
                    if (null === json_decode($json, true)) {
                        throw new TransformationFailedException($this->__('Could not json_decode the string you entered.'));
                    }

                    return $json;
                }
            ))
        ;
    }

    public function getBlockPrefix()
    {
        return 'zikulamenumodule_menu';
    }
}
