Group Events
============

Class: `\Zikula\GroupsModule\GroupEvents`

    /**
     * Occurs after a group is created. All handlers are notified. The full group record created is available
     * as the subject.
     */
    const GROUP_CREATE = 'group.create';

    /**
     * Occurs after a group is updated. All handlers are notified. The full updated group record is available
     * as the subject.
     */
    const GROUP_UPDATE = 'group.update';

    /**
     * Occurs after a group is deleted from the system. All handlers are notified. The full group record
     * deleted is available as the subject.
     */
    const GROUP_DELETE = 'group.delete';

    /**
     * Occurs after a user is added to a group. All handlers are notified. It does not apply to pending
     * membership requests. The uid and gid are available as the subject.
     */
    const GROUP_ADD_USER = 'group.adduser';

    /**
     * Occurs after a user is removed from a group. All handlers are notified. The uid and gid are
     * available as the subject.
     */
    const GROUP_REMOVE_USER = 'group.removeuser';

    /**
     * Occurs after a group application has been processed. The subject is the GroupApplicationEntity.
     * Arguments are the form data from \Zikula\GroupsModule\Form\Type\ManageApplicationType
     */
    const GROUP_APPLICATION_PROCESSED = 'group.application.processed';

    /**
     * Occurs after the successful creation of a group application. The subject is the GroupApplicationEntity.
     */
    const GROUP_NEW_APPLICATION = 'group.application.new';
