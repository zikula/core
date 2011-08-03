<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="exampledoctrine_user_category",
 *            uniqueConstraints={@ORM\UniqueConstraint(name="cat_unq",columns={"registryId", "categoryId", "entityId"})})
 */
class ExampleDoctrine_Entity_UserCategory extends Zikula_Doctrine2_Entity_EntityCategory
{
    /**
     * @ORM\ManyToOne(targetEntity="ExampleDoctrine_Entity_User", inversedBy="categories")
     * @ORM\JoinColumn(name="entityId", referencedColumnName="id")
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
