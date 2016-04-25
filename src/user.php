<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Zikula_Request_Http as Request;

include 'lib/bootstrap.php';
$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$core->init(Zikula_Core::STAGE_ALL, $request);
$url = $core->getContainer()->get('router')->generate('zikulausersmodule_user_index', array(), \Symfony\Component\Routing\RouterInterface::ABSOLUTE_URL);
$url = str_replace('/user.php', '', $url);
$response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
$response->send();
