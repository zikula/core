<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.phpdoctrine.org>.
 */

namespace Doctrine\ORM\Mapping;

/**
 * A MappingException indicates that something is wrong with the mapping setup.
 *
 * @since 2.0
 */
class MappingException extends \Doctrine\ORM\ORMException
{
    public static function pathRequired()
    {
        return new self("Specifying the paths to your entities is required ".
            "in the AnnotationDriver to retrieve all class names.");
    }

    public static function identifierRequired($entityName)
    {
        if (null !== ($parent = get_parent_class($entityName))) {
            return new self(sprintf(
                'No identifier/primary key specified for Entity "%s" sub class of "%s". Every Entity must have an identifier/primary key.',
                $entityName, $parent
            ));
        }

        return new self(sprintf(
            'No identifier/primary key specified for Entity "%s". Every Entity must have an identifier/primary key.',
            $entityName
        ));

    }

    public static function invalidInheritanceType($entityName, $type)
    {
        return new self("The inheritance type '$type' specified for '$entityName' does not exist.");
    }

    public static function generatorNotAllowedWithCompositeId()
    {
        return new self("Id generators can't be used with a composite id.");
    }

    public static function missingFieldName($entity)
    {
        return new self("The field or association mapping misses the 'fieldName' attribute in entity '$entity'.");
    }

    public static function missingTargetEntity($fieldName)
    {
        return new self("The association mapping '$fieldName' misses the 'targetEntity' attribute.");
    }

    public static function missingSourceEntity($fieldName)
    {
        return new self("The association mapping '$fieldName' misses the 'sourceEntity' attribute.");
    }

    public static function mappingFileNotFound($entityName, $fileName)
    {
        return new self("No mapping file found named '$fileName' for class '$entityName'.");
    }

     /**
     * Exception for invalid property name override.
     *
     * @param string $className The entity's name
     * @param string $fieldName
     */
    public static function invalidOverrideFieldName($className, $fieldName)
    {
        return new self("Invalid field override named '$fieldName' for class '$className'.");
    }

    /**
     * Exception for invalid property type override.
     *
     * @param string $className The entity's name
     * @param string $fieldName
     */
    public static function invalidOverrideFieldType($className, $fieldName)
    {
        return new self("The column type of attribute '$fieldName' on class '$className' could not be changed.");
    }

    public static function mappingNotFound($className, $fieldName)
    {
        return new self("No mapping found for field '$fieldName' on class '$className'.");
    }

    public static function queryNotFound($className, $queryName)
    {
        return new self("No query found named '$queryName' on class '$className'.");
    }

    public static function resultMappingNotFound($className, $resultName)
    {
        return new self("No result set mapping found named '$resultName' on class '$className'.");
    }

    public static function emptyQueryMapping($entity, $queryName)
    {
        return new self('Query named "'.$queryName.'" in "'.$entity.'" could not be empty.');
    }

    public static function nameIsMandatoryForQueryMapping($className)
    {
        return new self("Query name on entity class '$className' is not defined.");
    }

    public static function missingQueryMapping($entity, $queryName)
    {
        return new self('Query named "'.$queryName.'" in "'.$entity.' requires a result class or result set mapping.');
    }

    public static function missingResultSetMappingEntity($entity, $resultName)
    {
        return new self('Result set mapping named "'.$resultName.'" in "'.$entity.' requires a entity class name.');
    }

    public static function missingResultSetMappingFieldName($entity, $resultName)
    {
        return new self('Result set mapping named "'.$resultName.'" in "'.$entity.' requires a field name.');
    }

    public static function nameIsMandatoryForSqlResultSetMapping($className)
    {
        return new self("Result set mapping name on entity class '$className' is not defined.");
    }

    public static function oneToManyRequiresMappedBy($fieldName)
    {
        return new self("OneToMany mapping on field '$fieldName' requires the 'mappedBy' attribute.");
    }

    public static function joinTableRequired($fieldName)
    {
        return new self("The mapping of field '$fieldName' requires an the 'joinTable' attribute.");
    }

    /**
     * Called if a required option was not found but is required
     *
     * @param string $field which field cannot be processed?
     * @param string $expectedOption which option is required
     * @param string $hint  Can optionally be used to supply a tip for common mistakes,
     *                      e.g. "Did you think of the plural s?"
     * @return MappingException
     */
    static function missingRequiredOption($field, $expectedOption, $hint = '')
    {
        $message = "The mapping of field '{$field}' is invalid: The option '{$expectedOption}' is required.";

        if ( ! empty($hint)) {
            $message .= ' (Hint: ' . $hint . ')';
        }

        return new self($message);
    }

