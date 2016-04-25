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
use DateUtil;
use SecurityUtil;
use System;
use ModUtil;
use UserUtil;
use Zikula;
use Zikula_View;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Event\GenericEvent;
use Zikula\UsersModule\Constant as UsersConstant;

/**
 * The administrative system-level and database-level functions for the Users module.
 */
class AdminApi extends \Zikula_AbstractApi
{
    /**
     * Find users.
     *
     * @param mixed[] $args {
     *      @type string $uname         A fragment of a user name on which to search using an SQL
     *                                  LIKE clause. The user name will be surrounded by wildcards.
     *      @type int    $ugroup        A group id in which to search (only users who are members of
     *                                  the specified group are returned).
     *      @type string $email         A fragment of an e-mail address on which to search using an
     *                                  SQL LIKE clause. The e-mail address will be surrounded by
     *                                  wildcards.
     *      @type string $regdateafter  An SQL date-time (in the form '1970-01-01 00:00:00'); only
     *                                  user accounts with a registration date after the date
     *                                  specified will be returned.
     *      @type string $regdatebefore An SQL date-time (in the form '1970-01-01 00:00:00'); only
     *                                  user accounts with a registration date before the date
     *                                  specified will be returned.
     *      @type array  $dynadata      An array of search values to be passed to the designated
     *                                  profile module. Only those user records also satisfying the
     *                                  profile module's search of its data are returned.
     *      @type string $condition     An SQL condition for finding users; overrides all other
     *                                  parameters.
     *                      }
     *
     * @return array|bool array of items if succcessful, false otherwise
     */
    public function findUsers($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_READ)) {
            return false;
        }

        $profileModule = System::getVar('profilemodule', '');
        $useProfileMod = (!empty($profileModule) && ModUtil::available($profileModule));

        $qb = $this->entityManager->createQueryBuilder()
                                  ->select('u')
                                  ->from('ZikulaUsersModule:UserEntity', 'u')
                                  ->orderBy('u.uname', 'ASC');

        if (!isset($args['condition']) || !$args['condition']) {
            // Do not include anonymous user
            $where[] = "u.uid <> 1";

            foreach ($args as $arg => $value) {
                if ($value) {
                    switch ($arg) {
                        case 'uname':
                            $qb->andWhere($qb->expr()->like('u.uname', ':value'))
                                ->setParameter('value', $value);
                            break;

                        case 'email':
                            $qb->andWhere($qb->expr()->like('u.email', ':value'))
                               ->setParameter('value', $value);
                            break;

                        case 'ugroup':
                            $uidList = UserUtil::getUsersForGroup($value);
                            if (is_array($uidList) && !empty($uidList)) {
                                $qb->andWhere($qb->expr()->in('u.uid', ':uids'))
                                   ->setParameter('uids', $uidList);
                            }
                            break;

                        case 'regdateafter':
                            $qb->andWhere('u.user_regdate > :value')
                               ->setParameter('value', $value);
                            break;

                        case 'regdatebefore':
                            $qb->andWhere('u.user_regdate < :value')
                               ->setParameter('value', $value);
                            break;

                        case 'dynadata':
                            if ($useProfileMod) {
                                $uidList = ModUtil::apiFunc($profileModule, 'user', 'searchDynadata', array('dynadata' => $value));
                                if (is_array($uidList) && !empty($uidList)) {
                                    $qb->andWhere($qb->expr()->in('u.uid', ':uids'))
                                       ->setParameter('uids', $uidList);
                                }
                            }
                            break;

                        default:
                            // Skip unknown values--do nothing, and no error--might be other legitimate arguments.
                    }
                }
            }
        }

        // TODO - Should this exclude pending delete too?
        $qb->andWhere('u.activated <> :statusfilter')
           ->setParameter('statusfilter', UsersConstant::ACTIVATED_PENDING_REG);

        $query = $qb->getQuery();
        $objArray = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        return $objArray;
    }

    /**
     * Delete one or more user account records, or mark one or more account records for deletion.
     *
     * If records are marked for deletion, they remain in the system and accessible by the system, but are given an
     * 'activated' status that prevents the user from logging in. Records marked for deletion will not appear on the
     * regular users list. The delete hook and delete events are not triggered if the records are only marked for
     * deletion.
     *
     * @param mixed[] $args {
     *      @type numeric|array $uid  A single (numeric integer) user id, or an array of user ids to delete.
     *      @type boolean       $mark If true, then mark for deletion, but do not actually delete. defaults to false.
     *                      }
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True if successful, false otherwise.
     *
     * @throws \InvalidArgumentException Thrown if uid is either not set or invalid
     */
    public function deleteUser($args)
    {
        if (!SecurityUtil::checkPermission("{$this->name}::", 'ANY', ACCESS_DELETE)) {
            return false;
        }

        if (!isset($args['uid']) || (!is_numeric($args['uid']) && !is_array($args['uid']))) {
            throw new \InvalidArgumentException("Error! Illegal argument were passed to 'deleteuser'");
        }

        if (isset($args['mark']) && is_bool($args['mark'])) {
            $markOnly = $args['mark'];
        } else {
            $markOnly = false;
        }

        // ensure we always have an array
        if (!is_array($args['uid'])) {
            $args['uid'] = array($args['uid']);
        }

        $curUserUid = UserUtil::getVar('uid');
        $userList = array();
        foreach ($args['uid'] as $uid) {
            if (!is_numeric($uid) || ((int)$uid != $uid) || ($uid == $curUserUid)) {
                return false;
            }

            $userObj = UserUtil::getVars($uid);

            if (!$userObj) {
                return false;
            } elseif (!SecurityUtil::checkPermission("{$this->name}::", "{$userObj['uname']}::{$userObj['uid']}", ACCESS_DELETE)) {
                return false;
            }

            $userList[] = $userObj;
        }

        foreach ($userList as $userObj) {
            if ($markOnly) {
                UserUtil::setVar('activated', UsersConstant::ACTIVATED_PENDING_DELETE, $userObj['uid']);
            } else {
                // delete verification records for this user
                ModUtil::apiFunc($this->name, 'user', 'resetVerifyChgFor', array('uid' => $userObj['uid']));

                // delete session
                $query = $this->entityManager->createQueryBuilder()
                                             ->delete()
                                             ->from('ZikulaUsersModule:UserSessionEntity', 'u')
                                             ->where('u.uid = :uid')
                                             ->setParameter('uid', $userObj['uid'])
                                             ->getQuery();
                $query->getResult();

                // delete user
                $user = $this->entityManager->find('ZikulaUsersModule:UserEntity', $userObj['uid']);
                $user->removeGroups();
                $this->entityManager->remove($user);
                $this->entityManager->flush();

                // Let other modules know we have deleted an item
                $deleteEvent = new GenericEvent($userObj);
                $this->getDispatcher()->dispatch('user.account.delete', $deleteEvent);
            }
        }

        return $args['uid'];
    }

    /**
     * Send an e-mail message to one or more users.
     *
     * @param mixed[] $args {
     *      @type numeric|array $uid A single (numeric integer) uid or an array of uids to which the e-mail should be sent.
     *      @type mixed[] $sendmail {
     *          @type string        $from            The name of the e-mail message's sender.
     *          @type string        $rpemail         The e-mail address of the e-mail message's sender.
     *          @type string        $subject         The e-mail message's subject.
     *          @type string        $message         The e-mail message's body (the message itself).
     *          @type array         $recipientsname  An array indexed by uid of each recipient's name.
     *          @type array         $recipientsemail An array indexed by uid of each recipient's e-mail address.
     *                              }
     *                      }
     *
     * @return bool True on success; otherwise false
     *
     * @throws \InvalidArgumentException Thrown if either the uid or sendmail parameters weren't provided or were invalid
     * @throws AccessDeniedException Thrown if the current user does not have sufficient access to send mail.
     * @throws \RuntimeException Thrown if the e-mail message couldn't be sent
     */
    public function sendmail($args)
    {
        if (!SecurityUtil::checkPermission("{$this->name}::MailUsers", '::', ACCESS_COMMENT)) {
            throw new AccessDeniedException();
        }

        if (isset($args['uid']) && !empty($args['uid'])) {
            if (is_array($args['uid'])) {
                $recipientUidList = $args['uid'];
            } else {
                $recipientUidList = array($args['uid']);
            }
        } else {
            throw new \InvalidArgumentException(__('Error! No users selected to receive e-mail, or invalid uid list.'));
        }

        if (isset($args['sendmail']) && !empty($args['sendmail']) && is_array($args['sendmail'])) {
            $sendmail = $args['sendmail'];
        } else {
            throw new \InvalidArgumentException(__('Error! E-mail message to be sent not specified or invalid.'));
        }

        $missingFields = array();
        if (empty($sendmail['from'])) {
            $missingFields[] = 'from';
        }
        if (empty($sendmail['rpemail'])) {
            $missingFields[] = 'reply-to e-mail address';
        }
        if (empty($sendmail['subject'])) {
            $missingFields[] = 'subject';
        }
        if (empty($sendmail['message'])) {
            $missingFields[] = 'message';
        }
        if (!empty($missingFields)) {
            $count = count($missingFields);
            $msg = _fn('Error! The required field \'%2$s\' was blank or missing',
                    'Error! %1$d required fields were blank or missing: \'%2$s\'.',
                    $count, array($count, implode("', '", $missingFields)));
            throw new \InvalidArugmentsException($msg);
        }
        unset($missingFields);

        // Set default email format from Mailer, if not specified
        $html = null;
        if ($sendmail['format'] == 'html') {
            $html = true;
        } elseif ($sendmail['format'] == 'text') {
            $html = false;
        }

        $bcclist = array();
        $recipientlist = array();
        $recipientscount = 0;
        foreach ($sendmail['recipientsemail'] as $uid => $recipient) {
            if (in_array($uid, $recipientUidList)) {
                $bcclist[] = array('name'    => $sendmail['recipientsname'][$uid],
                                   'address' => $recipient);
                $recipientlist[] = $recipient;
            }
            if (count($bcclist) == $sendmail['batchsize']) {
                if (ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendmessage',
                                 array('fromname'       => $sendmail['from'],
                                       'fromaddress'    => $sendmail['rpemail'],
                                       'toname'         => UserUtil::getVar('uname'),
                                       'toaddress'      => UserUtil::getVar('email'),
                                       'replytoname'    => UserUtil::getVar('uname'),
                                       'replytoaddress' => $sendmail['rpemail'],
                                       'subject'        => $sendmail['subject'],
                                       'body'           => $sendmail['message'],
                                       'html'           => $html,
                                       'bcc'            => $bcclist)) == true) {
                    $recipientscount += count($bcclist);
                    $bcclist = array();
                } else {
                    throw new \RuntimeException($this->__('Error! Could not send the e-mail message.'));
                }
            }
        }
        if (count($bcclist) != 0) {
            $sendMessageArgs = array(
                'fromname'      => $sendmail['from'],
                'fromaddress'   => $sendmail['rpemail'],
                'toname'        => UserUtil::getVar('uname'),
                'toaddress'     => UserUtil::getVar('email'),
                'replytoname'   => UserUtil::getVar('uname'),
                'replytoaddress' => $sendmail['rpemail'],
                'subject'       => $sendmail['subject'],
                'body'          => $sendmail['message'],
                'html'          => $html,
                'bcc'           => $bcclist,
            );
            if (ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendMessage', $sendMessageArgs)) {
                $recipientscount += count($bcclist);
            } else {
                throw new \RuntimeException($this->__('Error! Could not send the e-mail message.'));
            }
        }
        if ($recipientscount > 0) {
            $this->registerStatus($this->_fn(
                'Done! E-mail message has been sent to %1$d user. ',
                'Done! E-mail message has been sent to %1$d users. ',
                $recipientscount,
                array($recipientscount)
            ));
        }

        return true;
    }

    /**
     * Get available admin panel links.
     *
     * @return array Array of admin links.
     */
    public function getLinks()
    {
        $links = array();

        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_MODERATE)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_view'),
                'text' => $this->__('Users list'),
                'icon' => 'list');
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_MODERATE)) {
            $pending = ModUtil::apiFunc($this->name, 'registration', 'countAll');
            if ($pending) {
                $links[] = array(
                    'url' => $this->get('router')->generate('zikulausersmodule_admin_viewregistrations'),
                    'text' => $this->__('Pending registrations') . ' ('.DataUtil::formatForDisplay($pending).')',
                    'icon' => 'plus');
            }
        }

        // To create a new user (or import users) when registration is enabled, ADD access is required.
        // If registration is disabled, then ADMIN access required.
        // ADMIN access is always required for exporting the users.
        if ($this->getVar(UsersConstant::MODVAR_REGISTRATION_ENABLED, false)) {
            $createUserAccessLevel = ACCESS_ADD;
        } else {
            $createUserAccessLevel = ACCESS_ADMIN;
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', $createUserAccessLevel)) {
            $submenulinks = array();
            $submenulinks[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_newuser'),
                'text' => $this->__('Create new user'));
            $submenulinks[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_import'),
                'text' => $this->__('Import users'));
            if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_ADMIN)) {
                $submenulinks[] = array(
                     'url' => $this->get('router')->generate('zikulausersmodule_admin_exporter'),
                     'text' => $this->__('Export users'));
            }
            $links[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_newuser'),
                'text' => $this->__('Create new user'),
                'icon' => 'plus',
                'links' => $submenulinks);
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_MODERATE)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_search'),
                'text' => $this->__('Find users'),
                'icon' => 'search');
        }
        if (SecurityUtil::checkPermission('ZikulaUsersModule::MailUsers', '::', ACCESS_MODERATE)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_mailusers'),
                'text' => $this->__('E-mail users'),
                'icon' => 'envelope');
        }
        if (SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_ADMIN)) {
            $links[] = array(
                'url' => $this->get('router')->generate('zikulausersmodule_admin_config'),
                'text' => $this->__('Settings'),
                'icon' => 'wrench');
        }

        return $links;
    }

    /**
     * Retrieve a list of users whose field specified by the key match one of the values specified in the keyValue.
     *
     * @param mixed[] $args {
     *      @type string $key         The field to be searched, typically 'uname' or 'email'.
     *      @type array  $valuesarray An array containing the values to be matched.
     *                      }
     *
     * @return array|bool An array of user records indexed by user name, each whose key field matches one value in the
     *                      valueArray; false on error.
     */
    public function checkMultipleExistence($args)
    {
        // Need read access to call this function
        if (!SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_READ)) {
            return false;
        }

        $valuesArray = $args['valuesarray'];
        $key = $args['key'];

        $dql = "SELECT u FROM Zikula\UsersModule\Entity\UserEntity u WHERE u.$key IN ('" . implode("', '", $valuesArray) . "')";
        $query = $this->entityManager->createQuery($dql);
        $users = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $userArr = array();
        foreach ($users as $user) {
            $userArr[$user['uname']] = $user;
        }

        return $userArr;
    }

    /**
     * Add new user accounts from the import process.
     *
     * @param array[] $args {
     *      @type array $importvalues An array of information used to create new user records. Each element of the
     *                                array should represent the minimum information to create a user record, including
     *                                'uname', 'email', 'pass', etc.
     *                      }
     *
     * @param array $args All parameters passed to this function.
     *
     * @return bool True on success; false otherwise.
     *
     * @throws \RuntimeException Thrown if the registration e-mail couldn't be sent or
     *                                  if the users, following addition to the database, couldn't be retrieved again or
     *                                  if the users couldn't be added to any groups
     */
    public function createImport($args)
    {
        // Need add access to call this function
        if (!SecurityUtil::checkPermission("{$this->name}::", '::', ACCESS_ADD)) {
            return false;
        }

        $importValues = $args['importvalues'];

        if (empty($importValues)) {
            return false;
        }

        // Prepare arrays.
        $usersArray = array();
        foreach ($importValues as $key => $value) {
            $usersArray[] = $value['uname'];
            if (!$value['activated']) {
                $importValues[$key]['activated'] = UsersConstant::ACTIVATED_PENDING_REG;
            }
        }

        $importValuesDB = $importValues;
        foreach ($importValuesDB as $key => $value) {
            $importValuesDB[$key]['pass'] = UserUtil::getHashedPassword($importValuesDB[$key]['pass']);
        }

        // create users
        foreach ($importValuesDB as $importValueDB) {
            $user = new \Zikula\UsersModule\Entity\UserEntity();
            $user->merge($importValueDB);
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();

        // get users. We need the users identities set them into their groups
        $usersInDB = ModUtil::apiFunc($this->name, 'admin', 'checkMultipleExistence',
                                      array('valuesarray' => $usersArray,
                                            'key' => 'uname'));
        if (!$usersInDB) {
            throw new \RuntimeException($this->__(
                'Error! The users have been created but something has failed trying to get them from the database. Now all these users do not have group.'));
        }

        // add user to groups
        $error_membership = false;
        foreach ($importValues as $value) {
            $groupsArray = explode('|', $value['groups']);
            foreach ($groupsArray as $group) {
                $adduser = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'adduser', array('gid' => $group, 'uid' => $usersInDB[$value['uname']]['uid'], 'verbose' => false));
                if (!$adduser) {
                    $error_membership = true;
                }
            }
        }

        if ($error_membership) {
            throw new \RuntimeException($this->__('Error! The users have been created but something has failed while trying to add the users to their groups. These users are not assigned to a group.'));
        }

        // check if module Mailer is active
        $modinfo = ModUtil::getInfoFromName('ZikulaMailerModule');
        if ($modinfo['state'] == ModUtil::TYPE_SYSTEM) {
            $sitename  = System::getVar('sitename');
            $siteurl   = System::getBaseUrl();

            $view = Zikula_View::getInstance($this->name, false);
            $view->assign('sitename', $sitename);
            $view->assign('siteurl', $siteurl);

            foreach ($importValues as $value) {
                if ($value['activated'] != UsersConstant::ACTIVATED_PENDING_REG) {
                    $createEvent = new GenericEvent($value);
                    $this->getDispatcher()->dispatch('user.account.create', $createEvent);
                } else {
                    $createEvent = new GenericEvent($value);
                    $this->getDispatcher()->dispatch('user.registration.create', $createEvent);
                }
                if ($value['activated'] && $value['sendmail']) {
                    $view->assign('email', $value['email']);
                    $view->assign('uname', $value['uname']);
                    $view->assign('pass', $value['pass']);
                    $message = $view->fetch('Email/importnotify_html.tpl');
                    $subject = $this->__f('Password for %1$s from %2$s', array($value['uname'], $sitename));
                    $sendMessageArgs = array(
                        'toaddress' => $value['email'],
                        'subject'   => $subject,
                        'body'      => $message,
                        'html'      => true,
                    );
                    if (!ModUtil::apiFunc('ZikulaMailerModule', 'user', 'sendMessage', $sendMessageArgs)) {
                        throw new \RuntimeException($this->__f('Error! A problem has occurred while sending e-mail messages. The error happened trying to send a message to the user %s. After this error, no more messages were sent.', $value['uname']));
                        break;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Extend a given user list with additional data
     *
     * @param array[] $args {
     *      @type array $groups Zikula user groups
     *            array $userList user list to extend
     *                }
     * @param array $args All parameters passed to this function.
     *
     * @return array Extended user list
     */
    public function extendUserList($args)
    {
        if (!isset($args['userList'])) {
            $args['userList'] = array();
        }

        if (!isset($args['groups'])) {
            $args['groups'] = $groups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getall');
        }

        $userList = $args['userList'];
        $userGroupsAccess = array();
        foreach ($args['groups'] as $group) {
            $userGroupsAccess[$group['gid']] = array('gid' => $group['gid']);
        }

        // Get the current user's uid
        $currentUid = UserUtil::getVar('uid');

        // Loop through each returned item adding in the options that the user has over
        // each item based on the permissions the user has.
        foreach ($userList as $key => $userObj) {
            $isCurrentUser = ($userObj['uid'] == $currentUid);
            $isGuestAccount = ($userObj['uid'] == 1);
            $isAdminAccount = ($userObj['uid'] == 2);
            $hasUsersPassword = (!empty($userObj['pass']) && ($userObj['pass'] != UsersConstant::PWD_NO_USERS_AUTHENTICATION));
            $currentUserHasModerateAccess = !$isGuestAccount && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_MODERATE);
            $currentUserHasEditAccess = !$isGuestAccount && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_EDIT);
            $currentUserHasDeleteAccess = !$isGuestAccount && !$isAdminAccount && !$isCurrentUser && SecurityUtil::checkPermission($this->name . '::', "{$userObj['uname']}::{$userObj['uid']}", ACCESS_DELETE);

            $userList[$key]['options'] = array(
                'lostUsername' => $currentUserHasModerateAccess,
                'lostPassword' => $hasUsersPassword && $currentUserHasModerateAccess,
                'toggleForcedPasswordChange' => $hasUsersPassword && $currentUserHasEditAccess,
                'modify' => $currentUserHasEditAccess,
                'deleteUsers' => $currentUserHasDeleteAccess,
            );

            if ($isGuestAccount) {
                $userList[$key]['userGroupsView'] = array();
            } else {
                // get user groups

                $userGroups = ModUtil::apiFunc('ZikulaGroupsModule', 'user', 'getusergroups', array(
                    'uid' => $userObj['uid'],
                    'clean' => 1
                ));

                // we need an associative array by the key to compare with the groups that the user can see
                $userGroupsByKey = array();
                foreach ($userGroups as $gid) {
                    $userGroupsByKey[$gid] = array('gid' => $gid);
                }

                $userList[$key]['userGroupsView'] = array_intersect_key($userGroupsAccess, $userGroupsByKey);
            }

            // format the dates
            if (!empty($userObj['user_regdate']) && ($userObj['user_regdate'] != '0000-00-00 00:00:00') && ($userObj['user_regdate'] != '1970-01-01 00:00:00')) {
                $userList[$key]['user_regdate'] = DateUtil::formatDatetime($userObj['user_regdate'], $this->__('%m-%d-%Y'));
            } else {
                $userList[$key]['user_regdate'] = '---';
            }

            if (!empty($userObj['lastlogin']) && ($userObj['lastlogin'] != '0000-00-00 00:00:00') && ($userObj['lastlogin'] != '1970-01-01 00:00:00')) {
                $userList[$key]['lastlogin'] = DateUtil::formatDatetime($userObj['lastlogin'], $this->__('%m-%d-%Y'));
            } else {
                $userList[$key]['lastlogin'] = '---';
            }

            $userList[$key]['_Users_mustChangePassword'] = (isset($userObj['__ATTRIBUTES__']) && isset($userObj['__ATTRIBUTES__']['_Users_mustChangePassword']) && $userObj['__ATTRIBUTES__']['_Users_mustChangePassword']);
        }

        return $userList;
    }
}
