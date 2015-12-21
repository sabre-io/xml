<?php

namespace Sabre\Xml\Serializer;

use Sabre\Xml\Writer;

/**
 * This file provides a number of 'serializer' helper functions.
 * These can be used to easily specify custom serializers for specific
 * PHP objects/values.
 */

/**
 * The valueObject serializer turns a simple PHP object into a classname.
 *
 * Every public property will be encoded as an xml element with the same
 * name, in the XML namespace as specified.
 *
 * @param Writer $writer
 * @param object $valueObject
 * @param string $namespace
 */
function valueObject(Writer $writer, $valueObject, $namespace) {
    foreach (get_object_vars($valueObject) as $key => $val) {
        $writer->writeElement('{' . $namespace . '}' . $key, $val);
    }
}
