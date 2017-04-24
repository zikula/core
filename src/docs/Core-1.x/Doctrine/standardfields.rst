================================
 Metadata Doctrine extension
================================

IMPORTANT NOTE: THIS FEATURE IS DEPRECATED AND REMOVED IN FUTURE VERSIONS OF ZIKULA.

Getting started
===============

Entity
------

Use @StandardFields and @Timestampable annotations to automatically update user ids and timestamps::

    use Doctrine\ORM\Mapping as ORM;
    use Gedmo\Mapping\Annotation as Gedmo;
    use Zikula\Core\Doctrine\StandardFields\Mapping\Annotation as ZK;

    /**
     * @ORM\Entity
     * @ORM\Table(name="yourmodule_user")
     */
    class YourModule_Entity_User extends Zikula_EntityAccess
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        private $id;

        /**
         * @ORM\Column(length=30)
         */
        private $username;

        /**
         * @ORM\Column(length=30)
         */
        private $password;

        /**
         * @ORM\Column(type="integer")
         * @ZK\StandardFields(type="userid", on="create")
         */
        private $createdUserId;

        /**
         * @ORM\Column(type="integer")
         * @ZK\StandardFields(type="userid", on="update")
         */
        private $updatedUserId;

        /**
         * @ORM\Column(type="datetime")
         * @Gedmo\Timestampable(on="create")
         */
        private $createdTime;

        /**
         * @ORM\Column(type="datetime")
         * @Gedmo\Timestampable(on="update")
         */
        private $updatedTime;

        // getter and setter
    }

bootstrap.php file
------------------

Add this code to the modules bootstrap.php file::

    $helper = ServiceUtil::getService('doctrine_extensions');
    $helper->getListener('timestampable');
    $helper->getListener('standardfields');

