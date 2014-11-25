<?php

namespace Sabre\Xml;

class UtilTest extends \PHPUnit_Framework_TestCase {

    function testParseClarkNotation() {

        $this->assertEquals([
            'http://sabredav.org/ns',
            'elem',
        ], Util::parseClarkNotation('{http://sabredav.org/ns}elem'));

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testParseClarkNotationFail() {

        Util::parseClarkNotation('http://sabredav.org/ns}elem');

    }

}
