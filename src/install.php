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

ini_set('max_execution_time', 86400);
ini_set('memory_limit', '64M');

include 'lib/bootstrap.php';
include 'install/lib.php';
install($core);
