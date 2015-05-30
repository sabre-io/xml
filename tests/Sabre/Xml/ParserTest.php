<?php

namespace Sabre\Xml;

/**
 * Test the XML reader.
 * -Test with valid document
 * -Test XML schema validation
 * -Test invalid documents (missing start and end tag, malformed start and end tag and malformed quotes)
 *
 *
 * PHP version 5
 *
 * Class ParserTest
 * @author     Daan Biesterbos <daanbiesterbos@gmail.com>
 * @license http://sabre.io/license/ Modified BSD License
 * @since     1.0.0
 */
class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Simple test to check if the parser is able to parse documents under normal circumstances.
     * Check if the result is as expected.
     * - Use multiple schema's and multiple namespaces
     * - Test attributes
     * - Test elements with child nodes
     * - Test element without child nodes and attributes ( <movie:bestseller /> )
     */
    function testParserShouldSucceed()
    {
        $reader = new Reader();
        $reader->XML($this->xmlValidDocument);
        $result = $reader->parse();
        $this->assertNotEmpty($result);
        $result = $result['value'];

        // Validate store name
        $this->assertEquals('Awesome Shop Inc.', $result[0]['value'], "The store name is not valid.");

        // Validate customers
        $customers = $result[1]['value'];
        $this->assertInternalType('array', $customers, "Array of customers expected.");
        $this->assertCount(2, $customers, "There should be 2 customers");

        // Validate customer 1
        $this->assertNotEmpty($customers[0]['attributes']['code'], "Failed to parse code attribute of customer 1.");
        $customer1 = $customers[0]['value'];
        $this->assertEquals('K.M. Johnson', $customer1[0]['value']);
        $this->assertEquals(33, $customer1[1]['value']);
        $this->assertEquals('male', $customer1[2]['value']);
        $books = $customer1[3]['value'];
        $this->assertNotEmpty($books[0]['attributes']['code'], "The book element should have a code attribute.");
        $book1 = $books[0]['value'];
        $this->assertEquals('Boring story oversees', $book1[0]['value'], "Failed to parse the book title.");
        $this->assertEquals(1988, $book1[1]['value'], "Failed to parse the published year.");
        $this->assertEquals('roman', $book1[2]['value'], "Failed to parse the books genre.");
        $author = $book1[3]['value'];
        $this->assertEquals('M.R. Lame', $author[0]['value'], "Failed to parse the authors name.");
        $this->assertEquals(22, $author[1]['value'], "Failed to parse the authors age.");
        $this->assertEquals('male', $author[2]['value'], "Failed to parse the authors gender.");
        $publisher = $book1[4]['value'];
        $this->assertEquals('C.A. Wulff', $publisher[0]['value'], "Failed to parse the authors name.");
        $this->assertEquals(44, $publisher[1]['value'], "Failed to parse the authors age.");
        $this->assertEquals('female', $publisher[2]['value'], "Failed to parse the authors gender.");

        // Validate customer 2
        $this->assertNotEmpty($customers[1]['attributes']['code'], "Failed to parse code attribute of customer 2.");
        $customer2 = $customers[1]['value'];
        $this->assertEquals('L.O. Lolsson', $customer2[0]['value']);
        $this->assertEquals(42, $customer2[1]['value']);
        $this->assertEquals('female', $customer2[2]['value']);
        $movies = $customer2[3]['value'];
        $movie1 = $movies[0]['value'];
        $this->assertEquals('Robin Hood and the wrath of the seven midgets.', $movie1[0]['value'], "Failed to parse the movie title.");
        $this->assertEquals(1999, $movie1[1]['value'], "Failed to parse the movie year.");
        $this->assertEquals('action', $movie1[2]['value'], "Failed to parse the movie genre.");
        $actors = $movie1[3]['value'];
        $actor1 = $actors[0]['value'];
        $this->assertNotEmpty($actors[0]['attributes']['code'], "Failed to parse the actors code attribute.");
        $this->assertEquals('O.K. Dude', $actor1[0]['value'], "Failed to parse the actors name.");
        $this->assertEquals(22, $actor1[1]['value'], "Failed to parse the actors age");
        $this->assertEquals('male', $actor1[2]['value'], "Failed to parse the actors gender.");
        $this->assertArrayHasKey(4, $movie1, "Failed to parse the bestseller element. Note that this element has no contents and no attributes.");
        $this->assertEquals("{http://sabre-test-set.com/Movie}bestseller", $movie1[4]['name'], "Failed to parse the bestseller element. Note that this element has no contents and no attributes.");
    }
    
    /**
     * Test if the schema validation works. When enabled each node is validated on the fly.
     * By calling the parse method the whole tree processed and thus we should get an exception.
     */
    function testParserShouldFailSchemaValidation()
    {
        $reader = new Reader();
        $reader->XML($this->xmlWithInvalidNodes);
        $this->assertTrue($reader->setSchema(__DIR__ . '/../../schema/Store.xsd'));
        $failed = false;
        try{
            $reader->parse();
        } catch (LibXMLException $e) {
            $failed = true;
        }
        $this->assertTrue($failed, 'The parser should have failed. The element "name3" is invalid.');
    }

    /**
     * Ensure that the validation works for valid documents
     */
    function testParserShouldPassSchemaValidation()
    {
        $reader = new Reader();
        $reader->XML($this->xmlValidDocument);
        $this->assertTrue($reader->setSchema(__DIR__ . '/../../schema/Store.xsd'));
        $failed = false;
        try{
            $reader->parse();
        } catch (LibXMLException $e) {
            echo $e->getMessage() . PHP_EOL;
            $failed = true;
        }
        $this->assertFalse($failed, 'The parser should not have failed. The given XML is valid.');
    }

    /**
     * Test if the parser can handle missing start tags.
     */
    function testParserShouldHandleMissingStartTag()
    {
        // Not supported on HHVM. Not registering the tick function will lead to an eternal loop on HHVM if the test fails.
        // But at least the test will run as long as they succeed. That's better than nothing or an exception.
        if (function_exists('register_tick_function')) {
            // Register tick function
            register_tick_function(function() {
                throw new \LogicException("Test failed. The reader seems to be trapped in a eternal loop. Failed to recognize the missing start tag...");
            });
        }


        // Parse elements, use tick counter to break out of eternal loop and throw an exception
        declare (ticks = 10000000); // Don't use low values, apparently phpunit is also using ticks and will collide with low values)
        $reader = new Reader();
        $reader->XML($this->xmlMissingStartNode);
        $reader->setSchema(__DIR__ . '/../../schema/Store.xsd');
        $trappedInEternalLoop = null;
        try{
            $reader->parse();
        } catch (ParseException $e) {
            $trappedInEternalLoop = false;
        } catch (\LogicException $e) {
            $trappedInEternalLoop = true;
        }

        $this->assertNotNull($trappedInEternalLoop, "That's not good. The parser should have failed!");
        $this->assertFalse($trappedInEternalLoop, "Eternal loop detected!");
    }

    /**
     * Test if the parser can handle missing end tags
     */
    function testParserShouldHandleMissingEndTag()
    {
        // Not supported on HHVM. Not registering the tick function will lead to an eternal loop on HHVM if the test fails.
        // But at least the test will run as long as they succeed. That's better than nothing or an exception.
        if (function_exists('register_tick_function')) {
            // Register tick function
            register_tick_function(function() {
                throw new \LogicException("Test failed. The reader seems to be trapped in a eternal loop. Failed to recognize the missing end tag...");
            });
        }

        // Parse elements, use tick counter to break out of eternal loop and throw an exception
        declare (ticks = 10000000); // Don't use low values, apparently phpunit is also using ticks and will collide with low values)
        $reader = new Reader();
        $reader->XML($this->xmlMissingEndNode);
        $reader->setSchema(__DIR__ . '/../../schema/Store.xsd');
        $trappedInEternalLoop = null;
        try{
            $reader->parse();
        } catch (ParseException $e) {
            $trappedInEternalLoop = false;
        } catch (\LogicException $e) {
            $trappedInEternalLoop = true;
        }
        $this->assertNotNull($trappedInEternalLoop, "That's not good. The parser should have failed!");
        $this->assertFalse($trappedInEternalLoop, "Eternal loop detected!");
    }

    /**
     * Test if the parser can handle malformed start tags
     */
    function testParserShouldHandleMalformedStartTag()
    {
        // Not supported on HHVM. Not registering the tick function will lead to an eternal loop on HHVM if the test fails.
        // But at least the test will run as long as they succeed. That's better than nothing or an exception.
        if (function_exists('register_tick_function')) {
            // Register tick function
            register_tick_function(function() {
                throw new \LogicException("Test failed. The reader seems to be trapped in a eternal loop. Failed to recognize the malformed start tag...");
            });
        }

        // Parse elements, use tick counter to break out of eternal loop and throw an exception
        declare (ticks = 10000000); // Don't use low values, apparently phpunit is also using ticks and will collide with low values)
        $reader = new Reader();
        $reader->XML($this->xmlMalformedStartTag);
        $reader->setSchema(__DIR__ . '/../../schema/Store.xsd');
        $trappedInEternalLoop = null;
        try{
            $reader->parse();
        } catch (ParseException $e) {
            $trappedInEternalLoop = false;
        } catch (\LogicException $e) {
            $trappedInEternalLoop = true;
        }
        $this->assertNotNull($trappedInEternalLoop, "That's not good. The parser should have failed!");
        $this->assertFalse($trappedInEternalLoop, "Eternal loop detected!");
    }

    /**
     * Test if the parser can handle malformed end tags
     */
    function testParserShouldHandleMalformedEndTag()
    {
        // Not supported on HHVM. Not registering the tick function will lead to an eternal loop on HHVM if the test fails.
        // But at least the test will run as long as they succeed. That's better than nothing or an exception.
        if (function_exists('register_tick_function')) {
            // Register tick function
            register_tick_function(function() {
                throw new \LogicException("Test failed. The reader seems to be trapped in a eternal loop. Failed to recognize the malformed end tag...");
            });
        }

        // Parse elements, use tick counter to break out of eternal loop and throw an exception
        declare (ticks = 10000000); // Don't use low values, apparently phpunit is also using ticks and will collide with low values)
        $reader = new Reader();
        $reader->XML($this->xmlMalformedEndTag);
        $reader->setSchema(__DIR__ . '/../../schema/Store.xsd');
        $trappedInEternalLoop = null;
        try{
            $reader->parse();
        } catch (ParseException $e) {
            $trappedInEternalLoop = false;
        } catch (\LogicException $e) {
            $trappedInEternalLoop = true;
        }
        $this->assertNotNull($trappedInEternalLoop, "That's not good. The parser should have failed!");
        $this->assertFalse($trappedInEternalLoop, "Eternal loop detected!");
    }

    /**
     * Test if the parser can handle malformed end tags
     */
    function testParserShouldHandleMalformedQuotes()
    {
        // Not supported on HHVM. Not registering the tick function will lead to an eternal loop on HHVM if the test fails.
        // But at least the test will run as long as they succeed. That's better than nothing or an exception.
        if (function_exists('register_tick_function')) {
            // Register tick function
            register_tick_function(function() {
                throw new \LogicException("Test failed. The reader seems to be trapped in a eternal loop. Failed to recognize the malformed quotes...");
            });
        }

        // Parse elements, use tick counter to break out of eternal loop and throw an exception
        declare (ticks = 10000000); // Don't use low values, apparently phpunit is also using ticks and will collide with low values)
        $reader = new Reader();
        $reader->XML($this->xmlMalformedQuotes);
        $reader->setSchema(__DIR__ . '/../../schema/Store.xsd');
        $trappedInEternalLoop = null;
        try{
            $reader->parse();
        } catch (ParseException $e) {
            $trappedInEternalLoop = false;
        } catch (\LogicException $e) {
            $trappedInEternalLoop = true;
        }
        $this->assertNotNull($trappedInEternalLoop, "That's not good. The parser should have failed!");
        $this->assertFalse($trappedInEternalLoop, "Eternal loop detected!");
    }


    private $xmlMissingStartNode = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="dKje98">
            <p:name>K.M. Johnson</p:name>
            <p:age>33</p:age>
            <p:gender>male</p:gender>
            <p:books>
                <b:book code="9488884889-33">
                    <b:title>Boring story oversees</b:title>
                    <b:year>1988</b:year>
                    <b:genre>roman</b:genre>
                    <b:author code="doj883jAA">
                        <p:name>M.R. Lame</p:name>
                        <p:gender>male</p:gender>
                    </b:author>
                        <p:name>C.A. Wulff</p:name>
                        <p:age>44</p:age>
                        <p:gender>female</p:gender>
                    </b:publisher>
                </b:book>
            </p:books>
        </p:customer>
        <p:customer code="kdKK9o2L">
            <p:name>L.O. Lolsson</p:name>
            <p:age>42</p:age>
            <p:gender>female</p:gender>
            <p:movies>
                <m:movie>
                    <m:title>Robin Hood and the wrath of the seven midgets.</m:title>
                    <m:year>1999</m:year>
                    <m:genre>action</m:genre>
                    <m:actors>
                        <p:actor code="3okJJs0si">
                            <p:name>O.K. Dude</p:name>
                            <p:age>22</p:age>
                            <p:gender>male</p:gender>
                        </p:actor>
                    </m:actors>
                </m:movie>
            </p:movies>
        </p:customer>
    </s:customers>
