<?php
/**
 * Zikula Application Framework
 *
 * @copyright (c) 2002, Zikula Development Team
 * @link http://www.zikula.org
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_System_Modules
 * @subpackage SysInfo
 * @license http://www.gnu.org/copyleft/gpl.html
 */

class SysInfo_Api_Admin extends AbstractApi
{
    /**
     * get available admin panel links
     *
     * @return array array of admin links
     */
    public function getlinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'main'), 'text' => $this->__('System summary'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'phpinfo', array('info' => 4)), 'text' => $this->__('PHP configuration'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'phpinfo', array('info' => 8)), 'text' => $this->__('PHP modules'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'phpinfo', array('info' => 16)), 'text' => $this->__('Server environment'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'phpinfo', array('info' => 32)), 'text' => $this->__('PHP variables'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'extensions'), 'text' => $this->__('Zikula extensions'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'filesystem'), 'text' => $this->__('Zikula file system'));
        }
        if (SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            $links[] = array('url' => ModUtil::url('SysInfo', 'admin', 'ztemp'), 'text' => $this->__('Zikula ztemp directory'));
        }

        return $links;
    }

    /**
     * Get a list of all files and folders within the filesystem
     * @return string HTML output string
     */
    public function filelist ($args)
    {
        if (!isset($args['startdir'])) {
            $args['startdir'] = './';
        }
        if (!isset($args['searchSubdirs']) || !is_numeric($args['searchSubdirs'])) {
            $args['searchSubdirs'] = 1;
        }
        if (!isset($args['directoriesonly']) || !is_numeric($args['directoriesonly'])) {
            $args['directoriesonly'] = 0;
        }
        if (!isset($args['maxlevel'])) {
            $args['maxlevel'] = 'all';
        }
        if (!isset($args['level']) || !is_numeric($args['level'])) {
            $args['level'] = 1;
        }
        if (!isset($args['ztemp']) || !is_numeric($args['ztemp'])) {
            $args['ztemp'] = 0;
        }

        if (!SecurityUtil::checkPermission('SysInfo::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $startdir = $args['startdir'];
        $searchSubdirs = $args['searchSubdirs'];
        $directoriesonly = $args['directoriesonly'];
        $maxlevel = $args['maxlevel'];
        $level = $args['level'];
        $ztemp = $args['ztemp'];

        // this process could take a while....
        @set_time_limit(0);
        @ini_set('memory_limit', '128M');

        $ignoredDirectory[] = '.';
        $ignoredDirectory[] = '..';
        $ignoredDirectory[] = '.svn';
        $ztemp = DataUtil::formatForOS(CacheUtil::getLocalDir(),true);
        if ($ztemp == 0) {
            $ignoredDirectory[] = $ztemp;
        }

        global $directorylist;
        if (is_dir($startdir)) {
            if ($dh = @opendir($startdir)) {
                while (($file = readdir($dh)) !== false) {
                    if (!(array_search($file,$ignoredDirectory) > -1)) {
                        if (filetype($startdir . $file) == 'dir') {
                            $directorylist[$startdir . $file]['dir'] = $this->__('Folder');
                            $directorylist[$startdir . $file]['path'] = $startdir;
                            $directorylist[$startdir . $file]['name'] = $file;
                            $directorylist[$startdir . $file]['writable'] = (bool)is_writable($startdir . $file);
                            if ($searchSubdirs) {
                                if ((($maxlevel) == 'all') or ($maxlevel > $level)) {
                                    ModUtil::apiFunc('SysInfo', 'admin', 'filelist',
                                            array ('startdir' => $startdir . $file . '/',
                                            'searchSubdirs' => $searchSubdirs,
                                            'directoriesonly' => $directoriesonly,
                                            'maxlevel' => $maxlevel,
                                            'level' => $level + 1));
                                }
                            }
                        } else {
                            if (!$directoriesonly) {
                                $directorylist[$startdir . $file]['dir'] = $this->__('File');
                                $directorylist[$startdir . $file]['path'] = $startdir;
                                $directorylist[$startdir . $file]['name'] = $file;
                                $directorylist[$startdir . $file]['writable'] = (bool)is_writable($startdir . $file);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
        return($directorylist);
    }
}