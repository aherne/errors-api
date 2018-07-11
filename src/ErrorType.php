<?php
namespace Lucinda\MVC\STDERR;

/**
 * C++ style enum of possible error types
 */
class ErrorType
{
    const NONE = 0; // exceptions that are not errors
    const SERVER = 1; // exceptions thrown when operations with server fail
    const CLIENT = 2; // exceptions thrown by programmer when client is at fault.
    const SYNTAX = 3; // exceptions thrown automatically when a programming error occurs (eg: PHP error)
    const LOGICAL = 4; // exceptions thrown by programmer to signal a faulty situation
}