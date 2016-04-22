<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container = \ServiceUtil::getManager();
$twig = $container->get('twig');
if (!$twig->hasExtension('Text')) {
    $twig->addExtension(new Twig_Extensions_Extension_Text());
}
if (!$twig->hasExtension('Intl')) {
    $twig->addExtension(new Twig_Extensions_Extension_Intl());
}
