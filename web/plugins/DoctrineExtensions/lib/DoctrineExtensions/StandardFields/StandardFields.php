<?php

namespace DoctrineExtensions\StandardFields;

/**
 * This interface is not necessary but can be implemented for
 * Entities which in some cases needs to be identified as
 * StandardFields
 */
interface StandardFields
{
    // timestampable expects annotations on properties
    
    /**
     * @ZK\StandardFields(on="create")
     * user id which should be updated on insert only
     */
    
    /**
     * @ZK\StandardFields(on="update")
     * user id which should be updated on update and insert
     */
    
    /**
     * @ZK\StandardFields(on="change", field="field", value="value")
     * user id which should be updated on changed "property" 
     * value and become equal to given "value"
     */
    
    /**
     * example
     * 
     * @ZK\StandardFields(on="create")
     * @Column(type="integer")
     * $createdUserId
     */
}