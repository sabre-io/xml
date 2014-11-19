<?php

namespace Sabre\XML\Element;

use Sabre\XML;
use LogicException;

/**
 * Uri element.
 *
 * This represents a single uri. An example of how this may be encoded:
 *
 *    <link>/foo/bar</link>
 *    <d:href xmlns:d="DAV:">http://example.org/hi</d:href>
 *
 * If the uri is relative, it will be automatically expanded to an absolute
 * url during writing and reading, if the baseUri property is set on the
 * reader and/or writer.
 *
 * @copyright Copyright (C) 2013-2014 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Uri implements XML\Element {

    /**
     * Uri element value.
     *
     * @var string
     */
    protected $value;

    /**
     * Constructor
     *
     * @param string $value
     */
    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * This method is called during xml writing.
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
    function serializeXml(XML\Writer $writer) {

        $writer->text(
            \Sabre\Uri\resolve(
                $writer->baseUri,
                $this->value
            )
        );

    }

    /**
     * This method is called during xml parsing.
     *
     * This method is called statically, this is because in theory this method
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
    static function deserializeXml(XML\Reader $reader) {

        return new self(
            \Sabre\Uri\resolve(
                $reader->baseUri,
                $reader->readText()
            )
        );

    }

}
