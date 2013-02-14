<?php

namespace Zikula\Bundle\CoreBundle\Tests\Twig;

use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;

class IntegrationTest extends \Twig_Test_IntegrationTestCase
{
    public function getExtensions()
    {
        return array(new CoreExtension());
    }

    public function getFixturesDir()
    {
        return dirname(__FILE__).'/Fixtures/';
    }
}
