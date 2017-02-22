<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\MenuModule\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\MenuModule\Entity\RepositoryInterface\MenuItemRepositoryInterface;
use Zikula\MenuModule\Form\DataTransformer\MenuItemEntityTransformer;

class HiddenMenuItemType extends AbstractType
{
    /**
     * @var MenuItemRepositoryInterface
     */
    private $repo;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * HiddenMenuItemType constructor.
     *
     * @param MenuItemRepositoryInterface $repo
     * @param TranslatorInterface $translator
     */
    public function __construct(MenuItemRepositoryInterface $repo, TranslatorInterface $translator)
    {
        $this->repo = $repo;
        $this->translator = $translator;
    }

    /**
* @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new MenuItemEntityTransformer($this->repo, $this->translator);
        $builder->addViewTransformer($transformer);
    }

    /**
* @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => $this->translator->__('The selected item does not exist')
        ]);
    }

    /**
* @inheritDoc
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
    }
}
