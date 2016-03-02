<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula_Form
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
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
