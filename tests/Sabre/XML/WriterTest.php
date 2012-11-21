<?php

namespace Sabre\XML;

class WriterTest extends \PHPUnit_Framework_TestCase {

    protected $writer;

    function setUp() {

        $this->writer = new Writer();
        $this->writer->namespaceMap = array(
            'http://sabredav.org/ns' => 's',
        );
        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->startDocument();

    }

    function compare($input, $output) {

        $this->writer->write($input);
        $this->assertEquals($output, $this->writer->outputMemory());

    }


    function testSimple() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => 'text',
        ), <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">text</s:root>

HI
        );

    }

    function testNull() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => null,
        ), <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns"/>

HI
        );

    }

    function testArrayFormat2() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => array(
                array(
                    'name' => '{http://sabredav.org/ns}elem1',
                    'value' => 'text',
                    'attributes' => array(
                        'attr1' => 'attribute value',
                    ),
                ),
            ),
        ), <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <s:elem1 attr1="attribute value">text</s:elem1>
</s:root>

HI
        );

    }

    function testCustomNamespace() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => array(
                '{urn:foo}elem1' => 'bar',
            ),
        ), <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <x1:elem1 xmlns:x1="urn:foo">bar</x1:elem1>
</s:root>

HI
        );

    }

    function testAttributes() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => array(
                array(
                    'name' => '{http://sabredav.org/ns}elem1',
                    'value' => 'text',
                    'attributes' => array(
                        'attr1' => 'val1',
                        '{http://sabredav.org/ns}attr2' => 'val2',
                        '{urn:foo}attr3' => 'val3',
                    ),
                ),
            ),
        ), <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <s:elem1 attr1="val1" s:attr2="val2" x1:attr3="val3" xmlns:x1="urn:foo">text</s:elem1>
</s:root>

HI
        );

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testInvalidFormat() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => array(
                array('incorrect' => '0', 'keynames' => 1)
            ),
        ), "");

    }

    function testWriteElement() {

        $this->compare(array(
            '{http://sabredav.org/ns}root' => new Element\Mock()
        ), <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <s:elem1>hiiii!</s:elem1>
</s:root>

HI
        );

    }
}

