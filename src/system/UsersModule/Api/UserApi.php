<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Api;

use DataUtil;
use ModUtil;
use SecurityUtil;
use System;
use UserUtil;
use Zikula_View;
use Doctrine\ORM\AbstractQuery;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\RouterInterface;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * The system-level and database-level functions for user-initiated actions for the Users module.
 */
class UserApi extends \Zikula_AbstractApi
{
    /**
     * Get all users (for which the current user has permission to read).
     *
     * @param mixed[] $args {
     *      @type string  $letter   The first letter of the set of user names to return.
     *      @type int     $starnum  First item to return (optional).
     *      @type int     $numitems Number if items to return (optional).
     *      @type array   $sort     The field(s) on which to sort the result (optional).
     *                      }
     *
     * @return array An array of users, or false on failure.
     *
     * @throws AccessDeniedException Thrown if the current user does not have overview access.
     */
    public function getAll($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('ZikulaUsersModule::', '::', ACCESS_OVERVIEW)) {
            throw new AccessDeniedException();
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('u')
           ->from('ZikulaUsersModule:UserEntity', 'u');

        // add clauses for filtering activation states
        $qb->andWhere($qb->expr()->neq('u.activated', $qb->expr()->literal(UsersConstant::ACTIVATED_PENDING_REG)));
        $qb->andWhere($qb->expr()->neq('u.activated', $qb->expr()->literal(UsersConstant::ACTIVATED_PENDING_DELETE)));

        // add clause for filtering letter
        if (isset($args['letter']) && !empty($args['letter'])) {
            $qb->andWhere($qb->expr()->like('u.uname', ':uname'))->setParameter('uname', $args['letter'] . '%');
        }

        // add ordering
        if (isset($args['sort']) && !empty($args['sort']) && is_array($args['sort'])) {
            foreach ($args['sort'] as $sort => $sortDir) {
                $qb->addOrderBy('u.' . $sort, $sortDir);
            }
        } else {
            $qb->addOrderBy('u.uname', 'ASC');
        }

        // add limit and offset
        $startnum = (!isset($args['startnum']) || empty($args['startnum']) || $args['startnum'] < 0) ? 0 : (int)$args['startnum'];
        $numitems = (!isset($args['numitems']) || empty($args['numitems']) || $args['numitems'] < 0) ? 0 : (int)$args['numitems'];
        if ($numitems > 0) {
            $qb->setFirstResult($startnum)
               ->setMaxResults($numitems);
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $objArray = $query->getResult(AbstractQuery::HYDRATE_ARRAY);

        return $objArray;
    }

    /**
     * Get a specific user record.
     *
     * @param mixed[] $args {
     *      @type int    $uid   The id of user to get (required, unless uname specified).
     *      @type string $uname The user name of user to get (ignored if uid is specified, otherwise required).
     *                      }
     *
     * @return array The user record as an array, or false on failure.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args, or if the data cannot be loaded from the database.
     */
    public function get($args)
    {
        // Argument check
        if (isset($args['uid'])) {
            if (!is_numeric($args['uid']) || ((int)$args['uid'] != $args['uid'])) {
                throw new \InvalidArgumentException(__('Invalid arguments array received'));
            } else {
                $key = (int)$args['uid'];
                $field = 'uid';
            }
        } elseif (!isset($args['uname']) || !is_string($args['uname'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            $key = $args['uname'];
            $field = 'uname';
        }

        $obj = UserUtil::getVars($key, false, $field);

        // Check for a DB error
        if ($obj === false) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // Return the item array
        return $obj;
    }

    /**
     * Count and return the number of users.
     *
     * @param string[] $args {
     *      @type string $letter If specified, then only those user records whose user name begins with the specified letter are counted.
     *                       }
     *
     * @return int Number of users.
     *
     * @todo Shouldn't there be some sort of limit on the select/loop??
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function countItems($args)
    {
        // Check validity of letter arg.
        // $args['letter'] is really an SQL LIKE filter
        if (isset($args['letter']) && (empty($args['letter']) || !is_string($args['letter']) || strstr($args['letter'], '%'))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('count(u.uid)')
           ->from('ZikulaUsersModule:UserEntity', 'u');

        // add clauses for filtering activation states
        $qb->andWhere($qb->expr()->neq('u.activated', $qb->expr()->literal(UsersConstant::ACTIVATED_PENDING_REG)));
        $qb->andWhere($qb->expr()->neq('u.activated', $qb->expr()->literal(UsersConstant::ACTIVATED_PENDING_DELETE)));

        // add clause for filtering letter
        if (isset($args['letter']) && !empty($args['letter'])) {
            $qb->andWhere($qb->expr()->like('u.uname', ':uname'))->setParameter('uname', $args['letter'] . '%');
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $count = $query->getSingleScalarResult();

        return (int)$count;
    }

    /**
     * Sends a notification e-mail of a specified type to a user or registrant.
     *
     * @param mixed[] $args {
     *      @type string $toAddress        The destination e-mail address.
     *      @type string $notificationType The type of notification, converted to the name of a template
     *                                     in the form users_userapi_{type}mail.tpl and/or .txt.
     *      @type array  $templateArgs     One or more arguments to pass to the renderer for use in the template.
     *      @type string $subject          The e-mail subject, overriding the template's subject.
     *                      }
     *
     * @return <type>
     */
    public function sendNotification($args)
    {
        $toAddress = $args['toAddress'];
        $notificationType = isset($args['notificationType']) ? $args['notificationType'] : '';
        $templateArgs = isset($args['templateArgs']) ? $args['templateArgs'] : [];
        $subject = isset($args['subject']) ? $args['subject'] : '';

        return $this->getContainer()->get('zikulausersmodule.helper.notification_helper')->sendNotification($toAddress, $notificationType, $templateArgs, $subject);
    }

    /**
     * Send the user an account information recovery e-mail.
     *
     * @param mixed[] $args {
     *      @type string $idfield The value 'email'.
     *      @type string $id      The user's e-mail address.
     *                      }
     *
     * @return bool True if user name sent; otherwise false.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     * @throws \RuntimeException Thrown if the e-mail couldn't be sent
     */
    public function mailUname($args)
    {
        $emailMessageSent = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield'])
                || (($args['idfield'] != 'email') && ($args['idfield'] != 'uid'))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $adminRequested = (isset($args['adminRequest']) && is_bool($args['adminRequest']) && $args['adminRequest']);

        if ($args['idfield'] == 'email') {
            $query = $this->entityManager->createQueryBuilder()
                                         ->select('count(u.uid)')
                                         ->from('ZikulaUsersModule:UserEntity', 'u')
                                         ->where('u.email = :email')
                                         ->setParameter('email', $args['id'])
                                         ->getQuery();

            $ucount = (int)$query->getSingleScalarResult();

            if ($ucount > 1) {
                return false;
            }
        }

        $userObj = UserUtil::getVars($args['id'], true, $args['idfield']);

        if ($userObj) {
            $authenticationMethods = UserUtil::getUserAccountRecoveryInfo($userObj['uid']);

            $view = Zikula_View::getInstance($this->name, false);
            $viewArgs = array(
                'uname'                 => $userObj['uname'],
                'email'                 => $userObj['email'],
                'has_password'          => !empty($userObj['pass']) && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION),
                'authentication_methods' => $authenticationMethods,
                'sitename'              => System::getVar('sitename'),
                'hostname'              => System::serverGetVar('REMOTE_ADDR'),
                'url'                   => $this->getContainer()->get('router')->generate('zikulausersmodule_user_login', array(), RouterInterface::ABSOLUTE_URL),
                'adminRequested'        => $adminRequested,
            );
            $view->assign($viewArgs);
            $htmlBody = $view->fetch('Email/lostuname_html.tpl');
            $plainTextBody = $view->fetch('Email/lostuname_txt.tpl');

            $subject = $this->__f('Account information for %s', $userObj['uname']);

            $emailMessageSent = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendMessage', array(
                'toaddress' => $userObj['email'],
                'subject'   => $subject,
                'body'      => $htmlBody,
                'altbody'   => $plainTextBody
            ));

            if (!$emailMessageSent) {
                throw new \RuntimeException($this->__('Error! Unable to send user name e-mail message.'));
            }
        }

        return $emailMessageSent;
    }

