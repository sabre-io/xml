<?php

declare(strict_types=1);

namespace Sabre\Xml;

use XMLReader;

/**
 * This is a wrapper around ContextStackTrait to use for unit tests.
 *
 * @license http://sabre.io/license/ Modified BSD License
 */
class ContextStack extends XMLReader
{
    use ContextStackTrait;
}
