<?php

namespace Zikula\Bundle\CoreBundle\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ZikulaVersionDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = array(
            'version' => \Zikula_Core::VERSION_NUM,
            'ghZikulaCoreUrl' => 'https://www.github.com/zikula/core',
            'ghZikulaDocsUrl' => 'https://www.github.com/zikula/zikula-docs',
            'ghZikulaBootstrapDocsUrl' => 'http://zikula.github.io/bootstrap-docs',
        );
    }

    public function getVersion()
    {
        return $this->data['version'];
    }

    public function getGhZikulaCoreUrl()
    {
        return $this->data['ghZikulaCoreUrl'];
    }

    public function getGhZikulaDocsUrl()
    {
        return $this->data['ghZikulaDocsUrl'];
    }

    public function getGhZikulaBootstrapDocsUrl()
    {
        return $this->data['ghZikulaBootstrapDocsUrl'];
    }

    public function getName()
    {
        return 'zikula_version';
    }
}
