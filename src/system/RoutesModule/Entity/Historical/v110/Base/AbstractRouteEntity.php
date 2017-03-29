<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Entity\Historical\v110\Base;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Symfony\Component\Validator\Constraints as Assert;
use Zikula\RoutesModule\Validator\Constraints as RoutesAssert;

use RuntimeException;
use ServiceUtil;
use Zikula\Core\Doctrine\EntityAccess;

/**
 * Entity class that defines the entity structure and behaviours.
 *
 * This is the base entity class for route entities.
 * The following annotation marks it as a mapped superclass so subclasses
 * inherit orm properties.
 *
 * @ORM\MappedSuperclass
 *
 * @abstract
 */
abstract class AbstractRouteEntity extends EntityAccess
{
    /**
     * @var string The tablename this object maps to
     */
    protected $_objectType = 'route';

    /**
     * @Assert\Type(type="bool")
     * @var boolean Option to bypass validation if needed
     */
    protected $_bypassValidation = false;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", unique=true)
     * @Assert\Type(type="integer")
     * @Assert\NotNull()
     * @Assert\LessThan(value=1000000000, message="Length of field value must not be higher than 9.")) {
     * @var integer $id
     */
    protected $id = 0;

    /**
     * the current workflow state
     * @ORM\Column(length=20)
     * @Assert\NotBlank()
     * @RoutesAssert\ListEntry(entityName="route", propertyName="workflowState", multiple=false)
     * @var string $workflowState
     */
    protected $workflowState = 'initial';

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank()
     * @RoutesAssert\ListEntry(entityName="route", propertyName="routeType", multiple=false)
     * @var string $routeType
     */
    protected $routeType = 'additional';

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Assert\Length(min="0", max="255")
     * @var string $replacedRouteName
     */
    protected $replacedRouteName = '';

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="255")
     * @var string $bundle
     */
    protected $bundle = '';

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="255")
     * @var string $controller
     */
    protected $controller = '';

    /**
     * @ORM\Column(name="route_action", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="255")
     * @var string $action
     */
    protected $action = '';

    /**
     * @ORM\Column(name="route_path", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(min="0", max="255")
     * @var string $path
     */
    protected $path = '';

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Assert\Length(min="0", max="255")
     * @var string $host
     */
    protected $host = '';

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank()
     * @RoutesAssert\ListEntry(entityName="route", propertyName="schemes", multiple=true)
     * @var string $schemes
     */
    protected $schemes = 'http';

    /**
     * @ORM\Column(length=255)
     * @Assert\NotBlank()
     * @RoutesAssert\ListEntry(entityName="route", propertyName="methods", multiple=true)
     * @var string $methods
     */
    protected $methods = 'GET';

    /**
     * @ORM\Column(type="boolean")
     * @Assert\IsTrue(message="This option is mandatory.")
     * @Assert\Type(type="bool")
     * @var boolean $prependBundlePrefix
     */
    protected $prependBundlePrefix = true;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\IsTrue(message="This option is mandatory.")
     * @Assert\Type(type="bool")
     * @var boolean $translatable
     */
    protected $translatable = true;

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Assert\Length(min="0", max="255")
     * @var string $translationPrefix
     */
    protected $translationPrefix = '';

    /**
     * @ORM\Column(name="route_defaults", type="array")
     * @Assert\NotBlank()
     * @Assert\Type(type="array")
     * @var array $defaults
     */
    protected $defaults = [];

    /**
     * @ORM\Column(type="array")
     * @Assert\NotNull()
     * @Assert\Type(type="array")
     * @var array $requirements
     */
    protected $requirements = [];

    /**
     * @ORM\Column(name="route_condition", length=255, nullable=true)
     * @Assert\Length(min="0", max="255")
     * @var string $condition
     */
    protected $condition = '';

    /**
     * @ORM\Column(length=255, nullable=true)
     * @Assert\Length(min="0", max="255")
     * @var string $description
     */
    protected $description = '';

    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(type="integer")
     * @Assert\Type(type="integer")
     * @Assert\NotNull()
     * @Assert\LessThan(value=2147483647, message="Length of field value must not be higher than 11.")) {
     * @var integer $sort
     */
    protected $sort = 0;

    /**
     * @Gedmo\SortableGroup
     * @ORM\Column(name="sort_group", length=255)
     * @Assert\NotNull()
     * @Assert\Length(min="0", max="255")
     * @var string $group
     */
    protected $group = '';


    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     * @Assert\Type(type="integer")
     * @var integer $createdUserId
     */
    protected $createdUserId;

    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     * @Assert\Type(type="integer")
     * @var integer $updatedUserId
     */
    protected $updatedUserId;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Assert\DateTime()
     * @var \DateTime $createdDate
     */
    protected $createdDate;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     * @Assert\DateTime()
     * @var \DateTime $updatedDate
     */
    protected $updatedDate;


    /**
     * Constructor.
     * Will not be called by Doctrine and can therefore be used
     * for own implementation purposes. It is also possible to add
     * arbitrary arguments as with every other class method.
     *
     * @param TODO
     */
    public function __construct()
    {
    }

    /**
     * Gets the _object type.
     *
     * @return string
     */
    public function get_objectType()
    {
        return $this->_objectType;
    }

    /**
     * Sets the _object type.
     *
     * @param string $_objectType
     *
     * @return void
     */
    public function set_objectType($_objectType)
    {
        $this->_objectType = $_objectType;
    }

    /**
     * Gets the _bypass validation.
     *
     * @return boolean
     */
    public function get_bypassValidation()
    {
        return $this->_bypassValidation;
    }

    /**
     * Sets the _bypass validation.
     *
     * @param boolean $_bypassValidation
     *
     * @return void
     */
    public function set_bypassValidation($_bypassValidation)
    {
        $this->_bypassValidation = $_bypassValidation;
    }


    /**
     * Gets the id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id.
     *
     * @param integer $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Gets the workflow state.
     *
     * @return string
     */
    public function getWorkflowState()
    {
        return $this->workflowState;
    }

    /**
     * Sets the workflow state.
     *
     * @param string $workflowState
     *
     * @return void
     */
    public function setWorkflowState($workflowState)
    {
        $this->workflowState = $workflowState;
    }

    /**
     * Gets the route type.
     *
     * @return string
     */
    public function getRouteType()
    {
        return $this->routeType;
    }

    /**
     * Sets the route type.
     *
     * @param string $routeType
     *
     * @return void
     */
    public function setRouteType($routeType)
    {
        $this->routeType = $routeType;
    }

    /**
     * Gets the replaced route name.
     *
     * @return string
     */
    public function getReplacedRouteName()
    {
        return $this->replacedRouteName;
    }

    /**
     * Sets the replaced route name.
     *
     * @param string $replacedRouteName
     *
     * @return void
     */
    public function setReplacedRouteName($replacedRouteName)
    {
        $this->replacedRouteName = $replacedRouteName;
    }

    /**
     * Gets the bundle.
     *
     * @return string
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Sets the bundle.
     *
     * @param string $bundle
     *
     * @return void
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    }

    /**
     * Gets the controller.
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Sets the controller.
     *
     * @param string $controller
     *
     * @return void
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Gets the action.
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets the action.
     *
     * @param string $action
     *
     * @return void
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * Gets the path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Gets the host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Sets the host.
     *
     * @param string $host
     *
     * @return void
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * Gets the schemes.
     *
     * @return string
     */
    public function getSchemes()
    {
        return $this->schemes;
    }

    /**
     * Sets the schemes.
     *
     * @param string $schemes
     *
     * @return void
     */
    public function setSchemes($schemes)
    {
        $this->schemes = $schemes;
    }

    /**
     * Gets the methods.
     *
     * @return string
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Sets the methods.
     *
     * @param string $methods
     *
     * @return void
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * Gets the prepend bundle prefix.
     *
     * @return boolean
     */
    public function getPrependBundlePrefix()
    {
        return $this->prependBundlePrefix;
    }

    /**
     * Sets the prepend bundle prefix.
     *
     * @param boolean $prependBundlePrefix
     *
     * @return void
     */
    public function setPrependBundlePrefix($prependBundlePrefix)
    {
        if ($prependBundlePrefix !== $this->prependBundlePrefix) {
            $this->prependBundlePrefix = (bool)$prependBundlePrefix;
        }
    }

    /**
     * Gets the translatable.
     *
     * @return boolean
     */
    public function getTranslatable()
    {
        return $this->translatable;
    }

    /**
     * Sets the translatable.
     *
     * @param boolean $translatable
     *
     * @return void
     */
    public function setTranslatable($translatable)
    {
        if ($translatable !== $this->translatable) {
            $this->translatable = (bool)$translatable;
        }
    }

    /**
     * Gets the translation prefix.
     *
     * @return string
     */
    public function getTranslationPrefix()
    {
        return $this->translationPrefix;
    }

    /**
     * Sets the translation prefix.
     *
     * @param string $translationPrefix
     *
     * @return void
     */
    public function setTranslationPrefix($translationPrefix)
    {
        $this->translationPrefix = $translationPrefix;
    }

    /**
     * Gets the defaults.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * Sets the defaults.
     *
     * @param array $defaults
     *
     * @return void
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * Gets the requirements.
     *
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * Sets the requirements.
     *
     * @param array $requirements
     *
     * @return void
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
    }

    /**
     * Gets the condition.
     *
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Sets the condition.
     *
     * @param string $condition
     *
     * @return void
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * Gets the description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description.
     *
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Gets the sort.
     *
     * @return integer
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Sets the sort.
     *
     * @param integer $sort
     *
     * @return void
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
    }

    /**
     * Gets the group.
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the group.
     *
     * @param string $group
     *
     * @return void
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * Gets the created user id.
     *
     * @return integer
     */
    public function getCreatedUserId()
    {
        return $this->createdUserId;
    }

    /**
     * Sets the created user id.
     *
     * @param integer $createdUserId
     *
     * @return void
     */
    public function setCreatedUserId($createdUserId)
    {
        $this->createdUserId = $createdUserId;
    }

    /**
     * Gets the updated user id.
     *
     * @return integer
     */
    public function getUpdatedUserId()
    {
        return $this->updatedUserId;
    }

    /**
     * Sets the updated user id.
     *
     * @param integer $updatedUserId
     *
     * @return void
     */
    public function setUpdatedUserId($updatedUserId)
    {
        $this->updatedUserId = $updatedUserId;
    }

    /**
     * Gets the created date.
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Sets the created date.
     *
     * @param \DateTime $createdDate
     *
     * @return void
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

    /**
     * Gets the updated date.
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Sets the updated date.
     *
     * @param \DateTime $updatedDate
     *
     * @return void
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;
    }

    /**
     * Returns the formatted title conforming to the display pattern
     * specified for this entity.
     *
     * @return string The display title
     */
    public function getTitleFromDisplayPattern()
    {
        $serviceManager = ServiceUtil::getManager();
        $listHelper = $serviceManager->get('zikula_routes_module.listentries_helper');

        $formattedTitle = ''
                . $this->getPath()
                . ' ('
                . $this->getSort()
                . ')';

        return $formattedTitle;
    }

    /**
     * Start validation and raise exception if invalid data is found.
     *
     * @return boolean Whether everything is valid or not
     */
    public function validate()
    {
        if ($this->_bypassValidation === true) {
            return true;
        }

        $serviceManager = ServiceUtil::getManager();

        $validator = $serviceManager->get('validator');
        $errors = $validator->validate($this);

        if (count($errors) > 0) {
            $flashBag = $serviceManager->get('session')->getFlashBag();
            foreach ($errors as $error) {
                $flashBag->add('error', $error->getMessage());
            }

            return false;
        }

        return true;
    }

    /**
     * Return entity data in JSON format.
     *
     * @return string JSON-encoded data
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Creates url arguments array for easy creation of display urls.
     *
     * @return array The resulting arguments list
     */
    public function createUrlArgs()
    {
        $args = [];

        $args['id'] = $this['id'];

        if (property_exists($this, 'slug')) {
            $args['slug'] = $this['slug'];
        }

        return $args;
    }

    /**
     * Create concatenated identifier string (for composite keys).
     *
     * @return String concatenated identifiers
     */
    public function createCompositeIdentifier()
    {
        $itemId = $this['id'];

        return $itemId;
    }

    /**
     * Determines whether this entity supports hook subscribers or not.
     *
     * @return boolean
     */
    public function supportsHookSubscribers()
    {
        return false;
    }

    /**
     * Returns an array of all related objects that need to be persisted after clone.
     *
     * @param array $objects The objects are added to this array. Default: []
     *
     * @return array of entity objects
     */
    public function getRelatedObjectsToPersist(&$objects = [])
    {
        return [];
    }

    /**
     * ToString interceptor implementation.
     * This method is useful for debugging purposes.
     *
     * @return string The output string for this entity
     */
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * Clone interceptor implementation.
     * This method is for example called by the reuse functionality.
     * Performs a quite simple shallow copy.
     *
     * See also:
     * (1) http://docs.doctrine-project.org/en/latest/cookbook/implementing-wakeup-or-clone.html
     * (2) http://www.php.net/manual/en/language.oop5.cloning.php
     * (3) http://stackoverflow.com/questions/185934/how-do-i-create-a-copy-of-an-object-in-php
     */
    public function __clone()
    {
        // If the entity has an identity, proceed as normal.
        if ($this->id) {
            // unset identifiers
            $this->setId(0);

            $this->setCreatedDate(null);
            $this->setCreatedUserId(null);
            $this->setUpdatedDate(null);
            $this->setUpdatedUserId(null);

        }
        // otherwise do nothing, do NOT throw an exception!
    }
}
