<?php

namespace Gedmo\Translatable\Document\Repository;

use Gedmo\Translatable\TranslatableListener;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Gedmo\Tool\Wrapper\MongoDocumentWrapper;
use Gedmo\Translatable\Mapping\Event\Adapter\ODM as TranslatableAdapterODM;
use Doctrine\ODM\MongoDB\Mapping\Types\Type;

/**
 * The TranslationRepository has some useful functions
 * to interact with translations.
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Translatable.Document.Repository
 * @subpackage TranslationRepository
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class TranslationRepository extends DocumentRepository
{
    /**
     * Current TranslatableListener instance used
     * in EntityManager
     *
     * @var TranslatableListener
     */
    private $listener;

    /**
     * {@inheritdoc}
     */
    public function __construct(DocumentManager $dm, UnitOfWork $uow, ClassMetadata $class)
    {
        if ($class->getReflectionClass()->isSubclassOf('Gedmo\Translatable\Document\MappedSuperclass\AbstractPersonalTranslation')) {
            throw new \Gedmo\Exception\UnexpectedValueException('This repository is useless for personal translations');
        }
        parent::__construct($dm, $uow, $class);
    }

    /**
     * Makes additional translation of $document $field into $locale
     * using $value
     *
     * @param object $document
     * @param string $field
     * @param string $locale
     * @param mixed $value
     * @return TranslationRepository
     */
    public function translate($document, $field, $locale, $value)
    {
        $meta = $this->dm->getClassMetadata(get_class($document));
        $listener = $this->getTranslatableListener();
        $config = $listener->getConfiguration($this->dm, $meta->name);
        if (!isset($config['fields']) || !in_array($field, $config['fields'])) {
            throw new \Gedmo\Exception\InvalidArgumentException("Document: {$meta->name} does not translate field - {$field}");
        }
        $modRecordValue = (!$listener->getPersistDefaultLocaleTranslation() && $locale === $listener->getDefaultLocale())
            || $listener->getTranslatableLocale($document, $meta) === $locale
        ;
        if ($modRecordValue) {
            $meta->getReflectionProperty($field)->setValue($document, $value);
            $this->dm->persist($document);
        } else {
            if (isset($config['translationClass'])) {
                $class = $config['translationClass'];
            } else {
                $ea = new TranslatableAdapterODM();
                $class = $listener->getTranslationClass($ea, $config['useObjectClass']);
            }
            $foreignKey = $meta->getReflectionProperty($meta->identifier)->getValue($document);
            $objectClass = $config['useObjectClass'];
            $transMeta = $this->dm->getClassMetadata($class);
            $trans = $this->findOneBy(compact('locale', 'field', 'objectClass', 'foreignKey'));
            if (!$trans) {
                $trans = $transMeta->newInstance();
                $transMeta->getReflectionProperty('foreignKey')->setValue($trans, $foreignKey);
                $transMeta->getReflectionProperty('objectClass')->setValue($trans, $objectClass);
                $transMeta->getReflectionProperty('field')->setValue($trans, $field);
                $transMeta->getReflectionProperty('locale')->setValue($trans, $locale);
            }
            $mapping = $meta->getFieldMapping($field);
            $type = Type::getType($mapping['type']);
            $transformed = $type->convertToDatabaseValue($value);
            $transMeta->getReflectionProperty('content')->setValue($trans, $transformed);
            if ($this->dm->getUnitOfWork()->isInIdentityMap($document)) {
                $this->dm->persist($trans);
            } else {
                $oid = spl_object_hash($document);
                $listener->addPendingTranslationInsert($oid, $trans);
            }
        }
        return $this;
    }

    /**
     * Loads all translations with all translatable
     * fields from the given entity
     *
     * @param object $document
     * @return array list of translations in locale groups
     */
    public function findTranslations($document)
    {
        $result = array();
        $wrapped = new MongoDocumentWrapper($document, $this->dm);
        if ($wrapped->hasValidIdentifier()) {
            $documentId = $wrapped->getIdentifier();

            $translationMeta = $this->getClassMetadata();
            $qb = $this->createQueryBuilder();
            $q = $qb->field('foreignKey')->equals($documentId)
                ->field('objectClass')->equals($wrapped->getMetadata()->rootDocumentName)
                ->sort('locale', 'asc')
                ->getQuery();

            $q->setHydrate(false);
            $data = $q->execute();
            if ($data instanceof Cursor) {
                $data = $data->toArray();
            }
            if ($data && is_array($data) && count($data)) {
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']] = $row['content'];
                }
            }
        }
        return $result;
    }

    /**
     * Find the object $class by the translated field.
     * Result is the first occurence of translated field.
     * Query can be slow, since there are no indexes on such
     * columns
     *
     * @param string $field
     * @param string $value
     * @param string $class
     * @return object - instance of $class or null if not found
     */
    public function findObjectByTranslatedField($field, $value, $class)
    {
        $document = null;
        $meta = $this->dm->getClassMetadata($class);
        $translationMeta = $this->getClassMetadata();
        if ($meta->hasField($field)) {
            $qb = $this->createQueryBuilder();
            $q = $qb->field('field')->equals($field)
                ->field('objectClass')->equals($meta->rootDocumentName)
                ->field('content')->equals($value)
                ->getQuery();

            $q->setHydrate(false);
            $result = $q->execute();
            if ($result instanceof Cursor) {
                $result = $result->toArray();
            }
            $id = count($result) ? $result[0]['foreignKey'] : null;
            if ($id) {
                $document = $this->dm->find($class, $id);
            }
        }
        return $document;
    }

    /**
     * Loads all translations with all translatable
     * fields by a given document primary key
     *
     * @param mixed $id - primary key value of document
     * @return array
     */
    public function findTranslationsByObjectId($id)
    {
        $result = array();
        if ($id) {
            $translationMeta = $this->getClassMetadata();
            $qb = $this->createQueryBuilder();
            $q = $qb->field('foreignKey')->equals($id)
                ->sort('locale', 'asc')
                ->getQuery();

            $q->setHydrate(false);
            $data = $q->execute();

            if ($data instanceof Cursor) {
                $data = $data->toArray();
            }
            if ($data && is_array($data) && count($data)) {
                foreach ($data as $row) {
                    $result[$row['locale']][$row['field']] = $row['content'];
                }
            }
        }
        return $result;
    }

    /**
     * Get the currently used TranslatableListener
     *
     * @throws \Gedmo\Exception\RuntimeException - if listener is not found
     * @return TranslatableListener
     */
    private function getTranslatableListener()
    {
        if (!$this->listener) {
            foreach ($this->dm->getEventManager()->getListeners() as $event => $listeners) {
                foreach ($listeners as $hash => $listener) {
                    if ($listener instanceof TranslatableListener) {
                        $this->listener = $listener;
                        break;
                    }
                }
                if ($this->listener) {
                    break;
                }
            }

            if (is_null($this->listener)) {
                throw new \Gedmo\Exception\RuntimeException('The translation listener could not be found');
            }
        }
        return $this->listener;
    }
}
