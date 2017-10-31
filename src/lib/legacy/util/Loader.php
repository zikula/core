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
 * Loader class
 *
 * @deprecated remove at Core-2.0
 */
class Loader
{
    /**
     * Load a file from the specified location in the file tree
     *
     * @param fileName    The name of the file to load
     * @param path        The path prefix to use (optional) (default=null)
     * @param exitOnError whether or not exit upon error (optional) (default=true)
     * @param returnVar   The variable to return from the sourced file (optional) (default=null)
     *
     * @return string The file which was loaded
     */
    public static function loadFile($fileName, $path = null, $exitOnError = true, $returnVar = null)
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        if (!$fileName) {
            throw new \Exception(__f("Error! Invalid file specification '%s'.", $fileName));
        }

        $file = null;
        if ($path) {
            $file = "$path/$fileName";
        } else {
            $file = $fileName;
        }

        $file = DataUtil::formatForOS($file);

        if (is_file($file) && is_readable($file)) {
            if (include_once($file)) {
                if ($returnVar) {
                    return $$returnVar;
                } else {
                    return $file;
                }
            }
        }

        if ($exitOnError) {
            throw new \Exception(__f("Error! Could not load the file '%s'.", $fileName));
        }

        return false;
    }

    /**
     * Load all files from the specified location in the pn file tree
     *
     * @param files        An array of filenames to load
     * @param path         The path prefix to use (optional) (default='null')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return boolean true
     */
    public static function loadAllFiles($files, $path = null, $exitOnError = false)
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        return self::loadFiles($files, $path, true, $exitOnError);
    }

    /**
     * Return after the first successful file load. This corresponds to the
     * default behaviour of loadFiles().
     *
     * @param files        An array of filenames to load
     * @param path         The path prefix to use (optional) (default='null')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @return boolean true
     */
    public static function loadOneFile($files, $path = null, $exitOnError = false)
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        return self::loadFiles($files, $path, false, $exitOnError);
    }

    /**
     * Load multiple files from the specified location in the pn file tree
     * Note that in it's default invokation, this method exits after the
     * first successful file load.
     *
     * @param files       Array of filenames to load
     * @param path        The path prefix to use (optional) (default='null')
     * @param all         whether or not to load all files or exit upon 1st successful load (optional) (default=false)
     * @param exitOnError whether or not exit upon error (optional) (default=true)
     * @param returnVar   The variable to return if $all==false (optional) (default=null)
     *
     * @return boolean true
     */
    public static function loadFiles($files, $path = null, $all = false, $exitOnError = false, $returnVar = '')
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        if (!is_array($files) || !$files) {
            throw new \Exception(__('Error! Invalid file array specification.'));
        }

        $files = array_unique($files);

        $loaded = false;
        foreach ($files as $file) {
            $rc = self::loadFile($file, $path, $exitOnError, $returnVar);

            if ($rc) {
                $loaded = true;
            }

            if ($loaded && !$all) {
                break;
            }
        }

        if ($returnVar && !$all) {
            return $rc;
        }

        return $loaded;
    }

    /**
     * Load a class file from the specified location in the file tree
     *
     * @param className    The class-basename to load
     * @param classPath    The path prefix to use (optional) (default='lib')
     * @param exitOnError  whether or not exit upon error (optional) (default=true)
     *
     * @deprecated since 1.3.0
     *
     * @return string The file name which was loaded
     */
    public static function loadClass($className, $classPath = 'lib', $exitOnError = true)
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', [__CLASS__ . '#' . __FUNCTION__, 'autoloading'], E_USER_DEPRECATED));
        if (!$className) {
            throw new \Exception(__f("Error! Invalid class specification '%s'.", $className));
        }

        if (class_exists($className)) {
            return $className;
        }

        $classFile = $className . '.class.php';
        $rc = self::loadFile($classFile, "config/classes/$classPath", false);
        if (!$rc) {
            $rc = self::loadFile($classFile, $classPath, $exitOnError);
        }

        return $rc;
    }

    /**
     * Load a DBObject extended class from the given module. The given class name is
     * prefixed with 'PN' and underscores are removed to produce a proper class name.
     *
     * @param module        The module to load from
     * @param base_obj_type The base object type for which to load the class
     * @param array         If true, load the array class instead of the single-object class
     * @param exitOnError   whether or not exit upon error (optional) (default=true)
     * @param prefix        Override parameter for the default PN prefix (default=PN)
     *
     * @deprecated since 1.3.0
     *
     * @return string The ClassName which was loaded from the file
     */
    public static function loadClassFromModule($module, $base_obj_type, $array = false, $exitOnError = false, $prefix = 'PN')
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', [__CLASS__ . '#' . __FUNCTION__, 'autoloading'], E_USER_DEPRECATED));
        if (!$module) {
            throw new \Exception(__f("Error! Invalid module specification '%s'.", $module));
        }

        if (!$base_obj_type) {
            throw new \Exception(__f("Error! Invalid 'base_obj_type' specification '%s'.", $base_obj_type));
        }

        $prefix = (string) $prefix;

        if (false !== strpos($base_obj_type, '_')) {
            $c = $base_obj_type;
            $class = '';
            while (false !== ($p = strpos($c, '_'))) {
                $class .= ucwords(substr($c, 0, $p));
                $c = substr($c, $p + 1);
            }
            $class .= ucwords($c);
        } else {
            $class = ucwords($base_obj_type);
        }

        $class = $prefix . $class;
        if ($array) {
            $class .= 'Array';
        }

        // prevent unncessary reloading
        if (class_exists($class)) {
            return $class;
        }

        $classFiles = [];
        $classFiles[] = 'config/classes/' . $module . '/' . $class . '.class.php';
        $classFiles[] = 'system/' . $module . '/classes/' . $class . '.class.php';
        $classFiles[] = 'modules/' . $module . '/classes/' . $class . '.class.php';

        foreach ($classFiles as $classFile) {
            $classFile = DataUtil::formatForOS($classFile);
            if (is_readable($classFile)) {
                if (self::includeOnce($classFile)) {
                    return $class;
                }

                if ($exitOnError) {
                    throw new \Exception(__f('Error! Unable to load class [%s]', $classFile));
                }

                return false;
            }
        }

        return false;
    }

    /**
     * Load a PNObjectArray extended class from the given module. The given class name is
     * prefixed with 'PN' and underscores are removed to produce a proper class name.
     *
     * @param module        The module to load from
     * @param base_obj_type The base object type for which to load the class
     * @param exitOnError   whether or not exit upon error (optional) (default=true)
     * @param prefix        Override parameter for the default PN prefix (default=PN)
     *
     * @return string The ClassName which was loaded from the file
     */
    public static function loadArrayClassFromModule($module, $base_obj_type, $exitOnError = false, $prefix = 'PN')
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use %2$s instead.', [__CLASS__ . '#' . __FUNCTION__, 'autoloading'], E_USER_DEPRECATED));

        return self::loadClassFromModule($module, $base_obj_type, true, $exitOnError, $prefix);
    }

    /**
     * Internal include_once
     *
     * @deprecated since 1.3.0
     * @return bool True if file was included - false if not found or included before
     */
    public static function includeOnce($file)
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use autoloading or only if absolutely necessary, %2$s instead.', [__CLASS__ . '#' . __FUNCTION__, 'include_once'], E_USER_DEPRECATED));
        if (0 === strpos($file, 'includes/')) {
            return true;
        }

        return include_once $file;
    }

    /**
     * Internal require_once
     *
     * @deprecated since 1.3.0
     * @param  string $file
     * @return bool
     */
    public static function requireOnce($file)
    {
        @trigger_error('Loader is deprecated. please use Composer and namespaces instead.', E_USER_DEPRECATED);

        LogUtil::log(__f('Warning! Function %1$s is deprecated. Please use autoloading or only if absolutely necessary, %2$s instead.', [__CLASS__ . '#' . __FUNCTION__, 'require_once'], E_USER_DEPRECATED));
        if (0 === strpos($file, 'includes/')) {
            return true;
        }

        return require_once $file;
    }
}