</s:store>
XML;

    private $xmlMissingEndNode = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="dKje98">
            <p:name>K.M. Johnson</p:name>
            <p:age>33</p:age>
            <p:gender>male</p:gender>
            <p:books>
                <b:book code="9488884889-33">
                    <b:title>Boring story oversees</b:title>
                    <b:year>1988</b:year>
                    <b:genre>roman</b:genre>
                    <b:author code="doj883jAA">
                        <p:name>M.R. Lame</p:name>
                        <p:gender>male</p:gender>
                    </b:author>
                    <b:publisher code="ldsKDjhn3">
                        <p:name>C.A. Wulff</p:name>
                        <p:age>44</p:age>
                        <p:gender>female</p:gender>
                </b:book>
            </p:books>
        </p:customer>
        <p:customer code="kdKK9o2L">
            <p:name>L.O. Lolsson</p:name>
            <p:age>42</p:age>
            <p:gender>female</p:gender>
            <p:movies>
                <m:movie>
                    <m:title>Robin Hood and the wrath of the seven midgets.</m:title>
                    <m:year>1999</m:year>
                    <m:genre>action</m:genre>
                    <m:actors>
                        <p:actor code="3okJJs0si">
                            <p:name>O.K. Dude</p:name>
                            <p:age>22</p:age>
                            <p:gender>male</p:gender>
                        </p:actor>
                    </m:actors>
                </m:movie>
            </p:movies>
        </p:customer>
    </s:customers>
