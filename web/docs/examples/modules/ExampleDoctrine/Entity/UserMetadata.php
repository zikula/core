<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="exampledoctrine_user_metadata")
 */
class ExampleDoctrine_Entity_UserMetadata extends Zikula_Doctrine2_Entity_EntityMetadata
{
    /**
     * @ORM\OneToOne(targetEntity="ExampleDoctrine_Entity_User", inversedBy="metadata")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id", unique=true)
     * @var ExampleDoctrine_Entity_User
     */
    private $entity;
    
    public function getEntity()
    {
        return $this->entity;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
}
