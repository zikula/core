<?php

namespace Gedmo\Translatable;

use Doctrine\Common\EventArgs,
    Doctrine\Common\Persistence\Mapping\ClassMetadata,
    Gedmo\Mapping\MappedEventSubscriber,
    Gedmo\Translatable\Mapping\Event\TranslatableAdapter;

/**
 * The translation listener handles the generation and
 * loading of translations for entities which implements
 * the Translatable interface.
 *
 * This behavior can inpact the performance of your application
 * since it does an additional query for each field to translate.
 *
 * Nevertheless the annotation metadata is properly cached and
 * it is not a big overhead to lookup all entity annotations since
 * the caching is activated for metadata
 *
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @package Gedmo.Translatable
 * @subpackage TranslationListener
 * @link http://www.gediminasm.org
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class TranslationListener extends MappedEventSubscriber
{
    /**
     * Locale which is set on this listener.
     * If Entity being translated has locale defined it
     * will override this one
     *
     * @var string
     */
    protected $locale = 'en_us';

    /**
     * Default locale, this changes behavior
     * to not update the original record field if locale
     * which is used for updating is not default. This
     * will load the default translation in other locales
     * if record is not translated yet
     *
     * @var string
     */
    private $defaultLocale = '';

    /**
     * If this is set to false, when if entity does
     * not have a translation for requested locale
     * it will show a blank value
     *
     * @var boolean
     */
    private $translationFallback = false;

    /**
     * List of translations which do not have the foreign
     * key generated yet - MySQL case. These translations
     * will be updated with new keys on postPersist event
     *
     * @var array
     */
    private $pendingTranslationInserts = array();

    /**
     * Currently in case if there is TranslationQueryWalker
     * in charge. We need to skip issuing additional queries
     * on load
     *
     * @var boolean
     */
    private $skipOnLoad = false;

    /**
     * List of additional translations for object
     * hash key
     *
     * @var array
     */
    private $additionalTranslations = array();

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'postLoad',
            'postPersist',
            'onFlush',
            'loadClassMetadata'
        );
    }

    /**
     * Set to skip or not onLoad event
     *
     * @param boolean $bool
     * @return TranslationListener
     */
    public function setSkipOnLoad($bool)
    {
        $this->skipOnLoad = (bool)$bool;
        return $this;
    }

    /**
     * Add additional translation for $oid object
     *
     * @param string $oid
     * @param string $field
     * @param string $locale
     * @param mixed $value
     * @return TranslationListener
     */
    public function addTranslation($oid, $field, $locale, $value)
    {
        $this->additionalTranslations[$oid][$field][$locale] = $value;
        return $this;
    }

    /**
     * Mapps additional metadata
     *
     * @param EventArgs $eventArgs
     * @return void
     */
    public function loadClassMetadata(EventArgs $eventArgs)
    {
        $ea = $this->getEventAdapter($eventArgs);
        $this->loadMetadataForObjectClass($ea->getObjectManager(), $eventArgs->getClassMetadata());
    }

    /**
     * Enable or disable translation fallback
     * to original record value
     *
     * @param boolean $bool
     * @return TranslationListener
     */
    public function setTranslationFallback($bool)
    {
        $this->translationFallback = (bool)$bool;
        return $this;
    }

    /**
     * Weather or not is using the translation
     * fallback to original record
     *
     * @return boolean
     */
    public function getTranslationFallback()
    {
        return $this->translationFallback;
    }

    /**
     * Get the translation class to be used
     * for the object $class
     *
     * @param TranslatableAdapter $ea
     * @param string $class
     * @return string
     */
    public function getTranslationClass(TranslatableAdapter $ea, $class)
    {
        return isset($this->configurations[$class]['translationClass']) ?
            $this->configurations[$class]['translationClass'] :
            $ea->getDefaultTranslationClass();
    }

    /**
     * Set the locale to use for translation listener
     *
     * @param string $locale
     * @return TranslationListener
     */
    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Sets the default locale, this changes behavior
     * to not update the original record field if locale
     * which is used for updating is not default
     *
     * @param string $locale
     * @return TranslationListener
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
        return $this;
    }

    /**
     * Get currently set global locale, used
     * extensively during query execution
     *
     * @return string
     */
    public function getListenerLocale()
    {
        $this->validateLocale($this->locale);
        return strtolower($this->locale);
    }

    /**
     * Gets the locale to use for translation. Loads object
     * defined locale first..
     *
     * @param object $object
     * @param ClassMetadata $meta
     * @throws RuntimeException - if language or locale property is not
     *         found in entity
     * @return string
     */
    public function getTranslatableLocale($object, ClassMetadata $meta)
    {
        $locale = $this->locale;
        if (isset($this->configurations[$meta->name]['locale'])) {
            $class = $meta->getReflectionClass();
            $reflectionProperty = $class->getProperty($this->configurations[$meta->name]['locale']);
            if (!$reflectionProperty) {
                $column = $this->configurations[$meta->name]['locale'];
                throw new \Gedmo\Exception\RuntimeException("There is no locale or language property ({$column}) found on object: {$meta->name}");
            }
            $reflectionProperty->setAccessible(true);
            $value = $reflectionProperty->getValue($object);
            if (is_string($value) && strlen($value)) {
                $locale = $value;
            }
        }
        $this->validateLocale($locale);
        return strtolower($locale);
    }

    /**
     * Looks for translatable objects being inserted or updated
     * for further processing
     *
     * @param EventArgs $args
     * @return void
     */
    public function onFlush(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $uow = $om->getUnitOfWork();
        // check all scheduled inserts for Translatable objects
        foreach ($ea->getScheduledObjectInsertions($uow) as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            $config = $this->getConfiguration($om, $meta->name);
            if (isset($config['fields'])) {
                $this->handleTranslatableObjectUpdate($ea, $object, true);
            }
            $oid = spl_object_hash($object);
            // check for additional translations
            if (isset($this->additionalTranslations[$oid])) {
                $objectId = $ea->extractIdentifier($om, $object);
                $transClass = $this->getTranslationClass($ea, $meta->name);
                foreach ($this->additionalTranslations[$oid] as $field => $translations) {
                    foreach ($translations as $locale => $content) {
                        $trans = new $transClass;
                        $trans
                            ->setField($field)
                            ->setObjectClass($meta->name)
                            ->setForeignKey($objectId)
                            ->setLocale($locale);
                        $trans->setContent($ea->getTranslationValue($object, $field, $content));
                        if (!$objectId) {
                            $this->pendingTranslationInserts[spl_object_hash($object)][] = $trans;
                        } else {
                            $ea->insertTranslationRecord($trans);
                        }
                    }
                }
            }
        }
        // check all scheduled updates for Translatable entities
        foreach ($ea->getScheduledObjectUpdates($uow) as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            $config = $this->getConfiguration($om, $meta->name);
            if (isset($config['fields'])) {
                // check if there are translation changes
                $changeSet = $ea->getObjectChangeSet($uow, $object);
                foreach ($config['fields'] as $field) {
                    if (array_key_exists($field, $changeSet)) {
                        // needs handling
                        $this->handleTranslatableObjectUpdate($ea, $object, false);
                        break;
                    }
                }
            }
            $oid = spl_object_hash($object);
            // check for additional translations
            if (isset($this->additionalTranslations[$oid])) {
                $objectId = $ea->extractIdentifier($om, $object);
                $transClass = $this->getTranslationClass($ea, $meta->name);
                foreach ($this->additionalTranslations[$oid] as $field => $translations) {
                    foreach ($translations as $locale => $content) {
                        $trans = $ea->findTranslation($objectId, $meta->name, $locale, $field, $transClass);
                        if (!$trans) {
                            $trans = new $transClass;
                            $trans
                                ->setField($field)
                                ->setObjectClass($meta->name)
                                ->setForeignKey($objectId)
                                ->setLocale($locale);
                        }
                        $trans->setContent($ea->getTranslationValue($object, $field, $content));
                        if ($trans->getId()) {
                            $om->persist($trans);
                            $transMeta = $om->getClassMetadata($transClass);
                            $uow->computeChangeSet($transMeta, $trans);
                        } else {
                            $ea->insertTranslationRecord($trans);
                        }
                    }
                }
            }
        }
        // check scheduled deletions for Translatable entities
        foreach ($ea->getScheduledObjectDeletions($uow) as $object) {
            $meta = $om->getClassMetadata(get_class($object));
            $config = $this->getConfiguration($om, $meta->name);
            if (isset($config['fields'])) {
                $objectId = $ea->extractIdentifier($om, $object);
                $transClass = $this->getTranslationClass($ea, $meta->name);
                $ea->removeAssociatedTranslations($objectId, $transClass);
            }
        }
    }

     /**
     * Checks for inserted object to update their translation
     * foreign keys
     *
     * @param EventArgs $args
     * @return void
     */
    public function postPersist(EventArgs $args)
    {
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        // check if entity is tracked by translatable and without foreign key
        if (array_key_exists($meta->name, $this->configurations) && count($this->pendingTranslationInserts)) {
            $oid = spl_object_hash($object);
            if (array_key_exists($oid, $this->pendingTranslationInserts)) {
                // load the pending translations without key
                $objectId = $ea->extractIdentifier($om, $object);
                foreach ($this->pendingTranslationInserts[$oid] as $translation) {
                    $translation->setForeignKey($objectId);
                    $ea->insertTranslationRecord($translation);
                }
                unset($this->pendingTranslationInserts[$oid]);
            }
        }
    }

    /**
     * After object is loaded, listener updates the translations
     * by currently used locale
     *
     * @param EventArgs $args
     * @return void
     */
    public function postLoad(EventArgs $args)
    {
        if ($this->skipOnLoad) {
            return;
        }
        $ea = $this->getEventAdapter($args);
        $om = $ea->getObjectManager();
        $object = $ea->getObject();
        $meta = $om->getClassMetadata(get_class($object));
        $config = $this->getConfiguration($om, $meta->name);

        if (isset($config['fields'])) {
            // fetch translations
            $result = $ea->loadTranslations(
                $object,
                $this->getTranslationClass($ea, $meta->name),
                $this->getTranslatableLocale($object, $meta)
            );
            // translate object's translatable properties
            foreach ($config['fields'] as $field) {
                $translated = '';
                foreach ((array)$result as $entry) {
                    if ($entry['field'] == $field) {
                        $translated = $entry['content'];
                        break;
                    }
                }
                // update translation
                if ($translated || !$this->translationFallback) {
                    $ea->setTranslationValue($object, $field, $translated);
                    // ensure clean changeset
                    $ea->setOriginalObjectProperty(
                        $om->getUnitOfWork(),
                        spl_object_hash($object),
                        $field,
                        $meta->getReflectionProperty($field)->getValue($object)
                    );
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * Validates the given locale
     *
     * @param string $locale - locale to validate
     * @throws InvalidArgumentException if locale is not valid
     * @return void
     */
    protected function validateLocale($locale)
    {
        if (!is_string($locale) || !strlen($locale)) {
            throw new \Gedmo\Exception\InvalidArgumentException('Locale or language cannot be empty and must be set through Listener or Entity');
        }
    }

    /**
     * Creates the translation for object being flushed
     *
     * @param TranslatableAdapter $ea
     * @param object $object
     * @param boolean $isInsert
     * @throws UnexpectedValueException - if locale is not valid, or
     *      primary key is composite, missing or invalid
     * @return void
     */
    private function handleTranslatableObjectUpdate(TranslatableAdapter $ea, $object, $isInsert)
    {
        $om = $ea->getObjectManager();
        $meta = $om->getClassMetadata(get_class($object));
        // no need cache, metadata is loaded only once in MetadataFactoryClass
        $translationClass = $this->getTranslationClass($ea, $meta->name);
        $translationMetadata = $om->getClassMetadata($translationClass);

        // check for the availability of the primary key
        $objectId = $ea->extractIdentifier($om, $object);
        // load the currently used locale
        $locale = $this->getTranslatableLocale($object, $meta);

        $uow = $om->getUnitOfWork();
        $config = $this->getConfiguration($om, $meta->name);
        $translatableFields = $config['fields'];
        foreach ($translatableFields as $field) {
            $translation = null;
            // check if translation allready is created
            if (!$isInsert) {
                $translation = $ea->findTranslation(
                    $objectId,
                    $meta->name,
                    $locale,
                    $field,
                    $translationClass
                );
            }
            // create new translation
            if (!$translation) {
                $translation = new $translationClass();
                $translation->setLocale($locale);
                $translation->setField($field);
                $translation->setObjectClass($meta->name);
                $translation->setForeignKey($objectId);
                $scheduleUpdate = !$isInsert;
            }

            // set the translated field, take value using reflection
            $value = $meta->getReflectionProperty($field)->getValue($object);
            $translation->setContent($ea->getTranslationValue($object, $field));
            if ($isInsert && is_null($objectId)) {
                // if we do not have the primary key yet available
                // keep this translation in memory to insert it later with foreign key
                $this->pendingTranslationInserts[spl_object_hash($object)][] = $translation;
            } else {
                // persist and compute change set for translation
                $om->persist($translation);
                $uow->computeChangeSet($translationMetadata, $translation);
            }
        }
        // check if we have default translation and need to reset the translation
        if (!$isInsert && strlen($this->defaultLocale)) {
            $this->validateLocale($this->defaultLocale);
            $changeSet = $modifiedChangeSet = $ea->getObjectChangeSet($uow, $object);
            foreach ($changeSet as $field => $changes) {
                if (in_array($field, $translatableFields)) {
                    if ($locale != $this->defaultLocale && strlen($changes[0])) {
                        $meta->getReflectionProperty($field)->setValue($object, $changes[0]);
                        $ea->setOriginalObjectProperty($uow, spl_object_hash($object), $field, $changes[0]);
                        unset($modifiedChangeSet[$field]);
                    }
                }
            }
            // cleanup current changeset
            $ea->clearObjectChangeSet($uow, spl_object_hash($object));
            // recompute changeset only if there are changes other than reverted translations
            if ($modifiedChangeSet) {
                foreach ($modifiedChangeSet as $field => $changes) {
                    $ea->setOriginalObjectProperty($uow, spl_object_hash($object), $field, $changes[0]);
                }
                $uow->computeChangeSet($meta, $object);
            }
        }
    }
}