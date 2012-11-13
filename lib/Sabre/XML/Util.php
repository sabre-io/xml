<?php

namespace Sabre\XML;

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

        return array(
            $matches[1],
            $matches[2]
        );

    }


}
