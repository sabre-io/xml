<?php

namespace Sabre\Xml;

class ServiceTest extends \PHPUnit_Framework_TestCase {

    function testGetReader() {

        $elems = [
            '{http://sabre.io/ns}test' => 'Test!',
        ];

        $util = new Service();
        $util->elementMap = $elems;

        $reader = $util->getReader();
        $this->assertInstanceOf('Sabre\\Xml\\Reader', $reader);
        $this->assertEquals($elems, $reader->elementMap);

    }

    function testGetWriter() {

        $ns = [
            'http://sabre.io/ns' => 's',
        ];

        $util = new Service();
        $util->namespaceMap = $ns;

        $writer = $util->getWriter();
        $this->assertInstanceOf('Sabre\\Xml\\Writer', $writer);
        $this->assertEquals($ns, $writer->namespaceMap);

    }

    /**
     * @depends testGetReader
     */
    function testParse() {

        $xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
        $util = new Service();
        $result = $util->parse($xml, null, $rootElement);
        $this->assertEquals('{http://sabre.io/ns}root', $rootElement);

        $expected = [
            [
                'name'       => '{http://sabre.io/ns}child',
                'value'      => 'value',
                'attributes' => [],
            ]
        ];

        $this->assertEquals(
            $expected,
            $result
        );

    }

    /**
     * @depends testGetReader
     */
    function testParseStream() {

        $xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $xml);
        rewind($stream);

        $util = new Service();
        $result = $util->parse($stream, null, $rootElement);
        $this->assertEquals('{http://sabre.io/ns}root', $rootElement);

        $expected = [
            [
                'name'       => '{http://sabre.io/ns}child',
                'value'      => 'value',
                'attributes' => [],
            ]
        ];

        $this->assertEquals(
            $expected,
            $result
        );

    }

    /**
     * @depends testGetReader
     */
    function testExpect() {

        $xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
        $util = new Service();
        $result = $util->expect('{http://sabre.io/ns}root', $xml);

        $expected = [
            [
                'name'       => '{http://sabre.io/ns}child',
                'value'      => 'value',
                'attributes' => [],
            ]
        ];

        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @depends testGetReader
     */
    function testExpectStream() {

        $xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $xml);
        rewind($stream);

        $util = new Service();
        $result = $util->expect('{http://sabre.io/ns}root', $stream);

        $expected = [
            [
                'name'       => '{http://sabre.io/ns}child',
                'value'      => 'value',
                'attributes' => [],
            ]
        ];

        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @depends testGetReader
     * @expectedException \Sabre\Xml\ParseException
     */
    function testExpectWrong() {

        $xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
        $util = new Service();
        $util->expect('{http://sabre.io/ns}error', $xml);

    }

    /**
     * @depends testGetWriter
     */
    function testWrite() {

        $util = new Service();
        $util->namespaceMap = [
            'http://sabre.io/ns' => 's',
        ];
        $result = $util->write('{http://sabre.io/ns}root', [
            '{http://sabre.io/ns}child' => 'value',
        ]);

        $expected = <<<XML
<?xml version="1.0"?>
<s:root xmlns:s="http://sabre.io/ns">
 <s:child>value</s:child>
</s:root>

XML;
        $this->assertEquals(
            $expected,
            $result
        );

    }

    function testParseClarkNotation() {

        $this->assertEquals([
            'http://sabredav.org/ns',
            'elem',
        ], Service::parseClarkNotation('{http://sabredav.org/ns}elem'));

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testParseClarkNotationFail() {

        Service::parseClarkNotation('http://sabredav.org/ns}elem');

    }

}
