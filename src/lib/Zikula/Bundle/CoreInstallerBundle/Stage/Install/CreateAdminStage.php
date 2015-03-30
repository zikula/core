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

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
use Zikula\Bundle\CoreInstallerBundle\Form\Type\CreateAdminType;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class CreateAdminStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
{
    private $yamlManager;
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function getName()
    {
        return 'createadmin';
    }

    public function getFormType()
    {
        return new CreateAdminType();
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:createadmin.html.twig";
    }

    public function isNecessary()
    {
        $params = $this->yamlManager->getParameters();
        if (!empty($params['username']) && !empty($params['password']) && !empty($params['email'])) {

            return false;
        }

        return true;
    }

    public function getTemplateParams()
    {
        return array();
    }

    public function handleFormResult(FormInterface $form)
    {
        $data = $form->getData();
        foreach ($data as $k => $v) {
            $data[$k] = base64_encode($v); // encode so values are 'safe' for json
        }
        $this->writeParams($data);
    }

    private function writeParams($data)
    {
        $params = array_merge($this->yamlManager->getParameters(), $data);
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException(__f('Cannot write parameters to %s file.', 'custom_parameters.yml'));
        }
    }
}