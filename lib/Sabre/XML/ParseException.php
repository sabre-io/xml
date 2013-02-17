<?php

namespace Sabre\XML;

use
    Exception,
    LibXMLError;

/**
 * This exception is thrown when the Readers runs into a parsing error.
 *
 * This exception effectively wraps 1 or more LibXMLError objects.
 * 
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class ParseException extends Exception {

    /**
     * The error list. 
     * 
     * @var LibXMLError[] 
     */
    protected $errors;

    /**
     * Creates the exception.
     *
     * You should pass a list of LibXMLError objects in its constructor.
     * 
     * @param LibXMLError[] $errors
     */
    public function __construct(array $errors, $node = null, Exception $previousException = null) {

        $this->errors = $errors;
        parent::__construct($error[0]->message . ' on line ' . $error[0]->line . ', column ' . $error[0]->column, $code, $previousException);

    }

    /**
     * Returns the LibXML errors
     * 
     * @return void
     */
    public function getErrors() {

        return $this->errors;

    } 

}
