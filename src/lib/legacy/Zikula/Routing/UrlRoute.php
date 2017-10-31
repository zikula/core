<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Url route base class.
 *
 * @deprecated
 */
class Zikula_Routing_UrlRoute
{
    /**
     * The pattern for the url scheme treated by this route.
     *
     * @var string
     */
    protected $urlPattern;

    /**
     * Array with default values for the parameters.
     *
     * @var array
     */
    protected $defaults;

    /**
     * Array with input requirement checks for regex expression.
     *
     * @var array
     */
    protected $requirements;

    /**
     * Whether the regular expression for this route has been generated or not.
     *
     * @var boolean
     */
    protected $compiled;

    /**
     * The regular expression for catching urls for this route.
     *
     * @var string
     */
    protected $regex;

    /**
     * Array with variables determined during regex compilation.
     *
     * @var array
     */
    protected $variables;

    /**
     * Array with tokens determined during regex compilation.
     *
     * @var array
     */
    protected $tokens;

    /**
     * Constructor.
     *
     * @param string $urlPattern   Pattern for url scheme
     * @param array  $defaults     Default values for parameters
     * @param array  $requirements Input requirement checks for regex
     */
    public function __construct($urlPattern, array $defaults, array $requirements)
    {
        @trigger_error('UrlRoute is deprecated, please use Symfony routing instead.', E_USER_DEPRECATED);

        // check if given url pattern ends with a trailing slash
        if ('/' != substr($urlPattern, -1)) {
            // add missing trailing slash
            $urlPattern .= '/';
        }

        // append a star for automatic addition of all params which were not considered in the pattern
        $urlPattern .= '*';

        // store given arguments
        $this->urlPattern = $urlPattern;
        $this->defaults = $defaults;
        $this->requirements = $requirements;

        // set reasonable default values
        $this->compiled = false;
        $this->variables = [];
        $this->tokens = [];
    }

    /**
     * Create url for given arguments.
     *
     * @param array $params Argument values for url parameters in this route
     *
     * @throws InvalidArgumentException If required params are missing
     *
     * @return string Url
     */
    public function generate($params)
    {
        // compile the regex if not already done
        if (!$this->compiled) {
            $this->compile();
        }

        // create a list of all parameters, merging the default values with given input arguments
        $allParams = array_merge($this->defaults, $params);

        // check whether there are some variables required, but not specified or given
        $diff = array_diff_key(array_flip($this->variables), $allParams);
        if ($diff) {
            throw new InvalidArgumentException('The "' . $this->urlPattern . '" route has some missing mandatory parameters (' . implode(', ', $diff) . ').');
        }

        // start creation of the url
        $url = '';
        // process the pattern by handling each single token (read out during compilation)
        foreach ($this->tokens as $token) {
            switch ($token[0]) {
                case 'variable':
                    $url .= urlencode($allParams[$token[1]]);
                    break;
                case 'text':
                    // exclude star sign for additional parameters
                    if ('*' != $token[1]) {
                        $url .= $token[1];
                    }
                    break;
                case 'separator':
                    $url .= $token[1];
                    break;
            }
        }

        // check if url ends with a trailing slash
        if ('/' == substr($url, -1)) {
            // remove the trailing slash
            $url = substr($url, 0, strlen($url) - 1);
        }

        // look for the star sign
        if (false !== strpos($this->regex, '<_star>')) {
            // append additional parameters
            $additionalArgs = [];
            foreach (array_diff_key($allParams, array_flip($this->variables), $this->defaults) as $key => $value) {
                $additionalArgs[] = urlencode($key) . '/' . urlencode($value);
            }
            $url .= '/' . implode('/', $additionalArgs);
        }

        // return the result
        return $url;
    }

