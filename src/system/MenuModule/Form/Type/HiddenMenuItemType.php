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

namespace Zikula\MenuModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;
use Zikula\MenuModule\Form\DataTransformer\MenuItemEntityTransformer;

class HiddenMenuItemType extends AbstractType
{
    use TranslatorTrait;

    /**
     * @var MenuItemRepositoryInterface
     */
    private $repository;

    public function __construct(
        TranslatorInterface $translator,
        MenuItemRepositoryInterface $repository
    ) {
        $this->setTranslator($translator);
        $this->repository = $repository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new MenuItemEntityTransformer($this->repository, $this->translator);
        $builder->addViewTransformer($transformer);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => $this->trans('The selected item does not exist')
        ]);
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
