<?php
namespace Lucinda\MVC\STDERR;

/**
 * C++ style enum of possible error types
 */
class ErrorType
{
    const NONE = "NONE";  // Exceptions that are not errors
    const SERVER = "SERVER"; // Exceptions thrown when operations with server fail
    const CLIENT = "CLIENT"; // Exceptions thrown when client is performing an illegal operation (eg: asking for a resource that doesn't exist)
    const SYNTAX = "SYNTAX"; // Exceptions thrown automatically when a syntax error occurs
    const LOGICAL = "LOGICAL"; // Exceptions thrown on logical errors in code
}