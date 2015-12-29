<?php

namespace Sabre\XML\Deserializer;

use
    Sabre\Xml\Reader;

class DeserializersTest extends \PHPUnit_Framework_TestCase {

    function testKeyValue()
    {
        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
  <struct>
    <elem1 />
    <elem2>hi</elem2>
    <elem3 xmlns="http://sabredav.org/another-ns">
       <elem4>foo</elem4>
       <elem5>foo &amp; bar</elem5>
    </elem3>
  </struct>
</root>
BLA;

        $reader = new Reader();
        $reader->elementMap = [
            '{http://sabredav.org/ns}struct' => function(Reader $reader) {
                return keyValue($reader, 'http://sabredav.org/ns');
            }
        ];
        $reader->xml($input);
        $output = $reader->parse();

        $this->assertEquals([
            'name'  => '{http://sabredav.org/ns}root',
            'value' => [
                [
                    'name'  => '{http://sabredav.org/ns}struct',
                    'value' => [
                        'elem1'                                 => null,
                        'elem2'                                 => 'hi',
                        '{http://sabredav.org/another-ns}elem3' => [
                            [
                                'name'       => '{http://sabredav.org/another-ns}elem4',
                                'value'      => 'foo',
                                'attributes' => [],
                            ],
                            [
                                'name'       => '{http://sabredav.org/another-ns}elem5',
                                'value'      => 'foo & bar',
                                'attributes' => [],
                            ],
                        ]
                    ],
                    'attributes' => [],
                ]
            ],
            'attributes' => [],
        ], $output);
    }

    function testDeserializeValueObject() {

        $input = <<<XML
<?xml version="1.0"?>
<foo xmlns="urn:foo">
   <firstName>Harry</firstName>
   <lastName>Turtle</lastName>
</foo>
XML;

        $reader = new Reader();
        $reader->xml($input);
        $reader->elementMap = [
            '{urn:foo}foo' => function(Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            }
        ];

        $output = $reader->parse();

        $vo = new TestVo();
        $vo->firstName = 'Harry';
        $vo->lastName = 'Turtle';

        $expected = [
            'name'       => '{urn:foo}foo',
            'value'      => $vo,
            'attributes' => []
        ];

        $this->assertEquals(
            $expected,
            $output
        );

    }

    function testDeserializeValueObjectIgnoredElement() {

        $input = <<<XML
<?xml version="1.0"?>
<foo xmlns="urn:foo">
   <firstName>Harry</firstName>
   <lastName>Turtle</lastName>
   <email>harry@example.org</email>
</foo>
XML;

        $reader = new Reader();
        $reader->xml($input);
        $reader->elementMap = [
            '{urn:foo}foo' => function(Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            }
        ];

        $output = $reader->parse();

        $vo = new TestVo();
        $vo->firstName = 'Harry';
        $vo->lastName = 'Turtle';

        $expected = [
            'name'       => '{urn:foo}foo',
            'value'      => $vo,
            'attributes' => []
        ];

        $this->assertEquals(
            $expected,
            $output
        );

    }

    function testDeserializeValueObjectAutoArray() {

        $input = <<<XML
<?xml version="1.0"?>
<foo xmlns="urn:foo">
   <firstName>Harry</firstName>
   <lastName>Turtle</lastName>
   <link>http://example.org/</link>
   <link>http://example.net/</link>
</foo>
XML;

        $reader = new Reader();
        $reader->xml($input);
        $reader->elementMap = [
            '{urn:foo}foo' => function(Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            }
        ];

        $output = $reader->parse();

        $vo = new TestVo();
        $vo->firstName = 'Harry';
        $vo->lastName = 'Turtle';
        $vo->link = [
            'http://example.org/',
            'http://example.net/',
        ];


        $expected = [
            'name'       => '{urn:foo}foo',
            'value'      => $vo,
            'attributes' => []
        ];

        $this->assertEquals(
            $expected,
            $output
        );

    }

}

class TestVo {

    public $firstName;
    public $lastName;

    public $link = [];

}
