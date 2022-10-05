<?php

declare(strict_types=1);

namespace Sabre\Xml\Deserializer;

use PHPUnit\Framework\TestCase;
use Sabre\Xml\Reader;

class ValueObjectTest extends TestCase
{
    public function testDeserializeValueObject(): void
    {
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
            '{urn:foo}foo' => function (Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            },
        ];

        $output = $reader->parse();

        $vo = new TestVo();
        $vo->firstName = 'Harry';
        $vo->lastName = 'Turtle';

        $expected = [
            'name' => '{urn:foo}foo',
            'value' => $vo,
            'attributes' => [],
        ];

        $this->assertEquals(
            $expected,
            $output
        );
    }

    public function testDeserializeValueObjectIgnoredElement(): void
    {
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
            '{urn:foo}foo' => function (Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            },
        ];

        $output = $reader->parse();

        $vo = new TestVo();
        $vo->firstName = 'Harry';
        $vo->lastName = 'Turtle';

        $expected = [
            'name' => '{urn:foo}foo',
            'value' => $vo,
            'attributes' => [],
        ];

        $this->assertEquals(
            $expected,
            $output
        );
    }

    public function testDeserializeValueObjectAutoArray(): void
    {
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
            '{urn:foo}foo' => function (Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            },
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
            'name' => '{urn:foo}foo',
            'value' => $vo,
            'attributes' => [],
        ];

        $this->assertEquals(
            $expected,
            $output
        );
    }

    public function testDeserializeValueObjectEmpty(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<foo xmlns="urn:foo" />
XML;

        $reader = new Reader();
        $reader->xml($input);
        $reader->elementMap = [
            '{urn:foo}foo' => function (Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            },
        ];

        $output = $reader->parse();

        $vo = new TestVo();

        $expected = [
            'name' => '{urn:foo}foo',
            'value' => $vo,
            'attributes' => [],
        ];

        $this->assertEquals(
            $expected,
            $output
        );
    }

    public function testDeserializeValueObjectEmptyString(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<doc>
<foo xmlns="urn:foo"></foo>
</doc>
XML;

        $reader = new Reader();
        $reader->xml($input);
        $reader->elementMap = [
            '{urn:foo}foo' => function (Reader $reader) {
                return valueObject($reader, 'Sabre\\Xml\\Deserializer\\TestVo', 'urn:foo');
            },
        ];

        $output = $reader->parse();

        $vo = new TestVo();

        $expected = [
            'name' => '{urn:foo}foo',
            'value' => $vo,
            'attributes' => [],
        ];

        $this->assertEquals(
            $expected,
            $output['value'][0]
        );
    }
}

class TestVo
{
    public string $firstName;
    public string $lastName;

    /**
     * @var array<int, string>
     */
    public array $link = [];
}
