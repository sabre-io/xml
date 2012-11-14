<?php

namespace Sabre\XML;

use XMLReader;

class Element {

    protected $value;

    public function __construct($value = null) {

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
     * @param Writer $writer
     * @return void
     */
    public function serialize(Writer $writer) {

        $writer->write($this->value);

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * @param Reader $reader
     * @return mixed
     */
    static public function deserialize(Reader $reader) {

        $subTree = $reader->parseSubTree();
        return $subTree['elements']?:$subTree['text'];

    }

}