    /**
     * Send the user a lost password confirmation code.
     *
     * @param string[] $args {
     *      @type string $email The user's e-mail address.
     *                       }
     *
     * @return bool True if confirmation code sent; otherwise false.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args, or if the data cannot be loaded from the database.
     * @throws \RuntimeException Thrown if the confirmation code couldn't be created, saved or sent by e-mail
     */
    public function mailConfirmationCode($args)
    {
        $emailMessageSent = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield'])
                || (($args['idfield'] != 'uname') && ($args['idfield'] != 'email') && ($args['idfield'] != 'uid'))
                ) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if ($args['idfield'] == 'email') {
            $ucount = UserUtil::getEmailUsageCount($args['id']);

            if ($ucount > 1) {
                return false;
            }
        }

        $adminRequested = (isset($args['adminRequest']) && is_bool($args['adminRequest']) && $args['adminRequest']);

        $user = UserUtil::getVars($args['id'], true, $args['idfield']);

        if ($user) {
            $confirmationCode = UserUtil::generatePassword();
            $hashedConfirmationCode = UserUtil::getHashedPassword($confirmationCode);

            if ($hashedConfirmationCode !== false) {
                $query = $this->entityManager->createQueryBuilder()
                                             ->delete()
                                             ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                                             ->where('v.uid = :uid')
                                             ->andWhere('v.changetype = :changetype')
                                             ->setParameter('uid', $user['uid'])
                                             ->setParameter('changetype', UsersConstant::VERIFYCHGTYPE_PWD)
                                             ->getQuery();
                $query->getResult();

                $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));

