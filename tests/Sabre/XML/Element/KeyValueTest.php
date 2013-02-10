<?php

namespace Sabre\XML\Element;

use
    Sabre\XML\Reader,
    Sabre\XML\Writer;

class KeyValueTest extends \PHPUnit_Framework_TestCase {

    function testDeserialize() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
  <struct>
    <elem1 />
    <elem2>hi</elem2>
    <elem3>
       <elem4>foo</elem4>
       <elem5>foo &amp; bar</elem5>
    </elem3>
    <elem6>Hi<!-- ignore me -->there</elem6>
  </struct>
  <struct />
  <otherThing>
    <elem1 />
  </otherThing>
</root>
BLA;

        $reader = new Reader();
        $reader->elementMap = [
            '{http://sabredav.org/ns}struct' => 'Sabre\\XML\\Element\\KeyValue',
        ];
        $reader->xml($input);

        $output = $reader->parse();

        $this->assertEquals([
            [
                'name' => '{http://sabredav.org/ns}root',
                'value' => [
                    [
                        'name' => '{http://sabredav.org/ns}struct',
                        'value' => [
                            '{http://sabredav.org/ns}elem1' => null,
                            '{http://sabredav.org/ns}elem2' => 'hi',
                            '{http://sabredav.org/ns}elem3' => [
                                [
                                    'name' => '{http://sabredav.org/ns}elem4',
                                    'value' => 'foo',
                                    'attributes' => [],
                                ],
                                [
                                    'name' => '{http://sabredav.org/ns}elem5',
                                    'value' => 'foo & bar',
                                    'attributes' => [],
                                ],
                            ],
                            '{http://sabredav.org/ns}elem6' => 'Hithere',
                        ],
                        'attributes' => [],
                    ],
                    [
                        'name' => '{http://sabredav.org/ns}struct',
                        'value' => [],
                        'attributes' => [],
                    ],
                    [
                        'name' => '{http://sabredav.org/ns}otherThing',
                        'value' => [
                            [
                                'name' => '{http://sabredav.org/ns}elem1',
                                'value' => null,
                                'attributes' => [],
                            ],
                        ],
                        'attributes' => [],
                    ],
                ],
                'attributes' => [],
            ],
        ], $output);

    }

    function testSerialize() {

        $value = [
            '{http://sabredav.org/ns}elem1' => null,
            '{http://sabredav.org/ns}elem2' => 'textValue',
            '{http://sabredav.org/ns}elem3' => [
                '{http://sabredav.org/ns}elem4' => 'text2',
                '{http://sabredav.org/ns}elem5' =>  null,
            ],
            '{http://sabredav.org/ns}elem6' => 'text3',
        ];

        $writer = new Writer();
        $writer->namespaceMap = [
            'http://sabredav.org/ns' => null
        ];
        $writer->openMemory();
        $writer->startDocument('1.0');
        $writer->setIndent(true);
        $writer->write([
            '{http://sabredav.org/ns}root' => new KeyValue($value),
        ]);

        $output = $writer->outputMemory();

        $expected = <<<XML
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
 <elem1/>
 <elem2>textValue</elem2>
 <elem3>
  <elem4>text2</elem4>
  <elem5/>
 </elem3>
 <elem6>text3</elem6>
</root>

XML;

        $this->assertEquals($expected, $output);

    }

}

