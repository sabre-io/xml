<?php

namespace Sabre\XML;

use XMLReader;

/**
 * The Reader class expands upon PHP's built-in XMLReader.
 *
 * The intended usage, is to assign certain xml elements to PHP classes. These
 * need to be registered using the $elementMap public property.
 *
 * After this is done, a single call to parse() will parse the entire document,
 * and delegate sub-sections of the document to element classes.
 *
 * @copyright Copyright (C) 2012-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Reader extends XMLReader {

    /**
     * This is the element map. It contains a list of xml elements (in clark
     * notation) as keys and PHP class names as values.
     *
     * The PHP class names must implement Sabre\XML\Element.
     *
     * @var array
     */
    public $elementMap = [];

    /**
     * Returns the current nodename in clark-notation.
     *
     * For example: "{http://www.w3.org/2005/Atom}feed".
     * This method returns null if we're not currently on an element.
     *
     * @return string|null
     */
    public function getClark() {

        if (!$this->namespaceURI) {
            return null;
        }
        return '{' . $this->namespaceURI . '}' . $this->localName;

    }

    /**
     * Reads the entire document.
     *
     * @return array
     */
    public function parse() {

        while($this->nodeType !== self::ELEMENT) {
            $this->read();
        }
        return [$this->parseCurrentElement()];

    }

    /**
     * Parses all elements below the current element.
     *
     * This method will return a string if this was a text-node, or an array if
     * there were sub-elements.
     *
     * If there's both text and sub-elements, the text will be discarded.
     *
     * @return array|string
     */
    public function parseSubTree() {

        $previousDepth = $this->depth;

        $text = null;
        $elements = [];
        $attributes = [];

        $this->read();

        do {

            switch($this->nodeType) {
                case self::ELEMENT :
                    $elements[] = $this->parseCurrentElement();
                    // Skipping the rest of the sub-tree.
                    //$this->next();
                    break;
                case self::TEXT :
                    $text .= $this->value;
                    $this->read();
                default :
                    // Advance to the next element
                    $this->read();
                    break;
            }

        } while ($this->depth > $previousDepth);

        if ($this->nodeType === self::END_ELEMENT) {
            // Advancing one more after the last END_ELEMENT.
            $this->read();
        }

        return ($elements?$elements:$text);

    }

    /**
     * Parses the current XML element.
     *
     * This method returns arn array with 3 properties:
     *   * name - A clark-notation xml element name.
     *   * value - The parsed value.
     *   * attributes - A key-value list of attributes.
     *
     * @return array
     */
    protected function parseCurrentElement() {

        $name = $this->getClark();

        $attributes = [];

        if ($this->hasAttributes) {
            $attributes = $this->parseAttributes();
        }


        if (isset($this->elementMap[$name])) {
            $value = call_user_func( [ $this->elementMap[$name], 'deserializeXml' ], $this);
        } else {
            $value = Element\Base::deserializeXml($this);
        }

        return [
            'name' => $name,
            'value' => $value,
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
    public function parseAttributes() {

        $attributes = [];

        while($this->moveToNextAttribute()) {
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
