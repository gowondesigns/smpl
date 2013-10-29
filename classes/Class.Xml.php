<?php
/**
 * Class.XML
 * @package SMPL\XML
 */

/**
 * based on Array2XML by Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * Usage:
 *       $xml = Array2XML::createXML('root_node_name', $php_array);
 *       echo $xml->saveXML();
 * @package XML
 */
class XML
{
    /**
     * Shared instance of 
     * @var DOMDocument $xml
     */
    private static $xml = null;
    
    /**
     * String encoding for XML document
     * @var string $encoding
     */
    private static $encoding = 'UTF-8';

    /**
     * Initialize the root XML node [optional]
     * @param $version
     * @param $encoding
     * @param $formatOutput
     */
    public static function Initialize($version = '1.0', $encoding = 'UTF-8', $formatOutput = true) {
        self::$xml = new DOMDocument($version, $encoding);
        self::$xml->formatOutput = $formatOutput;
        self::$encoding = $encoding;
    }

    /**
     * Create XML from given array
     * @param string $rootNodeName - name of the root node to be converted
     * @param array $inputArray - aray to be converterd
     * @return DOMDocument
     */
    public static function CreateXML($rootNodeName, $inputArray = array()) {
        /** @var $xml DOMDocument */
        $xml = self::GetXmlRoot();
        $xml->appendChild(self::ConvertArrayToXml($rootNodeName, $inputArray));

        self::$xml = null;    // clear the xml node in the class for 2nd time use.
        return $xml;
    }

    /**
     * Convert an Array to XML
     * @param string $nodeName - name of the root node to be converted
     * @param array $inputArray - aray to be converterd
     * @throws Exception
     * @return DOMNode
     */
    private static function ConvertArrayToXml($nodeName, $inputArray = array())
    {
        /** @var $xml DOMDocument */
        $xml = self::GetXmlRoot();
        $node = $xml->createElement($nodeName);

        if(is_array($inputArray)){
            // get the attributes first.;
            if(isset($inputArray['@attributes'])) {
                foreach($inputArray['@attributes'] as $key => $value) {
                    if(!self::IsValidTagName($key)) {
                        throw new Exception('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$nodeName);
                    }
                    $node->setAttribute($key, self::BooleanToString($value));
                }
                unset($inputArray['@attributes']); //remove the key from the array once done.
            }

            // check if it has a value stored in @value, if yes store the value and return
            // else check if its directly stored as string
            if(isset($inputArray['@value'])) {
                $node->appendChild($xml->createTextNode(self::BooleanToString($inputArray['@value'])));
                unset($inputArray['@value']);    //remove the key from the array once done.
                //return from recursion, as a note with value cannot have child nodes.
                return $node;
            } else if(isset($inputArray['@cdata'])) {
                $node->appendChild($xml->createCDATASection(self::BooleanToString($inputArray['@cdata'])));
                unset($inputArray['@cdata']);    //remove the key from the array once done.
                //return from recursion, as a note with cdata cannot have child nodes.
                return $node;
            }
        }

        //create subnodes using recursion
        if(is_array($inputArray)){
            // recurse to get the node for that key
            foreach($inputArray as $key=>$value){
                if (!self::IsValidTagName($key)) {
                    trigger_error('Illegal character in tag name. tag: ' . $key . ' in node: ' . $nodeName, E_USER_ERROR);
                }
                if(is_array($value) && is_numeric(key($value))) {
                    // MORE THAN ONE NODE OF ITS KIND;
                    // if the new array is numeric index, means it is array of nodes of the same kind
                    // it should follow the parent key name
                    foreach($value as $v){
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
            $node->appendChild($xml->createTextNode(self::BooleanToString($inputArray)));
        }

        return $node;
    }

    /*
     * Get the root XML node, if there isn't one, create it.
     * @return DOMDocument     
     */
    private static function GetXmlRoot()
    {
        if (empty(self::$xml)) {
            self::Initialize();
        }
        return self::$xml;
    }

    /*
     * Get string representation of boolean value
     * @param mixed $value
     * @return mixed          
     */
    private static function BooleanToString($value)
    {
        if ($value === true) {
            return 'true';
        }
        elseif ($value === false) {
            return 'false';
        }
        return $value;
    }

    /*
     * Check if the tag name or attribute name contains illegal characters
     * Ref: http://www.w3.org/TR/xml/#sec-common-syn
     * @param string $tag
     * @return bool          
     */
    private static function IsValidTagName($tag){
        $matches = Pattern::Validate(Pattern::XML_VALID_TAG_NAME, $tag, Pattern::RETURN_MATCHES);
        return ($matches[0] == $tag);
    }
}
?>