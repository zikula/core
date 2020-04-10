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

namespace Zikula\UsersModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Form\DataTransformer\UserFieldTransformer;

/**
 * Form type providing auto completion based search for user names.
 */
class UserLiveSearchType extends AbstractType
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new UserFieldTransformer($this->userRepository);
        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['inline_usage'] = $options['inline_usage'];

        $fieldName = $form->getConfig()->getName();
        $parentData = $form->getParent()->getData();
        $accessor = PropertyAccess::createPropertyAccessor();
        $fieldNameGetter = 'get' . ucfirst($fieldName);
        $user = null !== $parentData && method_exists($parentData, $fieldNameGetter) ? $accessor->getValue($parentData, $fieldNameGetter) : null;

        $view->vars['user_name'] = null !== $user && is_object($user) ? $user->getUname() : '';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'inline_usage' => false
            ])
            ->setAllowedTypes('inline_usage', 'bool')
        ;
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function getBlockPrefix()
    {
        return 'zikulausersmodule_userlivesearch';
    }
}
