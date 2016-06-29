<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Form\Type;

use ModUtil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Configuration form type class.
 */
class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $translator = $options['translator'];

        $builder
            ->add('itemsperpage', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Items per page'),
                'empty_data' => 10,
                'scale' => 0,
                'max_length' => 3,
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('limitsummary', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', [
                'label' => $translator->__('Number of characters to display in item summaries'),
                'empty_data' => 255,
                'scale' => 0,
                'max_length' => 5
            ])
            ->add('plugins', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', [
                'label' => $translator->__('Enabled plugins'),
                'empty_data' => [],
                'choices' => $this->getPluginChoices($options['plugins']),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => true
            ])
            ->add('opensearch_enabled', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('Enable OpenSearch'),
                'required' => false
            ])
            ->add('opensearch_adult_content', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $translator->__('This page contains adult content'),
                'required' => false
            ])
            ->add('save', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Save'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $translator->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
        ;
    }

    /**
     * Builds the choices for search plugin selection.
     *
     * @param array $plugins List of available plugins.
     *
     * @return array Plugin choices array.
     */
    private function getPluginChoices(array $plugins = [])
    {
        $pluginChoices = [];
        foreach ($plugins as $key => $plugin) {
            if (!isset($plugin['title'])) {
                continue;
            }
            $modid = ModUtil::getIdFromName($plugin['title']);
            $modinfo = ModUtil::getInfo($modid);

            $pluginChoices[$modinfo['displayname']] = $plugin['title'];
        }

        return $pluginChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'zikulasearchmodule_config';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translator' => null,
            'plugins' => []
        ]);
    }
}
