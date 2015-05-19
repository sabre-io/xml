<?php

namespace Sabre\Xml;

class WriterTest extends \PHPUnit_Framework_TestCase {

    protected $writer;

    function setUp() {

        $this->writer = new Writer();
        $this->writer->namespaceMap = [
            'http://sabredav.org/ns' => 's',
        ];
        $this->writer->openMemory();
        $this->writer->setIndent(true);
        $this->writer->startDocument();

    }

    function compare($input, $output) {

        $this->writer->write($input);
        $this->assertEquals($output, $this->writer->outputMemory());

    }


    function testSimple() {

        $this->compare([
            '{http://sabredav.org/ns}root' => 'text',
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">text</s:root>

HI
        );

    }

    /**
     * @depends testSimple
     */
    function testSimpleQuotes() {

        $this->compare([
            '{http://sabredav.org/ns}root' => '"text"',
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">&quot;text&quot;</s:root>

HI
        );

    }

    function testSimpleAttributes() {

        $this->compare([
            '{http://sabredav.org/ns}root' => [
                'value'      => 'text',
                'attributes' => [
                    'attr1' => 'attribute value',
                ],
            ],
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns" attr1="attribute value">text</s:root>

HI
        );

    }

    function testMixedSyntax() {
        $this->compare([
            '{http://sabredav.org/ns}root' => [
                'single'   => 'value',
                'multiple' => [
                    [
                        'name'  => 'foo',
                        'value' => 'bar',
                    ],
                    [
                        'name'  => 'foo',
                        'value' => 'foobar',
                    ],
                ],
                'attributes' => [
                    'value'      => null,
                    'attributes' => [
                        'foo' => 'bar',
                    ],
                ],
                [
                    'name'       => 'verbose',
                    'value'      => 'syntax',
                    'attributes' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <single>value</single>
 <multiple>
  <foo>bar</foo>
  <foo>foobar</foo>
 </multiple>
 <attributes foo="bar"/>
 <verbose foo="bar">syntax</verbose>
</s:root>

HI
        );
    }

    function testNull() {

        $this->compare([
            '{http://sabredav.org/ns}root' => null,
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns"/>

HI
        );

    }

    function testArrayFormat2() {

        $this->compare([
            '{http://sabredav.org/ns}root' => [
                [
                    'name'       => '{http://sabredav.org/ns}elem1',
                    'value'      => 'text',
                    'attributes' => [
                        'attr1' => 'attribute value',
                    ],
                ],
            ],
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <s:elem1 attr1="attribute value">text</s:elem1>
</s:root>

HI
        );

    }

    function testCustomNamespace() {

        $this->compare([
            '{http://sabredav.org/ns}root' => [
                '{urn:foo}elem1' => 'bar',
            ],
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <x1:elem1 xmlns:x1="urn:foo">bar</x1:elem1>
</s:root>

HI
        );

    }

    function testEmptyNamespace() {

        // Empty namespaces are allowed, so we should support this.
        $this->compare([
            '{http://sabredav.org/ns}root' => [
                '{}elem1' => 'bar',
            ],
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <elem1 xmlns="">bar</elem1>
</s:root>

HI
        );

    }

    function testAttributes() {

        $this->compare([
            '{http://sabredav.org/ns}root' => [
                [
                    'name'       => '{http://sabredav.org/ns}elem1',
                    'value'      => 'text',
                    'attributes' => [
                        'attr1'                         => 'val1',
                        '{http://sabredav.org/ns}attr2' => 'val2',
                        '{urn:foo}attr3'                => 'val3',
                    ],
                ],
            ],
        ], <<<HI
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

        $this->compare([
            '{http://sabredav.org/ns}root' => [
                ['incorrect' => '0', 'keynames' => 1]
            ],
        ], "");

    }

    function testBaseElement() {

        $this->compare([
            '{http://sabredav.org/ns}root' => new Element\Base('hello')
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">hello</s:root>

HI
        );

    }

    function testElementObj() {

        $this->compare([
            '{http://sabredav.org/ns}root' => new Element\Mock()
        ], <<<HI
<?xml version="1.0"?>
<s:root xmlns:s="http://sabredav.org/ns">
 <s:elem1>hiiii!</s:elem1>
</s:root>

HI
        );

    }

    function testEmptyNamespacePrefix() {

        $this->writer->namespaceMap['http://sabredav.org/ns'] = null;
        $this->compare([
            '{http://sabredav.org/ns}root' => new Element\Mock()
        ], <<<HI
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
 <elem1>hiiii!</elem1>
</root>

HI
        );

    }

    function testWriteElement() {

        $this->writer->writeElement("{http://sabredav.org/ns}foo", 'content');

        $output = <<<HI
<?xml version="1.0"?>
<s:foo xmlns:s="http://sabredav.org/ns">content</s:foo>

HI;

        $this->assertEquals($output, $this->writer->outputMemory());


    }

    function testWriteElementComplex() {

        $this->writer->writeElement("{http://sabredav.org/ns}foo", new Element\KeyValue(['{http://sabredav.org/ns}bar' => 'test']));

        $output = <<<HI
<?xml version="1.0"?>
<s:foo xmlns:s="http://sabredav.org/ns">
 <s:bar>test</s:bar>
</s:foo>

HI;

        $this->assertEquals($output, $this->writer->outputMemory());

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testWriteBadObject() {

        $this->writer->write(new \StdClass());

    }

    function testStartElementSimple() {

        $this->writer->startElement("foo");
        $this->writer->endElement();

        $output = <<<HI
<?xml version="1.0"?>
<foo xmlns:s="http://sabredav.org/ns"/>

HI;

        $this->assertEquals($output, $this->writer->outputMemory());


    }
}
