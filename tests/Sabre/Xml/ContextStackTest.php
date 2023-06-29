<?php

declare(strict_types=1);

namespace Sabre\Xml;

use PHPUnit\Framework\TestCase;

/**
 * Test for the ContextStackTrait.
 *
 * @copyright Copyright (C) 2009-2015 fruux GmbH (https://fruux.com/).
 * @author Evert Pot (http://evertpot.com/)
 * @license http://sabre.io/license/ Modified BSD License
 */
class ContextStackTest extends TestCase
{
    private ContextStack $stack;

    public function setUp(): void
    {
        $this->stack = new ContextStack();
    }

    public function testPushAndPull(): void
    {
        $this->stack->contextUri = '/foo/bar';
        // Use a class that exists so that phpstan will be happy that the value of the elementMap
        // element is a class-string. That is a type that is expected for elementMap in ContextStackTrait.
        $testClass = 'Sabre\Xml\ContextStack';
        if (class_exists($testClass)) {
            $this->stack->elementMap['{DAV:}foo'] = $testClass;
        } else {
            self:self::fail("$testClass not found");
        }
        $this->stack->namespaceMap['DAV:'] = 'd';

        $this->stack->pushContext();

        self::assertEquals('/foo/bar', $this->stack->contextUri);
        self::assertEquals($testClass, $this->stack->elementMap['{DAV:}foo']);
        self::assertEquals('d', $this->stack->namespaceMap['DAV:']);

        $this->stack->contextUri = '/gir/zim';
        $this->stack->elementMap['{DAV:}foo'] = 'stdClass';
        $this->stack->namespaceMap['DAV:'] = 'dd';

        $this->stack->popContext();

        self::assertEquals('/foo/bar', $this->stack->contextUri);
        self::assertEquals($testClass, $this->stack->elementMap['{DAV:}foo']);
        self::assertEquals('d', $this->stack->namespaceMap['DAV:']);
    }
}
