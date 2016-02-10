<?php

namespace Zikula\ThemeModule\Engine\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * Class Theme
 * @package Zikula\ThemeModule\Engine\Annotation
 *
 * This annotation is used in a Controller Action Method.
 *  like so: @Theme('admin')
 * Possible values are:
 *  - 'admin'
 *  - 'print'
 *  - 'atom'
 *  - 'rss'
 *  - any valid theme name (e.g. 'ZikulaAndreas08Theme')
 * @see \Zikula\ThemeModule\Engine\Engine::changeThemeByAnnotation
 */
class Theme extends Annotation
{
}
