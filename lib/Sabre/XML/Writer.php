<?php

namespace Sabre\XML;

use
    XMLWriter,
    InvalidArgumentException;

class Writer extends XMLWriter {

    public $namespaceMap = array();

    public $adhocNamespaces = array();

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

                    if (!array_key_exists('name', $subItem) || !array_key_exists('value', $subItem)) {
                        print_r($subItem);
                        throw new InvalidArgumentException('When passing an array to ->write with numeric indices, every item must have a "name" and a "value"');
                    }
                    $this->startElement($subItem['name']);
                    if (isset($subItem['attributes'])) {
                        $this->writeAttributes($subItem['attributes']);
                    }
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

    /**
     * Writes a list of attributes.
     *
     * Attributes are specified as a key->value array.
     *
     * The key is an attribute name. If the key is a 'localName', the current
     * xml namespace is assumed. If it's a 'clark notation key', this namespace
     * will be used instead.
     *
     * @param array $attributes
     * @return void
     */
    public function writeAttributes(array $attributes) {

        foreach($attributes as $name=>$value) {
            $this->writeAttribute($name, $value);
        }

    }

    /**
     * Writes a new attribute.
     *
     * The name may be specified in clark-notation.
     *
     * Returns true when successful.
     *
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function writeAttribute($name, $value) {

        if ($name[0] === '{') {
            list(
                $namespace,
                $localName
            ) = Util::parseClarkNotation($name);

            if (isset($this->namespaceMap[$namespace])) {

                // It's an attribute with a namespace we know
                $this->writeAttributeNS(
                    $this->namespaceMap[$namespace],
                    $localName,
                    null,
                    $value
                );
            } else {

                // We don't know the namespace, we must add it in-line
                if (!isset($this->adhocNamespaces[$namespace])) {
                    $this->adhocNamespaces[$namespace] = 'x' . (count($this->adhocNamespaces)+1);
                }
                $this->writeAttributeNS(
                    $this->adhocNamespaces[$namespace],
                    $localName,
                    $namespace,
                    $value
                );

            }
        } else {
            return parent::writeAttribute($name, $value);
        }

    }

}
