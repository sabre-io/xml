<?php

namespace Sabre\XML;

use XMLReader;

class Reader extends XMLReader {

    public $elementMap = array();

    /**
     * Returns the current nodename in clark-notation.
     *
     * For example: "{http://www.w3.org/2005/Atom}feed".
     * This method returns null if we're not currently on an element.
     *
     * @return string|null
     */
    protected function getClark() {

        if ($this->nodeType !== self::ELEMENT) {
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
        return $this->parseCurrentElement();

    }

    /**
     * Parses all elements below the current element.
     *
     * This method will return an array. The array has the following properties:
     *   * text - All text node, concatenated
     *   * elements - an array of elements. Every element is an array with a
     *                nodename, and the parsed value.
     *
     * @return array
     */
    public function parseSubTree() {

        $previousDepth = $this->depth;

        $text = null;
        $elements = array();

        $this->read();

        $result = array();
        do {

            switch($this->nodeType) {
                case self::ELEMENT :
                    $elements[] = array(
                        'name' => $this->getClark(),
                        'value' => $this->parseCurrentElement()
                    );
                    // Skipping the rest of the sub-tree.
                    $this->next();
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

        return array(
            'elements' => $elements,
            'text' => $text,
        );

    }

    /**
     * Parses the current XML element.
     *
     * @return mixed
     */
    protected function parseCurrentElement() {

        $clark = $this->getClark();
        if (isset($this->elementMap[$clark])) {
            $result = call_user_func( array( $this->elementMap[$clark], 'deserialize'), $this);
        } else {
            $result = Element::deserialize($this);
        }

        return $result;

    }
}
