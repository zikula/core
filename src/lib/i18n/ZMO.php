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
 * Provides a simple gettext replacement that works independently from the system's gettext abilities.
 *
 * It can read MO files and use them for translating strings.
 * The files are passed to gettext_reader as a Stream (see streams.php)
 *
 * This version has the ability to cache all strings and translations to
 * speed up the string lookup.
 * While the cache is enabled by default, it can be switched off with the
 * second parameter in the constructor (e.g. whenusing very large MO files
 * that you don't want to keep in memory)
 */
class ZMO
{
    /**
     * Public variable that holds error code (0 if no error).
     *
     * @var integer
     */
    public $error = 0;

    /**
     * Byte order.
     *
     * Possible values:
     *  0: low endian
     *  1: big endian.
     *
     * @var integer
     */
    private $byteorder = 0;

    /**
     * Stream.
     *
     * @var StreamReader_Abstract
     */
    private $stream = null;

    /**
     * Short circuit.
     *
     * @var boolean
     */
    private $short_circuit = false;

    /**
     * Enable cache.
     *
     * @var boolean
     */
    private $enable_cache = false;

    /**
     * Offset of original table.
     *
     * @var integer
     */
    private $originals = null;

    /**
     * Offset of translation table.
     *
     * @var integer
     */
    private $translations = null;

    /**
     * Cache header field for plural forms.
     *
     * @var string
     */
    private $pluralheader = null;

    /**
     * Total string count.
     *
     * @var integer
     */
    private $total = 0;

    /**
     * Table for original strings (offsets).
     *
     * @var array
     */
    private $table_originals = null;

    /**
     * Table for translated strings (offsets).
     *
     * @var array
     */
    private $table_translations = null;

    /**
     * Cache translations.
     *
     * Original -> translation mapping.
     *
     * @var array
     */
    private $cache_translations = null;

    /**
     * Encoding.
     *
     * @var string
     */
    private $encoding;

    // Methods

    /**
     * Constructor.
     *
     * @param StreamReader_Abstract $reader       The StreamReader object
     * @param boolean               $enable_cache Enable or disable caching of strings (default on)
     */
    public function __construct(StreamReader_Abstract $reader, $enable_cache = true)
    {
        // If there isn't a StreamReader, turn on short circuit mode.
        if ($reader->getError()) {
            $this->short_circuit = true;

            return;
        }

        // Caching can be turned off
        $this->enable_cache = $enable_cache;

        $this->stream = $reader;
        $magic = $this->readint();

        if ($magic == -1794895138 || $magic == 2500072158) {
            // (int)0x950412de; PHP 5.2 wont convert this properly
            $this->byteorder = 0;
        } elseif ($magic == -569244523 || $magic == 3725722773) {
            // (int)0xde120495; PHP 5.2 wont convert this properly
            $this->byteorder = 1;
        } else {
            // not MO file
            $this->error = 1;

            return false;
        }

        // TODO D Do we care about revision?
        $revision = $this->readint();

        $this->total = $this->readint();
        $this->originals = $this->readint();
        $this->translations = $this->readint();
        $this->encoding = (version_compare(\PHP_VERSION, '5.6.0', '<')) ? ini_get('mbstring.internal_encoding') : ini_get('default_charset');
    }

    /**
     * Error getter.
     *
     * @return string Error
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Error getter.
     *
     * @return string Error
     */
    public function getOriginals()
    {
        return $this->originals;
    }

    /**
     * Translations getter.
     *
     * @return integer Translations
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Plural header getter.
     *
     * @return string Plural header
     */
    public function getPluralheader()
    {
        return $this->pluralheader;
    }

    /**
     * Total getter.
     *
     * @return integer Total
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Cache translations getter.
     *
     * @return array Cache translations
     */
    public function getCache_translations()
    {
        return $this->cache_translations;
    }

    /**
     * Encoding getter.
     *
     * @return string Encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set encoding.
     *
     * @param string $encoding Encoding
     *
     * @return void
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * Encodes text.
     *
     * @param string $text Text
     *
     * @return string
     */
    public function encode($text)
    {
        $source_encoding = mb_detect_encoding($text);
        if ($source_encoding != $this->encoding) {
            return mb_convert_encoding($text, $this->encoding, $source_encoding);
        }

        return $text;
    }

    /**
     * Reads a 32bit Integer from the Stream.
     *
     * @return integer from the Stream
     */
    private function readint()
    {
        if ($this->byteorder == 0) {
            // low endian
            $data = unpack('V', $this->stream->read(4));
        } else {
            // big endian
            $data = unpack('N', $this->stream->read(4));
        }

        return array_shift($data);
    }

    /**
     * Reads an array of Integers from the Stream.
     *
     * @param integer $count How many elements should be read
     *
     * @return array Array of Integers
     */
    public function readintarray($count)
    {
        if ($this->byteorder == 0) {
            // low endian
            return unpack('V' . $count, $this->stream->read(4 * $count));
        }

        // big endian
        return unpack('N' . $count, $this->stream->read(4 * $count));
    }

    /**
     * Loads the translation tables from the MO file into the cache.
     *
     * If caching is enabled, also loads all strings into a cache
     * to speed up translation lookups.
     *
     * @return void
     */
    private function load_tables()
    {
        if (is_array($this->cache_translations) && is_array($this->table_originals) && is_array($this->table_translations)) {
            return;
        }

        // get original and translations tables
        $this->stream->seekto($this->originals);
        $this->table_originals = $this->readintarray($this->total * 2);
        $this->stream->seekto($this->translations);
        $this->table_translations = $this->readintarray($this->total * 2);

        if ($this->enable_cache) {
            $this->cache_translations = [];
            // read all strings in the cache
            for ($i = 0; $i < $this->total; $i++) {
                $this->stream->seekto($this->table_originals[$i * 2 + 2]);
                $original = $this->stream->read($this->table_originals[$i * 2 + 1]);
                $this->stream->seekto($this->table_translations[$i * 2 + 2]);
                $translation = $this->stream->read($this->table_translations[$i * 2 + 1]);
                $this->cache_translations[$original] = $translation;
            }
        }
    }

    /**
     * Returns a string from the "originals" table.
     *
     * @param integer $num Offset number of original string
     *
     * @return string Requested string if found, otherwise ''
     */
    private function get_original_string($num)
    {
        $length = $this->table_originals[$num * 2 + 1];
        $offset = $this->table_originals[$num * 2 + 2];
        if (!$length) {
            return '';
        }
        $this->stream->seekto($offset);
        $data = $this->stream->read($length);

        return (string)$data;
    }

    /**
     * Returns a string from the "translations" table.
     *
     * @param integer $num Offset number of original string
     *
     * @return string Requested string if found, otherwise ''
     */
    private function get_translation_string($num)
    {
        $length = $this->table_translations[$num * 2 + 1];
        $offset = $this->table_translations[$num * 2 + 2];
        if (!$length) {
            return '';
        }
        $this->stream->seekto($offset);
        $data = $this->stream->read($length);
        $data = $this->encode($data);

        return (string)$data;
    }

    /**
     * Binary search for string
     *
     * @param string  $string String
     * @param integer $start  Internally used in recursive function
     * @param integer $end    Internally used in recursive function
     *
     * @return integer String number (offset in originals table)
     */
    private function find_string($string, $start = -1, $end = -1)
    {
        if ($start == -1 || $end == -1) {
            // find_string is called with only one parameter, set start end end
            $start = 0;
            $end = $this->total;
        }
        if (abs($start - $end) <= 1) {
            // We're done, now we either found the string, or it doesn't exist
            $txt = $this->get_original_string($start);
            if ($string == $txt) {
                return $start;
            }

            return -1;
        }
        if ($start > $end) {
            // start > end -> turn around and start over
            return $this->find_string($string, $end, $start);
        }

        // Divide table in two parts
        $half = (int)(($start + $end) / 2);
        $cmp = strcmp($string, $this->get_original_string($half));
        if ($cmp == 0) {
            // string is exactly in the middle => return it
            return $half;
        }
        if ($cmp < 0) {
            // The string is in the upper half
            return $this->find_string($string, $start, $half);
        }

        // The string is in the lower half
        return $this->find_string($string, $half, $end);
    }

    /**
     * Translates a string.
     *
     * @param string $string Strint to be translated
     *
     * @return string Translated string (or original, if not found)
     */
    public function translate($string)
    {
        if ($this->short_circuit) {
            return $string;
        }
        $this->load_tables();

        if ($this->enable_cache) {
            // Caching enabled, get translated string from cache
            if (array_key_exists($string, $this->cache_translations)) {
                return $this->cache_translations[$string];
            }

            return $string;
        } else {
            // Caching not enabled, try to find string
            $num = $this->find_string($string);
            if ($num == -1) {
                return $string;
            }

            return $this->get_translation_string($num);
        }
    }

    /**
     * Get possible plural forms from MO header.
     *
     * @return string plural form header
     */
    private function get_plural_forms()
    {
        // lets assume message number 0 is header
        // this is true, right?
        $this->load_tables();

        // cache header field for plural forms
        if (!is_string($this->pluralheader)) {
            if ($this->enable_cache) {
                $header = $this->cache_translations[''];
            } else {
                $header = $this->get_translation_string(0);
            }
            if (preg_match('#(nplurals=\d+;\s{0,}plural=[\s\d\w\(\)\?:%><=!&\|]+)\s{0,};\s{0,}\\n#', $header, $regs)) {
                $expr = $regs[1];
            } else {
                $expr = "nplurals=2; plural=n == 1 ? 0 : 1;";
            }
            $this->pluralheader = $expr .';';
        }

        return $this->pluralheader;
    }

    /**
     * Detects which plural form to take
     *
     * @param integer $n Count
     *
     * @return integer Array index of the right plural form
     */
    private function select_string($n)
    {
        $string = $this->get_plural_forms();
        $string = str_replace('nplurals', "\$total", $string);
        $string = str_replace("n", $n, $string);
        $string = str_replace('plural', "\$plural", $string);

        $total = 0;
        $plural = 0;

        eval($string);
        if ($plural >= $total) {
            $plural = $total - 1;
        }

        return $plural;
    }

    /**
     * Plural version of gettext.
     *
     * @param string $single Single
     * @param string $plural Plural
     * @param string $number Number
     *
     * @return string Translated plural form
     */
    public function ngettext($single, $plural, $number)
    {
        if ($this->short_circuit) {
            if ($number != 1) {
                return $plural;
            }

            return $single;
        }

        // find out the appropriate form
        $select = $this->select_string($number);

        // this should contains all strings separated by nulls
        $key = $single . chr(0) . $plural;

        if ($this->enable_cache) {
            if (!array_key_exists($key, $this->cache_translations)) {
                return ($number != 1) ? $plural : $single;
            }

            $result = $this->cache_translations[$key];
            $list = explode(chr(0), $result);

            return $list[$select];
        } else {
            $num = $this->find_string($key);
            if ($num == -1) {
                return ($number != 1) ? $plural : $single;
            }

            $result = $this->get_translation_string($num);
            $list = explode(chr(0), $result);

            return $list[$select];
        }
    }
}
