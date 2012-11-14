SabreTooth XML library
======================

The SabreTooth XML library is a specialized xml reader and writer.

I often found myself repeating the same pattern for xml manipulation over and
over. This library implements that pattern.

At it's heart, the library maps xml elements to PHP value objects.

The following assumptions are made:

* XML namespaces are used everywhere.
* XML is written and read sequentially.
* All XML elements map to PHP classes and scalars.
* Elements generally contain either just text, just sub-elements or nothing all.
* Elements are represented by classes. A class has a serialize() and a
  deserialize() method.
* Namespace prefixes must be completely ignored by an xml reader.

This is not your average XML library. The intention is not to make this super
simple, but rather very powerful for complex XML applications.

Installation
------------

This library requires PHP 5.3 and the XMLReader and XMLWriter extensions.
Installation is done using composer.

The general composer instructions can be found on the [composer website](http://getcomposer.org/doc/00-intro.md).

After that, just declare the vobject dependency as follows:

```json
"require" : {
    "sabre/http" : "master-dev"
}
```

Then, run `composer.phar update` and you should be good.

Sample XML document
-------------------

All the following examples use an Atom xml document.
The document can be found here:

[atom.xml](samples/atom.xml)

Note that this sample was taken from [wikipedia](https://en.wikipedia.org/wiki/Atom_%28standard%29).

Reading XML documents
---------------------

To read an XML document, simply instantiate the reader class. The reader class
is a subclass of PHP's XMLReader.

Simple example:

```php
include __DIR__ . '/vendor/autoload.php';

use Sabre\XML;

$reader = new XML\Reader();
$reader->open('atom.xml');

$output = $reader->parse();
var_dump($output);
```

The `parse` method reads the entire file, and the resulting data is returned
from that method.

The result will look like this: [samples/atom.parsed1.txt]

Quite ugly indeed, but we'll get to cleaning that up later.

Writing XML documents
---------------------

To write that same XML document, we use the Writer class.

```php
$writer = new XML\Writer();
$writer->openMemory();
$writer->setIndent(true); // for pretty indentation
$writer->write(array($output));

echo $writer->outputMemory();
```

The output will look like this: [atom.written1.xml](samples/atom.written1.xml)

Ugly you say? This library will inline xml namespaces everywhere, unless they
are specified in advance:

```php
$writer = new XML\Writer();
$writer->namespaceMap = array(
    'http://www.w3.org/2005/Atom' => 'a',
);
$writer->openMemory();
$writer->setIndent(true); // for pretty indentation
$writer->write(array($output));

echo $writer->outputMemory();
```

The output looks pretty normal now: [atom.written2.xml](samples/atom.written2.xml)

Mapping XML elements
--------------------

Normally when writing an Atom parser using this tool, there will be a number of
elements that make sense to create using classes for.

A great example would be the `entry` element:

```php
class AtomEntry {

  public $title;
  public $links;
  public $id;
  public $updated;
  public $summary;

  /* etc.. */

}
```

Similarly we'd also create an element for the entire feed:

```php
class AtomFeed {

    public $title;
    public $subTitle;
    public $links;
    public $id;
    public $updated;

}
```

Lets start with a simple one though, that recurs in a bunch of places: `link`.

The `link` element can a `href`, `rel` and `type` attribute. There's actually a
bunch more if you're going for a full parser.

We'll just focus on those three though..

Our base class:

```php
class AtomLink {

    public $href;
    public $rel;
    public $type;

}
```

Now the SabreXML additions:

```php
use Sabre\XML;

class AtomLink implements XML\Element {

    public $href;
    public $rel;
    public $type;

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * @param XML\Writer $reader
     * @return void
     */
    public function serialize(XML\Writer $writer) {

        $writer->writeAttribute('href', $this->href);
        $writer->writeAttribute('rel', $this->rel);
        $writer->writeAttribute('type', $this->type);

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * @param XML\Reader $reader
     * @return mixed
     */
    static public function deserialize(XML\Reader $reader) {

        $attributes = $reader->parseAttributes();

        $link = new self();
        foreach($attributes as $name=>$value) {
            if (property_exists($link,$name)) {
                $link->$name = $value;
            }
        }

        return $link;

    }

}
```

To automatically map all `link` elements to the new `AtomLink` class, register
it on the reader:

```php
$reader = new XML\Reader();
$reader->elementMap = array(
    '{http://www.w3.org/2005/Atom}link' => 'AtomLink'
);
$reader->open('samples/atom.xml');

$output = $reader->parse();
```

When inspecting the output, the `link` element will now properly be replaced
with our newly created object, and sending this back to the `Writer` will also
work as expected.

