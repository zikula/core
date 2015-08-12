<?php
/**
 * Created by PhpStorm.
 * User: craig
 * Date: 8/11/15
 * Time: 7:13 PM
 */

namespace Zikula\Core\Theme\Asset;


interface ResolverInterface
{
    public function compile();
    public function getBag();
}