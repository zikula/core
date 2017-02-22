<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ExtensionsModule\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExtensionVarRepositoryFunctionalTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
    }

    public function testFindAll()
    {
        $entities = $this->em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findAll()
        ;
        $this->assertInternalType('array', $entities);
        $this->assertTrue(count($entities) > 0);
    }

    public function testFindBy()
    {
        $entity = $this->em
            ->getRepository('ZikulaExtensionsModule:ExtensionVarEntity')
            ->findBy(['modname' => 'ZikulaExtensionsModule', 'name' => 'itemsperpage']) // set in installer
        ;
        /** @var \Zikula\ExtensionsModule\Entity\ExtensionVarEntity $entity */
        $this->assertEquals(40, $entity[0]->getValue(), "Expected value is 25 based on ZikulaExtensionsModule::install().");
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        parent::tearDown();
//        $this->em->close();
    }
}
