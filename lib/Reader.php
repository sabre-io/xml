<?php

namespace Sabre\Xml;

use XMLReader;

/**
 * The Reader class expands upon PHP's built-in XMLReader.
 *
 * The intended usage, is to assign certain XML elements to PHP classes. These
 * need to be registered using the $elementMap public property.
 *
 * After this is done, a single call to parse() will parse the entire document,
 * and delegate sub-sections of the document to element classes.
 *
 * @copyright Copyright (C) 2009-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Reader extends XMLReader {

    use ContextStackTrait;

    /**
     * Returns the current nodename in clark-notation.
     *
     * For example: "{http://www.w3.org/2005/Atom}feed".
     * Or if no namespace is defined: "{}feed".
     *
     * This method returns null if we're not currently on an element.
     *
     * @return string|null
     */
    function getClark() {

        if (! $this->localName) {
            return null;
        }

        return '{' . $this->namespaceURI . '}' . $this->localName;

    }

    /**
     * Reads the entire document.
     *
     * This function returns an array with the following three elements:
     *    * name - The root element name.
     *    * value - The value for the root element.
     *    * attributes - An array of attributes.
     *
     * This function will also disable the standard libxml error handler (which
     * usually just results in PHP errors), and throw exceptions instead.
     *
     * @return array
     */
    function parse() {

        $previousSetting = libxml_use_internal_errors(true);

        // Really sorry about the silence operator, seems like I have no
        // choice. See:
        //
        // https://bugs.php.net/bug.php?id=64230
        while ($this->nodeType !== self::ELEMENT && @$this->read()) {
            // noop
        }
        $result = $this->parseCurrentElement();

        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        if ($errors) {
            throw new LibXMLException($errors);
        } else {
            return $result;
        }

    }

    /**
     * Parses all elements below the current element.
     *
     * This method will return a string if this was a text-node, or an array if
     * there were sub-elements.
     *
     * If there's both text and sub-elements, the text will be discarded.
     *
     * If the $elementMap argument is specified, the existing elementMap will
     * be overridden while parsing the tree, and restored after this process.
     *
     * @param array $elementMap
     * @return array|string
     */
    function parseInnerTree(array $elementMap = null) {

        $previousDepth = $this->depth;

        $text = null;
        $elements = [];
        $attributes = [];

        if ($this->nodeType === self::ELEMENT && $this->isEmptyElement) {
            // Easy!
            $this->next();
            return null;
        }

        if (!is_null($elementMap)) {
            $this->pushContext();
            $this->elementMap = $elementMap;
        }


        // Really sorry about the silence operator, seems like I have no
        // choice. See:
        //
        // https://bugs.php.net/bug.php?id=64230
        if (!@$this->read()) return false;

        while (true) {

            switch ($this->nodeType) {
                case self::ELEMENT :
                    $elements[] = $this->parseCurrentElement();
                    break;
                case self::TEXT :
                case self::CDATA :
                    $text .= $this->value;
                    $this->read();
                    break;
                case self::END_ELEMENT :
                    // Ensuring we are moving the cursor after the end element.
                    $this->read();
                    break 2;
                case self::NONE :
                    throw new ParseException('We hit the end of the document prematurely. This likely means that some parser "eats" too many elements. Do not attempt to continue parsing.');
                default :
                    // Advance to the next element
                    $this->read();
                    break;
            }

        }

        if (!is_null($elementMap)) {
            $this->popContext();
        }
        return ($elements ? $elements : $text);

    }

    /**
     * Reads all text below the current element, and returns this as a string.
     *
     * @return string
     */
    function readText() {

        $result = '';
        $previousDepth = $this->depth;

        while ($this->read() && $this->depth != $previousDepth) {
            if (in_array($this->nodeType, [XMLReader::TEXT, XMLReader::CDATA, XMLReader::WHITESPACE])) {
                $result .= $this->value;
            }
        }
        return $result;

    }

    /**
     * Parses the current XML element.
     *
     * This method returns arn array with 3 properties:
     *   * name - A clark-notation XML element name.
     *   * value - The parsed value.
     *   * attributes - A key-value list of attributes.
     *
     * @return array
     */
    function parseCurrentElement() {

        $name = $this->getClark();

        $attributes = [];

        if ($this->hasAttributes) {
            $attributes = $this->parseAttributes();
        }

        if (array_key_exists($name, $this->elementMap)) {
            $deserializer = $this->elementMap[$name];
            if (is_subclass_of($deserializer, 'Sabre\\Xml\\XmlDeserializable')) {
                $value = call_user_func([ $deserializer, 'xmlDeserialize' ], $this);
            } elseif (is_callable($deserializer)) {
                $value = call_user_func($deserializer, $this);
            } else {
                $type = gettype($deserializer);
                if ($type === 'string') {
                    $type .= ' (' . $deserializer . ')';
                } elseif ($type === 'object') {
                    $type .= ' (' . get_class($deserializer) . ')';
                }
                throw new \LogicException('Could not use this type as a deserializer: ' . $type);
            }
        } else {
            $value = Element\Base::xmlDeserialize($this);
        }

        return [
            'name'       => $name,
            'value'      => $value,
            'attributes' => $attributes,
        ];
    }

    /**
     * Grabs all the attributes from the current element, and returns them as a
     * key-value array.
     *
     * If the attributes are part of the same namespace, they will simply be
     * short keys. If they are defined on a different namespace, the attribute
     * name will be retured in clark-notation.
     *
     * @return void
     */
    function parseAttributes() {

        $attributes = [];

        while ($this->moveToNextAttribute()) {
            if ($this->namespaceURI) {

                // Ignoring 'xmlns', it doesn't make any sense.
                if ($this->namespaceURI === 'http://www.w3.org/2000/xmlns/') {
                    continue;
                }

                $name = $this->getClark();
                $attributes[$name] = $this->value;

            } else {
                $attributes[$this->localName] = $this->value;
            }
        }
        $this->moveToElement();

        return $attributes;

    }

}
