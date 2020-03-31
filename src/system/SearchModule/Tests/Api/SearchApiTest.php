<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Zikula\Bundle\CoreBundle\Doctrine\PaginatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SearchModule\Api\ApiInterface\SearchApiInterface;
use Zikula\SearchModule\Api\SearchApi;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Entity\RepositoryInterface\SearchStatRepositoryInterface;
use Zikula\SearchModule\Entity\SearchStatEntity;
use Zikula\SearchModule\Tests\Api\Fixtures\MockSearchResultRepository;
use Zikula\SearchModule\Tests\Api\Fixtures\MockSearchStatRepository;
use Zikula\SearchModule\Tests\Api\Fixtures\SearchableBar;
use Zikula\SearchModule\Tests\Api\Fixtures\SearchableFoo;

class SearchApiTest extends TestCase
{
    /**
     * @var SearchStatRepositoryInterface
     */
    private $searchStatRepo;

    protected function setUp(): void
    {
        $this->searchStatRepo = new MockSearchStatRepository();
    }

    public function testSearch(): void
    {
        $api = $this->getApi();
        $searchResult = $api->search('bar', true);
        $this->assertEquals(1, $searchResult['paginator']->getNumResults());
        $this->assertCount(0, $searchResult['errors']);
        /** @var \Doctrine\Common\Collections\ArrayCollection $results */
        $results = $searchResult['paginator']->getResults();
        $result = $results->get(0);
        $this->assertEquals('test', $result->getSesid());
        $this->assertEquals('ZikulaFooModule found using bar', $result->getText());
        $this->assertEquals('ZikulaFooModule result', $result->getTitle());

        $searchResult = $api->search('fee', true);
        $this->assertEquals(0, $searchResult['paginator']->getNumResults());
        $this->assertCount(0, $searchResult['errors']);

        $searchResult = $api->search('top bar fee', true);
        $this->assertEquals(2, $searchResult['paginator']->getNumResults());
        $this->assertCount(0, $searchResult['errors']);
        $results = $searchResult['paginator']->getResults();
        $result = $results->get(0);
        $this->assertEquals('test', $result->getSesid());
        $this->assertEquals('ZikulaFooModule found using top, bar, fee', $result->getText());
        $this->assertEquals('ZikulaFooModule result', $result->getTitle());
        $result = $results->get(1);
        $this->assertEquals('ZikulaBarModule found using top, bar, fee', $result->getText());
        $this->assertEquals('ZikulaBarModule result', $result->getTitle());

        $searchResult = $api->search('top bar fee', true, 'EXACT');
        $this->assertEquals(0, $searchResult['paginator']->getNumResults());
    }

    public function testLog(): void
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

    private function getApi(): SearchApiInterface
    {
        $variableApi = $this->getMockBuilder(VariableApiInterface::class)->getMock();
        $variableApi->method('get')->willReturnArgument(2);
        $searchResultRepo = new MockSearchResultRepository();
        $searchableModuleCollector = new SearchableModuleCollector([new SearchableFoo()]);
        $searchableModuleCollector->add(new SearchableBar());
        $storage = new MockArraySessionStorage();
        $session = new Session($storage, new AttributeBag(), new FlashBag());
        $session->setId('test');
        $session->start();

        return new SearchApi($variableApi, $searchResultRepo, $this->searchStatRepo, $session, $searchableModuleCollector);
    }
}