                $codeSaved = new \Zikula\UsersModule\Entity\UserVerificationEntity();
                $codeSaved['changetype'] = UsersConstant::VERIFYCHGTYPE_PWD;
                $codeSaved['uid'] = $user['uid'];
                $codeSaved['newemail'] = '';
                $codeSaved['verifycode'] = $hashedConfirmationCode;
                $codeSaved['created_dt'] = $nowUTC->format(UsersConstant::DATETIME_FORMAT);
                $this->entityManager->persist($codeSaved);
                $this->entityManager->flush();

                if ($codeSaved) {
                    $urlArgs = array();
                    $urlArgs['code'] = $confirmationCode;
                    $urlArgs[$args['idfield']] = $args['id'];

                    $view = Zikula_View::getInstance($this->name, false);
                    $viewArgs = array(
                        'uname'         => $user['uname'],
                        'sitename'      => System::getVar('sitename'),
                        'hostname'      => System::serverGetVar('REMOTE_ADDR'),
                        'code'          => $confirmationCode,
                        'url'           => $this->getContainer()->get('router')->generate('zikulausersmodule_user_lostpasswordcode', $urlArgs, RouterInterface::ABSOLUTE_URL),
                        'adminRequested' => $adminRequested,
                    );
                    $view->assign($viewArgs);
                    $htmlBody = $view->fetch('Email/lostpassword_html.tpl');
                    $plainTextBody = $view->fetch('Email/lostpassword_txt.tpl');

                    $subject = $this->__f('Confirmation code for %s', $user['uname']);

                    $emailMessageSent = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendMessage', array(
                        'toaddress' => $user['email'],
                        'subject'   => $subject,
                        'body'      => $htmlBody,
                        'altbody'   => $plainTextBody
                    ));

                    if (!$emailMessageSent) {
                        throw new \RuntimeException($this->__('Error! Unable to send confirmation code e-mail message.'));
                    }
                } else {
                    throw new \RuntimeException($this->__('Error! Unable to save confirmation code.'));
                }
            } else {
                throw new \RuntimeException($this->__("Error! Unable to create confirmation code."));
            }
        }

        return $emailMessageSent;
    }

    /**
     * Check a lost password confirmation code.
     *
     * @param string[] $args {
     *      @type string $idfield Either 'uname' or 'email'.
     *      @type string $id      The user's user name or e-mail address, depending on the value of idfield.
     *      @type string $code    The confirmation code.
     *                       }
     *
     * @return bool True if the new password was sent; otherwise false.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args, or if the data cannot be loaded from the database.
     * @throws \RuntimeException Thrown if the confirmation code couldn't be obtained
     */
    public function checkConfirmationCode($args)
    {
        $codeIsGood = false;

        if (!isset($args['id']) || empty($args['id']) || !isset($args['idfield']) || empty($args['idfield']) || !isset($args['code'])
                || empty($args['code']) || (($args['idfield'] != 'uname') && ($args['idfield'] != 'email'))) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $user = UserUtil::getVars(urldecode($args['id']), true, $args['idfield']);

        if (!$user) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        } else {
            // delete all the records for password reset confirmation that have expired
            $chgPassExpireDays = $this->getVar(UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
            if ($chgPassExpireDays > 0) {
                $staleRecordUTC = new \DateTime(null, new \DateTimeZone('UTC'));
                $staleRecordUTC->modify("-{$chgPassExpireDays} days");
                $staleRecordUTCStr = $staleRecordUTC->format(UsersConstant::DATETIME_FORMAT);

                $query = $this->entityManager->createQueryBuilder()
                                             ->delete()
                                             ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                                             ->where('v.created_dt < :staleRecordUTCStr')
                                             ->andWhere('v.changetype = :changetype')
                                             ->setParameter('staleRecordUTCStr', $staleRecordUTCStr)
                                             ->setParameter('changetype', UsersConstant::VERIFYCHGTYPE_PWD)
                                             ->getQuery();
                $query->getResult();
            }

            $verifychgObj = $this->entityManager->getRepository('ZikulaUsersModule:UserVerificationEntity')->findOneBy(array('uid' => $user['uid'], 'changetype' => UsersConstant::VERIFYCHGTYPE_PWD));
            if ($verifychgObj) {
                $codeIsGood = UserUtil::passwordsMatch($args['code'], $verifychgObj['verifycode']);
            } else {
                throw new \RuntimeException('Sorry! Could not retrieve a confirmation code for that account.');
            }
        }

        return $codeIsGood;
    }

    /**
     * Display a message indicating that the user's session has expired.
     *
     * @return string The rendered template.
     */
    public function expiredSession()
    {
        $view = Zikula_View::getInstance($this->name, false);
        $view->assign('returnpage', urlencode(System::getCurrentUri()));

        return $view->fetch('User/api_expiredsession.tpl');
    }

    /**
     * Retrieve the account links for each user module.
     *
     * @return array An array of links for the user account page.
     */
    public function accountLinks()
    {
        // Get all user modules
        $mods = ModUtil::getAllMods();

        if ($mods == false) {
            return false;
        }

        $accountlinks = array();

        foreach ($mods as $mod) {
            // saves 17 system checks
            if ($mod['type'] == 3 && !in_array($mod['name'], array('ZikulaAdminModule', 'ZikulaCategoriesModule', 'ZikulaGroupsModule', 'ZikulaThemeModule', $this->name))) {
                continue;
            }

            $modpath = ($mod['type'] == ModUtil::TYPE_SYSTEM) ? 'system' : 'modules';

            $module = ModUtil::getModule($this->name);

            $ooAccountApiFileNs = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/Api/AccountApi.php");
            $ooAccountApiFile = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/Api/Account.php");
            $ooAccountApiFileOld = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/lib/{$mod['directory']}/Api/Account.php");
            $legacyAccountApiFile = DataUtil::formatForOS("{$modpath}/{$mod['directory']}/pnaccountapi.php");
            if (null !== $module && class_exists($module->getNamespace().'\\Api\\AccountApi') || file_exists($ooAccountApiFileNs) || file_exists($ooAccountApiFile) || file_exists($ooAccountApiFileOld) || file_exists($legacyAccountApiFile)) {
                $items = ModUtil::apiFunc($mod['name'], 'account', 'getAll');
                if ($items) {
                    foreach ($items as $k => $item) {
                        // check every retured link for permissions
                        if (SecurityUtil::checkPermission('ZikulaUsersModule::', "$mod[name]::$item[title]", ACCESS_READ)) {
                            if (!isset($item['module'])) {
                                $item['module']  = $mod['name'];
                            }
                            // insert the indexed item
                            $accountlinks["$mod[name]{$k}"] = $item;
                        }
                    }
                }
            } else {
                $items = false;
            }
        }

        return $accountlinks;
    }

    /**
     * Save the preliminary user e-mail until user's confirmation.
     *
     * @param string[] $args {
     *      @type string $newemail The new e-mail address to store pending confirmation.
     *                       }
     *
     * @return bool True if success and false otherwise.
     *
     * @throws AccessDeniedException Thrown if the current user is logged in.
     */
    public function savePreEmail($args)
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        $nowUTC = new \DateTime(null, new \DateTimeZone('UTC'));

        $uid = UserUtil::getVar('uid');
        $uname = UserUtil::getVar('uname');

        // generate a randomize value of 7 characters needed to confirm the e-mail change
        $confirmCode = UserUtil::generatePassword();
        $confirmCodeHash = UserUtil::getHashedPassword($confirmCode);

        $query = $this->entityManager->createQueryBuilder()
                                     ->delete()
                                     ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                                     ->where('v.uid = :uid')
                                     ->andWhere('v.changetype = :changetype')
                                     ->setParameter('uid', $uid)
                                     ->setParameter('changetype', UsersConstant::VERIFYCHGTYPE_EMAIL)
                                     ->getQuery();
        $query->getResult();

        $obj = new \Zikula\UsersModule\Entity\UserVerificationEntity();
        $obj['changetype'] = UsersConstant::VERIFYCHGTYPE_EMAIL;
        $obj['uid'] = $uid;
        $obj['newemail'] = $args['newemail'];
        $obj['verifycode'] = $confirmCodeHash;
        $obj['created_dt'] = $nowUTC->format(UsersConstant::DATETIME_FORMAT);
        $this->entityManager->persist($obj);
        $this->entityManager->flush();

        // send confirmation e-mail to user with the changing code
        $subject = $this->__f('Confirmation change of e-mail for %s', $uname);

        $view = Zikula_View::getInstance($this->name, false);
        $viewArgs = array(
            'uname'     => $uname,
            'email'     => UserUtil::getVar('email'),
            'newemail'  => $args['newemail'],
            'sitename'  => System::getVar('sitename'),
            'url'       => $this->getContainer()->get('router')->generate('zikulausersmodule_user_confirmchemail', array('confirmcode' => $confirmCode), RouterInterface::ABSOLUTE_URL),
        );
        $view->assign($viewArgs);

        $message = $view->fetch('Email/userverifyemail_html.tpl');
        $sent = ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendMessage', array(
            'toaddress' => $args['newemail'],
            'subject'   => $subject,
            'body'      => $message,
            'html'      => true
        ));

        if (!$sent) {
            return false;
        }

        return true;
    }

    /**
     * Retrieve the user's new e-mail address that is awaiting his confirmation.
     *
     * @return string The e-mail address waiting for confirmation for the current user.
     *
     * @throws AccessDeniedException Thrown if the current user is logged in.
     */
    public function getUserPreEmail()
    {
        if (!UserUtil::isLoggedIn()) {
            throw new AccessDeniedException();
        }

        // delete all the records from e-mail confirmation that have expired
        $chgEmailExpireDays = $this->getVar(UsersConstant::MODVAR_EXPIRE_DAYS_CHANGE_EMAIL, UsersConstant::DEFAULT_EXPIRE_DAYS_CHANGE_EMAIL);
        if ($chgEmailExpireDays > 0) {
            $staleRecordUTC = new \DateTime(null, new \DateTimeZone('UTC'));
            $staleRecordUTC->modify("-{$chgEmailExpireDays} days");
            $staleRecordUTCStr = $staleRecordUTC->format(UsersConstant::DATETIME_FORMAT);

            $query = $this->entityManager->createQueryBuilder()
                                         ->delete()
                                         ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                                         ->where('v.created_dt < :staleRecordUTCStr')
                                         ->andWhere('v.changetype = :changetype')
                                         ->setParameter('staleRecordUTCStr', $staleRecordUTCStr)
                                         ->setParameter('changetype', UsersConstant::VERIFYCHGTYPE_PWD)
                                         ->getQuery();
            $query->getResult();
        }

        $uid = UserUtil::getVar('uid');

        $item = $this->entityManager->getRepository('ZikulaUsersModule:UserVerificationEntity')->findOneBy(array('uid' => $uid, 'changetype' => UsersConstant::VERIFYCHGTYPE_EMAIL));

        return $item;
    }

    /**
     * Removes a record from the users_verifychg table for a specified uid and changetype.
     *
     * @param mixed[] $args {
     *      @type int       $uid        The uid of the verifychg record to remove. Required.
     *      @type int|array $changetype The changetype(s) of the verifychg record to remove. If more
     *                                  than one type is to be removed, use an array. Optional. If
     *                                  not specifed, all verifychg records for the user will be
     *                                  removed. Note: specifying an empty array will remove none.
     *                      }
     *
     * @return void|bool Null on success, false on error.
     *
     * @throws \InvalidArgumentException Thrown if invalid parameters are received in $args.
     */
    public function resetVerifyChgFor($args)
    {
        if (!isset($args['uid'])) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        $uid = $args['uid'];

        if (!is_numeric($uid) || ((int)$uid != $uid) || ($uid <= 1)) {
            throw new \InvalidArgumentException(__('Invalid arguments array received'));
        }

        if (!isset($args['changetype'])) {
            $changeType = null;
        } else {
            $changeType = $args['changetype'];
            if (!is_array($changeType)) {
                $changeType = array($changeType);
            } elseif (empty($changeType)) {
                return;
            }
            foreach ($changeType as $theType) {
                if (!is_numeric($theType) || ((int)$theType != $theType) || ($theType < 0)) {
                    throw new \InvalidArgumentException(__('Invalid arguments array received'));
                }
            }
        }

        $qb = $this->entityManager->createQueryBuilder()
                                  ->delete()
                                  ->from('ZikulaUsersModule:UserVerificationEntity', 'v')
                                  ->where('v.uid = :uid')
                                  ->setParameter('uid', $uid);
        if (isset($changeType)) {
            $qb->andWhere($qb->expr()->in('v.changetype', ':changeType'))
               ->setParameter('changeType', $changeType);
        }
        $query = $qb->getQuery();
        $query->getResult();
    }

    /**
     * Get available user menu links.
     *
     * @return array An array of menu links.
     */
    public function getLinks()
    {
        $messageModule = System::getVar('messagemodule', '');
        $profileModule = System::getVar('profilemodule', '');
        $isLoggedIn = UserUtil::isLoggedIn();

        $links = array();

        // we do not check permissions for guests here (see #1874)
        if (!$isLoggedIn/* && SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)*/) {
            $links[] = array(
                'icon' => 'sign-in',
                'text' => $this->__('Log in'),
                'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_login')
            );

            $links[] = array(
                'icon' => 'key',
                'links' => array(
                    array(
                        'text' => $this->__('Recover Lost User Name'),
                        'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_lostuname')
                    ),
                    array(
                        'text' => $this->__('Recover Lost Password'),
                        'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_lostpassword')
                    ),
                    array(
                        'text' => $this->__('Enter Password Recovery Code'),
                        'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_lostpasswordcode')
                    )
                ),
                'text' => $this->__('Recover account information or password'),
                'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_lostpwduname'),
            );

            if ($this->getVar('reg_allowreg')) {
                $links[] = array(
                    'icon' => 'plus',
                    'text'  => $this->__('New account'),
                    'url'   => $this->getContainer()->get('router')->generate('zikulausersmodule_registration_selectregistrationmethod')
                );
            }
        }

        if ($isLoggedIn && SecurityUtil::checkPermission($this->name.'::', '::', ACCESS_READ)) {
            $links[] = array(
                'icon' => 'wrench',
                'text' => $this->__('Account Settings'),
                'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_index')
            );
        }

        if ($isLoggedIn && !empty($profileModule) && ModUtil::available($profileModule) && SecurityUtil::checkPermission($profileModule . '::', '::', ACCESS_READ)) {
            $links[] = array(
                'text' => $this->__('Profile'),
                'url' => ModUtil::url($profileModule, 'user', 'view'),
                'icon' => 'user',
                'links' => array(
                    array(
                        'text' => $this->__('Edit Profile'),
                        'url' => ModUtil::url($profileModule, 'user', 'modify')
                    ),
                    array(
                        'text' => $this->__('Change Email Address'),
                        'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_changeemail')
                    ),
                    array(
                        'text' => $this->__('Change Password'),
                        'url' => $this->getContainer()->get('router')->generate('zikulausersmodule_user_changepassword')
                    )
                )
            );
        }

        if ($isLoggedIn && !empty($messageModule) && ModUtil::available($messageModule) && SecurityUtil::checkPermission($messageModule . '::', '::', ACCESS_READ)) {
            $links[] = array(
                'icon' => 'envelope',
                'text' => $this->__('Messages'),
                'url' => ModUtil::url($messageModule, 'user', 'main')
            );
        }

        if (!empty($profileModule) && ModUtil::available($profileModule) && SecurityUtil::checkPermission($profileModule . ':Members:', '::', ACCESS_READ)) {
            $links[9999] = array(
                'icon' => 'list',
                'text' => $this->__('Registered Users'),
                'url' => ModUtil::url($profileModule, 'user', 'viewmembers')
            );

            if (SecurityUtil::checkPermission($profileModule . ':Members:recent', '::', ACCESS_READ)) {
                $links[9999]['links'][] = array(
                    'text' => $this->__f('Last %s Registered Users', ModUtil::getVar($profileModule, 'recentmembersitemsperpage')),
                    'url' => ModUtil::url($profileModule, 'user', 'recentmembers')
                );
            }

            if (SecurityUtil::checkPermission($profileModule . ':Members:online', '::', ACCESS_READ)) {
                $links[9999]['links'][] = array(
                    'text' => $this->__('Users Online'),
                    'url' => ModUtil::url($profileModule, 'user', 'online')
                );
            }
        }

        return $links;
    }

    /**
     * Convenience function for several functions; converts registration errors into easily displayable sets of data.
     *
     * @param array[] $args {
     *      @type array $registrationErrors The array of registration errors from getRegistrationErrors or one of its related functions.
     *                      }
     *
     * @return array Modified error information.
     */
    public function processRegistrationErrorsForDisplay($args)
    {
        $errorFields = array();
        $errorMessages = array();

        if (isset($args['registrationErrors']) && is_array($args['registrationErrors']) && !empty($args['registrationErrors'])) {
            $registrationErrors = $args['registrationErrors'];

            foreach ($registrationErrors as $field => $messageList) {
                $errorFields[$field] = true;
                $errorMessages = array_merge($errorMessages, is_array($messageList) ? $messageList : array($messageList));
            }
        }

        return array(
            'errorFields' => $errorFields,
            'errorMessages' => $errorMessages,
        );
    }
}
