<?php

namespace Lucinda\STDERR;

/**
 * Enum that contains all available error types associated to a handled exception via following constants:
 * - NONE: handled exception is not an error
 * - SERVER: handled exception was caused by server connection failing or being mishandled
 * - CLIENT: handled exception was caused by caller trying to perform illegal operations on server (eg: trying XSS/CSRF)
 * - SYNTAX: handled exception was thrown automatically after developer performed syntax errors in code (eg: PHP, SQL)
 * - LOGICAL: handled exception was thrown by developer to signal logical errors in application flow
 */
enum ErrorType: string
{
    case NONE = "NONE";  // Exceptions that are not errors
    case SERVER = "SERVER"; // Exceptions thrown when operations with server fail
    case CLIENT = "CLIENT"; // Exceptions thrown when client is at fault (eg: asking for a resource that doesn't exist)
    case SYNTAX = "SYNTAX"; // Exceptions thrown automatically when a syntax error occurs
    case LOGICAL = "LOGICAL"; // Exceptions thrown on logical errors in code
}
