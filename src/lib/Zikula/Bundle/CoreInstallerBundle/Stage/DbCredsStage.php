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
use Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType;
use Zikula\Component\Wizard\AbortStageException;
use Zikula\Component\Wizard\FormHandlerInterface;
use Zikula\Component\Wizard\InjectContainerInterface;
use Zikula\Component\Wizard\StageInterface;

class DbCredsStage implements StageInterface, FormHandlerInterface, InjectContainerInterface
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
        $this->yamlManager = new YamlDumper($this->container->get('kernel')->getRootDir() .'/config', 'custom_parameters.yml');
    }

    public function getName()
    {
        return 'dbcreds';
    }

    public function getFormType()
    {
        return new DbCredsType();
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:dbcreds.html.twig";
    }

    public function isNecessary()
    {
        $params = $this->yamlManager->getParameters();
        if (!empty($params['database_host']) && !empty($params['database_user']) && !empty($params['database_password']) && !empty($params['database_name'])) {
            // test the connection here.
            $test = $this->testDBConnection($params);
            if ($test !== true) {
                throw new AbortStageException($test);
            }

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
        $this->writeParams($form->getData());
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

    public function testDBConnection($params)
    {
        try {
            $dbh = new \PDO("$params[database_driver]:host=$params[database_host];dbname=$params[database_name]", $params['database_user'], $params['database_password']);
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        return true;
    }
}