</s:store>
XML;

    private $xmlWithInvalidNodes = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="dKje98">
            <p:name3>K.M. Johnson</p:name3>
            <p:age>33</p:age>
            <p:gender>male</p:gender>
            <p:books>
                <b:book code="9488884889-33">
                    <b:title>Boring story oversees</b:title>
                    <b:year>1988</b:year>
                    <b:genre>roman</b:genre>
                    <b:author code="doj883jAA">
                        <p:name>M.R. Lame</p:name>
                        <p:gender>male</p:gender>
                    </b:author>
                    <b:publisher code="ldsKDjhn3">
                        <p:name>C.A. Wulff</p:name>
                        <p:age>44</p:age>
                        <p:gender>female</p:gender>
                    </b:publisher>
                </b:book>
            </p:books>
        </p:customer>
        <p:customer code="kdKK9o2L">
            <p:name>L.O. Lolsson</p:name>
            <p:age>42</p:age>
            <p:gender>female</p:gender>
            <p:movies>
                <m:movie>
                    <m:title>Robin Hood and the wrath of the seven midgets.</m:title>
                    <m:year>1999</m:year>
                    <m:genre>action</m:genre>
                    <m:actors>
                        <p:actor code="3okJJs0si">
                            <p:name>O.K. Dude</p:name>
                            <p:age>22</p:age>
                            <p:gender>male</p:gender>
                        </p:actor>
                    </m:actors>
                </m:movie>
            </p:movies>
        </p:customer>
    </s:customers>
