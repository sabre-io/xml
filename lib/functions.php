<?php

namespace Sabre\Xml\Deserialize;

use Sabre\Xml\Reader;

/*
 * Deserializes Xml Elements of the given Reader into a array.
 * The namespace of elements which match the given namespace are stripped.
 *
 * @param Xml\Reader $reader
 * @param string $namespace
 *
 * @return array
 */
function namespaceAware(Reader $reader, $namespace)
{
    // If there's no children, we don't do anything.
    if ($reader->isEmptyElement) {
        $reader->next();
        return [];
    }

    $values = [];

    $reader->read();
    do {

        if ($reader->nodeType === Reader::ELEMENT) {
            if ($reader->namespaceURI == $namespace) {
                $values[$reader->localName] = $reader->parseCurrentElement()['value'];
            } else {
                $clark = $reader->getClark();
                $values[$clark] = $reader->parseCurrentElement()['value'];
            }
        } else {
            $reader->read();
        }
    } while ($reader->nodeType !== Reader::END_ELEMENT);

    $reader->read();

    return $values;
}
