<?php

namespace Sabre\Xml;

/**
 * XML parsing and writing service.
 *
 * You are encouraged to make a instance of this for your application and
 * potentially extend it, as a central API point for dealing with xml and
 * configuring the reader and writer.
 *
 * @copyright Copyright (C) 2009-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class Service {

    /**
     * This is the element map. It contains a list of XML elements (in clark
     * notation) as keys and PHP class names as values.
     *
     * The PHP class names must implement Sabre\Xml\Element.
     *
     * Values may also be a callable. In that case the function will be called
     * directly.
     *
     * @var array
     */
    public $elementMap = [];

    /**
     * This is a list of namespaces that you want to give default prefixes.
     *
     * You must make sure you create this entire list before starting to write.
     * They should be registered on the root element.
     *
     * @var array
     */
    public $namespaceMap = [];

    /**
     * Returns a fresh XML Reader
     *
     * @return Reader
     */
    function getReader() {

        $r = new Reader();
        $r->elementMap = $this->elementMap;
        return $r;

    }

    /**
     * Returns a fresh xml writer
     *
     * @return Writer
     */
    function getWriter() {

        $w = new Writer();
        $w->namespaceMap = $this->namespaceMap;
        return $w;

    }

    /**
     * Parses a document in full.
     *
     * Input may be specified as a string or readable stream resource.
     * The returned value is the value of the root document.
     *
     * Specifying the $contextUri allows the parser to figure out what the URI
     * of the document was. This allows relative URIs within the document to be
     * expanded easily.
     *
     * The $rootElementName is specified by reference and will be populated
     * with the root element name of the document.
     *
     * @param string|resource $input
     * @param string|null $contextUri
     * @param string|null $rootElementName
     * @throws ParseException
     * @return array|object|string
     */
    function parse($input, $contextUri = null, &$rootElementName = null) {

        if (is_resource($input)) {
            // Unfortunately the XMLReader doesn't support streams. When it
            // does, we can optimize this.
            $input = stream_get_contents($input);
        }
        $r = $this->getReader();
        $r->contextUri = $contextUri;
        $r->xml($input);

        $result = $r->parse();
        $rootElementName = $result['name'];
        return $result['value'];

    }

    /**
     * Parses a document in full, and specify what the expected root element
     * name is.
     *
     * This function works similar to parse, but the difference is that the
     * user can specify what the expected name of the root element should be,
     * in clark notation.
     *
     * This is useful in cases where you expected a specific document to be
     * passed, and reduces the amount of if statements.
     *
     * @param string $rootElementName
     * @param string|resource $input
     * @param string|null $contextUri
     * @return void
     */
    function expect($rootElementName, $input, $contextUri = null) {

        if (is_resource($input)) {
            // Unfortunately the XMLReader doesn't support streams. When it
            // does, we can optimize this.
            $input = stream_get_contents($input);
        }
        $r = $this->getReader();
        $r->contextUri = $contextUri;
        $r->xml($input);

        $result = $r->parse();
        if ($rootElementName !== $result['name']) {
            throw new ParseException('Expected ' . $rootElementName . ' but received ' . $result['name'] . ' as the root element');
        }
        return $result['value'];

    }

    /**
     * Generates an XML document in one go.
     *
     * The $rootElement must be specified in clark notation.
     * The value must be a string, an array or an object implementing
     * XmlSerializable. Basically, anything that's supported by the Writer
     * object.
     *
     * $contextUri can be used to specify a sort of 'root' of the PHP application,
     * in case the xml document is used as a http response.
     *
     * This allows an implementor to easily create URI's relative to the root
     * of the domain.
     *
     * @param string $rootElementName
     * @param string|array|XmlSerializable $value
     * @param string|null $contextUri
     */
    function write($rootElementName, $value, $contextUri = null) {

        $w = $this->getWriter();
        $w->openMemory();
        $w->contextUri = $contextUri;
        $w->setIndent(true);
        $w->startDocument();
        $w->writeElement($rootElementName, $value);
        return $w->outputMemory();

    }


    /**
     * Parses a clark-notation string, and returns the namespace and element
     * name components.
     *
     * If the string was invalid, it will throw an InvalidArgumentException.
     *
     * @param string $str
     * @throws InvalidArgumentException
     * @return array
     */
    static function parseClarkNotation($str) {

        if (!preg_match('/^{([^}]*)}(.*)$/', $str, $matches)) {
            throw new \InvalidArgumentException('\'' . $str . '\' is not a valid clark-notation formatted string');
        }

        return [
            $matches[1],
            $matches[2]
        ];

    }


}
