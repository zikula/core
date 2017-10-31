<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreInstallerBundle\Stage\Install;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\FormInterface;
use Zikula\Bundle\CoreBundle\YamlDumper;
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
        return 'Zikula\Bundle\CoreInstallerBundle\Form\Type\DbCredsType';
    }

    public function getFormOptions()
    {
        return [
            'translator' => $this->container->get('translator.default')
        ];
    }

    public function getTemplateName()
    {
        return "ZikulaCoreInstallerBundle:Install:dbcreds.html.twig";
    }

    public function isNecessary()
    {
        $params = $this->yamlManager->getParameters();
        if (!empty($params['database_host']) && !empty($params['database_user']) && !empty($params['database_name'])) {
            // test the connection here.
            $test = $this->testDBConnection($params);
            if (true !== $test) {
                throw new AbortStageException($test);
            }

            return false;
        }

        return true;
    }

    public function getTemplateParams()
    {
        return [];
    }

    public function handleFormResult(FormInterface $form)
    {
        $data = $form->getData();
        $params = array_merge($this->yamlManager->getParameters(), $data);
        $dbh = new \PDO("$params[database_driver]:host=$params[database_host];dbname=$params[database_name]", $params['database_user'], $params['database_password']);
        $params['database_server_version'] = $dbh->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $params['database_driver'] = 'pdo_' . $params['database_driver']; // doctrine requires prefix in custom_parameters.yml
        $this->writeParams($params);
        $this->container->get('zikula_core_installer.config.helper')->writeLegacyConfig($params);
    }

    private function writeParams($params)
    {
        try {
            $this->yamlManager->setParameters($params);
        } catch (IOException $e) {
            throw new AbortStageException(sprintf('Cannot write parameters to %s file.', 'custom_parameters.yml'));
        }
        // clear the cache
        $this->container->get('zikula.cache_clearer')->clear('symfony.config');
    }

    public function testDBConnection($params)
    {
        $params['database_driver'] = substr($params['database_driver'], 4);
        try {
            $dbh = new \PDO("$params[database_driver]:host=$params[database_host];dbname=$params[database_name]", $params['database_user'], $params['database_password']);
        } catch (\PDOException $e) {
            return $e->getMessage();
        }

        return true;
    }
}
