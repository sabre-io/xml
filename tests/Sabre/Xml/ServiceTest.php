<?php

declare(strict_types=1);

namespace Sabre\Xml;

use PHPUnit\Framework\TestCase;
use Sabre\Xml\Element\KeyValue;

class ServiceTest extends TestCase
{
    public function testGetReader(): void
    {
        $elems = [
            '{http://sabre.io/ns}test' => 'stdClass',
        ];

        $util = new Service();
        $util->elementMap = $elems;

        $reader = $util->getReader();
        $this->assertInstanceOf('Sabre\\Xml\\Reader', $reader);
        $this->assertEquals($elems, $reader->elementMap);
    }

    public function testGetWriter(): void
    {
        $ns = [
            'http://sabre.io/ns' => 'stdClass',
        ];

        $util = new Service();
        $util->namespaceMap = $ns;

        $writer = $util->getWriter();
        $this->assertInstanceOf('Sabre\\Xml\\Writer', $writer);
        $this->assertEquals($ns, $writer->namespaceMap);
    }

    /**
     * @dataProvider providesEmptyInput
     *
     * @param string|resource $input
     */
    public function testEmptyInputParse($input): void
    {
        $this->expectException('\Sabre\Xml\ParseException');
        $this->expectExceptionMessage('The input element to parse is empty. Do not attempt to parse');

        $util = new Service();
        $util->parse($input, '/sabre.io/ns');
    }

