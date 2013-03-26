<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Installer
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Zikula_Request_Http as Request;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;

ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');
mb_regex_encoding('UTF-8');
ini_set('memory_limit', '64M');
ini_set('max_execution_time', 86400);

include 'lib/bootstrap.php';
$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$container = $core->getContainer();
$dbname = $container['databases']['default']['dbname'];
$dbname = 'zikula135';
$ed = $core->getDispatcher();
$ed->dispatch('doctrine.boot', new \Zikula\Core\Event\GenericEvent());
/** @var $em EntityManager */
$em = $core->getContainer()->get('doctrine.entitymanager');
$conn = $em->getConnection();
$modules = array(
    'Admin', 'Blocks', 'Categories', 'Errors', 'Extensions', 'Groups',
    'Mailer', 'PageLock', 'Permissions', 'Search', 'SecurityCenter',
    'Settings', 'Theme', 'Users',
);

foreach ($modules as $module) {
    $conn->executeQuery("UPDATE $dbname.modules SET name = 'Zikula{$module}Module', directory = 'Zikula/Module/{$module}Module' WHERE name = '$module'");
    $conn->executeQuery("UPDATE $dbname.module_vars SET modname = 'Zikula{$module}Module' WHERE modname = '$module'");
    echo "Updated module: $module<br />\n";
}
echo "<br />\n";

$themes = array(
    'Andreas08', 'Atom', 'SeaBreeze', 'Mobile', 'Printer',
);
foreach ($themes as $theme) {
    $conn->executeQuery("UPDATE $dbname.themes SET name = 'Zikula{$theme}Theme', directory = 'Zikula/Theme/{$theme}Theme' WHERE name = '$theme'");
    echo "Updated theme: $theme<br />\n";
}
$conn->executeQuery("UPDATE $dbname.themes SET name = 'ZikulaRssTheme', directory = 'Zikula/Theme/RssTheme' WHERE name = 'RSS'");
echo "Updated theme: RSS<br />\n";

//$conn->executeQuery("UPDATE $dbname.module_vars SET value = 'ZikulaAndreas08Theme' WHERE modname = 'ZConfig' AND value='Default_Theme'");
//echo "Updated defualt theme to Andreas08<br />\n";

echo "<br /><br />Upgrade complete, please run normal upgrade.php now and delete this file.<br />\n";

