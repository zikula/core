<?php

namespace Symfony\Component\Validator;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Validator\Exception\MappingException;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Mapping\Loader\XmlFilesLoader;
use Symfony\Component\Validator\Mapping\Loader\YamlFilesLoader;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;

/**
 * Creates and configures new validator objects
 *
 * Usually you will use the static method buildDefault() to initialize a
 * factory with default configuration. To this method you can pass various
 * parameters that configure where the validator mapping is found. If you
 * don't pass a parameter, the mapping will be read from annotations.
 *
 * <code>
 * // read from annotations only
 * $factory = ValidatorFactory::buildDefault();
 *
 * // read from XML and YAML, suppress annotations
 * $factory = ValidatorFactory::buildDefault(array(
 *   '/path/to/mapping.xml',
 *   '/path/to/other/mapping.yml',
 * ), false);
 * </code>
 *
 * You then have to call getValidator() to create new validators.
 *
 * <code>
 * $validator = $factory->getValidator();
 * </code>
 *
 * When manually constructing a factory, the default configuration of the
 * validators can be passed to the constructor as a ValidatorContextInterface
 * object.
 *
 * <code>
 * $defaultContext = new ValidatorContext();
 * $defaultContext->setClassMetadataFactory($metadataFactory);
 * $defaultContext->setConstraintValidatorFactory($validatorFactory);
 * $factory = new ValidatorFactory($defaultContext);
 *
 * $form = $factory->getValidator();
 * </code>
 *
 * You can also override the default configuration by calling any of the
 * methods in this class. These methods return a ValidatorContextInterface object
 * on which you can override further settings or call getValidator() to create
 * a form.
 *
 * <code>
 * $form = $factory
 *     ->setClassMetadataFactory($customFactory);
 *     ->getValidator();
 * </code>
 *
 * ValidatorFactory instances should be cached and reused in your application.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 */
class ValidatorFactory implements ValidatorContextInterface
{
    /**
     * Holds the context with the default configuration
     * @var ValidatorContextInterface
     */
    protected $defaultContext;

    /**
     * Builds a validator factory with the default mapping loaders
     *
     * @param  array $mappingFiles          A list of XML or YAML file names
     *                                      where mapping information can be
     *                                      found. Can be empty.
     * @param  Boolean $annotations         Whether to use annotations for
     *                                      retrieving mapping information
     * @param  string $staticMethod         The name of the static method to
     *                                      use, if static method loading should
     *                                      be enabled
     * @throws MappingException             If any of the files in $mappingFiles
     *                                      has neither the extension ".xml" nor
     *                                      ".yml" nor ".yaml"
     */
    static public function buildDefault(array $mappingFiles = array(), $annotations = true, $staticMethod = null)
    {
        $xmlMappingFiles = array();
        $yamlMappingFiles = array();
        $loaders = array();
        $context = new ValidatorContext();

        foreach ($mappingFiles as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            if ($extension === 'xml') {
                $xmlMappingFiles[] = $file;
            } else if ($extension === 'yaml' || $extension === 'yml') {
                $yamlMappingFiles[] = $file;
            } else {
                throw new MappingException('The only supported mapping file formats are XML and YAML');
            }
        }

        if (count($xmlMappingFiles) > 0) {
            $loaders[] = new XmlFilesLoader($xmlMappingFiles);
        }

        if (count($yamlMappingFiles) > 0) {
            $loaders[] = new YamlFilesLoader($yamlMappingFiles);
        }

        if ($annotations) {
            $loaders[] = new AnnotationLoader(new AnnotationReader());
        }

        if ($staticMethod) {
            $loaders[] = new StaticMethodLoader($staticMethod);
        }

        if (count($loaders) > 1) {
            $loader = new LoaderChain($loaders);
        } else if (count($loaders) === 1) {
            $loader = $loaders[0];
        } else {
            throw new MappingException('No mapping loader was found for the given parameters');
        }

        $context->setClassMetadataFactory(new ClassMetadataFactory($loader));
        $context->setConstraintValidatorFactory(new ConstraintValidatorFactory());

        return new static($context);
    }

    /**
     * Sets the given context as default context
     *
     * @param ValidatorContextInterface $defaultContext  A preconfigured context
     */
    public function __construct(ValidatorContextInterface $defaultContext = null)
    {
        $this->defaultContext = null === $defaultContext ? new ValidatorContext() : $defaultContext;
    }

    /**
     * Overrides the class metadata factory of the default context and returns
     * the new context
     *
     * @param  ClassMetadataFactoryInterface $metadataFactory  The new factory instance
     * @return ValidatorContextInterface                       The preconfigured form context
     */
    public function setClassMetadataFactory(ClassMetadataFactoryInterface $metadataFactory)
    {
        $context = clone $this->defaultContext;

        return $context->setClassMetadataFactory($metadataFactory);
    }

    /**
     * Overrides the constraint validator factory of the default context and
     * returns the new context
     *
     * @param  ClassMetadataFactoryInterface $validatorFactory  The new factory instance
     * @return ValidatorContextInterface                        The preconfigured form context
     */
    public function setConstraintValidatorFactory(ConstraintValidatorFactoryInterface $validatorFactory)
    {
        $context = clone $this->defaultContext;

        return $context->setConstraintValidatorFactory($validatorFactory);
    }

    /**
     * Creates a new validator with the settings stored in the default context
     *
     * @return ValidatorInterface  The new validator
     */
    public function getValidator()
    {
        return $this->defaultContext->getValidator();
    }
}