    /**
     * Generic exception for invalid mappings.
     *
     * @param string $fieldName
     */
    public static function invalidMapping($fieldName)
    {
        return new self("The mapping of field '$fieldName' is invalid.");
    }

    /**
     * Exception for reflection exceptions - adds the entity name,
     * because there might be long classnames that will be shortened
     * within the stacktrace
     *
     * @param string $entity The entity's name
     * @param \ReflectionException $previousException
     */
    public static function reflectionFailure($entity, \ReflectionException $previousException)
    {
        return new self('An error occurred in ' . $entity, 0, $previousException);
    }

    public static function joinColumnMustPointToMappedField($className, $joinColumn)
    {
        return new self('The column ' . $joinColumn . ' must be mapped to a field in class '
                . $className . ' since it is referenced by a join column of another class.');
    }

    public static function classIsNotAValidEntityOrMappedSuperClass($className)
    {
        if (null !== ($parent = get_parent_class($className))) {
            return new self(sprintf(
                'Class "%s" sub class of "%s" is not a valid entity or mapped super class.',
                $className, $parent
            ));
        }

        return new self(sprintf(
            'Class "%s" is not a valid entity or mapped super class.',
            $className
        ));
    }

    public static function propertyTypeIsRequired($className, $propertyName)
    {
        return new self("The attribute 'type' is required for the column description of property ".$className."::\$".$propertyName.".");
    }

    public static function tableIdGeneratorNotImplemented($className)
    {
        return new self("TableIdGenerator is not yet implemented for use with class ".$className);
    }

