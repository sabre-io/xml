<?php

namespace Sabre\XML;

/**
 * Utility methods for XML parsing and writing
 *
 * @copyright Copyright (C) 2012-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Util {

    /**
     * Parses a clark-notation string, and returns the namespace and element
     * name components.
     *
     * If the string was invalid, it will throw an InvalidArgumentException.
     *
     * @param string $str
     * @throws InvalidArgumentException
     * @return array
     */
    static function parseClarkNotation($str) {

        if (!preg_match('/^{([^}]*)}(.*)$/',$str,$matches)) {
            throw new \InvalidArgumentException('\'' . $str . '\' is not a valid clark-notation formatted string');
        }

        return [
            $matches[1],
            $matches[2]
        ];

    }


}
