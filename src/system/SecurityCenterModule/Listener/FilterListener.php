<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Listener;

use Doctrine\ORM\EntityManagerInterface;
use IDS\Init as IdsInit;
use IDS\Monitor as IdsMonitor;
use IDS\Report as IdsReport;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\SecurityCenterModule\Entity\IntrusionEntity;
use Zikula\UsersModule\Constant;

/**
 * Event handler for the security center module
 *
 * Adds the intrustion detection filter request event.
 */
class FilterListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $isInstalled;

    /**
     * @var bool
     */
    private $isUpgrading;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var MailerApiInterface
     */
    private $mailer;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * FilterListener constructor.
     *
     * @param bool $isInstalled Installed flag
     * @param $isUpgrading
     * @param VariableApiInterface $variableApi VariableApi service instance
     * @param EntityManagerInterface $em Doctrine entity manager
     * @param MailerApiInterface $mailer MailerApi service instance
     * @param TranslatorInterface $translator
     * @param $cacheDir
     */
    public function __construct(
        $isInstalled,
        $isUpgrading,
        VariableApiInterface $variableApi,
        EntityManagerInterface $em,
        MailerApiInterface $mailer,
        TranslatorInterface $translator,
        $cacheDir
    ) {
        $this->isInstalled = $isInstalled;
        $this->isUpgrading = $isUpgrading;
        $this->variableApi = $variableApi;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->cacheDir = $cacheDir;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                ['idsInputFilter', 100]
            ]
        ];
    }

    /**
     * Protects against basic attempts of Cross-Site Scripting (XSS).
     *
     * @see    http://technicalinfo.net/papers/CSS.html
     *
     * @param GetResponseEvent $event
     *
     * @return void
     *
     * @throws \Exception Thrown if there was a problem running ids detection
     */
    public function idsInputFilter(GetResponseEvent $event)
    {
        if (!$this->isInstalled || $this->isUpgrading) {
            return;
        }

        if (1 != $this->getSystemVar('useids')) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }

        // Run IDS if desired
        try {
            $request = [];
            // build request array defining what to scan
            // @todo: change the order of the arrays to merge if ini_get('variables_order') != 'EGPCS'
            if (isset($_REQUEST)) {
                $request['REQUEST'] = $_REQUEST;
            }
            if (isset($_GET)) {
                $request['GET'] = $_GET;
            }
            if (isset($_POST)) {
                $request['POST'] = $_POST;
            }
            if (isset($_COOKIE)) {
                $request['COOKIE'] = $_COOKIE;
            }
            if (isset($_SERVER['HTTP_HOST'])) {
                $request['HOST'] = $_SERVER['HTTP_HOST'];
            }
            if (isset($_SERVER['HTTP_ACCEPT'])) {
                $request['ACCEPT'] = $_SERVER['HTTP_ACCEPT'];
            }
            if (isset($_SERVER['USER_AGENT'])) {
                $request['USER_AGENT'] = $_SERVER['USER_AGENT'];
            }
            // while i think that REQUEST_URI is unnecessary,
            // the REFERER would be important, but results in way too many false positives
            /*
            if (isset($_SERVER['REQUEST_URI'])) {
                $request['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
            }
            if (isset($_SERVER['HTTP_REFERER'])) {
                $request['REFERER'] = $_SERVER['HTTP_REFERER'];
            }
            */

            // initialise configuration object
            $init = IdsInit::init();

            // set configuration options
            $init->config = $this->getIdsConfig();

            // create new IDS instance
            $ids = new IdsMonitor($init);

            // run the request check and fetch the results
            $result = $ids->run($request);

            // analyze the results
            if (!$result->isEmpty()) {
                // process the IdsReport object
                $session = $event->getRequest()->hasSession() ? $event->getRequest()->getSession() : null;
                $this->processIdsResult($init, $result, $session, $event->getRequest());
            } else {
                // no attack detected
            }
        } catch (\Exception $e) {
            // sth went wrong - maybe the filter rules weren't found
            throw new \Exception($this->translator->__f('An error occured during executing PHPIDS: %s', ['%s' => $e->getMessage()]));
        }
    }

    /**
     * Retrieves configuration array for PHPIDS.
     *
     * @return array IDS configuration settings
     */
    private function getIdsConfig()
    {
        $config = [];

        // General configuration settings
        $config['General'] = [];

        $config['General']['filter_type'] = $this->getSystemVar('idsfilter', 'xml');
        if (empty($config['General']['filter_type'])) {
            $config['General']['filter_type'] = 'xml';
        }

        $config['General']['base_path'] = ''; //PHPIDS_PATH_PREFIX;
        // we don't use the base path because the tmp directory is in zkTemp (see below)
        $config['General']['use_base_path'] = false;

        // path to the filters used
        $config['General']['filter_path'] = $this->getSystemVar('idsrulepath', 'system/SecurityCenterModule/Resources/config/phpids_zikula_default.xml');
        // path to (writable) tmp directory
        $config['General']['tmp_path'] = $this->cacheDir . '/idsTmp';
        $config['General']['scan_keys'] = false;

        // we use a different HTML Purifier source
        // by default PHPIDS does also contain those files
        // we do this more efficiently in boostrap (drak).
        $config['General']['HTML_Purifier_Path'] = ''; // this must be set or IdsMonitor will never fill in the HTML_Purifier_Cache property (drak).
        $config['General']['HTML_Purifier_Cache'] = $this->cacheDir . '/purifier';

        // define which fields contain html and need preparation before hitting the PHPIDS rules
        $config['General']['html'] = $this->getSystemVar('idshtmlfields', []);

        // define which fields contain JSON data and should be treated as such for fewer false positives
        $config['General']['json'] = $this->getSystemVar('idsjsonfields', []);

        // define which fields shouldn't be monitored (a[b]=c should be referenced via a.b)
        $config['General']['exceptions'] = $this->getSystemVar('idsexceptions', []);

        // PHPIDS should run with PHP 5.1.2 but this is untested - set this value to force compatibilty with minor versions
        $config['General']['min_php_version'] = '5.1.6';

        // caching settings
        // @todo: add UI for those caching settings
        $config['Caching'] = [];

        // caching method (session|file|database|memcached|none), default file
        $config['Caching']['caching'] = 'none'; // deactivate caching for now
        $config['Caching']['expiration_time'] = 600;

        // file cache
        $config['Caching']['path'] = $config['General']['tmp_path'] . '/default_filter.cache';

        // database cache
        //$config['Caching']['wrapper'] = 'mysql:host=localhost;port=3306;dbname=phpids';
        //$config['Caching']['user'] = 'phpids_user';
        //$config['Caching']['password'] = '123456';
        //$config['Caching']['table'] = 'cache';

        // memcached
        //$config['Caching']['host'] = 'localhost';
        //$config['Caching']['port'] = 11211;
        //$config['Caching']['key_prefix'] = 'PHPIDS';
        //$config['Caching']['tmp_path'] = $config['General']['tmp_path'] . '/memcache.timestamp';

        return $config;
    }

    /**
     * Process results from IDS scan.
     *
     * @param IdsInit $init PHPIDS init object reference
     * @param IdsReport $result The result object from PHPIDS
     * @param SessionInterface $session
     * @param Request $request
     */
    private function processIdsResult(IdsInit $init, IdsReport $result, SessionInterface $session, Request $request)
    {
        // $result contains any suspicious fields enriched with additional info

        // Note: it is moreover possible to dump this information by simply doing
        //"echo $result", calling the IdsReport::$this->__toString() method implicitely.

        $requestImpact = $result->getImpact();
        if ($requestImpact < 1) {
            // nothing to do
            return;
        }

        // update total session impact to track an attackers activity for some time
        if (!empty($session)) {
            $sessionImpact = $session->get('idsImpact', 0) + $requestImpact;
            $session->set('idsImpact', $sessionImpact);
        } else {
            $sessionImpact = $requestImpact;
        }

        // let's see which impact mode we are using
        $idsImpactMode = $this->getSystemVar('idsimpactmode', 1);
        $idsImpactFactor = 1;
        if (1 == $idsImpactMode) {
            $idsImpactFactor = 1;
        } elseif (2 == $idsImpactMode) {
            $idsImpactFactor = 10;
        } elseif (3 == $idsImpactMode) {
            $idsImpactFactor = 5;
        }

        // determine our impact threshold values
        $impactThresholdOne   = $this->getSystemVar('idsimpactthresholdone', 1) * $idsImpactFactor;
        $impactThresholdTwo   = $this->getSystemVar('idsimpactthresholdtwo', 10) * $idsImpactFactor;
        $impactThresholdThree = $this->getSystemVar('idsimpactthresholdthree', 25) * $idsImpactFactor;
        $impactThresholdFour  = $this->getSystemVar('idsimpactthresholdfour', 75) * $idsImpactFactor;

        $usedImpact = (1 == $idsImpactMode) ? $requestImpact : $sessionImpact;

        // react according to given impact
        if ($usedImpact > $impactThresholdOne) {
            // db logging

            // determine IP address of current user
            $_REMOTE_ADDR = $request->server->get('REMOTE_ADDR');
            $_HTTP_X_FORWARDED_FOR = $request->server->get('HTTP_X_FORWARDED_FOR');
            $ipAddress = ($_HTTP_X_FORWARDED_FOR) ? $_HTTP_X_FORWARDED_FOR : $_REMOTE_ADDR;

            $currentPage = $request->getRequestUri();
            $currentUid = !empty($session) ? $session->get('uid', Constant::USER_ID_ANONYMOUS) : Constant::USER_ID_ANONYMOUS;
            $currentUser = $this->em->getReference('ZikulaUsersModule:UserEntity', $currentUid);

            $intrusionItems = [];

            foreach ($result as $event) {
                $eventName = $event->getName();
                $malVar = explode('.', $eventName, 2);

                $filters = [];
                /** @var \IDS\Event $event */
                foreach ($event as $filter) {
                    array_push($filters, [
                        'id' => $filter->getId(),
                        'description' => $filter->getDescription(),
                        'impact' => $filter->getImpact(),
                        'tags' => $filter->getTags(),
                        'rule' => $filter->getRule()
                    ]);
                }

                $tagVal = $malVar[1];

                $newIntrusionItem = [
                    'name'    => [$eventName],
                    'tag'     => $tagVal,
                    'value'   => $event->getValue(),
                    'page'    => $currentPage,
                    'user'    => $currentUser,
                    'ip'      => $ipAddress,
                    'impact'  => $result->getImpact(),
                    'filters' => serialize($filters),
                    'date'    => new \DateTime('now')
                ];

                if (array_key_exists($tagVal, $intrusionItems)) {
                    $intrusionItems[$tagVal]['name'][] = $newIntrusionItem['name'][0];
                } else {
                    $intrusionItems[$tagVal] = $newIntrusionItem;
                }
            }

            // log details to database
            foreach ($intrusionItems as $tag => $intrusionItem) {
                $intrusionItem['name'] = implode(', ', $intrusionItem['name']);

                $obj = new IntrusionEntity();
                $obj->merge($intrusionItem);
                $this->em->persist($obj);
            }

            $this->em->flush();
        }

        if ($this->getSystemVar('idsmail') && $usedImpact > $impactThresholdTwo) {
            // mail admin
            // prepare mail text
            $mailBody = $this->translator->__('The following attack has been detected by PHPIDS') . "\n\n";
            $mailBody .= isset($ipAddress) ? $this->translator->__f('IP: %s', ['%s' => $ipAddress]) . "\n" : '';
            $mailBody .= isset($currentUid) ? $this->translator->__f('UserID: %s', ['%s' => $currentUid]) . "\n" : '';
            $currentDate = new \DateTime();
            $mailBody .= $this->translator->__f('Date: %s', ['%s' => $currentDate->format('%b %d, %Y')]) . "\n";
            if (1 == $idsImpactMode) {
                $mailBody .= $this->translator->__f('Request Impact: %d', ['%d' => $requestImpact]) . "\n";
            } else {
                $mailBody .= $this->translator->__f('Session Impact: %d', ['%d' => $sessionImpact]) . "\n";
            }
            $mailBody .= $this->translator->__f('Affected tags: %s', ['%s' => implode(' ', $result->getTags())]) . "\n";

            $attackedParameters = '';
            foreach ($result as $event) {
                $attackedParameters .= $event->getName() . '=' . urlencode($event->getValue()) . ", ";
            }

            $mailBody .= $this->translator->__f('Affected parameters: %s', ['%s' => trim($attackedParameters)]) . "\n";
            $mailBody .= isset($currentPage) ? $this->translator->__f('Request URI: %s', ['%s' => urlencode($currentPage)]) : '';

            // prepare other mail arguments
            $siteName = $this->getSystemVar('sitename', $this->getSystemVar('sitename_en'));
            $adminMail = $this->getSystemVar('adminmail');

            // create new message instance
            /** @var Swift_Message */
            $message = Swift_Message::newInstance();

            $message->setFrom([$adminMail => $siteName]);
            $message->setTo([$adminMail => $this->translator->__('Site Administrator')]);

            $subject = $this->translator->__('Intrusion attempt detected by PHPIDS');
            $rc = $this->mailer->sendMessage($message, $subject, $mailBody);
        }

        if ($usedImpact > $impactThresholdThree) {
            // block request

            if ($this->getSystemVar('idssoftblock')) {
                // warn only for debugging the ruleset
                throw new \RuntimeException($this->translator->__('Malicious request code / a hacking attempt was detected. This request has NOT been blocked!'));
            } else {
                throw new AccessDeniedException($this->translator->__('Malicious request code / a hacking attempt was detected. Thus this request has been blocked.'), null, $result);
            }
        }

        // TODO $impactThresholdFour is not considered yet

        return;
    }

    /**
     * Returns a system var.
     *
     * @param string $variableName The variable name
     * @param mixed  $default      The default value
     *
     * @return mixed Result returned by variable api call
     */
    private function getSystemVar($variableName, $default = false)
    {
        return $this->variableApi->getSystemVar($variableName, $default);
    }
}
