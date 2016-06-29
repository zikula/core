<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Twig;

/**
 * Twig extension base class.
 */
class TwigExtension extends \Twig_Extension
{
    /**
     * Returns a list of custom Twig filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('zikulagroupsmodule_profileLink', [$this, 'profileLink'])
        ];
    }

    /**
     * Returns a link to the user's profile.
     *
     * @param int     $uid       The user's id (optional).
     * @param string  $class     The class name for the link (optional).
     * @param integer $maxLength If set then user names are truncated to x chars.
     *
     * @return string
     */
    public function profileLink($uid, $class = '', $maxLength = 0)
    {
        $result = '';
        $image = '';

        if ($uid == '') {
            return $result;
        }

        if (\ModUtil::getVar('ZConfig', 'profilemodule') != '') {
            include_once 'lib/legacy/viewplugins/modifier.profilelinkbyuid.php';
            $result = smarty_modifier_profilelinkbyuid($uid, $class, $image, $maxLength);
        } else {
            $result = \UserUtil::getVar('uname', $uid);
        }

        return $result;
    }

    /**
     * Returns internal name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'zikulagroupsmodule_twigextension';
    }
}
