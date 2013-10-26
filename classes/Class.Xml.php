<?php
/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *          - replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *          - fixed a edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *          - fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *          - Added support for CDATA section using @cdata instead of @value.
 * Version: 0.5 (07 December 2011)
 *          - Changed logic to check numeric array indices not starting from 0.
 * Version: 0.6 (04 March 2012)
 *          - Code now doesn't @cdata to be placed in an empty array
 * Version: 0.7 (24 March 2012)
 *          - Reverted to version 0.5
 * Version: 0.8 (02 May 2012)
 *          - Removed htmlspecialchars() before adding to text node or attributes.
 *
 * Usage:
 *       $xml = Array2XML::createXML('root_node_name', $php_array);
 *       echo $xml->saveXML();
 */
/**
 * XML2Array: A class to convert XML to array in PHP
 * It returns the array which can be converted back to XML using the Array2XML script
 * It takes an XML string or a DOMDocument object as an input.
 *
 * See Array2XML: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-xml-to-array-in-php-xml2array
 * License: Apache License 2.0
 *          http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (07 Dec 2011)
 * Version: 0.2 (04 Mar 2012)
 * 			Fixed typo 'DomDocument' to 'DOMDocument'
 *
 * Usage:
 *       $array = XML2Array::createArray($xml);
 * @package XML 
 */
class XML {
    private static $xml = null;
    private static $encoding = 'UTF-8';

    /**
     * Initialize the root XML node [optional]
     * @param string $version
     * @param string $encoding
     * @param bool $formatOutput
     */
    public static function Initialize($version = '1.0', $encoding = 'UTF-8', $formatOutput = true) {
        self::$xml = new DOMDocument($version, $encoding);
        self::$xml->formatOutput = $formatOutput;
		    self::$encoding = $encoding;
    }

    /**
     * Convert an XML to Array
     * @param string|DOMDocument $inputXml Input string or XML DOMDocument object
     * @return DOMDocument
     */
    public static function CreateArray($inputXml) {
        $xml = self::getXMLRoot();
    		if (is_string($inputXml)) {
    		    $parsed = $xml->loadXML($inputXml);
    		    if (!$parsed) {
                trigger_error('Error parsing the XML string.', E_USER_ERROR);
            }
    		}
        elseif (is_a($inputXml, 'DOMDocument')) {
            $xml = self::$xml = $inputXml;
        }
        else {
            trigger_error('The input XML object should be of type: DOMDocument.', E_USER_ERROR);      			
    		}
        
    		$array[$xml->documentElement->tagName] = self::ConvertXmlToArray($xml->documentElement);
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $array;
    }

    /**
     * Convert an Array to XML
     * @param DOMNode $node - XML as a string or as an object of DOMDocument
     * @return mixed
     */
    private static function ConvertXmlToArray(DOMNode $node) {
    		$output = array();
    
    		switch ($node->nodeType) {
      			case XML_CDATA_SECTION_NODE:
      				  $output['@cdata'] = trim($node->textContent);
      			break;
      
      			case XML_TEXT_NODE:
      				  $output = trim($node->textContent);
      			break;
      
      			case XML_ELEMENT_NODE:
        				// for each child node, call the covert function recursively
        				for ($i = 0, $length = $node->childNodes->length; $i < $length; $i++) {
          					$child = $node->childNodes->item($i);
          					$value = self::ConvertXmlToArray($child);
          					if (isset($child->tagName)) {
            						$tag = $child->tagName;
            
            						// assume more nodes of same kind are coming
            						if (!isset($output[$tag])) {
            							$output[$tag] = array();
            						}
            						$output[$tag][] = $value;
          					}
                    else {
            						//check if it is not an empty text node
            						if ($value !== '') {
            						    $output = $value;
            						}
          					}
        				}
    
        				if(is_array($output)) {
          					// if only one node of its kind, assign it directly instead if array($value);
          					foreach ($output as $tag => $value) {
          						  if (is_array($value) && count($value)==1) {
          							   $output[$tag] = $value[0];
          						  }
          					}
          					//replace null nodes with empty strings
                    if (empty($output)) {
          						  $output = '';
          					}
        				}
    
        				// loop through the attributes and collect them
        				if ($node->attributes->length) {
        				    $attributes = array();
                    foreach($node->attributes as $attrName => $attrNode) {
        						    $attributes[$attrName] = (string) $attrNode->value;
        					  }
                    // if its an leaf node, store the value in @value instead of directly storing it.
                    if(!is_array($output)) {
        						    $output = array('@value' => $output);
                    }
                    $output['@attributes'] = $attributes;
                }
    				break;
    		}
    		return $output;
    }

