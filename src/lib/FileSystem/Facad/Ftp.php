<?php
/**
 * Copyright 2009-2010 Zikula Foundation - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv2 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * FileSystem_Facad_Ftp is a facad interface for FTP connections.
 */
class FileSystem_Facad_Ftp
{
    public static function put($ftp_stream, $remote_file, $local_file, $mode, $startpos=0)
    {
        return ftp_put($ftp_stream, $remote_file, $local_file, $mode, $startpos);
    }
}
