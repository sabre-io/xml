<?php

namespace Sabre\XML\Element;

use Sabre\XML;

/**
 * 'Struct' parses out simple xml trees.
 *
 * Attributes will be removed, and duplicate child elements are discarded.
 * Elements with mixed text and child elements are also not supported, in those
 * cases the text will be discarded.
 *
 * For example, Struct will parse:
 *
 * <?xml version="1.0"?>
 * <s:root xmlns:s="http://sabredav.org/ns">
 *   <s:elem1>
 *      <s:elem2>value2</s:elem2>
 *      <s:elem3>value3</s:elem3>
 *   </s:elem1>
 *   <s:elem4>value4</s:elem>
 *   <s:elem5 />
 * </s:root>
 *
 * Into:
 *
 * [
 *   "{http://sabredav.org/ns}elem1" => [
 *      "{http://sabredav.org/ns}elem2" => "value2",
 *      "{http://sabredav.org/ns}elem3" => "value3",
 *   ],
 *   "{http://sabredav.org/ns}elem4" => "value4",
 *   "{http://sabredav.org/ns}elem5" => null,
 * ];
 *
 * @copyright Copyright (C) 2012-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Struct implements XML\Element {

    /**
     * Value to serialize
     *
     * @var array
     */
    protected $value;

    /**
     * Constructor
     *
     * @param array $value
     */
    public function __construct(array $value = []) {

        $this->value = $value;

    }

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * Important note 2: If you are writing any new elements, you are also
     * responsible for closing them.
     *
     * @param XML\Writer $writer
     * @return void
     */
    public function serializeXml(XML\Writer $writer) {

        $writer->write($this->value);

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called staticly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * Important note 2: You are responsible for advancing the reader to the
     * next element. Not doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseSubTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @param XML\Reader $reader
     * @return mixed
     */
    static public function deserializeXml(XML\Reader $reader) {

        // If there's no children, we don't do anything.
        if ($reader->isEmptyElement) {
            $reader->next();
            return [];
        }

        $values = [];

        $stack = [&$values];

        $currentValue = null;
        $currentValue = &$values;

        do {

            $reader->read();

            switch($reader->nodeType) {

                case XML\Reader::ELEMENT :
                    if (!is_array($currentValue)) $currentValue = [];
                    $clark = $reader->getClark();
                    $currentValue[$clark] = null;
                    if (!$reader->isEmptyElement) {
                        // The stack gets higher
                        $stack[] = &$currentValue[$clark];
                        $currentValue = &$currentValue[$clark];
                    }
                    break;
                case XML\Reader::TEXT :
                    // We only do something with text, if there already was
                    // text, or no value at all (null).
                    if (is_null($currentValue)) {
                        $currentValue = $reader->value;
                    } elseif (is_string($currentValue)) {
                        $currentValue.= $reader->value;
                    }
                    break;
                case XML\Reader::END_ELEMENT :
                    // Unwinding the stack.
                    array_pop($stack);
                    if (count($stack)>0) {
                        $currentValue =& $stack[count($stack)-1];
                    }
                    break;

            }

        } while (count($stack) > 0);

        return $values;

    }

}

