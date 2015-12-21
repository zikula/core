AbstractCategoryAssignment
==========================

classname: \Zikula\CategoriesModule\Entity\AbstractCategoryAssignment

replaces: \Zikula\Core\Doctrine\Entity\AbstractEntityCategory

The classes are aliased, so the former name will continue to work until Core-2.0. The new class is unchanged except the name.

The class has been moved into the CategoriesModule because that is where it belongs. The renaming of the class is to
help the reader better understand the purpose of the class. It is not a Category, but rather a CategoryAssignment. In
the  'old' MySql terminology, it might be considered a 'join' table (but join tables 
[don't really exist](http://stackoverflow.com/a/18655514/2600812).)

It would be better to rename/refactor Entity properties to reflect the more accurate naming, e.g. `categoryAssignments`
instead of `categories`.


Purpose
-------

The class exists to make connection to Core categories easier for third-party entities. Simply create a child class
Entity that extends `AbstractCategoryAssignment` and define the required methods. In your Entity, define the assignment
property as OneToMany:

    /**
     * category assignments
     *
     * @ORM\OneToMany(targetEntity="Zikula\PagesModule\Entity\CategoryAssignmentEntity",
     *                mappedBy="entity", cascade={"remove", "persist"},
     *                orphanRemoval=true, fetch="EAGER")
     */
    private $categoryAssignments;


Getter/Setter may vary by implementation, but remember you are not getting/setting a *category* but rather a 
CategoryAssignment. Therefore your getter/setter must accommodate this based on the data they work with. See the 
CategoriesType.md document for an example.