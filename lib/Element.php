<?php

namespace Sabre\Xml;

use XMLReader;

/**
 * This is the XML element interface.
 *
 * Elements are responsible for serializing and deserializing part of an XML
 * document into PHP values.
 *
 * It combines XmlSerializable and XmlDeserializable into one logical class
 * that does both.
 *
 * @copyright Copyright (C) 2009-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
interface Element extends XmlSerializable, XmlDeserializable {

}