</s:store>
XML;

    private $xmlValidDocument = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="dKje98">
            <p:name>K.M. Johnson</p:name>
            <p:age>33</p:age>
            <p:gender>male</p:gender>
            <p:books>
                <b:book code="9488884889-33">
                    <b:title>Boring story oversees</b:title>
                    <b:year>1988</b:year>
                    <b:genre>roman</b:genre>
                    <b:author code="doj883jAA">
                        <p:name>M.R. Lame</p:name>
                        <p:age>22</p:age>
                        <p:gender>male</p:gender>
                    </b:author>
                    <b:publisher code="ldsKDjhn3">
                        <p:name>C.A. Wulff</p:name>
                        <p:age>44</p:age>
                        <p:gender>female</p:gender>
                    </b:publisher>
                </b:book>
            </p:books>
        </p:customer>
        <p:customer code="kdKK9o2L">
            <p:name>L.O. Lolsson</p:name>
            <p:age>42</p:age>
            <p:gender>female</p:gender>
            <p:movies>
                <m:movie>
                    <m:title>Robin Hood and the wrath of the seven midgets.</m:title>
                    <m:year>1999</m:year>
                    <m:genre>action</m:genre>
                    <m:actors>
                        <p:actor code="3okJJs0si">
                            <p:name>O.K. Dude</p:name>
                            <p:age>22</p:age>
                            <p:gender>male</p:gender>
                        </p:actor>
                    </m:actors>
                    <m:bestseller />
                </m:movie>
            </p:movies>
        </p:customer>
    </s:customers>
