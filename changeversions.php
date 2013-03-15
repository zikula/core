#!/Applications/MAMP/bin/php/php5.3.6/bin/php
<?php

use Zikula_Request_Http as Request;


include 'src/lib/bootstrap.php';
$request = Request::createFromGlobals();
$core->getContainer()->set('request', $request);
$core->init();

if ($core->hasBooted()) {
    echo "CORE HAS BOOTED\n";
}

if (System::getVar('installed')) {
    echo "system installed\n";
}

echo "PHP IS HERE!\n";
?>
