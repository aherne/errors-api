<?php
namespace Lucinda\MVC\STDERR;

/**
 * C++ style enum of possible error types
 */
class ErrorType
{
    /**
     * @var integer Exceptions that are not errors
     */
    const NONE = 1;
    /**
     * @var integer Exceptions thrown when operations with server fail
     */
    const SERVER = 2;
    /**
     * @var integer Exceptions thrown by programmer when client is at fault (eg: asking for a resource that doesn't exist)
     */
    const CLIENT = 3;
    /**
     * @var integer Exceptions thrown automatically when a syntax error occurs
     */
    const SYNTAX = 4;
    /**
     * @var integer Exceptions thrown on logical errors in code
     */
    const LOGICAL = 5; 
}