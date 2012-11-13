<?php

namespace Sabre\XML;

use XMLReader;

class Element {

    protected $value;

    public function __construct($value) {

        $this->value = $value;

    }

    public function serialize(Writer $writer) {

        $writer->write($this->value);

    }

    static public function deserialize(Reader $reader) {

        $subTree = $reader->parseSubTree();
        return $subTree['elements']?:$subTree['text'];

    }

}

