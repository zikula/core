<?php

/*
 * This file is part of Twig.
 *
 * (c) 2010 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Integration test helper
 *
 * @package twig
 * @author  Fabien Potencier <fabien@symfony.com>
 * @author  Karma Dordrak <drak@zikula.org>
 */
abstract class Zikula_Twig_Test_IntegrationTestCase extends PHPUnit_Framework_TestCase
{
    abstract public function getTests();
    abstract public function testIntegration($file, $message, $condition, $templates, $exception, $outputs);

    /**
     * Data provider of fixtures
     *
     * @param $fixturesDir
     *
     * @return array
     * @throws InvalidArgumentException
     */
    protected function getFixtures($fixturesDir)
    {
        $fixturesDir = realpath($fixturesDir);
        $tests = array();

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixturesDir), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
            if (!preg_match('/\.test$/', $file)) {
                continue;
            }

            $test = file_get_contents($file->getRealpath());

            if (preg_match('/
                    --TEST--\s*(.*?)\s*(?:--CONDITION--\s*(.*))?\s*((?:--TEMPLATE(?:\(.*?\))?--(?:.*))+)\s*--EXCEPTION--\s*(.*)/sx', $test, $match)) {
                $message = $match[1];
                $condition = $match[2];
                $templates = self::parseTemplates($match[3]);
                $exception = $match[4];
                $outputs = array(null, array(), null, '');
            } elseif (preg_match('/--TEST--\s*(.*?)\s*(?:--CONDITION--\s*(.*))?\s*((?:--TEMPLATE(?:\(.*?\))?--(?:.*?))+)--DATA--.*?--EXPECT--.*/s', $test, $match)) {
                $message = $match[1];
                $condition = $match[2];
                $templates = self::parseTemplates($match[3]);
                $exception = false;
                preg_match_all('/--DATA--(.*?)(?:--CONFIG--(.*?))?--EXPECT--(.*?)(?=\-\-DATA\-\-|$)/s', $test, $outputs, PREG_SET_ORDER);
            } else {
                throw new InvalidArgumentException(sprintf('Test "%s" is not valid.', str_replace($fixturesDir.'/', '', $file)));
            }

            $tests[] = array(str_replace($fixturesDir.'/', '', $file), $message, $condition, $templates, $exception, $outputs);
        }

        return $tests;
    }

    protected function doIntegrationTest($file, $message, $condition, $templates, $exception, $outputs)
    {
        if ($condition) {
            eval('$ret = '.$condition.';');
            if (!$ret) {
                $this->markTestSkipped($condition);
            }
        }

        $loader = new Twig_Loader_Array($templates);

        foreach ($outputs as $match) {
            $config = array_merge(array(
                'cache' => false,
                'strict_variables' => true,
            ), $match[2] ? eval($match[2].';') : array());
            $twig = new Twig_Environment($loader, $config);
            $twig->addExtension(new Zikula_Twig_Test_TestExtension());
            $twig->addExtension(new Twig_Extension_Debug());
            $policy = new Twig_Sandbox_SecurityPolicy(array(), array(), array(), array(), array());
            $twig->addExtension(new Twig_Extension_Sandbox($policy, false));
            $twig->addGlobal('global', 'global');

            try {
                $template = $twig->loadTemplate('index.twig');
            } catch (Exception $e) {
                if (false !== $exception) {
                    $this->assertEquals(trim($exception), trim(sprintf('%s: %s', get_class($e), $e->getMessage())));

                    return;
                }

                if ($e instanceof Twig_Error_Syntax) {
                    $e->setTemplateFile($file);

                    throw $e;
                }

                throw new Twig_Error(sprintf('%s: %s', get_class($e), $e->getMessage()), -1, $file, $e);
            }

            try {
                $output = trim($template->render(eval($match[1].';')), "\n ");
            } catch (Exception $e) {
                if (false !== $exception) {
                    $this->assertEquals(trim($exception), trim(sprintf('%s: %s', get_class($e), $e->getMessage())));

                    return;
                }

                if ($e instanceof Twig_Error_Syntax) {
                    $e->setTemplateFile($file);
                } else {
                    $e = new Twig_Error(sprintf('%s: %s', get_class($e), $e->getMessage()), -1, $file, $e);
                }

                $output = trim(sprintf('%s: %s', get_class($e), $e->getMessage()));
            }
            $expected = trim($match[3], "\n ");

            if ($expected != $output)  {
                echo 'Compiled template that failed:';

                foreach (array_keys($templates) as $name)  {
                    echo "Template: $name\n";
                    $source = $loader->getSource($name);
                    echo $twig->compile($twig->parse($twig->tokenize($source, $name)));
                }
            }
            $this->assertEquals($expected, $output, $message.' (in '.$file.')');
        }
    }

    protected static function parseTemplates($test)
    {
        $templates = array();
        preg_match_all('/--TEMPLATE(?:\((.*?)\))?--(.*?)(?=\-\-TEMPLATE|$)/s', $test, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $templates[($match[1] ? $match[1] : 'index.twig')] = $match[2];
        }

        return $templates;
    }
}

