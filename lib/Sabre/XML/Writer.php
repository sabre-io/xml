<?php

namespace Sabre\XML;

use
    XMLWriter,
    InvalidArgumentException;

class Writer extends XMLWriter {

    public $namespaceMap = array();

    public $adhodNamespaces = array();

    public $elementMap = array();

    protected $namespacesWritten = false;

    public function write($value) {

        if (is_scalar($value)) {
            $this->text($value);
        } elseif ($value instanceof Element) {
            $value->serialize($this);
        } elseif (is_null($value)) {
            // noop
        } elseif (is_array($value)) {

            reset($value);
            if (is_int(key($value))) {

                // It's an array with numeric indices. We expect every item to
                // be an array with a name and a value.
                foreach($value as $subItem) {

                    print_r($subItem);
                    if (!isset($subItem['name']) || !isset($subItem['value'])) {
                        throw new InvalidArgumentException('When passing an array to ->write with numeric indices, every item must have a "name" and a "value"');
                    }
                    $this->startElement($subItem['name']);
                    $this->write($subItem['value']);
                    $this->endElement();

                }

            } else {

                // If it's an array with text-indices, we expect every item's
                // key to be an xml element name in clark notation.
                foreach($value as $name=>$subValue) {

                    $this->startElement($name);
                    $this->write($subValue);
                    $this->endElement();

                }

            }

        }

    }

    /**
     * Starts an element.
     *
     * @param string $name
     * @return bool
     */
    public function startElement($name) {

        if ($name[0]==='{') {
            list($namespace, $localName) =
                Util::parseClarkNotation($name);

            if (isset($this->namespaceMap[$namespace])) {
                $result = $this->startElementNS($this->namespaceMap[$namespace], $localName, null);
            } else {

                if (!isset($this->adhocNamespaces[$namespace])) {
                    $this->adhocNamespaces[$namespace] = 'x' . (count($this->adhocNamespaces)+1);
                }
                $result = $this->startElementNS($this->adhocNamespaces[$namespace], $localName, $namespace);
            }
        } else {
            $result = parent::startElement($name);
        }
        if (!$this->namespacesWritten) {

            foreach($this->namespaceMap as $namespace => $prefix) {
                $this->writeAttributeNS('xmlns',$prefix,null,$namespace);
            }
            $this->namespacesWritten = true;

        }

        return $result;

    }

}