    /**
     * @param string $entity The entity's name
     * @param string $fieldName The name of the field that was already declared
     */
    public static function duplicateFieldMapping($entity, $fieldName)
    {
        return new self('Property "'.$fieldName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function duplicateAssociationMapping($entity, $fieldName)
    {
        return new self('Property "'.$fieldName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function duplicateQueryMapping($entity, $queryName)
    {
        return new self('Query named "'.$queryName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function duplicateResultSetMapping($entity, $resultName)
    {
        return new self('Result set mapping named "'.$resultName.'" in "'.$entity.'" was already declared, but it must be declared only once');
    }

    public static function singleIdNotAllowedOnCompositePrimaryKey($entity)
    {
        return new self('Single id is not allowed on composite primary key in entity '.$entity);
    }

    public static function unsupportedOptimisticLockingType($entity, $fieldName, $unsupportedType)
    {
        return new self('Locking type "'.$unsupportedType.'" (specified in "'.$entity.'", field "'.$fieldName.'") '
                        .'is not supported by Doctrine.'
        );
    }

    public static function fileMappingDriversRequireConfiguredDirectoryPath($path = null)
    {
        if ( ! empty($path)) {
            $path = '[' . $path . ']';
        }

        return new self(
            'File mapping drivers must have a valid directory path, ' .
            'however the given path ' . $path . ' seems to be incorrect!'
        );
    }

    /**
     * Throws an exception that indicates that a class used in a discriminator map does not exist.
     * An example would be an outdated (maybe renamed) classname.
     *
     * @param string $className The class that could not be found
     * @param string $owningClass The class that declares the discriminator map.
     * @return self
     */
    public static function invalidClassInDiscriminatorMap($className, $owningClass)
    {
        return new self(
            "Entity class '$className' used in the discriminator map of class '$owningClass' ".
            "does not exist."
        );
    }

    public static function duplicateDiscriminatorEntry($className, array $entries, array $map)
    {
        return new self(
            "The entries " . implode(', ',  $entries) . " in discriminator map of class '" . $className . "' is duplicated. " .
            "If the discriminator map is automatically generated you have to convert it to an explicit discriminator map now. " .
            "The entries of the current map are: @DiscriminatorMap({" . implode(', ', array_map(
                function($a, $b) { return "'$a': '$b'"; }, array_keys($map), array_values($map)
            )) . "})"
        );
    }

    public static function missingDiscriminatorMap($className)
    {
        return new self("Entity class '$className' is using inheritance but no discriminator map was defined.");
    }

    public static function missingDiscriminatorColumn($className)
    {
        return new self("Entity class '$className' is using inheritance but no discriminator column was defined.");
    }

    public static function invalidDiscriminatorColumnType($className, $type)
    {
        return new self("Discriminator column type on entity class '$className' is not allowed to be '$type'. 'string' or 'integer' type variables are suggested!");
    }

    public static function nameIsMandatoryForDiscriminatorColumns($className)
    {
        return new self("Discriminator column name on entity class '$className' is not defined.");
    }

    public static function cannotVersionIdField($className, $fieldName)
    {
        return new self("Setting Id field '$fieldName' as versionale in entity class '$className' is not supported.");
    }

    public static function sqlConversionNotAllowedForIdentifiers($className, $fieldName, $type)
    {
        return new self("It is not possible to set id field '$fieldName' to type '$type' in entity class '$className'. The type '$type' requires conversion SQL which is not allowed for identifiers.");
    }

    /**
     * @param  string $className
     * @param  string $columnName
     * @return self
     */
    public static function duplicateColumnName($className, $columnName)
    {
        return new self("Duplicate definition of column '".$columnName."' on entity '".$className."' in a field or discriminator column mapping.");
    }

    public static function illegalToManyAssocationOnMappedSuperclass($className, $field)
    {
        return new self("It is illegal to put an inverse side one-to-many or many-to-many association on mapped superclass '".$className."#".$field."'.");
    }

    /**
     * @param string $className
     * @param string $targetEntity
     * @param string $targetField
     * @return self
     */
    public static function cannotMapCompositePrimaryKeyEntitiesAsForeignId($className, $targetEntity, $targetField)
    {
        return new self("It is not possible to map entity '".$className."' with a composite primary key ".
            "as part of the primary key of another entity '".$targetEntity."#".$targetField."'.");
    }

    public static function noSingleAssociationJoinColumnFound($className, $field)
    {
        return new self("'$className#$field' is not an association with a single join column.");
    }

    public static function noFieldNameFoundForColumn($className, $column)
    {
        return new self("Cannot find a field on '$className' that is mapped to column '$column'. Either the ".
            "field does not exist or an association exists but it has multiple join columns.");
    }

    public static function illegalOrphanRemovalOnIdentifierAssociation($className, $field)
    {
        return new self("The orphan removal option is not allowed on an association that is ".
            "part of the identifier in '$className#$field'.");
    }

    public static function illegalOrphanRemoval($className, $field)
    {
        return new self("Orphan removal is only allowed on one-to-one and one-to-many ".
                "associations, but " . $className."#" .$field . " is not.");
    }

    public static function illegalInverseIdentifierAssocation($className, $field)
    {
        return new self("An inverse association is not allowed to be identifier in '$className#$field'.");
    }

    public static function illegalToManyIdentifierAssoaction($className, $field)
    {
        return new self("Many-to-many or one-to-many associations are not allowed to be identifier in '$className#$field'.");
    }

    public static function noInheritanceOnMappedSuperClass($className)
    {
        return new self("Its not supported to define inheritance information on a mapped superclass '" . $className . "'.");
    }

    public static function mappedClassNotPartOfDiscriminatorMap($className, $rootClassName)
    {
        return new self(
            "Entity '" . $className . "' has to be part of the discriminator map of '" . $rootClassName . "' " .
            "to be properly mapped in the inheritance hierachy. Alternatively you can make '".$className."' an abstract class " .
            "to avoid this exception from occuring."
        );
    }

    public static function lifecycleCallbackMethodNotFound($className, $methodName)
    {
        return new self("Entity '" . $className . "' has no method '" . $methodName . "' to be registered as lifecycle callback.");
    }

    public static function invalidFetchMode($className, $annotation)
    {
        return new self("Entity '" . $className . "' has a mapping with invalid fetch mode '" . $annotation . "'");
    }

    public static function compositeKeyAssignedIdGeneratorRequired($className)
    {
        return new self("Entity '". $className . "' has a composite identifier but uses an ID generator other than manually assigning (Identity, Sequence). This is not supported.");
    }

    public static function invalidTargetEntityClass($targetEntity, $sourceEntity, $associationName)
    {
        return new self("The target-entity " . $targetEntity . " cannot be found in '" . $sourceEntity."#".$associationName."'.");
    }

    public static function invalidCascadeOption(array $cascades, $className, $propertyName)
    {
        $cascades = implode(", ", array_map(function ($e) { return "'" . $e . "'"; }, $cascades));
        return new self(sprintf(
            "You have specified invalid cascade options for %s::$%s: %s; available options: 'remove', 'persist', 'refresh', 'merge', and 'detach'",
            $className,
            $propertyName,
            $cascades
        ));
    }
}
