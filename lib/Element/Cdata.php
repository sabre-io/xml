<?php

namespace Sabre\Xml\Element;

use
    Sabre\Xml,
    LogicException;

/**
 * CDATA element.
 *
 * This element allows you to easily inject CDATA.
 *
 * Note that we strongly recommend avoiding CDATA nodes, unless you definitely
 * know what you're doing, or you're working with unchangable systems that
 * require CDATA.
 *
 * @copyright Copyright (C) 2013-2014 fruux GmbH. All rights reserved.
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Cdata implements Xml\XmlSerializable
{
    /**
     * CDATA element value.
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
     * @param Xml\Writer $writer
     * @return void
     */
    function xmlSerialize(Xml\Writer $writer) {

        $writer->writeCData($this->value);

    }

}
