<?php

declare(strict_types=1);

namespace Sabre\Xml\Deserializer;

use PHPUnit\Framework\TestCase;
use Sabre\Xml\Service;

class EnumTest extends TestCase
{
    public function testDeserialize(): void
    {
        $service = new Service();
        $service->elementMap['{urn:test}root'] = 'Sabre\Xml\Deserializer\enum';

        $xml = <<<XML
<?xml version="1.0"?>
<root xmlns="urn:test">
   <foo1/>
   <foo2/>
</root>
XML;

        $result = $service->parse($xml);

        $expected = [
            '{urn:test}foo1',
            '{urn:test}foo2',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDeserializeDefaultNamespace(): void
    {
        $service = new Service();
        $service->elementMap['{urn:test}root'] = function ($reader) {
            return enum($reader, 'urn:test');
        };

        $xml = <<<XML
<?xml version="1.0"?>
<root xmlns="urn:test">
   <foo1/>
   <foo2/>
</root>
XML;

        $result = $service->parse($xml);

        $expected = [
            'foo1',
            'foo2',
        ];

        $this->assertEquals($expected, $result);
    }

    public function testEmptyEnum(): void
    {
        $service = new Service();
        $service->elementMap['{urn:test}enum'] = 'Sabre\Xml\Deserializer\enum';

        $xml = <<<XML
<?xml version="1.0"?>
<root xmlns="urn:test">
<inner>
  <enum></enum>
</inner>
</root>
XML;

        $result = $service->parse($xml);

        $this->assertEquals([[
            'name' => '{urn:test}inner',
            'value' => [[
                'name' => '{urn:test}enum',
                'value' => [],
                'attributes' => [],
            ]],
            'attributes' => [],
        ]], $result);
    }
}
