<?php
namespace Lucinda\MVC\STDERR;

/**
 * Enum that contains all available error types associated to a handled exception via following constants:
 * - NONE: handled exception is not an error
 * - SERVER: handled exception was caused by server connection failing or being mishandled
 * - CLIENT: handled exception was caused by caller trying to perform illegal operations on server (eg: trying XSS/CSRF)
 * - SYNTAX: handled exception was thrown automatically after developer performed syntax errors in code (eg: PHP, SQL)
 * - LOGICAL: handled exception was thrown by developer to signal logical errors in application flow
 */
class ErrorType
{
    const NONE = "NONE";  // Exceptions that are not errors
    const SERVER = "SERVER"; // Exceptions thrown when operations with server fail
    const CLIENT = "CLIENT"; // Exceptions thrown when client is performing an illegal operation (eg: asking for a resource that doesn't exist)
    const SYNTAX = "SYNTAX"; // Exceptions thrown automatically when a syntax error occurs
    const LOGICAL = "LOGICAL"; // Exceptions thrown on logical errors in code
}
