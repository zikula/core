<?php
/*
 *  $Id: Xml.php 1080 2007-02-10 18:17:08Z jwage $
 *
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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * Doctrine_Parser_Xml
 *
 * @package     Doctrine
 * @subpackage  Parser
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @version     $Revision: 1080 $
 * @author      Jonathan H. Wage <jwage@mac.com>
 */
class Doctrine_Parser_Xml extends Doctrine_Parser
{
    /**
     * dumpData
     * 
     * Convert array to xml and dump to specified path or return the xml
     *
     * @param  string $array Array of data to convert to xml
     * @param  string $path  Path to write xml data to
     * @param string $charset The charset of the data being dumped
     * @return string $xml
     * @return void
     */
    public function dumpData($array, $path = null, $charset = null)
    {
        $data = self::arrayToXml($array, 'data', null, $charset);
        
        return $this->doDump($data, $path);
    }

    /**
     * arrayToXml
     *
     * @param  string $array        Array to convert to xml    
     * @param  string $rootNodeName Name of the root node
     * @param  string $xml          SimpleXmlElement
     * @return string $asXml        String of xml built from array
     */
    public static function arrayToXml($array, $rootNodeName = 'data', $xml = null, $charset = null)
    {
        if ($xml === null) {
            $xml = new SimpleXmlElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName/>");
        }

        foreach($array as $key => $value)
        {
            $key = preg_replace('/[^a-z]/i', '', $key);

            if (is_array($value) && ! empty($value)) {
                $node = $xml->addChild($key);

                foreach ($value as $k => $v) {
                    if (is_numeric($v)) {
                        unset($value[$k]);
                        $node->addAttribute($k, $v);
                    }
                }

                self::arrayToXml($value, $rootNodeName, $node, $charset);
            } else if (is_int($key)) {               
                $xml->addChild($value, 'true');
            } else {
                $charset = $charset ? $charset : 'utf-8';
                if (strcasecmp($charset, 'utf-8') !== 0 && strcasecmp($charset, 'utf8') !== 0) {
                    $value = iconv($charset, 'UTF-8', $value);
                }
                $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                $xml->addChild($key, $value);
            }
        }

        return $xml->asXML();
    }

    /**
     * loadData
     *
     * Load xml file and return array of data
     *
     * @param  string $path  Path to load xml data from
     * @return array  $array Array of data converted from xml
     */
    public function loadData($path)
    {
        $contents = $this->doLoad($path);
        
        $simpleXml = simplexml_load_string($contents);
        
        return $this->prepareData($simpleXml);
    }

    /**
     * prepareData
     *
     * Prepare simple xml to array for return
     *
     * @param  string $simpleXml 
     * @return array  $return
     */
    public function prepareData($simpleXml)
    {
        if ($simpleXml instanceof SimpleXMLElement) {
            $children = $simpleXml->children();
            $return = null;
        }

        foreach ($children as $element => $value) {
            if ($value instanceof SimpleXMLElement) {
                $values = (array) $value->children();

                if (count($values) > 0) {
                    $return[$element] = $this->prepareData($value);
                } else {
                    if ( ! isset($return[$element])) {
                        $return[$element] = (string) $value;
                    } else {
                        if ( ! is_array($return[$element])) {
                            $return[$element] = array($return[$element], (string) $value);
                        } else {
                            $return[$element][] = (string) $value;
                        }
                    }
                }
            }
        }

        if (is_array($return)) {
            return $return;
        } else {
            return array();
        }
    }
}