    /**
     * Convert an Array to XML
     * @param string $nodeName - name of the root node to be converted
     * @param array $inputArray - aray to be converterd
     * @return DomDocument
     */
    public static function CreateXML($nodeName, array $inputArray) {
        $xml = self::GetXMLRoot();
        $xml->appendChild(self::ConvertArrayToXml($nodeName, $inputArray));
        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $xml;
    }

    /**
     * Convert an Array to XML
     * @param string $nodeName - name of the root node to be converted
     * @param array $inputArray - aray to be converterd
     * @throws Exception
     * @return DOMDocument
     */
    private static function ConvertArrayToXml($nodeName, array $inputArray) {
        /** @var $xml DOMDocument */
        $xml = self::GetXMLRoot();
        $node = $xml->createElement($nodeName);

        if (is_array($inputArray)) {
            // get the attributes first.;
            if(isset($inputArray['@attributes'])) {
                foreach($inputArray['@attributes'] as $key => $value) {
                    if(!self::isValidTagName($key)) {
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$nodeName);
                    }
                    $node->setAttribute($key, self::bool2str($value));
                }
                unset($inputArray['@attributes']); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if(isset($inputArray['@value'])) {
                $node->appendChild($xml->createTextNode(self::bool2str($inputArray['@value'])));
                unset($inputArray['@value']);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } else if(isset($inputArray['@cdata'])) {
                $node->appendChild($xml->createCDATASection(self::bool2str($inputArray['@cdata'])));
                unset($inputArray['@cdata']);    //remove the key from the array once done.
                //return from recursion, as a note with cdata cannot have child nodes.
                return $node;
            }
        }

        //create subnodes using recursion
        if (is_array($inputArray)) {
            // recurse to get the node for that key
            foreach ($inputArray as $key => $value) {
                if(!self::isValidTagName($key)) {
                    throw new Exception('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '.$nodeName);
                }
                if(is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach($value as $k=>$v){
                        $node->appendChild(self::ConvertArrayToXml($key, $v));
                    }
                } else {
                    // ONLY ONE NODE OF ITS KIND
                    $node->appendChild(self::ConvertArrayToXml($key, $value));
                }
                unset($inputArray[$key]); //remove the key from the array once done.
            }
        }

        // after we are done with all the keys in the array (if it is one)
        // we check if it has any text value, if yes, append it.
        if(!is_array($inputArray)) {
            $node->appendChild($xml->createTextNode(self::bool2str($inputArray)));
        }

        return $node;
    }

    /*
     * Get the root XML node, if there isn't one, create it.
     * @return DOMDocument     
     */
    private static function GetXMLRoot(){
        if (empty(self::$xml)) {
            self::Initialize();
        }
        return self::$xml;
    }
    

    /*
     * Get string representation of boolean value
     * @param bool $bool Assumes boolean value. All non-explicit-true values will output a "false"     
     * @return string     
     */
    private static function bool2str($bool){
        if (is_bool($bool) === false) {
            trigger_error('$bool is not a boolean value. Method will return FALSE.', E_USER_WARNING);
            return 'false';
        }
        if ($bool === true) {
            return 'true';
        }
        else {
            return 'false';
        }
    }

    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     * @return bool     
     */
    private static function isValidTagName($tag){
        $pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
        return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
    }
}
?>