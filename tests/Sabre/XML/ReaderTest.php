<?php

namespace Sabre\XML;

class ReaderTest extends \PHPUnit_Framework_TestCase {

    function testGetClark() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns" />
BLA;
        $reader = new Reader();
        $reader->xml($input);

        $reader->next();

        $this->assertEquals('{http://sabredav.org/ns}root', $reader->getClark());

    }

    function testGetClarkNoNS() {

        $input = <<<BLA
<?xml version="1.0"?>
<root />
BLA;
        $reader = new Reader();
        $reader->xml($input);

        $reader->next();

        $this->assertNull($reader->getClark());

    }

    function testSimple() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
  <elem1 attr="val" />
  <elem2>
    <elem3>Hi!</elem3>
  </elem2>
</root>
BLA;

        $reader = new Reader();
        $reader->xml($input);

        $output = $reader->parse();

        $expected = [
            'name' => '{http://sabredav.org/ns}root',
            'value' => [
                [
                    'name' => '{http://sabredav.org/ns}elem1',
                    'value' => null,
                    'attributes' => [
                        'attr' => 'val',
                    ],
                ],
                [
                    'name' => '{http://sabredav.org/ns}elem2',
                    'value' => [
                        [
                            'name' => '{http://sabredav.org/ns}elem3',
                            'value' => 'Hi!',
                            'attributes' => [],
                        ],
                    ],
                    'attributes' => [],
                ],

            ],
            'attributes' => [],

        ];

        $this->assertEquals($expected, $output);

    }

    function testCDATA() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
  <foo><![CDATA[bar]]></foo>
</root>
BLA;

        $reader = new Reader();
        $reader->xml($input);

        $output = $reader->parse();

        $expected = [
            'name' => '{http://sabredav.org/ns}root',
            'value' => [
                [
                    'name' => '{http://sabredav.org/ns}foo',
                    'value' => 'bar',
                    'attributes' => [],
                ],

            ],
            'attributes' => [],

        ];

        $this->assertEquals($expected, $output);

    }

    function testSimpleNamespacedAttribute() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns" xmlns:foo="urn:foo">
  <elem1 foo:attr="val" />
</root>
BLA;

        $reader = new Reader();
        $reader->xml($input);

        $output = $reader->parse();

        $expected = [
            'name' => '{http://sabredav.org/ns}root',
            'value' => [
                [
                    'name' => '{http://sabredav.org/ns}elem1',
                    'value' => null,
                    'attributes' => [
                        '{urn:foo}attr' => 'val',
                    ],
                ],
            ],
            'attributes' => [],
        ];

        $this->assertEquals($expected, $output);

    }

    function testMappedElement() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
  <elem1 />
</root>
BLA;

        $reader = new Reader();
        $reader->elementMap = [
            '{http://sabredav.org/ns}elem1' => 'Sabre\\XML\\Element\\Mock'
        ];
        $reader->xml($input);

        $output = $reader->parse();

        $expected = [
            'name' => '{http://sabredav.org/ns}root',
            'value' => [
                [
                    'name' => '{http://sabredav.org/ns}elem1',
                    'value' => 'foobar',
                    'attributes' => [],
                ],
            ],
            'attributes' => [],

        ];

        $this->assertEquals($expected, $output);

    }

    function testParseProblem() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
BLA;

        $reader = new Reader();
        $reader->elementMap = [
            '{http://sabredav.org/ns}elem1' => 'Sabre\\XML\\Element\\Mock'
        ];
        $reader->xml($input);

        try {
            $output = $reader->parse();
            $this->fail('We expected a ParseException to be thrown');
        } catch (LibXMLException $e) {

            $this->assertInternalType('array', $e->getErrors());

        }

    }

    /**
     * @expectedException \Sabre\XML\ParseException
     */
    function testBrokenParserClass() {

        $input = <<<BLA
<?xml version="1.0"?>
<root xmlns="http://sabredav.org/ns">
<elem1 />
</root>
BLA;

        $reader = new Reader();
        $reader->elementMap = [
            '{http://sabredav.org/ns}elem1' => 'Sabre\\XML\\Element\\Eater'
        ];
        $reader->xml($input);
        $reader->parse();


    }

}

