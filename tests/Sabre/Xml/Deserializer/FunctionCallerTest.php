<?php

declare(strict_types=1);

namespace Sabre\Xml\Deserializer;

use PHPUnit\Framework\TestCase;
use Sabre\Xml\Reader;

class FunctionCallerTest extends TestCase
{
    public function testDeserializeFunctionCaller(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<person xmlns="urn:foo">
 <name>John</name>
 <age>18</age>
 <address>
  <street>X</street>
  <number>12</number>
 </address>
 <languages>
  <language>
   <value>English</value>
  </language>
  <language>
   <value>Portuguese</value>
  </language>
  <language>
   <value>German</value>
  </language>
 </languages>
</person>
XML;

        $reader = new Reader();
        $reader::XML($input);
        $reader->elementMap['{urn:foo}person'] = (fn (Reader $reader) => functionCaller($reader, [Person::class, 'fromXml'], 'urn:foo'));
        $reader->elementMap['{urn:foo}address'] = (fn (Reader $reader) => functionCaller($reader, [Address::class, 'fromXml'], 'urn:foo'));
        $reader->elementMap['{urn:foo}language'] = (fn (Reader $reader) => functionCaller($reader, [Language::class, 'fromXml'], 'urn:foo'));
        $reader->elementMap['{urn:foo}languages'] = (fn (Reader $reader) => repeatingElements($reader, '{urn:foo}language'));

        $output = $reader->parse();

        $person = new Person(
            'John',
            18,
            new Address('X', 12),
            [new Language('English'), new Language('Portuguese'), new Language('German')]
        );

        $expected = [
            'name' => '{urn:foo}person',
            'value' => $person,
            'attributes' => [],
        ];

        self::assertEquals(
            $expected,
            $output
        );
    }

    public function testDeserializeFunctionCallerWithDifferentTypesOfCallable(): void
    {
        $input = <<<XML
<?xml version="1.0"?>
<person xmlns="urn:foo">
 <name>John</name>
 <age>18</age>
 <address>
  <street>X</street>
  <number>12</number>
 </address>
 <languages>
  <language>
   <value>English</value>
  </language>
  <language>
   <value>Portuguese</value>
  </language>
  <language>
   <value>German</value>
  </language>
 <language />
 </languages>
</person>
XML;

        $reader = new Reader();
        $reader::XML($input);
        $reader->elementMap['{urn:foo}person'] = (fn (Reader $reader) => functionCaller($reader, Person::class.'::fromXml', 'urn:foo'));
        $reader->elementMap['{urn:foo}address'] = (fn (Reader $reader) => functionCaller($reader, __NAMESPACE__.'\newAddressFromXml', 'urn:foo'));
        $reader->elementMap['{urn:foo}language'] = (fn (Reader $reader) => functionCaller($reader, fn (string $value): Language => new Language($value), 'urn:foo'));
        $reader->elementMap['{urn:foo}languages'] = (fn (Reader $reader) => repeatingElements($reader, '{urn:foo}language'));

        $output = $reader->parse();

        $person = new Person(
            'John',
            18,
            new Address('X', 12),
            [new Language('English'), new Language('Portuguese'), new Language('German'), null]
        );

        $expected = [
            'name' => '{urn:foo}person',
            'value' => $person,
            'attributes' => [],
        ];

        self::assertEquals(
            $expected,
            $output
        );
    }
}
final readonly class Person
{
    /**
     * @param array<int, Language|null> $languages
     */
    public function __construct(private string $name, private int $age, private Address $address, private array $languages)
    {
    }

    /**
     * @param array<int, Language|null> $languages
     */
    public static function fromXml(string $name, string $age, Address $address, array $languages): self
    {
        return new self($name, (int) $age, $address, $languages);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return array<int, Language|null>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }
}
final readonly class Address
{
    public function __construct(private string $street, private int $number)
    {
    }

    public static function fromXml(string $street, string $number): self
    {
        return new self($street, (int) $number);
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
final readonly class Language
{
    public function __construct(private string $value)
    {
    }

    public static function fromXml(string $value): self
    {
        return new self($value);
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
function newAddressFromXml(string $street, string $number): Address
{
    return new Address($street, (int) $number);
}
