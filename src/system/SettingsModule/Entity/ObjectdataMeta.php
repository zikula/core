<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SettingsModule\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObjectdataMeta
 *
 * @ORM\Table(name="objectdata_meta")
 * @ORM\Entity
 */
class ObjectdataMeta
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=40, nullable=false)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(name="tablename", type="string", length=40, nullable=false)
     */
    private $tablename;

    /**
     * @var string
     *
     * @ORM\Column(name="idcolumn", type="string", length=40, nullable=false)
     */
    private $idcolumn;

    /**
     * @var integer
     *
     * @ORM\Column(name="obj_id", type="integer", nullable=false)
     */
    private $objId;

    /**
     * @var string
     *
     * @ORM\Column(name="permissions", type="string", length=255, nullable=true)
     */
    private $permissions;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_title", type="string", length=80, nullable=true)
     */
    private $dcTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_author", type="string", length=80, nullable=true)
     */
    private $dcAuthor;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_subject", type="string", length=255, nullable=true)
     */
    private $dcSubject;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_keywords", type="string", length=128, nullable=true)
     */
    private $dcKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_description", type="string", length=255, nullable=true)
     */
    private $dcDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_publisher", type="string", length=128, nullable=true)
     */
    private $dcPublisher;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_contributor", type="string", length=128, nullable=true)
     */
    private $dcContributor;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dc_startdate", type="datetime", nullable=true)
     */
    private $dcStartdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dc_enddate", type="datetime", nullable=true)
     */
    private $dcEnddate;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_type", type="string", length=128, nullable=true)
     */
    private $dcType;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_format", type="string", length=128, nullable=true)
     */
    private $dcFormat;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_uri", type="string", length=255, nullable=true)
     */
    private $dcUri;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_source", type="string", length=128, nullable=true)
     */
    private $dcSource;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_language", type="string", length=32, nullable=true)
     */
    private $dcLanguage;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_relation", type="string", length=255, nullable=true)
     */
    private $dcRelation;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_coverage", type="string", length=64, nullable=true)
     */
    private $dcCoverage;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_entity", type="string", length=64, nullable=true)
     */
    private $dcEntity;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_comment", type="string", length=255, nullable=true)
     */
    private $dcComment;

    /**
     * @var string
     *
     * @ORM\Column(name="dc_extra", type="string", length=255, nullable=true)
     */
    private $dcExtra;

    /**
     * @var string
     *
     * @ORM\Column(name="obj_status", type="string", length=1, nullable=false)
     */
    private $objStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="cr_date", type="datetime", nullable=false)
     */
    private $crDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="cr_uid", type="integer", nullable=false)
     */
    private $crUid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lu_date", type="datetime", nullable=false)
     */
    private $luDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="lu_uid", type="integer", nullable=false)
     */
    private $luUid;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return ObjectdataMeta
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set tablename
     *
     * @param string $tablename
     * @return ObjectdataMeta
     */
    public function setTablename($tablename)
    {
        $this->tablename = $tablename;

        return $this;
    }

    /**
     * Get tablename
     *
     * @return string
     */
    public function getTablename()
    {
        return $this->tablename;
    }

    /**
     * Set idcolumn
     *
     * @param string $idcolumn
     * @return ObjectdataMeta
     */
    public function setIdcolumn($idcolumn)
    {
        $this->idcolumn = $idcolumn;

        return $this;
    }

    /**
     * Get idcolumn
     *
     * @return string
     */
    public function getIdcolumn()
    {
        return $this->idcolumn;
    }

    /**
     * Set objId
     *
     * @param integer $objId
     * @return ObjectdataMeta
     */
    public function setObjId($objId)
    {
        $this->objId = $objId;

        return $this;
    }

    /**
     * Get objId
     *
     * @return integer
     */
    public function getObjId()
    {
        return $this->objId;
    }

    /**
     * Set permissions
     *
     * @param string $permissions
     * @return ObjectdataMeta
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * Get permissions
     *
     * @return string
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * Set dcTitle
     *
     * @param string $dcTitle
     * @return ObjectdataMeta
     */
    public function setDcTitle($dcTitle)
    {
        $this->dcTitle = $dcTitle;

        return $this;
    }

    /**
     * Get dcTitle
     *
     * @return string
     */
    public function getDcTitle()
    {
        return $this->dcTitle;
    }

    /**
     * Set dcAuthor
     *
     * @param string $dcAuthor
     * @return ObjectdataMeta
     */
    public function setDcAuthor($dcAuthor)
    {
        $this->dcAuthor = $dcAuthor;

        return $this;
    }

    /**
     * Get dcAuthor
     *
     * @return string
     */
    public function getDcAuthor()
    {
        return $this->dcAuthor;
    }

    /**
     * Set dcSubject
     *
     * @param string $dcSubject
     * @return ObjectdataMeta
     */
    public function setDcSubject($dcSubject)
    {
        $this->dcSubject = $dcSubject;

        return $this;
    }

    /**
     * Get dcSubject
     *
     * @return string
     */
    public function getDcSubject()
    {
        return $this->dcSubject;
    }

    /**
     * Set dcKeywords
     *
     * @param string $dcKeywords
     * @return ObjectdataMeta
     */
    public function setDcKeywords($dcKeywords)
    {
        $this->dcKeywords = $dcKeywords;

        return $this;
    }

    /**
     * Get dcKeywords
     *
     * @return string
     */
    public function getDcKeywords()
    {
        return $this->dcKeywords;
    }

    /**
     * Set dcDescription
     *
     * @param string $dcDescription
     * @return ObjectdataMeta
     */
    public function setDcDescription($dcDescription)
    {
        $this->dcDescription = $dcDescription;

        return $this;
    }

    /**
     * Get dcDescription
     *
     * @return string
     */
    public function getDcDescription()
    {
        return $this->dcDescription;
    }

    /**
     * Set dcPublisher
     *
     * @param string $dcPublisher
     * @return ObjectdataMeta
     */
    public function setDcPublisher($dcPublisher)
    {
        $this->dcPublisher = $dcPublisher;

        return $this;
    }

    /**
     * Get dcPublisher
     *
     * @return string
     */
    public function getDcPublisher()
    {
        return $this->dcPublisher;
    }

    /**
     * Set dcContributor
     *
     * @param string $dcContributor
     * @return ObjectdataMeta
     */
    public function setDcContributor($dcContributor)
    {
        $this->dcContributor = $dcContributor;

        return $this;
    }

    /**
     * Get dcContributor
     *
     * @return string
     */
    public function getDcContributor()
    {
        return $this->dcContributor;
    }

    /**
     * Set dcStartdate
     *
     * @param \DateTime $dcStartdate
     * @return ObjectdataMeta
     */
    public function setDcStartdate($dcStartdate)
    {
        $this->dcStartdate = $dcStartdate;

        return $this;
    }

    /**
     * Get dcStartdate
     *
     * @return \DateTime
     */
    public function getDcStartdate()
    {
        return $this->dcStartdate;
    }

    /**
     * Set dcEnddate
     *
     * @param \DateTime $dcEnddate
     * @return ObjectdataMeta
     */
    public function setDcEnddate($dcEnddate)
    {
        $this->dcEnddate = $dcEnddate;

        return $this;
    }

    /**
     * Get dcEnddate
     *
     * @return \DateTime
     */
    public function getDcEnddate()
    {
        return $this->dcEnddate;
    }

    /**
     * Set dcType
     *
     * @param string $dcType
     * @return ObjectdataMeta
     */
    public function setDcType($dcType)
    {
        $this->dcType = $dcType;

        return $this;
    }

    /**
     * Get dcType
     *
     * @return string
     */
    public function getDcType()
    {
        return $this->dcType;
    }

    /**
     * Set dcFormat
     *
     * @param string $dcFormat
     * @return ObjectdataMeta
     */
    public function setDcFormat($dcFormat)
    {
        $this->dcFormat = $dcFormat;

        return $this;
    }

    /**
     * Get dcFormat
     *
     * @return string
     */
    public function getDcFormat()
    {
        return $this->dcFormat;
    }

    /**
     * Set dcUri
     *
     * @param string $dcUri
     * @return ObjectdataMeta
     */
    public function setDcUri($dcUri)
    {
        $this->dcUri = $dcUri;

        return $this;
    }

    /**
     * Get dcUri
     *
     * @return string
     */
    public function getDcUri()
    {
        return $this->dcUri;
    }

    /**
     * Set dcSource
     *
     * @param string $dcSource
     * @return ObjectdataMeta
     */
    public function setDcSource($dcSource)
    {
        $this->dcSource = $dcSource;

        return $this;
    }

    /**
     * Get dcSource
     *
     * @return string
     */
    public function getDcSource()
    {
        return $this->dcSource;
    }

    /**
     * Set dcLanguage
     *
     * @param string $dcLanguage
     * @return ObjectdataMeta
     */
    public function setDcLanguage($dcLanguage)
    {
        $this->dcLanguage = $dcLanguage;

        return $this;
    }

    /**
     * Get dcLanguage
     *
     * @return string
     */
    public function getDcLanguage()
    {
        return $this->dcLanguage;
    }

    /**
     * Set dcRelation
     *
     * @param string $dcRelation
     * @return ObjectdataMeta
     */
    public function setDcRelation($dcRelation)
    {
        $this->dcRelation = $dcRelation;

        return $this;
    }

    /**
     * Get dcRelation
     *
     * @return string
     */
    public function getDcRelation()
    {
        return $this->dcRelation;
    }

    /**
     * Set dcCoverage
     *
     * @param string $dcCoverage
     * @return ObjectdataMeta
     */
    public function setDcCoverage($dcCoverage)
    {
        $this->dcCoverage = $dcCoverage;

        return $this;
    }

    /**
     * Get dcCoverage
     *
     * @return string
     */
    public function getDcCoverage()
    {
        return $this->dcCoverage;
    }

    /**
     * Set dcEntity
     *
     * @param string $dcEntity
     * @return ObjectdataMeta
     */
    public function setDcEntity($dcEntity)
    {
        $this->dcEntity = $dcEntity;

        return $this;
    }

    /**
     * Get dcEntity
     *
     * @return string
     */
    public function getDcEntity()
    {
        return $this->dcEntity;
    }

    /**
     * Set dcComment
     *
     * @param string $dcComment
     * @return ObjectdataMeta
     */
    public function setDcComment($dcComment)
    {
        $this->dcComment = $dcComment;

        return $this;
    }

    /**
     * Get dcComment
     *
     * @return string
     */
    public function getDcComment()
    {
        return $this->dcComment;
    }

    /**
     * Set dcExtra
     *
     * @param string $dcExtra
     * @return ObjectdataMeta
     */
    public function setDcExtra($dcExtra)
    {
        $this->dcExtra = $dcExtra;

        return $this;
    }

    /**
     * Get dcExtra
     *
     * @return string
     */
    public function getDcExtra()
    {
        return $this->dcExtra;
    }

    /**
     * Set objStatus
     *
     * @param string $objStatus
     * @return ObjectdataMeta
     */
    public function setObjStatus($objStatus)
    {
        $this->objStatus = $objStatus;

        return $this;
    }

    /**
     * Get objStatus
     *
     * @return string
     */
    public function getObjStatus()
    {
        return $this->objStatus;
    }

    /**
     * Set crDate
     *
     * @param \DateTime $crDate
     * @return ObjectdataMeta
     */
    public function setCrDate($crDate)
    {
        $this->crDate = $crDate;

        return $this;
    }

    /**
     * Get crDate
     *
     * @return \DateTime
     */
    public function getCrDate()
    {
        return $this->crDate;
    }

    /**
     * Set crUid
     *
     * @param integer $crUid
     * @return ObjectdataMeta
     */
    public function setCrUid($crUid)
    {
        $this->crUid = $crUid;

        return $this;
    }

    /**
     * Get crUid
     *
     * @return integer
     */
    public function getCrUid()
    {
        return $this->crUid;
    }

    /**
     * Set luDate
     *
     * @param \DateTime $luDate
     * @return ObjectdataMeta
     */
    public function setLuDate($luDate)
    {
        $this->luDate = $luDate;

        return $this;
    }

    /**
     * Get luDate
     *
     * @return \DateTime
     */
    public function getLuDate()
    {
        return $this->luDate;
    }

    /**
     * Set luUid
     *
     * @param integer $luUid
     * @return ObjectdataMeta
     */
    public function setLuUid($luUid)
    {
        $this->luUid = $luUid;

        return $this;
    }

    /**
     * Get luUid
     *
     * @return integer
     */
    public function getLuUid()
    {
        return $this->luUid;
    }
}
