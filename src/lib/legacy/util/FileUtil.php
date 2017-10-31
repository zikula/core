<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * FileUtil class.
 * @deprecated remove at Core-2.0
 */
class FileUtil
{
    /**
     * Given a filename (complete with path) get the file basename.
     *
     * @param string  $filename The filename to process
     * @param boolean $keepDot  Whether or not to return the dot with the basename
     *
     * @return string The file's filename
     */
    public static function getFilebase($filename, $keepDot = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!$filename) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::getFilename', 'filename']));
        }

        $base = basename($filename);
        $p = strrpos($base, '.');
        if (false !== $p) {
            if ($keepDot) {
                return substr($base, 0, $p + 1);
            } else {
                return substr($base, 0, $p);
            }
        }

        return $filename;
    }

    /**
     * Get the basename of a filename.
     *
     * @param string $filename The filename to process
     *
     * @return string The file's basename
     */
    public static function getBasename($filename)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!$filename) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::getBasename', 'filename']));
        }

        return basename($filename);
    }

    /**
     * Get the file's extension.
     *
     * @param string  $filename The filename to process
     * @param boolean $keepDot  Whether or not to return the '.' with the extension
     *
     * @return string The file's extension
     */
    public static function getExtension($filename, $keepDot = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!$filename) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::getExtension', 'filename']));
        }

        $p = strrpos($filename, '.');
        if (false !== $p) {
            if ($keepDot) {
                return substr($filename, $p);
            } else {
                return substr($filename, $p + 1);
            }
        }

        return '';
    }

    /**
     * Strip the file's extension.
     *
     * @param string  $filename The filename to process
     * @param boolean $keepDot  Whether or not to return the '.' with the extension
     *
     * @return string The filename without the extension
     */
    public static function stripExtension($filename, $keepDot = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!$filename) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::stripExtension', 'filename']));
        }

        $p = strrpos($filename, '.');
        if (false !== $p) {
            if ($keepDot) {
                return substr($filename, 0, $p + 1);
            } else {
                return substr($filename, 0, $p);
            }
        }

        return $filename;
    }

    /**
     * Generate a random filename.
     *
     * @param integer $min        Minimum number of characters
     * @param integer $max        Maximum number of characters
     * @param boolean $useupper   Whether to use uppercase characters
     * @param boolean $usenumbers Whether to use numeric characters
     * @param boolean $usespecial Whether to use special characters
     *
     * @return string The generated filename extension
     */
    public static function generateRandomFilename($min, $max, $useupper = false, $usenumbers = true, $usespecial = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        $rnd = '';
        $chars = 'abcdefghijklmnopqrstuvwxyz';

        if ($useupper) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($usenumbers) {
            $chars .= '0123456789';
        }

        if ($usespecial) {
            $chars .= '~@#$%^*()_+-={}|][';
        }

        $charlen = strlen($chars) - 1;

        $len = mt_rand($min, $max);
        for ($i = 0; $i < $len; $i++) {
            $rnd .= $chars[mt_rand(0, $charlen)];
        }

        return $rnd;
    }

    /**
     * Generate a file/directory listing (can be recusive).
     *
     * @param string  $rootPath                 The root-path we wish to start at
     * @param boolean $recurse                  Whether or not to recurse directories (optional) (default=true)
     * @param boolean $relativePath             Whether or not to list relative (vs abolute) paths (optional) (default=true)
     * @param string  $extensions               The file extension or array of extensions to scan for (optional) (default=null)
     * @param string  $type                     The type of object (file or directory or both) to return (optional) (default=null)
     * @param boolean $nestedData               Whether or not to return a nested data set (optional) (default=false)
     * @param boolean $regexpMatch              The regular expression matching test to apply to filenames (optional) (default=null)
     * @param boolean $regexpMatchCaseSensitive Wether or not the $regexpMatch is to be applied case sensitive (optional) (default=true)
     *
     * @return array The array of files in the given path
     */
    public static function getFiles($rootPath, $recurse = true, $relativePath = true, $extensions = null, $type = null, $nestedData = false, $regexpMatch = null, $regexpMatchCaseSensitive = true)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        $files = [];
        $type  = strtolower($type);

        if ($type && 'd' != $type && 'f' != $type) {
            throw new \Exception(__f('Error! Invalid type of object [%s] received.', $type));
        }

        if (!is_dir($rootPath) || !is_readable($rootPath)) {
            return $files;
        }

        $skiplist = ['.', '..', 'CVS', '.svn', '_svn', 'index.html', '.htaccess', '.DS_Store', '-vti-cnf'];

        $el       = (is_string($extensions) ? strlen($extensions) : 0);
        $dh       = opendir($rootPath);
        $caseFlag = $regexpMatchCaseSensitive ? '' : 'i';
        while (false !== ($file = readdir($dh))) {
            $relativepath = $relativePath;
            if (!in_array($file, $skiplist)) {
                $path = $rootPath . DIRECTORY_SEPARATOR . $file;

                if ('f' == $type && !$recurse && is_dir($path)) {
                    continue;
                }

                if ('d' == $type && !is_dir($path)) {
                    continue;
                }

                $filenameToStore = $path;
                if ($relativePath) {
                    $filenameToStore = $file;
                    if (is_string($relativepath)) {
                        if (!$nestedData) {
                            $filenameToStore = $relativepath . DIRECTORY_SEPARATOR . $file;
                        }
                        $relativepath = $relativepath . DIRECTORY_SEPARATOR . $file;
                    } else {
                        $relativepath = $file;
                    }
                }

                if ($recurse && is_dir($path)) {
                    if ($nestedData) {
                        $files[$filenameToStore] = (array)self::getFiles($path, $recurse, $relativepath, $extensions, $type, $nestedData);
                    } else {
                        $files = array_merge((array)$files,
                                              (array)self::getFiles($path, $recurse, $relativepath, $extensions, $type, $nestedData));
                    }
                } elseif (!$extensions && !$regexpMatch) {
                    $files[] = $filenameToStore;
                } elseif (is_array($extensions)) {
                    foreach ($extensions as $extension) {
                        if (substr($file, -strlen($extension)) == $extension) {
                            if ($regexpMatch) {
                                if (preg_match("/$regexpMatch/$caseFlag", $file)) {
                                    $files[] = $filenameToStore;
                                    break;
                                }
                            } else {
                                $files[] = $filenameToStore;
                                break;
                            }
                        }
                    }
                } elseif (substr($file, -$el) == $extensions) {
                    if ($regexpMatch) {
                        if (preg_match("/$regexpMatch/$caseFlag", $file)) {
                            $files[] = $filenameToStore;
                        }
                    } else {
                        $files[] = $filenameToStore;
                    }
                }
            }
        }

        closedir($dh);
        if (!$nestedData) {
            sort($files);
        }

        return $files;
    }

    /**
     * Recursiveley create a directory path.
     *
     * @param string  $path     The path we wish to generate
     * @param string  $mode     The (UNIX) mode we wish to create the files with
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @deprecated since 1.3.0
     * @see    http://php.net/mkdir
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function mkdirs($path, $mode = 0777, $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (is_dir($path)) {
            return true;
        }

        $path = DataUtil::formatForOS($path, $absolute);

        // mkdir does not set chmod properly
        mkdir($path, $mode, true);
        $fs = new \Symfony\Component\Filesystem\Filesystem();
        $fs->chmod($path, $mode, 0000, true);
    }

    /**
     * Recursiveley delete given directory path.
     *
     * @param string  $path     The path/folder we wish to delete
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function deldir($path, $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        $path = DataUtil::formatForOS($path, $absolute);

        if ($dh = opendir($path)) {
            while (false !== ($file = readdir($dh))) {
                if (is_dir("$path/$file") && ('.' != $file && '..' != $file)) {
                    self::deldir("$path/$file", $absolute);
                } elseif ('.' != $file && '..' != $file) {
                    unlink("$path/$file");
                }
            }
            closedir($dh);
        }

        return rmdir($path);
    }

    /**
     * Read a file's contents and return them as a string. This method also opens and closes the file.
     *
     * @param string  $filename The file to read
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @return mixed The file's contents or FALSE on failure
     */
    public static function readFile($filename, $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!strlen($filename)) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::readFile', 'filename']));
        }

        $fName = DataUtil::formatForOS($filename, $absolute);

        return file_get_contents($fName);
    }

    /**
     * Read a file's contents and return them as an array of lines. This method also opens and closes the file.
     *
     * @param string  $filename The file to read
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @return mixed The file's contents as array or FALSE on failure
     */
    public static function readFileLines($filename, $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        $lines = false;
        if ($data = self::readFile($filename, $absolute)) {
            $lines = explode("\n", $data);
        }

        return $lines;
    }

    /**
     * Read a serialized's file's contents and return them as a string. This method also opens and closes the file.
     *
     * @param string  $filename The file to read
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @return mixed The file's contents or FALSE on failure
     */
    public static function readSerializedFile($filename, $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        return unserialize(self::readFile($filename, $absolute));
    }

    /**
     * Take an existing filename and 'randomize' it.
     *
     * @param string $filename The filename to randomize
     * @param string $dir      The directory the file should be in
     *
     * @return string The 'randomized' filename
     */
    public static function randomizeFilename($filename, $dir)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        $ext = '';
        $time = time();

        if (!$filename) {
            $filename = self::generateRandomFilename(10, 15, true, true);
        } elseif (false !== strrchr($filename, '.')) {
            // do we have an extension?
            $ext = self::getExtension($filename);
            $filename = self::stripExtension($filename);
        }

        if ($dir) {
            $dir .= '/';
        }

        if ($ext) {
            $rnd = $dir . $filename . '_' . $time . '.' . $ext;
        } else {
            $rnd = $dir . $filename . '_' . $time;
        }

        return $rnd;
    }

    /**
     * Write a string to a file. This method also opens and closes the file.
     *
     * On versions >= PHP5 this method will use the file_put_contents API.
     *
     * @param string $filename The file to write
     * @param string $data     The data to write to the file
     * @param string $absolute Allow absolute paths (default=false) (optional)
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function writeFile($filename, $data = '', $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!$filename) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::writeFile', 'filename']));
        }

        $fName = DataUtil::formatForOS($filename, $absolute);

        return file_put_contents($fName, $data);
    }

    /**
     * Write a serialized string to a file. This method also opens and closes the file.
     *
     * @param string  $filename The file to write
     * @param string  $data     The data to write to the file
     * @param boolean $absolute Allow absolute paths (default=false) (optional)
     *
     * @return boolean TRUE on success, FALSE on failure
     */
    public static function writeSerializedFile($filename, $data, $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        return self::writeFile($filename, serialize($data), $absolute);
    }

    /**
     * Upload a file.
     *
     * @param string  $key         The filename key to use in accessing the file data
     * @param string  $destination The destination where the file should end up
     * @param string  $newName     The new name to give the file (optional) (default='')
     * @param boolean $absolute    Allow absolute paths (default=false) (optional)
     *
     * @return mixed TRUE if success, a string with the error message on failure
     */
    public static function uploadFile($key, $destination, $newName = '', $absolute = false)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        if (!$key) {
            throw new \Exception(__f('%s: called with invalid %s.', ['FileUtil::uploadFile', 'key']));
        }

        if (!$destination) {
            throw new \Exception(__f('%s: called with invalid %s.', ['FileUtil::uploadFile', 'destination']));
        }

        $msg = '';
        if (!is_dir($destination) || !is_writable($destination)) {
            if (SecurityUtil::checkPermission('::', '::', ACCESS_ADMIN)) {
                $msg = __f('The destination path [%s] does not exist or is not writable', $destination);
            } else {
                $msg = __('The destination path does not exist or is not writable');
            }
        } elseif (isset($_FILES[$key]['name'])) {
            $uploadfile = $_FILES[$key]['tmp_name'];
            $origfile   = $_FILES[$key]['name'];

            if ($newName) {
                $uploaddest = DataUtil::formatForOS("$destination/$newName", $absolute);
            } else {
                $uploaddest = DataUtil::formatForOS("$destination/$origfile", $absolute);
            }

            $rc = move_uploaded_file($uploadfile, $uploaddest);

            if ($rc) {
                return true;
            } else {
                $msg = self::uploadErrorMsg($_FILES[$key]['error']);
            }
        }

        return $msg;
    }

    /**
     * Get the upload error message.
     *
     * @param integer $code Upload result code
     *
     * @return string Empty on success, error message string otherwise
     */
    public static function uploadErrorMsg($code)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        $msg = '';

        switch ($code) {
            case 1:
                $msg = __('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
                break;
            case 2:
                $msg = __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.');
                break;
            case 3:
                $msg = __('The uploaded file was only partially uploaded.');
                break;
            case 4:
                $msg = __('No file was uploaded.');
                break;
            case 5:
                $msg = __('Uploaded file size 0 bytes.');
                break;
        }

        return $msg;
    }

    /**
     * Export data to a csv file.
     *
     * @param array  $datarows  The data to write to the csv file
     * @param array  $titlerow  The titles to write to the csv file (default is empty array) (optional)
     * @param string $delimiter The character to use for field delimeter (default is character ,) (one character only) (optional)
     * @param string $enclosure The character to use for field enclosure (default is character ") (one character only) (optional)
     * @param string $filename  The filename of the exported csv file (default is null) (optional)
     *
     * @return nothing
     */
    public static function exportCSV($datarows, $titlerow = [], $delimiter = ',', $enclosure = '"', $filename = null)
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        // check if $datarows is array
        if (!is_array($datarows)) {
            throw new \Exception(__f('%1$s: %2$s is not an array', ['FileUtil::exportCSV', 'datarows']));
        }

        // check if $datarows is empty
        if (0 == count($datarows)) {
            throw new \Exception(__f('%1$s: %2$s is empty', ['FileUtil::exportCSV', 'datarows']));
        }

        // create random filename if none is given or else format it appropriately
        if (!isset($filename)) {
            $filename = 'csv_'.time().'.csv';
        } else {
            $filename = DataUtil::formatForOS($filename);
        }

        //disable compression and set headers
        ob_end_clean();
        ini_set('zlib.output_compression', 0);
        header('Cache-Control: no-store, no-cache');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Transfer-Encoding: binary');

        // open a file for csv writing
        $out = fopen("php://output", 'w');

        // write out title row if it exists
        if (isset($titlerow) && is_array($titlerow) && count($titlerow) > 0) {
            fputcsv($out, $titlerow, $delimiter, $enclosure);
        }

        // write out data
        foreach ($datarows as $datarow) {
            fputcsv($out, $datarow, $delimiter, $enclosure);
        }

        //close the out file
        fclose($out);

        exit;
    }

    /**
     * Get system data directory path.
     *
     * @return string The path to the data directory
     */
    public static function getDataDirectory()
    {
        @trigger_error('FileUtil is deprecated, please use Symfony filesystem and finder components instead.', E_USER_DEPRECATED);

        return DataUtil::formatForOS(System::getVar('datadir'));
    }
}
