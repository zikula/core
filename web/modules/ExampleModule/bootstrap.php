<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

// Bootstrap - Used for global setup at module load time.
$helper = ServiceUtil::get('doctrine_extensions');
$helper->getListener('sluggable');
$helper->getListener('standardfields');