    /**
     * @depends testGetReader
     */
    public function testParse(): void
    {
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
                'name' => '{http://sabre.io/ns}child',
                'value' => 'value',
                'attributes' => [],
            ],
        ];

        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @depends testGetReader
     */
    public function testParseStream(): void
    {
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
                'name' => '{http://sabre.io/ns}child',
                'value' => 'value',
                'attributes' => [],
            ],
        ];

        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @dataProvider providesEmptyInput
     *
     * @param string|resource $input
     */
    public function testEmptyInputExpect($input): void
    {
        $this->expectException('\Sabre\Xml\ParseException');
        $this->expectExceptionMessage('The input element to parse is empty. Do not attempt to parse');

        $util = new Service();
        $util->expect('foo', $input, '/sabre.io/ns');
    }

    /**
     * @depends testGetReader
     */
    public function testExpect(): void
    {
        $xml = <<<XML
<root xmlns="http://sabre.io/ns">
  <child>value</child>
</root>
XML;
        $util = new Service();
        $result = $util->expect('{http://sabre.io/ns}root', $xml);

        $expected = [
            [
                'name' => '{http://sabre.io/ns}child',
                'value' => 'value',
                'attributes' => [],
            ],
        ];

        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testInvalidNameSpace(): void
    {
        $this->expectException(LibXMLException::class);
        $xml = '<D:propfind xmlns:D="DAV:"><D:prop><bar:foo xmlns:bar=""/></D:prop></D:propfind>';

        $util = new Service();
        $util->elementMap = [
            '{DAV:}propfind' => PropFindTestAsset::class,
        ];
        $util->namespaceMap = [
            'http://sabre.io/ns' => 'stdClass',
        ];
        $result = $util->expect('{DAV:}propfind', $xml);
    }

    /**
     * @dataProvider providesEmptyPropfinds
     */
    public function testEmptyPropfind(string $xml): void
    {
        $util = new Service();
        $util->elementMap = [
            '{DAV:}propfind' => PropFindTestAsset::class,
        ];
        $util->namespaceMap = [
            'http://sabre.io/ns' => 'stdClass',
        ];
        /**
         * @var PropFindTestAsset
         */
        $result = $util->expect('{DAV:}propfind', $xml);
        $this->assertIsObject($result);
        $this->assertInstanceOf(PropFindTestAsset::class, $result);
        $this->assertEquals(false, $result->allProp);
        $this->assertEquals([], $result->properties);
    }

    /**
     * @depends testGetReader
     */
    public function testExpectStream(): void
    {
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
                'name' => '{http://sabre.io/ns}child',
                'value' => 'value',
                'attributes' => [],
            ],
        ];

        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @depends testGetReader
     */
    public function testExpectWrong(): void
    {
        $this->expectException(ParseException::class);
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
    public function testWrite(): void
    {
        $util = new Service();
        $util->namespaceMap = [
            'http://sabre.io/ns' => 'stdClass',
        ];
        $result = $util->write('{http://sabre.io/ns}root', [
            '{http://sabre.io/ns}child' => 'value',
        ]);

        $expected = <<<XML
<?xml version="1.0"?>
<stdClass:root xmlns:stdClass="http://sabre.io/ns">
 <stdClass:child>value</stdClass:child>
</stdClass:root>

XML;
        $this->assertEquals(
            $expected,
            $result
        );
    }

    public function testMapValueObject(): void
    {
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
        $orderService->mapValueObject('{'.$ns.'}order', 'Sabre\Xml\Order');
        $orderService->mapValueObject('{'.$ns.'}status', 'Sabre\Xml\OrderStatus');
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

    public function testMapValueObjectArrayProperty(): void
    {
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
        $orderService->mapValueObject('{'.$ns.'}order', 'Sabre\Xml\Order');
        $orderService->mapValueObject('{'.$ns.'}status', 'Sabre\Xml\OrderStatus');
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

    public function testWriteVoNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $service = new Service();
        $service->writeValueObject(new \stdClass());
    }

    public function testParseClarkNotation(): void
    {
        $this->assertEquals([
            'http://sabredav.org/ns',
            'elem',
        ], Service::parseClarkNotation('{http://sabredav.org/ns}elem'));
    }

    public function testParseClarkNotationFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Service::parseClarkNotation('http://sabredav.org/ns}elem');
    }

    /**
     * @return array<int, array<int, string|resource|false>>
     */
    public function providesEmptyInput(): array
    {
        $emptyResource = fopen('php://input', 'r');
        $data[] = [$emptyResource];
        $data[] = [''];

        return $data;
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function providesEmptyPropfinds(): array
    {
        return [
            ['<D:propfind xmlns:D="DAV:"><D:prop></D:prop></D:propfind>'],
            ['<D:propfind xmlns:D="DAV:"><D:prop xmlns:s="http://sabredav.org/ns"></D:prop></D:propfind>'],
            ['<D:propfind xmlns:D="DAV:"><D:prop/></D:propfind>'],
            ['<D:propfind xmlns:D="DAV:"><D:prop xmlns:s="http://sabredav.org/ns"/></D:propfind>'],
            ['<D:propfind xmlns:D="DAV:"><D:prop>     </D:prop></D:propfind>'],
        ];
    }
}

/**
 * asset for testMapValueObject().
 *
 * @internal
 */
class Order
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var float|string
     */
    public $amount;
    public string $description;
    public OrderStatus $status;
    public string $empty;
    /**
     * @var array<int, string>
     */
    public array $link = [];
}

/**
 * asset for testMapValueObject().
 *
 * @internal
 */
class OrderStatus
{
    /**
     * @var int|string
     */
    public $id;
    /**
     * @var int|string
     */
    public $label;
}

/**
 * asset for testInvalidNameSpace.
 *
 * @internal
 */
class PropFindTestAsset implements XmlDeserializable
{
    public bool $allProp = false;

    /**
     * @var array<int, mixed>
     */
    public array $properties;

    public static function xmlDeserialize(Reader $reader): self
    {
        $self = new self();

        $reader->pushContext();
        $reader->elementMap['{DAV:}prop'] = 'Sabre\Xml\Element\Elements';

        foreach (KeyValue::xmlDeserialize($reader) as $k => $v) {
            switch ($k) {
                case '{DAV:}prop':
                    $self->properties = $v;
                    break;
                case '{DAV:}allprop':
                    $self->allProp = true;
            }
        }

        $reader->popContext();

        return $self;
    }
}
