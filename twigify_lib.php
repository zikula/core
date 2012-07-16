<?php

class Twiggifier
{
    private $template;
    private $matches;

    public function __construct($template, $matches)
    {
        $this->template = $template;
        $this->matches = $matches;
    }

    public function stripString($string)
    {
        foreach ($this->matches[0] as $key => $match) {
            $match = str_replace($string, '', $match);
            $this->template = str_replace($this->matches[0][$key], $match, $this->template);
        }
    }

    public function replaceString($search, $replace)
    {
        foreach ($this->matches[0] as $key => $match) {
            $match = str_replace($search, $replace, $match);
            $this->template = str_replace($this->matches[0][$key], $match, $this->template);
        }
    }

    public function convertIf($match, $key, $command = 'if')
    {
        // {if ....}
        if (!preg_match('/^if (.+?)\s{0,}$/', $match, $matches)) {
            return;
        }

        $content = '';
        $array = explode(' ', $matches[1]);
        foreach ($array as $element) {
            switch ($element) {
                case 'eq':
                    $content .= '==';
                    break;
                case 'neq':
                    $content .= '!=';
                    break;
                default:
                    $content .= $element;
                    break;
            }

            $content .= ' ';
        }

        $content = rtrim($content, ' ');

        $string = "{% $command ".str_replace('$', '', $content)." %}";
        $this->template = str_replace($this->matches[0][$key], $string, $this->template);
    }

    public function convertElseif($match, $key)
    {
        $this->convertIf($match, $key, 'elseif');
    }

    public function convertModurl($match, $key)
    {
        return; //todo
        // {modurl ....}
        if (!preg_match('/^modurl (.+?)\s{0,}$/', $match, $matches)) {
            return;
        }

        $content = '';
        $array = explode(' ', $matches[1]);
        foreach ($array as $element) {
            switch ($element) {
                case 'modname':
                    $content .= '';
                    break;
                case 'type':
                    $content .= '';
                    break;
                case 'func':
                    $content .= '';
                    break;
                default:
                    $content .= $element;
                    break;
            }

            $content .= ' ';
        }

        $content = rtrim($content, ' ');

        $string = "{{ path('".str_replace('$', '', $content)."') }}";
        $this->template = str_replace($this->matches[0][$key], $string, $this->template);
    }

    public function convertPagesetvar($match, $key, $command = 'pagesetvar')
    {
        preg_match('/^'.$command.' (.+?)\s{0,}$/', $match, $matches);

        $name = '';
        if (preg_match('/name=(?:"{0,1}|\'{0,1})(.+?)(?:"{0,1}|\'{0,1})\s|$/', $matches[1], $nameMatches)) {
            $name = $nameMatches[1];
        }

        $value = '';
        if (preg_match('/value=(?:"{0,1}|\'{0,1})(.+?)(?:"{0,1}|\'{0,1})(?:\s|$)/', $matches[1], $valueMatches)) {
            $value = $valueMatches[1];
        }

        $string = "{{ $command('$name', '$value') }}";
        $this->template = str_replace($this->matches[0][$key], $string, $this->template);
    }

    public function convertPageaddvar($match, $key)
    {
        $this->convertPagesetvar($match, $key, 'pageaddvar');
    }

    public function convertVariable($key)
    {
        $match = str_replace('$', '', $this->matches[1][$key]);
        $match = "{{ $match }}";
        $this->template = str_replace($this->matches[0][$key], $match, $this->template);
    }

    public function convertGt($match, $key)
    {
        if (preg_match('/^gt text="(.+?)"\s{0,}$/', $match, $matches)) {
            // gt text=""
            $string = "{{ __(\"{$matches[1]}\") }}";
            $this->template = str_replace($this->matches[0][$key], $string, $this->template);

            return;
        } elseif (preg_match('/^gt text=\'(.+?)\'\s{0,}$/', $match, $matches)) {
            // gt text=''
            $string = "{{ __('{$matches[1]}') }}";
            $this->template = str_replace($this->matches[0][$key], $string, $this->template);

            return;
        } elseif (preg_match('/^gt text="(.+?)"\s{1,}tag\d{1}="(.+?)"\s{0,}$/', $match, $matches)) {
            // gt text="" tag0=...
            // todo
        }
    }

    /**
     * Resolves the smarty call
     *
     * @param $match
     */
    public function resolve($match)
    {
        preg_match('/(.+?)(?:\s{1,}|$)/', $match, $matches);

        return $matches[1];
    }

    public function setMatches($matches)
    {
        $this->matches = $matches;

        return $this;
    }

    public function getMatches()
    {
        return $this->matches;
    }

    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate()
    {
        return $this->template;
    }
}

