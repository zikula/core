<?php

namespace Zikula\Bundle\CoreBundle\Tests\Twig;

use Zikula\Bundle\CoreBundle\Twig\Test\IntegrationTestCase;
use Zikula\Bundle\CoreBundle\Twig\Extension\CoreExtension;

class IntegrationTest extends IntegrationTestCase
{
    public function getExtensions()
    {
        return array(new CoreExtension());
    }

    public function getTests()
    {
        return $this->getFixtures(dirname(__FILE__).'/Fixtures/');
    }

    /**
     * @dataProvider getTests
     */
    public function testIntegration($file, $message, $condition, $templates, $exception, $outputs)
    {
        $this->doIntegrationTest($file, $message, $condition, $templates, $exception, $outputs);
    }
}
