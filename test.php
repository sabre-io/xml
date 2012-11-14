<?php

include __DIR__ . '/vendor/autoload.php';

use Sabre\XML;

class AtomLink extends XML\Element {

    public $href;
    public $rel;
    public $type;

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * @param XML\Writer $reader
     * @return void
     */
    public function serialize(XML\Writer $writer) {

        $writer->writeAttribute('href', $this->href);
        $writer->writeAttribute('rel', $this->rel);
        $writer->writeAttribute('type', $this->type);

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * @param XML\Reader $reader
     * @return mixed
     */
    static public function deserialize(XML\Reader $reader) {

        $attributes = $reader->parseAttributes();

        $link = new self();
        foreach($attributes as $name=>$value) {
            if (property_exists($link,$name)) {
                $link->$name = $value;
            }
        }

        return $link;

    }

}

$reader = new XML\Reader();
$reader->elementMap = array(
    '{http://www.w3.org/2005/Atom}link' => 'AtomLink'
);
$reader->open('samples/atom.xml');

$output = $reader->parse();

print_r($output);

$writer = new XML\Writer();
$writer->namespaceMap = array(
    'http://www.w3.org/2005/Atom' => 'a',
);
$writer->openMemory();
$writer->setIndent(true); // for pretty indentation
$writer->startDocument();
$writer->write($output);

echo $writer->outputMemory();

