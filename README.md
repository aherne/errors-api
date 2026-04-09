# Lucinda STDERR MVC

`lucinda/errors-mvc` is a PHP 8.1+ library that turns uncaught exceptions and PHP errors into MVC-style error responses.

It registers a front controller for the STDERR side of an application, reports the failure, selects a configured route and resolver, and writes either an HTTP response or a CLI STDERR response.

## Contents

- [What It Does](#what-it-does)
- [Runtime Flow](#runtime-flow)
- [Install](#install)
- [Quick Start](#quick-start)
- [XML Configuration](#xml-configuration)
- [Extension Points](#extension-points)
- [Core Types](#core-types)
- [Testing](#testing)

## What It Does

The package centers on [`FrontController`](src/FrontController.php), which:

- registers exception, error, and shutdown handlers
- converts non-fatal and fatal PHP errors into `ErrorException` instances
- loads application, route, and resolver metadata from XML
- reports the original exception through an injected reporter service
- exposes a `DisplayErrors` facet to downstream injected services
- builds an HTTP or CLI response from the matched route and resolver
- optionally runs a controller before resolving the final view
- falls back to an injected fatal resolver if the normal error pipeline fails

This package depends on `lucinda/abstract_mvc`, so routes, views, and resolvers follow Lucinda MVC conventions where applicable.

## Runtime Flow

The normal handling path in [`FrontController`](src/FrontController.php) is:

1. `__construct()` stores dependencies and starts listening immediately.
2. `handle(Throwable $exception)` prevents recursive re-entry.
3. The injected `ErrorReporter` receives the exception first.
4. The injected [`DisplayErrors`](src/DisplayErrors.php) facet is registered for dependency injection.
5. [`Application`](src/Application.php) loads XML metadata.
6. [`ValidatedRequest`](src/Service/ValidatedRequest.php) picks the final route and response format.
7. [`Request`](src/Request.php) wraps the matched route plus the handled exception.
8. A response is created:
   - HTTP: uses route `http_status` and resolver `content_type` / `charset`
   - CLI: uses route `exit_code` and writes to `STDERR`
9. If the route defines a controller, it is instantiated through Lucinda MVC dependency injection.
10. If a view is available, the configured view resolver renders the body.
11. The response is executed.

If the pipeline itself throws, `handleFatal()` reports that failure too and uses the injected `FatalErrorResolver` to generate a last-resort body.

## Install

```bash
composer require lucinda/errors-mvc
```

Requirements from [`composer.json`](composer.json):

- PHP `^8.1`
- `ext-simplexml`
- `lucinda/abstract_mvc ^3.0`

## Quick Start

Create the front controller early in your bootstrap:

```php
<?php

use Lucinda\STDERR\FrontController;

$stderr = new FrontController(
    __DIR__."/configuration/root.xml",
    __DIR__,
    $errorReporter,
    $fatalErrorResolver,
    $displayErrors
);
```

Constructor arguments are:

- `string $documentDescriptor`: path to the root XML file
- `string $includePath`: project root used as include path during handling
- `ErrorReporter $reporter`: reports handled failures
- `FatalErrorResolver $emergencyResolver`: renders the emergency fallback body
- `DisplayErrors $displayErrors`: a facet that decides whether application code should expose detailed errors

You can override the configured response format before handling:

```php
$stderr->setDisplayFormat("json");
```

For CLI flows, the final process exit code can be inspected with:

```php
$code = $stderr->getExitCode();
```

## XML Configuration

The current tests use a split root XML file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xml>
<xml>
  <application ref="tests/fixtures/application"/>
  <resolvers ref="tests/fixtures/resolvers"/>
  <routes ref="tests/fixtures/routes"/>
</xml>
```

### `application`

Example from [`tests/fixtures/application.xml`](tests/fixtures/application.xml):

```xml
<application
  default_route="default"
  default_format="txt"
  views_folder="tests/fixtures/views"
  views_extension="txt"
  version="1.0.0"
/>
```

Important behavior enforced by [`ValidatedRequest`](src/Service/ValidatedRequest.php):

- `default_route` must match a real route, otherwise handling fails
- `default_format` should match a real resolver, or the package falls back to it when an override format is unknown

### `resolvers`

Example from [`tests/fixtures/resolvers.xml`](tests/fixtures/resolvers.xml):

```xml
<resolvers>
  <resolver
    format="txt"
    content_type="text/plain"
    charset="UTF-8"
    class="App\View\PlainTextResolver"
  />
  <resolver
    format="json"
    content_type="application/json"
    charset="UTF-8"
    class="App\View\JsonResolver"
  />
</resolvers>
```

Current resolver requirements:

- `format` identifies the response format
- `class` is the Lucinda MVC view resolver to instantiate
- `content_type` is mandatory in this package
- `charset` is optional

[`ContentTypeDetector`](src/Service/ContentTypeDetector.php) combines `content_type` and `charset` into the outgoing `Content-Type` header for HTTP responses.

### `routes`

Example from [`tests/fixtures/routes.xml`](tests/fixtures/routes.xml):

```xml
<routes>
  <route
    id="default"
    view="default"
    http_status="500"
    error_type="LOGICAL"
    exit_code="1"
  />
  <route
    id="App\Exception\NotFoundException"
    view="not-found"
    http_status="404"
    error_type="CLIENT"
    exit_code="4"
  />
</routes>
```

Current route behavior:

- `id` should be the handled exception class name, or your fallback route id
- `controller` is optional
- `view` is the MVC view name used by Lucinda MVC view detection
- `http_status` is optional but validated against `Lucinda\MVC\Response\HttpStatus`
- `error_type` is optional but validated against [`ErrorType`](src/ErrorType.php)
- `exit_code` defaults to `1` and is used for CLI responses

Route matching is exact by exception class name. If no direct route exists, the configured default route is used.

## Extension Points

### `ErrorReporter`

[`ErrorReporter`](src/ErrorReporter.php) is injected into `FrontController` and is called for both normal and fatal handling:

```php
interface ErrorReporter
{
    public function report(\Throwable $error, ?\Throwable $previous = null);
}
```

Use this to forward failures to logs, observability tooling, or external reporting services.

### `FatalErrorResolver`

[`FatalErrorResolver`](src/FatalErrorResolver.php) is the emergency renderer used when the standard MVC path fails:

```php
interface FatalErrorResolver
{
    public function resolve(\Throwable $exception, ?\Throwable $previous = null): string;
}
```

It must return a final response body string.

### `DisplayErrors`

[`DisplayErrors`](src/DisplayErrors.php) is also injected into `FrontController`:

```php
interface DisplayErrors extends \Lucinda\MVC\Facet
{
    public function shouldDisplayErrors(): bool;
}
```

`FrontController` adds this object to the Lucinda MVC facet registry before route handling, so controllers, resolvers, and other injected services can make a centralized decision about whether detailed errors should be shown.

### Route Controllers

Routes can still point to a `controller` class, but this package no longer provides a dedicated STDERR controller base class.

Instead, route controllers are resolved through Lucinda MVC dependency injection. In practice, that means the class can depend on registered facets such as:

- [`Application`](src/Application.php)
- [`Request`](src/Request.php)
- `Lucinda\MVC\Response`
- [`DisplayErrors`](src/DisplayErrors.php)

If your controller is `ViewAware`, `FrontController` uses the returned view. Otherwise it just calls `run()` for side effects on the response.

### View Resolvers

Resolver classes are regular Lucinda MVC view resolvers. The test fixture [`tests/Support/PlainTextViewResolver.php`](tests/Support/PlainTextViewResolver.php) shows the expected shape:

```php
use Lucinda\MVC\Response\View;
use Lucinda\MVC\Response\ViewResolver;

class PlainTextViewResolver implements ViewResolver
{
    public function resolve(View $view): string
    {
        return "...";
    }
}
```

## Core Types

- [`Application`](src/Application.php): loads routes and resolvers from XML
- [`Request`](src/Request.php): wraps the matched route and original exception
- [`PHPException`](src/PHPException.php): bridges PHP errors into the registered `ErrorHandler`
- [`ErrorType`](src/ErrorType.php): route-level classification enum with `NONE`, `SERVER`, `CLIENT`, `SYNTAX`, and `LOGICAL`
- [`RouteInfo`](src/XmlTags/RouteInfo.php): extends Lucinda MVC route metadata with `http_status`, `error_type`, and `exit_code`
- [`ResolverInfo`](src/XmlTags/ResolverInfo.php): extends resolver metadata with `content_type` and `charset`

## Testing

The repository includes focused unit coverage for:

- front controller handling and emergency fallback
- request and application metadata
- resolver and route XML parsing
- content type and request validation services

Run the suite with:

```bash
php test.php
```