</s:store>
XML;

    private $xmlMalformedStartTag = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="dKje98">
            <p:name>K.M. Johnson</p:name>
            <p:age>33</p:age>
            <p:gend er>male</p:gender>
            <p:books>
                <b:book code="9488884889-33">
                    <b:title>Boring story oversees</b:title>
                    <b:year>1988</b:year>
                    <b:genre>roman</b:genre>
                    <b:author code="doj883jAA">
                        <p:name>M.R. Lame</p:name>
                        <p:age>22</p:age>
                        <p:gender>male</p:gender>
                    </b:author>
                    <b:publisher code="ldsKDjhn3">
                        <p:name>C.A. Wulff</p:name>
                        <p:age>44</p:age>
                        <p:gender>female</p:gender>
                    </b:publisher>
                </b:book>
            </p:books>
        </p:customer>
    </s:customers>
</s:store>
XML;
    private $xmlMalformedEndTag = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="kdKK9o2L">
            <p:name>L.O. Lolsson</p:name>
            <p:age>42</p:age>
            <p:gender>female</p:gender>
            <p:movies>
                <m:movie>
                    <m:title>Robin Hood and the wrath of the seven midgets.</m:title>
                    <m:year>1999</m:ye ar>
                    <m:genre>action</m:genre>
                    <m:actors>
                        <p:actor code="3okJJs0si">
                            <p:name>O.K. Dude</p:name>
                            <p:age>22</p:age>
                            <p:gender>male</p:gender>
                        </p:actor>
                    </m:actors>
                    <m:bestseller />
                </m:movie>
            </p:movies>
        </p:customer>
    </s:customers>
</s:store>
XML;

    private $xmlMalformedQuotes = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<s:store xmlns:s="http://sabre-test-set.com/Store"
         xmlns:p="http://sabre-test-set.com/Person"
         xmlns:b="http://sabre-test-set.com/Book"
         xmlns:m="http://sabre-test-set.com/Movie">
    <s:name>Awesome Shop Inc.</s:name>
    <s:customers>
        <p:customer code="kdKK9o2L">
            <p:name>L.O. Lolsson</p:name>
            <p:age>42</p:age>
            <p:gender>female</p:gender>
            <p:movies>
                <m:movie>
                    <m:title>Robin Hood and the wrath of the seven midgets.</m:title>
                    <m:year>1999</m:year>
                    <m:genre>action</m:genre>
                    <m:actors>
                        <p:actor code="3okJJs0si>
                            <p:name>O.K. Dude</p:name>
                            <p:age>22</p:age>
                            <p:gender>male</p:gender>
                        </p:actor>
                    </m:actors>
                    <m:bestseller />
                </m:movie>
            </p:movies>
        </p:customer>
    </s:customers>
</s:store>
XML;


}
