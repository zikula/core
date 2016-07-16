<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Block\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;

/**
 * Class SearchBlockType
 */
class SearchBlockType extends AbstractType
{
    use TranslatorTrait;

    /**
     * SearchBlockType constructor.
     *
     * @param TranslatorInterface $translator Translator service instance
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->setTranslator($translator);
    }

    /**
     * Sets the translator.
     *
     * @param TranslatorInterface $translator Translator service instance
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
        $builder->add('displaySearchBtn', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
            'label' => $this->__('Show \'Search now\' button'),
            'required' => false
        ]);

        /**
         * TODO: reenable custom form options from other modules
         * $searchModules = [];
         * if (is_array($options['plugins']) && count($options['plugins'])) {
         *     foreach ($options['plugins'] as $module) {
         *         $searchModules[] = [
         *             'module' => ModUtil::apiFunc($module['title'], 'search', 'options', $vars)
         *         ];
         *     }
         * }
         * // add field array with name "active"
         */
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'zikulasearchmodule_searchblock';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'plugins' => []
        ]);
    }
}
