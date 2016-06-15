<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;

class DefaultRegistrationType extends AbstractType
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * DefaultRegistrationType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_defaultregistration';
    }
}
