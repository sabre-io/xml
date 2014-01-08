<?php

namespace Sabre\XML\Element;

use Sabre\XML;

class Cdata implements XML\Element
{
    private $value;

    public function __construct($value = NULL)
    {
        $this->value = $value;
    }

    public function serializeXml(XML\Writer $writer)
    {
        $writer->writeCData($this->value);
    }

    public static function deserializeXml(XML\Reader $reader)
    {
        return $reader->parseInnerTree;
    }
}
