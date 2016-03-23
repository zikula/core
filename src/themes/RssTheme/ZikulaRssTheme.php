<?php

namespace Zikula\RssTheme;

use Symfony\Component\HttpFoundation\Response;
use Zikula\Bundle\CoreBundle\Bundle\AbstractCoreTheme;

class ZikulaRssTheme extends AbstractCoreTheme
{
    /**
     * Override parent method in order to add Content-type header to Response
     * @param string $realm
     * @param Response $response
     * @param null $moduleName
     * @return mixed
     */
    public function generateThemedResponse($realm, Response $response, $moduleName = null)
    {
        $newResponse = new Response();
        $newResponse->headers->add(array("Content-type" => "application/rss+xml"));

        return $this->getContainer()->get('templating')->renderResponse('ZikulaAtomTheme::master.html.twig', array('maincontent' => $response->getContent()), $newResponse);
    }
}
