<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Listener;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use IDS\Event;
use IDS\Init as IdsInit;
use IDS\Monitor as IdsMonitor;
use IDS\Report as IdsReport;
use RuntimeException;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\MailerModule\Api\ApiInterface\MailerApiInterface;
use Zikula\SecurityCenterModule\Entity\IntrusionEntity;
use Zikula\SecurityCenterModule\Helper\CacheDirHelper;
use Zikula\SecurityCenterModule\ZikulaSecurityCenterModule;
use Zikula\UsersModule\Constant;

/**
 * Event handler for the security center module
 *
 * Adds the intrustion detection filter request event.
 */
class FilterListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaSecurityCenterModule
     */
    private $securityCenterModule;

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
     * @var CacheDirHelper
     */
    private $cacheDirHelper;

    /**
     * @var bool
     */
    private $installed;

    /**
     * @var bool
     */
    private $isUpgrading;

    public function __construct(
        ZikulaSecurityCenterModule $securityCenterModule,
        VariableApiInterface $variableApi,
        EntityManagerInterface $em,
        MailerApiInterface $mailer,
        TranslatorInterface $translator,
        string $cacheDir,
        CacheDirHelper $cacheDirHelper,
        string $installed,
        $isUpgrading // cannot cast to bool because set with expression language
    ) {
        $this->securityCenterModule = $securityCenterModule;
        $this->variableApi = $variableApi;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->cacheDir = $cacheDir;
        $this->cacheDirHelper = $cacheDirHelper;
        $this->installed = '0.0.0' !== $installed;
        $this->isUpgrading = $isUpgrading;
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
     * @see http://technicalinfo.net/papers/CSS.html
     *
     * @throws Exception Thrown if there was a problem running ids detection
     */
    public function idsInputFilter(RequestEvent $event): void
    {
        if (!$this->installed || $this->isUpgrading) {
            return;
        }

        if (1 !== $this->getSystemVar('useids')) {
            return;
        }
        if (!$event->isMasterRequest()) {
            return;
        }

        // Run IDS if desired
        $request = $event->getRequest();
        try {
            $requestArgs = [];
            // build request array defining what to scan
            if ($request->query->count() > 0) {
                $requestArgs['GET'] = $request->query->all();
            }
            if ($request->request->count() > 0) {
                $requestArgs['POST'] = $request->request->all();
            }
            if ($request->cookies->count() > 0) {
                $requestArgs['COOKIE'] = $request->cookies->all();
            }
            if ($request->server->has('HTTP_HOST')) {
                $requestArgs['HOST'] = $request->server->get('HTTP_HOST');
            }
            if ($request->server->has('HTTP_ACCEPT')) {
                $requestArgs['ACCEPT'] = $request->server->get('HTTP_ACCEPT');
            }
            if ($request->server->has('USER_AGENT')) {
                $requestArgs['USER_AGENT'] = $request->server->get('USER_AGENT');
            }
            // while i think that REQUEST_URI is unnecessary,
            // the REFERER would be important, but results in way too many false positives
            /*
            if ($request->server->has('REQUEST_URI')) {
                $requestArgs['REQUEST_URI'] = $request->server->get('REQUEST_URI');
            }
            if ($request->server->has('HTTP_REFERER')) {
                $requestArgs['REFERER'] = $request->server->get('HTTP_REFERER');
            }
            */

            // initialise configuration object
            $init = IdsInit::init();

            // set configuration options
            $init->config = $this->getIdsConfig();

            // create new IDS instance
            $ids = new IdsMonitor($init);

            // run the request check and fetch the results
            $result = $ids->run($requestArgs);

            // analyze the results
            if (!$result->isEmpty()) {
                // process the IdsReport object
                $this->processIdsResult($result, $request);
            }
        } catch (Exception $exception) {
            // sth went wrong - maybe the filter rules weren't found
            throw new Exception($this->translator->trans('An error occured during executing PHPIDS: %message%', ['%message%' => $exception->getMessage()]));
        }
    }

    /**
     * Retrieves configuration array for PHPIDS.
     */
    private function getIdsConfig(): array
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
        $defaultPath = 'Resources/config/phpids_zikula_default.xml';
        $config['General']['filter_path'] = $this->securityCenterModule->getPath() . '/' . $this->getSystemVar('idsrulepath', $defaultPath);
        // path to (writable) tmp directory
        $config['General']['tmp_path'] = $this->cacheDir . '/idsTmp';
        $this->cacheDirHelper->ensureCacheDirectoryExists($config['General']['tmp_path']);
        $config['General']['scan_keys'] = false;

        // we use a different HTML Purifier source
        // by default PHPIDS does also contain those files
        // we do this more efficiently in boostrap (drak).
        $config['General']['HTML_Purifier_Path'] = ''; // this must be set or IdsMonitor will never fill in the HTML_Purifier_Cache property (drak).
        $config['General']['HTML_Purifier_Cache'] = $this->cacheDir . '/purifier';
        $this->cacheDirHelper->ensureCacheDirectoryExists($config['General']['tmp_path'], true);

        // define which fields contain html and need preparation before hitting the PHPIDS rules
        $config['General']['html'] = $this->getSystemVar('idshtmlfields', []);

        // define which fields contain JSON data and should be treated as such for fewer false positives
        $config['General']['json'] = $this->getSystemVar('idsjsonfields', []);

        // define which fields shouldn't be monitored (a[b]=c should be referenced via a.b)
        $config['General']['exceptions'] = $this->getSystemVar('idsexceptions', []);

        // caching settings
        $config['Caching'] = [];
        // caching method (session|file|database|memcached|none), default file
        $config['Caching']['caching'] = $this->getSystemVar('idscachingtype', 'none');
        $config['Caching']['expiration_time'] = $this->getSystemVar('idscachingexpiration', 600);

        // file cache
        $config['Caching']['path'] = $config['General']['tmp_path'] . '/default_filter.cache';

        return $config;
    }

    /**
     * Process results from IDS scan.
     */
    private function processIdsResult(
        IdsReport $result,
        Request $request
    ): void {
        // $result contains any suspicious fields enriched with additional info

        // Note: it is moreover possible to dump this information by simply doing
        //"echo $result", calling the IdsReport::$this->__toString() method implicitely.

        $requestImpact = $result->getImpact();
        if ($requestImpact < 1) {
            // nothing to do
            return;
        }

        $session = $request->hasSession() ? $request->getSession() : null;
        // update total session impact to track an attackers activity for some time
        if (null !== $session) {
            $sessionImpact = $session->get('idsImpact', 0) + $requestImpact;
            $session->set('idsImpact', $sessionImpact);
        } else {
            $sessionImpact = $requestImpact;
        }

        // let's see which impact mode we are using
        $idsImpactMode = $this->getSystemVar('idsimpactmode', 1);
        $idsImpactFactor = 1;
        if (1 === $idsImpactMode) {
            $idsImpactFactor = 1;
        } elseif (2 === $idsImpactMode) {
            $idsImpactFactor = 10;
        } elseif (3 === $idsImpactMode) {
            $idsImpactFactor = 5;
        }

        // determine our impact threshold values
        $impactThresholdOne   = $this->getSystemVar('idsimpactthresholdone', 1) * $idsImpactFactor;
        $impactThresholdTwo   = $this->getSystemVar('idsimpactthresholdtwo', 10) * $idsImpactFactor;
        $impactThresholdThree = $this->getSystemVar('idsimpactthresholdthree', 25) * $idsImpactFactor;
        $impactThresholdFour  = $this->getSystemVar('idsimpactthresholdfour', 75) * $idsImpactFactor;

        $usedImpact = (1 === $idsImpactMode) ? $requestImpact : $sessionImpact;

        // react according to given impact
        if ($usedImpact > $impactThresholdOne) {
            // db logging

            // determine IP address of current user
            $_REMOTE_ADDR = $request->server->get('REMOTE_ADDR');
            $_HTTP_X_FORWARDED_FOR = $request->server->get('HTTP_X_FORWARDED_FOR');
            $ipAddress = $_HTTP_X_FORWARDED_FOR ?: $_REMOTE_ADDR;

            $currentPage = $request->getRequestUri();
            $currentUid = null !== $session ? $session->get('uid', Constant::USER_ID_ANONYMOUS) : Constant::USER_ID_ANONYMOUS;
            $currentUser = $this->em->getReference('ZikulaUsersModule:UserEntity', $currentUid);

            $intrusionItems = [];

            foreach ($result as $event) {
                $eventName = $event->getName();
                $malVar = explode('.', $eventName, 2);

                $filters = [];
                /** @var Event $event */
                foreach ($event as $filter) {
                    $filters[] = [
                        'id' => $filter->getId(),
                        'description' => $filter->getDescription(),
                        'impact' => $filter->getImpact(),
                        'tags' => $filter->getTags(),
                        'rule' => $filter->getRule()
                    ];
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
                    'date'    => new DateTime('now')
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

        if ($usedImpact > $impactThresholdTwo && $this->getSystemVar('idsmail')) {
            // mail admin
            // prepare mail text
            $mailBody = $this->translator->trans('The following attack has been detected by PHPIDS') . "\n\n";
            $mailBody .= isset($ipAddress) ? $this->translator->trans('IP: %ip%', ['%ip%' => $ipAddress]) . "\n" : '';
            $mailBody .= isset($currentUid) ? $this->translator->trans('UserID: %userId%', ['%userId%' => $currentUid]) . "\n" : '';
            $currentDate = new DateTime();
            $mailBody .= $this->translator->trans('Date: %date%', ['%date%' => $currentDate->format('%b %d, %Y')]) . "\n";
            if (1 === $idsImpactMode) {
                $mailBody .= $this->translator->trans('Request impact: %impact%', ['%impact%' => $requestImpact]) . "\n";
            } else {
                $mailBody .= $this->translator->trans('Session impact: %impact%', ['%impact%' => $sessionImpact]) . "\n";
            }
            $mailBody .= $this->translator->trans('Affected tags: %tags%', ['%tags%' => implode(' ', $result->getTags())]) . "\n";

            $attackedParameters = '';
            foreach ($result as $event) {
                $attackedParameters .= $event->getName() . '=' . urlencode($event->getValue()) . ', ';
            }

            $mailBody .= $this->translator->trans('Affected parameters: %parameters%', ['%parameters%' => trim($attackedParameters)]) . "\n";
            $mailBody .= isset($currentPage) ? $this->translator->trans('Request URI: %uri%', ['%uri%' => urlencode($currentPage)]) : '';

            // prepare other mail arguments
            $siteName = $this->getSystemVar('sitename', $this->getSystemVar('sitename_en'));
            $adminMail = $this->getSystemVar('adminmail');

            // create new message instance
            $message = new Swift_Message();

            $message->setFrom([$adminMail => $siteName]);
            $message->setTo([$adminMail => $this->translator->trans('Site Administrator')]);

            $subject = $this->translator->trans('Intrusion attempt detected by PHPIDS');
            $this->mailer->sendMessage($message, $subject, $mailBody);
        }

        if ($usedImpact > $impactThresholdThree) {
            // block request

            if ($this->getSystemVar('idssoftblock')) {
                // warn only for debugging the ruleset
                throw new RuntimeException($this->translator->trans('Malicious request code / a hacking attempt was detected. This request has NOT been blocked!'));
            }
            throw new AccessDeniedException($this->translator->trans('Malicious request code / a hacking attempt was detected. Thus this request has been blocked.'), null);
        }

        if ($usedImpact > $impactThresholdFour && null !== $session) {
            // kick user (destroy session)
            $session->invalidate();
        }
    }

    /**
     * Returns a system var.
     *
     * @param mixed $default The default value
     *
     * @return mixed Result returned by variable api call
     */
    private function getSystemVar(string $variableName, $default = false)
    {
        return $this->variableApi->getSystemVar($variableName, $default);
    }
}
