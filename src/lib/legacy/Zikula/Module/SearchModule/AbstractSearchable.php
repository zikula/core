<?php

namespace Zikula\Module\SearchModule;

use Zikula\SearchModule\AbstractSearchable as AbstractSearchableActual;

/**
 * @deprecated remove at Core-2.0
 * @see Zikula\SearchModule\AbstractSearchable
 *
 * This class is necessary because of the refactoring of the SearchModule to psr-4
 * This class maintains the 1.4.x BC API
 *
 * Class AbstractSearchable
 * @package Zikula\Module\SearchModule
 */
abstract class AbstractSearchable extends AbstractSearchableActual
{
}