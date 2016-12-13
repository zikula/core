<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Bundle\CoreBundle\Twig;
use Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction\DefaultPathSimpleFunction;
use Zikula\Bundle\CoreBundle\Twig\Extension\SimpleFunction\DispatchEventSimpleFunction;
use Zikula\ThemeModule\Engine\AssetBag;

class CoreExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function getTokenParsers()
    {
        return [
            new Twig\TokenParser\SwitchTokenParser()
        ];
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        $functions = [
            new \Twig_SimpleFunction('button', [$this, 'button']),
            new \Twig_SimpleFunction('img', [$this, 'img']),
            new \Twig_SimpleFunction('icon', [$this, 'icon']),
            new \Twig_SimpleFunction('lang', [$this, 'lang']),
            new \Twig_SimpleFunction('langdirection', [$this, 'langDirection']),
            new \Twig_SimpleFunction('zasset', [$this, 'getAssetPath']),
            new \Twig_SimpleFunction('showflashes', [$this, 'showFlashes'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('array_unset', [$this, 'arrayUnset']),
            new \Twig_SimpleFunction('pageSetVar', [$this, 'pageSetVar']),
            new \Twig_SimpleFunction('pageAddAsset', [$this, 'pageAddAsset']),
            new \Twig_SimpleFunction('pageGetVar', [$this, 'pageGetVar']),
            new \Twig_SimpleFunction('getModVar', [$this, 'getModVar']),
            new \Twig_SimpleFunction('getSystemVar', [$this, 'getSystemVar']),
            new \Twig_SimpleFunction('setMetaTag', [$this, 'setMetaTag']),
            new \Twig_SimpleFunction('hasPermission', [$this, 'hasPermission']),
            new \Twig_SimpleFunction('defaultPath', [new DefaultPathSimpleFunction($this), 'getDefaultPath']),
            new \Twig_SimpleFunction('modAvailable', [$this, 'modAvailable']),
            new \Twig_SimpleFunction('callFunc', [$this, 'callFunc']),
        ];
        if (is_object($this->container)) {
            $functions[] = new \Twig_SimpleFunction('dispatchEvent', [new DispatchEventSimpleFunction($this->container->get('event_dispatcher')), 'dispatchEvent']);
        }

        return $functions;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('languageName', [$this, 'languageName']),
            new \Twig_SimpleFilter('yesNo', [$this, 'yesNo']),
            new \Twig_SimpleFilter('php', [$this, 'applyPhp']),
            new \Twig_SimpleFilter('protectMail', [$this, 'protectMailAddress'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('profileLinkByUserId', [$this, 'profileLinkByUserId'], ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('profileLinkByUserName', [$this, 'profileLinkByUserName'], ['is_safe' => ['html']])
        ];
    }

    public function getAssetPath($path)
    {
        return $this->container->get('zikula_core.common.theme.asset_helper')->resolve($path);
    }

    /**
     * Function to get the language direction
     *
     * @return string   the language direction
     */
    public function langDirection()
    {
        return \ZLanguage::getDirection();
    }

    public function button()
    {
    }

    public function img()
    {
    }

    public function icon()
    {
    }

    /**
     * Display flash messages in twig template. Defaults to bootstrap alert classes.
     *
     * <pre>
     *  {{ showflashes() }}
     *  {{ showflashes({'class': 'custom-class', 'tag': 'span'}) }}
     * </pre>
     *
     * @param array $params
     * @return string
     */
    public function showFlashes(array $params = [])
    {
        $result = '';
        $total_messages = [];
        $messageTypeMap = [
            \Zikula_Session::MESSAGE_ERROR => 'danger',
            \Zikula_Session::MESSAGE_WARNING => 'warning',
            \Zikula_Session::MESSAGE_STATUS => 'success',
            'danger' => 'danger',
            'success' => 'success',
            'info' => 'info'
        ];

        foreach ($messageTypeMap as $messageType => $bootstrapClass) {
            $session = $this->container->get('session');
            $messages = $session->isStarted() ? $session->getFlashBag()->get($messageType) : [];
            if (count($messages) > 0) {
                // Set class for the messages.
                $class = (!empty($params['class'])) ? $params['class'] : "alert alert-$bootstrapClass";
                $total_messages = $total_messages + $messages;
                // Build output of the messages.
                if (empty($params['tag']) || ($params['tag'] != 'span')) {
                    $params['tag'] = 'div';
                }
                $result .= '<' . $params['tag'] . ' class="' . $class . '"';
                if (!empty($params['style'])) {
                    $result .= ' style="' . $params['style'] . '"';
                }
                $result .= '>';
                $result .= implode('<hr />', $messages);
                $result .= '</' . $params['tag'] . '>';
            }
        }

        if (empty($total_messages)) {
            return "";
        }

        return $result;
    }

    /**
     * Delete a key of an array
     *
     * @param array  $array Source array
     * @param string $key   The key to remove
     *
     * @return array
     */
    public function arrayUnset($array, $key)
    {
        unset($array[$key]);

        return $array;
    }

    /**
     * @param string $code
     * @return string
     */
    public function languageName($code)
    {
        return \ZLanguage::getLanguageName($code);
    }

    public function yesNo($string)
    {
        if ($string != '0' && $string != '1') {
            return $string;
        }

        $translator = $this->container->get('translator.default');

        return (bool)$string ? $translator->__('Yes') : $translator->__('No');
    }

    /**
     * Apply an existing function (e.g. php's `md5`) to a string.
     *
     * @param $string
     * @param $func
     * @return mixed
     */
    public function applyPhp($string, $func)
    {
        if (function_exists($func)) {
            return $func($string);
        }

        return $string;
    }

    /**
     * Protect a given mail address by finding the text 'x@y' and replacing
     * it with HTML entities. This provides protection against email harvesters.
     *
     * @param string
     * @return string
     */
    public function protectMailAddress($string)
    {
        $string = preg_replace_callback(
            '/(.)@(.)/s',
            function ($m) {
                return "&#" . sprintf("%03d", ord($m[1])) . ";&#064;&#" .sprintf("%03d", ord($m[2])) . ";";
            },
            $string
        );

        return $string;
    }

    /**
     * Create a link to a users profile from the UID.
     *
     * Examples
     *
     *   Simple version, shows the username
     *   {{ uid|profileLinkByUserId() }}
     *   Simple version, shows username, using class="classname"
     *   {{ uid|profileLinkByUserId(class='classname') }}
     *   Using profile.gif instead of username, no class
     *   {{ uid|profileLinkByUserId(image='images/profile.gif') }}
     *
     * @param integer $userId    The users uid
     * @param string  $class     The class name for the link (optional)
     * @param string  $image     Path to the image to show instead of the username (optional)
     * @param integer $maxLength If set then user names are truncated to x chars
     * @return string The output
     */
    public function profileLinkByUserId($userId, $class = '', $image = '', $maxLength = 0)
    {
        if (empty($userId) || (int)$userId < 1) {
            return $userId;
        }

        $userId = (int)$userId;
        $userName = \UserUtil::getVar('uname', $userId);

        return $this->determineProfileLink($userId, $userName, $class, $image, $maxLength);
    }

    /**
     * Create a link to a users profile from the username.
     *
     * Examples
     *
     *   Simple version, shows the username
     *   {{ username|profileLinkByUserName() }}
     *   Simple version, shows username, using class="classname"
     *   {{ username|profileLinkByUserName(class='classname') }}
     *   Using profile.gif instead of username, no class
     *   {{ username|profileLinkByUserName('image'='images/profile.gif') }}
     *
     * @param string  $userName  The users name
     * @param string  $class     The class name for the link (optional)
     * @param string  $image     Path to the image to show instead of the username (optional)
     * @param integer $maxLength If set then user names are truncated to x chars
     * @return string The output
     */
    public function profileLinkByUserName($userName, $class = '', $image = '', $maxLength = 0)
    {
        if (empty($userName)) {
            return $userName;
        }

        $userId = \UserUtil::getIdFromName($userName);

        return $this->determineProfileLink($userId, $userName, $class, $image, $maxLength);
    }

    /**
     * Internal function used by profileLinkByUserId() and profileLinkByUserName().
     *
     * @param integer $userId    The users uid
     * @param string  $userName  The users name
     * @param string  $class     The class name for the link (optional)
     * @param string  $image     Path to the image to show instead of the username (optional)
     * @param integer $maxLength If set then user names are truncated to x chars
     * @return string The output
     */
    private function determineProfileLink($userId, $userName, $class = '', $image = '', $maxLength = 0)
    {
        $profileLink = '';

        $profileModule = \System::getVar('profilemodule', '');

        if ($userId && $userId > 1 && !empty($profileModule) && \ModUtil::available($profileModule)) {
            $userDisplayName = \ModUtil::apiFunc($profileModule, 'user', 'getUserDisplayName', ['uid' => $userId]);
            if (empty($userDisplayName)) {
                $userDisplayName = $userName;
            }

            if (!empty($class)) {
                $class = ' class="' . \DataUtil::formatForDisplay($class) . '"';
            }

            if (!empty($image)) {
                $show = '<img src="' . \DataUtil::formatForDisplay($image) . '" alt="' . \DataUtil::formatForDisplay($userDisplayName) . '" />';
            } elseif ($maxLength > 0) {
                // truncate the user name to $maxLength chars
                $length = strlen($userDisplayName);
                $truncEnd = ($maxLength > $length) ? $length : $maxLength;
                $show  = \DataUtil::formatForDisplay(substr($userDisplayName, 0, $truncEnd));
            } else {
                $show = \DataUtil::formatForDisplay($userDisplayName);
            }

            $profileLink = '<a' . $class . ' title="' . \DataUtil::formatForDisplay(__('Profile')) . ': ' . \DataUtil::formatForDisplay($userDisplayName) . '" href="' . \DataUtil::formatForDisplay(\ModUtil::url($profileModule, 'user', 'view', ['uid' => $userId], null, null, true)) . '">' . $show . '</a>';
        } elseif (!empty($image)) {
            $profileLink = ''; // image for anonymous user should be "empty"
        } else {
            $profileLink = \DataUtil::formatForDisplay($userName);
        }

        return $profileLink;
    }

    /**
     * Zikula imposes no restriction on page variable names.
     * Typical usage is to set `title` `meta.charset` `lang` etc.
     * array values are set using `.` in the `$name` string (e.g. `meta.charset`)
     * @param string $name
     * @param string $value
     */
    public function pageSetVar($name, $value)
    {
        if (empty($name) || empty($value)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $this->container->get('zikula_core.common.theme.pagevars')->set($name, $value);
    }

    /**
     * Zikula allows only the following asset types
     * <ul>
     *  <li>stylesheet</li>
     *  <li>javascript</li>
     *  <li>header</li>
     *  <li>footer</li>
     * </ul>
     *
     * @param string $type
     * @param string $value
     * @param int $weight
     */
    public function pageAddAsset($type, $value, $weight = AssetBag::WEIGHT_DEFAULT)
    {
        if (empty($type) || empty($value)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }
        if (!in_array($type, ['stylesheet', 'javascript', 'header', 'footer']) || !is_numeric($weight)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        // ensure proper variable types
        $value = (string) $value;
        $type = (string) $type;
        $weight = (int) $weight;

        if ('stylesheet' == $type) {
            $this->container->get('zikula_core.common.theme.assets_css')->add([$value => $weight]);
        } elseif ('javascript' == $type) {
            $this->container->get('zikula_core.common.theme.assets_js')->add([$value => $weight]);
        } elseif ('header' == $type) {
            $this->container->get('zikula_core.common.theme.assets_header')->add([$value => $weight]);
        } elseif ('footer' == $type) {
            $this->container->get('zikula_core.common.theme.assets_footer')->add([$value => $weight]);
        }
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function pageGetVar($name, $default = null)
    {
        if (empty($name)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->container->get('zikula_core.common.theme.pagevars')->get($name, $default);
    }

    /**
     * @param $module
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getModVar($module, $name, $default = null)
    {
        if (empty($module) || empty($name)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->container->get('zikula_extensions_module.api.variable')->get($module, $name, $default);
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getSystemVar($name, $default = null)
    {
        if (empty($name)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        return $this->container->get('zikula_extensions_module.api.variable')->getSystemVar($name, $default);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function setMetaTag($name, $value)
    {
        if (empty($name) || empty($value)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $metaTags = $this->container->hasParameter('zikula_view.metatags') ? $this->container->getParameter('zikula_view.metatags') : [];
        $metaTags[$name] = \DataUtil::formatForDisplay($value);
        $this->container->setParameter('zikula_view.metatags', $metaTags);
    }

    /**
     * @param string $component
     * @param string $instance
     * @param string $level
     * @return bool
     */
    public function hasPermission($component, $instance, $level)
    {
        if (empty($component) || empty($instance) || empty($level)) {
            $translator = $this->container->get('translator.default');
            throw new \InvalidArgumentException($translator->__('Empty argument at') . ':' . __FILE__ . '::' . __LINE__);
        }

        $result = $this->container->get('zikula_permissions_module.api.permission')->hasPermission($component, $instance, constant($level));

        return (bool) $result;
    }

    /**
     * @param string $modname
     * @param bool $force
     * @return bool
     */
    public function modAvailable($modname, $force = false)
    {
        $result = \ModUtil::available($modname, $force);

        return (bool)$result;
    }

    /**
     * Call a php callable with parameters.
     * @param array|string $callable
     * @param array $params
     * @return mixed
     */
    public function callFunc($callable, array $params = [])
    {
        if (function_exists($callable) && is_callable($callable)) {
            return call_user_func_array($callable, $params);
        }

        $translator = $this->container->get('translator.default');
        throw new \InvalidArgumentException($translator->__('Function does not exist or is not callable.') . ':' . __FILE__ . '::' . __LINE__);
    }
}
