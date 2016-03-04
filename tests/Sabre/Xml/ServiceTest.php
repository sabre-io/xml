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

    function testMapValueObject() {

        $input = <<<XML
<?xml version="1.0"?>
<order xmlns="http://sabredav.org/ns">
 <id>1234</id>
 <amount>99.99</amount>
 <description>black friday deal</description>
 <status>
  <id>5</id>
  <label>processed</label>
 </status>
</order>

XML;

        $ns = 'http://sabredav.org/ns';
        $orderService = new \Sabre\Xml\Service();
        $orderService->mapValueObject('{' . $ns . '}order', 'Sabre\Xml\Order');
        $orderService->mapValueObject('{' . $ns . '}status', 'Sabre\Xml\OrderStatus');
        $orderService->namespaceMap[$ns] = null;

        $order = $orderService->parse($input);
        $expected = new Order();
        $expected->id = 1234;
        $expected->amount = 99.99;
        $expected->description = 'black friday deal';
        $expected->status = new OrderStatus();
        $expected->status->id = 5;
        $expected->status->label = 'processed';

        $this->assertEquals($expected, $order);

        $writtenXml = $orderService->writeValueObject($order);
        $this->assertEquals($input, $writtenXml);
    }
    
    function testCircularMapValueObjects() {

        $input = <<<XML
<?xml version="1.0"?>
  <element name="order_request" xmlns="http://www.w3.org/2001/XMLSchema">
    <complexType>
      <sequence>
        <element name="request_id" />
        <element name="order_id" />
      </sequence>
    </complexType>
  </element>
XML;

        $ns = 'http://www.w3.org/2001/XMLSchema';
        $xsdService = new \Sabre\Xml\Service();
        $xsdService->mapValueObject('{' . $ns . '}element', 'Sabre\Xml\XsdElement');
        $xsdService->mapValueObject('{' . $ns . '}complexType', 'Sabre\Xml\XsdComplexType');
        $xsdService->mapValueObject('{' . $ns . '}sequence', 'Sabre\Xml\XsdSequence');
        $xsdService->namespaceMap[$ns] = null;

        $order = $xsdService->parse($input);
        $expected = new XsdElement();
        $expected->complexType = new XsdComplexType();
        $expected->complexType->sequence = new XsdSequence();
        
        $reqId = new XsdElement();
        $reqId->name = "request_id";
        $orderId = new XsdElement();
        $orderId->name = "order_id";
        $expected->complexType->sequence->element[] = $reqId
        $expected->complexType->sequence->element[] = $orderId
        
        $this->assertEquals($expected, $order);

        $writtenXml = $xsdService->writeValueObject($order);
        $this->assertEquals($input, $writtenXml);
    }    

    function testMapValueObjectArrayProperty() {

        $input = <<<XML
<?xml version="1.0"?>
<order xmlns="http://sabredav.org/ns">
 <id>1234</id>
 <amount>99.99</amount>
 <description>black friday deal</description>
 <status>
  <id>5</id>
  <label>processed</label>
 </status>
 <link>http://example.org/</link>
 <link>http://example.com/</link>
</order>

XML;

        $ns = 'http://sabredav.org/ns';
        $orderService = new \Sabre\Xml\Service();
        $orderService->mapValueObject('{' . $ns . '}order', 'Sabre\Xml\Order');
        $orderService->mapValueObject('{' . $ns . '}status', 'Sabre\Xml\OrderStatus');
        $orderService->namespaceMap[$ns] = null;

        $order = $orderService->parse($input);
        $expected = new Order();
        $expected->id = 1234;
        $expected->amount = 99.99;
        $expected->description = 'black friday deal';
        $expected->status = new OrderStatus();
        $expected->status->id = 5;
        $expected->status->label = 'processed';
        $expected->link = ['http://example.org/', 'http://example.com/'];

        $this->assertEquals($expected, $order);

        $writtenXml = $orderService->writeValueObject($order);
        $this->assertEquals($input, $writtenXml);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testWriteVoNotFound() {

        $service = new Service();
        $service->writeValueObject(new \StdClass());

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

/**
 * asset for testMapValueObject()
 * @internal
 */
class Order {
    public $id;
    public $amount;
    public $description;
    public $status;
    public $empty;
    public $link = [];
}

/**
 * asset for testMapValueObject()
 * @internal
 */
class OrderStatus {
    public $id;
    public $label;
}

/**
 * asset for testCircularMapValueObjects()
 * @internal
 */
class XsdComplexType {
    public $name;
    /** @var XsdSequence */
    public $sequence;
}

/**
 * asset for testCircularMapValueObjects()
 * @internal
 */
class XsdSequence {
    /** @var XsdElement[] */
    public $element = array();
}

/**
 * asset for testCircularMapValueObjects()
 * @internal
 */
class XsdElement {
    public $name;
    /** @var XsdComplexType|null */
    public $complexType;
}
