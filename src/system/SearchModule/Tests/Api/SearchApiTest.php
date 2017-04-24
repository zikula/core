<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Tests\Api;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SearchModule\Api\ApiInterface\SearchApiInterface;
use Zikula\SearchModule\Api\SearchApi;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;
use Zikula\SearchModule\Tests\Api\Fixtures\MockSearchStatRepository;
use Zikula\SearchModule\Tests\Api\Fixtures\SearchableBar;
use Zikula\SearchModule\Tests\Api\Fixtures\SearchableFoo;
use Zikula\SearchModule\Tests\Api\Fixtures\MockSearchResultRepository;

class SearchApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchStatRepositoryInterface
     */
    private $searchStatRepo;

    /**
     * SearchApiTest setUp.
     */
    public function setUp()
    {
        $this->searchStatRepo = new MockSearchStatRepository();
    }

    public function testInstance()
    {
        $api = $this->getApi();
        $this->assertInstanceOf(SearchApiInterface::class, $api);
    }

    public function testSearch()
    {
        $api = $this->getApi();
        $searchResult = $api->search('bar', true);
        $this->assertEquals(1, $searchResult['resultCount']);
        $this->assertCount(1, $searchResult['sqlResult']);
        $this->assertCount(0, $searchResult['errors']);
        $this->assertEquals('test', $searchResult['sqlResult'][0]->getSesid());
        $this->assertEquals('ZikulaFooModule found using bar', $searchResult['sqlResult'][0]->getText());
        $this->assertEquals('ZikulaFooModule result', $searchResult['sqlResult'][0]->getTitle());

        $searchResult = $api->search('fee', true);
        $this->assertEquals(0, $searchResult['resultCount']);
        $this->assertCount(0, $searchResult['sqlResult']);
        $this->assertCount(0, $searchResult['errors']);

        $searchResult = $api->search('top bar fee', true);
        $this->assertEquals(2, $searchResult['resultCount']);
        $this->assertCount(2, $searchResult['sqlResult']);
        $this->assertCount(0, $searchResult['errors']);
        $this->assertEquals('test', $searchResult['sqlResult'][1]->getSesid());
        $this->assertEquals('ZikulaFooModule found using top, bar, fee', $searchResult['sqlResult'][1]->getText());
        $this->assertEquals('ZikulaFooModule result', $searchResult['sqlResult'][1]->getTitle());
        $this->assertEquals('ZikulaBarModule found using top, bar, fee', $searchResult['sqlResult'][2]->getText());
        $this->assertEquals('ZikulaBarModule result', $searchResult['sqlResult'][2]->getTitle());

        $searchResult = $api->search('top bar fee', true, 'EXACT');
        $this->assertEquals(0, $searchResult['resultCount']);
    }

    public function testLog()
    {
        $api = $this->getApi();
        $api->log('foo');
        $this->assertEquals(1, $this->searchStatRepo->countStats());
        $api->log('bar');
        $this->assertEquals(2, $this->searchStatRepo->countStats());
        $api->log('bar');
        $this->assertEquals(2, $this->searchStatRepo->countStats());
        $barLog = $this->searchStatRepo->findOneBy(['search' => 'bar']);
        $this->assertInstanceOf(SearchStatEntity::class, $barLog);
        $this->assertEquals(2, $barLog->getCount());
        $fooLog = $this->searchStatRepo->findOneBy(['search' => 'foo']);
        $this->assertInstanceOf(SearchStatEntity::class, $fooLog);
        $this->assertEquals(1, $fooLog->getCount());
        $api->log('foo');
        $api->log('foo');
        $fooLog = $this->searchStatRepo->findOneBy(['search' => 'foo']);
        $this->assertEquals(3, $fooLog->getCount());
        $this->assertEquals(2, $this->searchStatRepo->countStats());
    }

    private function getApi()
    {
        $variableApi = $this->getMockBuilder(VariableApiInterface::class)->getMock();
        $variableApi->method('get')->willReturnArgument(2);
        $searchResultRepo = new MockSearchResultRepository();
        $searchableModuleCollector = new SearchableModuleCollector();
        $searchableModuleCollector->add('ZikulaFooModule', new SearchableFoo());
        $searchableModuleCollector->add('ZikulaBarModule', new SearchableBar());
        $storage = new MockArraySessionStorage();
        $session = new Session($storage, new AttributeBag(), new FlashBag());
        $session->setId('test');
        $session->start();

        return new SearchApi($variableApi, $searchResultRepo, $this->searchStatRepo, $session, $searchableModuleCollector);
    }
}