    /**
     * Parse a given url and return the params read out of it.
     *
     * @param string $url Input url
     *
     * @return mixed array Eith determined params or false on error
     */
    public function matchesUrl($url)
    {
        // compile the regex if not already done
        if (!$this->compiled) {
            $this->compile();
        }

        // check if the regex of this route instance does fit to given url
        if (!preg_match($this->regex, $url, $matches)) {
            return false;
        }

        // initialise list of parameters to be collected
        $parameters = [];

        // check for * in urlPattern
        if (isset($matches['_star'])) {
            // process additional parameters
            $additionalArgs = explode('/', $matches['_star']);
            $tmp = $additionalArgs;
            for ($i = 0, $max = count($additionalArgs); $i < $max; $i += 2) {
                if (!empty($tmp[$i])) {
                    $parameters[$tmp[$i]] = isset($tmp[$i + 1]) ? $tmp[$i + 1] : true;
                    System::queryStringSetVar($tmp[$i], $parameters[$tmp[$i]]);
                }
            }
            // unset this match to exclude it in further processing
            unset($matches['_star']);
        }

        // add default values for all parameters
        $parameters = array_merge($parameters, $this->defaults);

        // process all matches and add according variables
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Checks if this route can treat a given set of parameters.
     *
     * @param array $params The arguments which should be processed
     *
     * @return boolean Whether this route matches the given set of parameters or not
     */
    public function matchParameters($params)
    {
        // compile the regex if not already done
        if (!$this->compiled) {
            $this->compile();
        }

        if (!is_array($params)) {
            return false;
        }

        // create a list of all parameters, merging the default values with given input arguments
        $allParams = array_merge($this->defaults, $params);

        // all $variables must be defined in the $allParams array
        if (array_diff_key(array_flip($this->variables), $allParams)) {
            return false;
        }

        // check requirements
        foreach ($this->variables as $variable) {
            // no value no check
            if (!$allParams[$variable]) {
                continue;
            }

            if (!preg_match('#' . $this->requirements[$variable] . '#', $allParams[$variable])) {
                return false;
            }
        }

        // check that $params does not override a default value that is not a variable
        foreach ($this->defaults as $key => $value) {
            if (!isset($this->variables[$key]) && $allParams[$key] != $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compiles the url pattern including creation of regex and collecting tokens as well as variables.
     *
     * @throws InvalidArgumentException With invalid pattern
     *
     * @return boolean True
     */
    protected function compile()
    {
        // return if compilation has been done before already
        if ($this->compiled) {
            return true;
        }

        // parse pattern
        $pattern = $this->urlPattern;
        while (strlen($pattern)) {
            if (preg_match('#^:([a-zA-z0-6_]+)#', $pattern, $match)) {
                // variable
                $name = $match[1];
                $this->tokens[] = ['variable', $name];
                $this->variables[] = $name;

                $pattern = substr($pattern, strlen($match[0]));
            } elseif (preg_match('#^(?:/|\.|\-)#', $pattern, $match)) {
                // separator
                $this->tokens[] = ['separator', $match[0]];

                $pattern = substr($pattern, strlen($match[0]));
            } elseif (preg_match('#^(.+?)(?:(?:/|\.|\-)|$)#', $pattern, $match)) {
                // text
                $text = $match[1];
                $this->tokens[] = ['text', $text];

                $pattern = substr($pattern, strlen($match[1]));
            } else {
                throw new InvalidArgumentException('Invalid pattern "' . $this->urlPattern . '" near "' . $pattern . '"!');
            }
        }

        // create regex
        $regex = '#^';
        for ($i = 0, $max = count($this->tokens); $i < $max; $i++) {
            $token = $this->tokens[$i];
            if ('variable' == $token[0]) {
                if (!isset($this->requirements[$token[1]])) {
                    $this->requirements[$token[1]] = '[^/\.\-]+';
                }
                $regex .= '(?P<'.$token[1].'>'.$this->requirements[$token[1]].')';
            } elseif ('text' == $token[0] || 'separator' == $token[0]) {
                if ('*' == $token[1]) {
                    if ($this->tokens[$i - 1] && $this->tokens[$i - 1][0] == 'separator') {
                        $sep_regex = $this->tokens[$i - 1][1];
                    } else {
                        $sep_regex = '/';
                    }
                    $regex .= '(?:' . $sep_regex . '(?P<_star>.*))?';
                } else {
                    if ('separator' == $token[0] && $this->tokens[$i + 1] && $this->tokens[$i + 1][1] == '*') {
                    } else {
                        $regex .= $token[1];
                    }
                }
            }
        }
        $regex .= '$#';

        // store the result
        $this->regex = $regex;

        // activate compiled flag
        $this->compiled = true;

        return true;
    }
}
