<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula CoreInstaller bundle.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\LangType;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class LangStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    /**
     * @var YamlDumper
     */
    private $yamlManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml', 'parameters.yml');
    }

    public function getName()
    {
        return 'lang';
    }

    public function getFormType()
    {
        return new LangType();
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:lang.html.twig";
    }

    public function isNecessary()
    {
        $currentLangParameter = $this->yamlManager->getParameter('lang');
        if (!empty($currentLangParameter)) {

            return false;
        }
        $installedLanguages = \ZLanguage::getInstalledLanguages();
        if (count($installedLanguages) == 1) {
            $this->writeParams(array('lang' => $installedLanguages[0]));

            return false;
        } else {
            // see if the browser has a preference set
            $detector = new \ZLanguageBrowser($installedLanguages);
            $lang = $detector->discover();
            if ($lang !== false) {
                $this->writeParams(array('lang' => $lang));

                return false;
            } else {

                return true;
            }
        }
    }

    public function getTemplateParams()
    {
        return array();
    }

    public function handleFormResult(FormInterface $form)
    {
        $data = $form->getData();
        $this->writeParams($data);
    }

    private function writeParams($data = array())
    {
        $params = array_merge($this->yamlManager->getParameters(), $data);
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException(__f('Cannot write parameters to %s file.', 'custom_parameters.yml'));
        }
        // setup multilingual
        $this->container->setParameter('language_i18n', $data['lang']);
        $this->container->setParameter('multilingual', true);
        $this->container->setParameter('languageurl', true);
        $this->container->setParameter('language_detect', false);

//        $_lang = ZLanguage::getInstance();
//        $_lang->setup($request);
    }
}