class Zikula_Twig_Test_TestExtension extends Twig_Extension
{
    public function getTokenParsers()
    {
        return array(
            new Zikula_Twig_Test_TestTokenParser_☃(),
        );
    }

    public function getFilters()
    {
        return array(
            '☃'                => new Twig_Filter_Method($this, '☃Filter'),
            'escape_and_nl2br' => new Twig_Filter_Method($this, 'escape_and_nl2br', array('needs_environment' => true, 'is_safe' => array('html'))),
            'nl2br'            => new Twig_Filter_Method($this, 'nl2br', array('pre_escape' => 'html', 'is_safe' => array('html'))),
            'escape_something' => new Twig_Filter_Method($this, 'escape_something', array('is_safe' => array('something'))),
            'preserves_safety' => new Twig_Filter_Method($this, 'preserves_safety', array('preserves_safety' => array('html'))),
            '*_path'           => new Twig_Filter_Method($this, 'dynamic_path'),
            '*_foo_*_bar'      => new Twig_Filter_Method($this, 'dynamic_foo'),
        );
    }

    public function getFunctions()
    {
        return array(
            '☃'           => new Twig_Function_Method($this, '☃Function'),
            'safe_br'     => new Twig_Function_Method($this, 'br', array('is_safe' => array('html'))),
            'unsafe_br'   => new Twig_Function_Method($this, 'br'),
            '*_path'      => new Twig_Function_Method($this, 'dynamic_path'),
            '*_foo_*_bar' => new Twig_Function_Method($this, 'dynamic_foo'),
        );
    }

    public function ☃Filter($value)
    {
        return "☃{$value}☃";
    }

    public function ☃Function($value)
    {
        return "☃{$value}☃";
    }

    /**
     * nl2br which also escapes, for testing escaper filters
     */
    public function escape_and_nl2br($env, $value, $sep = '<br />')
    {
        return $this->nl2br(twig_escape_filter($env, $value, 'html'), $sep);
    }

    /**
     * nl2br only, for testing filters with pre_escape
     */
    public function nl2br($value, $sep = '<br />')
    {
        // not secure if $value contains html tags (not only entities)
        // don't use
        return str_replace("\n", "$sep\n", $value);
    }

    public function dynamic_path($element, $item)
    {
        return $element.'/'.$item;
    }

    public function dynamic_foo($foo, $bar, $item)
    {
        return $foo.'/'.$bar.'/'.$item;
    }

    public function escape_something($value)
    {
        return strtoupper($value);
    }

    public function preserves_safety($value)
    {
        return strtoupper($value);
    }

    public function br()
    {
        return '<br />';
    }

    public function getName()
    {
        return 'test';
    }
}

class Zikula_Twig_Test_TestTokenParser_☃ extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new Twig_Node_Print(new Twig_Node_Expression_Constant('☃', -1), -1);
    }

    public function getTag()
    {
        return '☃';
    }
}
