# STDERR MVC API

Table of contents:

- [About](#about)
- [Configuration](#configuration)
- [Binding Points](#binding-points)
- [Execution](#execution)
    - [Initialization](#initialization)
    - [Handling](#handling)
- [Installation](#installation)
- [Unit Tests](#unit-tests)
- [Reference Guide](#reference-guide)
- [Specifications](#specifications)
    - [How Is Response Format Detected](#how-is-response-format-detected)
    - [How Are View Resolvers Located](#how-are-view-resolvers-located)
    - [How Is Route Detected](#how-is-route-detected)
    - [How Are Controllers Located](#how-are-controllers-located)
    - [How Are Reporters Located](#how-are-reporters-located)
    - [How Are Views Located](#how-are-views-located)

## About

This API is a **skeleton** (requires [binding](#binding-points) by developers) created to efficiently handle errors or uncaught exceptions in a web application using a dialect of MVC paradigm where:

- *models* are reusable logic to report [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances handled or holders of data to be sent to views
- *views* are the response to send back to caller after an error/exception was handled
- *controllers* are binding [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances to models in order to configure views (typically send data to it)

![diagram](https://www.lucinda-framework.com/stderr-mvc-api.svg)

Just as MVC API for STDOUT handles http requests into responses, so does this API for STDERR handle errors or uncaught exceptions that happen during previous handling process. It does so in a manner that is both efficient and modular, without being bundled to any framework:

- first, MVC API for STDERR (this API) registers itself as sole handler of errors and uncaught exceptions encapsulated by [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances, then passively waits for latter to be triggered
- then, MVC API for STDOUT (your framework of choice) starts handling user requests into responses. Has any error or uncaught exception occurred in the process?
    - *yes*: MVC API for STDERR gets automatically awaken then starts handling respective [\Throwable](https://www.php.net/manual/en/class.throwable.php) into response
    - *no*: response is returned back to caller

Furthermore, this whole process is done in a manner that is made flexible through a combination of:

- **[configuration](#configuration)**: setting up an XML file where this API is configured
- **[binding points](#binding-points)**: binding user-defined components defined in XML/code to API prototypes in order to gain necessary abilities
- **[initialization](#initialization)**: instancing [FrontController](https://github.com/aherne/errors-api/blob/master/src/FrontController.php) to register itself as sole [\Throwable](https://www.php.net/manual/en/class.throwable.php) handler
- **[handling](#handling)**: once any error or uncaught exception has occurred in STDOUT phase, method *handle* of above is called automatically kick-starting [\Throwable](https://www.php.net/manual/en/class.throwable.php)-response process

API is fully PSR-4 compliant, only requiring [Abstract MVC API](https://github.com/aherne/mvc) for basic MVC logic, PHP7.1+ interpreter and SimpleXML extension. To quickly see how it works, check:

- **[installation](#installation)**: describes how to install API on your computer, in light of steps above
- **[unit tests](#unit-tests)**: API has 100% Unit Test coverage, using [UnitTest API](https://github.com/aherne/unit-testing) instead of PHPUnit for greater flexibility
- **[example](https://github.com/aherne/errors-api/blob/master/tests/FrontController.php)**: shows a deep example of API functionality based on [FrontController](https://github.com/aherne/errors-api/blob/master/src/FrontController.php) unit test
- **[reference guide](#reference-guide)**: describes all API classes, methods and fields relevant to developers

All classes inside belong to **Lucinda\STDERR** namespace!

## Configuration

To configure this API you must have a XML with following tags inside:

- **[application](#application)**: (mandatory) configures handling on general application basis
- **[display_errors](#display_errors)**: (optional) configures whether or not errors should be displayed
- **[reporters](#reporters)**: (optional) configures how your application will report handled error/exception
- **[resolvers](#resolvers)**: (mandatory) configures formats in which your application is able to resolve responses to a handled error/exception
- **[routes](#routes)**: (mandatory) configures error/exception based routing to controllers/views

### Application

Tag documentation is completely covered by inherited Abstract MVC API [specification](https://github.com/aherne/mvc#application)! Since STDIN for this API is made of handled throwables but there is no generic throwable value of *default_route* attribute must be **default**. 

### Display_Errors

Maximal syntax of this tag is:

```xml
<display_errors>
    <{ENVIRONMENT}>{VALUE}</{ENVIRONMENT}>
    ...
</display_errors>
```

Most of tag logic is already covered by Abstract MVC API [specification](https://github.com/aherne/mvc#application). Following extra sub-tags/attributes are defined:

- **display_errors**: (optional) holds whether or not handled error/exception details will be exposed back to callers based on:
    - **{ENVIRONMENT}**: name of development environment (to be replaced with "local", "dev", "live", etc). **{VALUE}** can be:
        - *1*: indicates error/exception details will be displayed to caller
        - *0*: indicates error/exception details won't be displayed to caller

If no **display_errors** is defined or no **{ENVIRONMENT}** subtag is found matching current development environment, value 0 is assumed ([\Throwable](https://www.php.net/manual/en/class.throwable.php) details won't be exposed)!

Tag example:

```xml
<display_errors>
    <local>1</local>
    <live>0</live>
</display_errors>
```

### Reporters

Maximal syntax of this tag is:

```xml
<reporters>
    <{ENVIRONMENT}>
        <reporter class="..." {OPTIONS}/>
        ...
    </{ENVIRONMENT}>
    ...
</reporters>
```

Where:

- **reporters**: (mandatory) holds settings to configure your application for error reporting based on:
    - **{ENVIRONMENT}**: (mandatory) name of development environment (to be replaced with "local", "dev", "live", etc). Holds one or more reporters, each defined by a tag:
        - **reporter**: (mandatory) configures an error/exception reporter based on attributes:
            - *class*: (mandatory) name of user-defined PS-4 autoload compliant class (including namespace) that will report [\Throwable](https://www.php.net/manual/en/class.throwable.php).<br/>Class must be a [Reporter](#abstract-class-reporter) instance!
            - {OPTIONS}: a list of extra attributes necessary to configure respective reporter identified by *class* above            

Tag example:

```xml
<reporters>
    <local>
        <reporter class="Lucinda\Project\Reporters\File" path="errors" format="%d %f %l %m"/>
    </local>
    <live>
        <reporter class="Lucinda\Project\Reporters\SysLog" application="unittest" format="%v %f %l %m"/>
    </live>
</reporters>
```

### Resolvers

Tag documentation is completely covered by inherited Abstract MVC API [specification](https://github.com/aherne/mvc#resolvers)!

### Routes

Maximal syntax of this tag is:

```xml
<routes>
    <route id="..." controller="..." view="..." error_type="..." http_status="..."/>
    ...
</routes>
```

Most of tag logic is already covered by Abstract MVC API [specification](https://github.com/aherne/mvc#application). Following extra observations need to be made:

- *id*: (mandatory) mapped error/exception class name or **default** (matching *default_route* @ [application](#application) tag).<br/>Class must be a [\Throwable](https://www.php.net/manual/en/class.throwable.php) instance!
- *controller*: (optional) name of user-defined PS-4 autoload compliant class (including namespace) that will mitigate requests and responses based on models.<br/>Class must be a [Controller](#abstract-class-controller) instance!
- *error_type*: (mandatory) defines default exception/error originator. Must match one of [ErrorType](https://github.com/aherne/errors-api/blob/master/src/ErrorType.php) enum cases values! Example: "LOGICAL"
- *http_status*: (mandatory) defines default response HTTP status. Must be a valid HTTP status code! Example: "500"

Tag example:

```xml
<routes>
    <route id="default" http_status="500" error_type="LOGICAL" view="500"/>
    <route id="Lucinda\MVC\STDOUT\PathNotFoundException" controller="Lucinda\Project\Controllers\PathNotFound" http_status="404" error_type="CLIENT" view="404"/>
</routes>
```

If handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) matches no route, **default** route is used!

## Binding Points

In order to remain flexible and achieve highest performance, API takes no more assumptions than those absolutely required! It offers developers instead an ability to bind to its prototypes in order to gain certain functionality.

### Declarative Binding

It offers developers an ability to **bind declaratively** to its prototype classes/interfaces via XML:

| XML Attribute @ Tag | Class Prototype | Ability Gained |
| --- | --- | --- |
| [controller @ route](#routes) | [Controller](#abstract-class-controller) | MVC controller for any [\Throwable](https://www.php.net/manual/en/class.throwable.php) |
| [class @ resolver](#resolvers) | [\Lucinda\MVC\ViewResolver](https://github.com/aherne/mvc#Abstract-Class-ViewResolver) | Resolving response in a particular format (eg: html) |
| [class @ reporter](#reporters) | [Reporter](#abstract-class-reporter) | Reporting [\Throwable](https://www.php.net/manual/en/class.throwable.php) to a storage medium |

### Programmatic Binding

It offers developers an ability to **bind programmatically** to its prototypes via [FrontController](#initialization) constructor:

| Class Prototype | Ability Gained |
| --- | --- |
| [ErrorHandler](#interface-errorhandler) | (mandatory) Handler to use if a [\Throwable](https://www.php.net/manual/en/class.throwable.php) while API handles request into response |

## Execution

### Initialization

Now that developers have finished setting up XML that configures the API, they are finally able to initialize it by instantiating [FrontController](https://github.com/aherne/errors-api/blob/master/src/FrontController.php).

As a handler of [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances, above needs to implement [ErrorHandler](#interface-errorhandler). Apart of method *run* required by interface above, [FrontController](https://github.com/aherne/errors-api/blob/master/src/FrontController.php) comes with following public methods, all related to initialization process:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | string $documentDescriptor, string $developmentEnvironment, string $includePath, [ErrorHandler](#interface-errorhandler) $emergencyHandler | void | API registers itself as sole [\Throwable](https://www.php.net/manual/en/class.throwable.php) handler then starts the wait process |
| setDisplayFormat | string $displayFormat | void | Sets future response to use a different display format from that defined by *default_format* @ [application](#application) tag |

Where:

- *$documentDescriptor*: relative location of XML [configuration](#configuration) file. Example: "configuration.xml"
- *$developmentEnvironment*: name of development environment (to be replaced with "local", "dev", "live", etc) to be used in deciding how to report or whether or not to expose handled [\Throwable](https://www.php.net/manual/en/class.throwable.php)
- *$includePath*: absolute location of your project root (necessary because sometimes include paths are lost when errors are thrown). Example: __DIR__
- *$emergencyHandler*: a [ErrorHandler](#interface-errorhandler) instance to be used in handling errors occurring during execution of [FrontController](https://github.com/aherne/errors-api/blob/master/src/FrontController.php)'s *handle* method
- *$displayFormat*: value of display format, matching to a *format* attribute of a *resolver* @ [resolvers](#resolvers) XML tag

Very important to notice that once handlers are registered, API employs Aspect Oriented Programming concepts to listen *asynchronously* for error events then triggering handler automatically. So once API is initialized, you can immediately start your preferred framework that handles http requests to responses!

### Handling

Once a [\Throwable](https://www.php.net/manual/en/class.throwable.php) event has occurred inside STDOUT request-response phase, *handle* method of [FrontController](https://github.com/aherne/errors-api/blob/master/src/FrontController.php) is called. This:

- registers handler presented as constructor argument to capture any error that might occur while handling
- constructs a [Application](#class-application) object based on XML where API is configured and development environment
- detects [Application\Route](#class-route) matching [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled
- constructs a [Request](#class-request) object based on handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) and [Application\Route](#class-application-route) above
- constructs a list of [Reporter](#abstract-class-reporter) instances based on [reporters](#reporters) tag @ XML matching development environment
- if found, for each [Reporter](#abstract-class-reporter) it calls its *run()* method in order to report [\Throwable](https://www.php.net/manual/en/class.throwable.php) to respective medium
- detects [Lucinda\MVC\Application\Format](https://github.com/aherne/mvc#class-format) matching *default_format* attribute [application](#application) tag @ XML or the one manually set via *setDisplayFormat* method
- constructs a [\Lucinda\MVC\Response](https://github.com/aherne/mvc#Class-Response) object based on all objects detected above
- detects [Controller](#abstract-class-controller) matching [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled or **default** if no route matches based on *id* attribute [routes](#routes) tag @ XML
- if found, it executes [Controller](#abstract-class-controller)'s *run* method in order to manipulate response
- if [\Lucinda\MVC\Response](https://github.com/aherne/mvc#Class-Response) doesn't have a body yet
    - constructs a [\Lucinda\MVC\ViewResolver](https://github.com/aherne/mvc#Abstract-Class-ViewResolver) object based on matching [resolver](#resolvers) tag @ XML and objects detected above. This will, for example, convert templates to a full response body.
    - if not found, program exits with error, otherwise it executes [\Lucinda\MVC\ViewResolver](https://github.com/aherne/mvc#Abstract-Class-ViewResolver)'s *run* method in order to convert view to a response body
- calls *commit* method of [\Lucinda\MVC\Response](https://github.com/aherne/mvc#Class-Response) to send back response to caller

All components that are in developers' responsibility ([Controller](#abstract-class-controller), [\Lucinda\MVC\ViewResolver](https://github.com/aherne/mvc#Abstract-Class-ViewResolver), [Reporter](#abstract-class-reporter)) implement [\Lucinda\MVC\Runnable](https://github.com/aherne/mvc#interface-runnable) interface.

## Installation

First choose a folder, then write this command there using console:

```console
composer require lucinda/errors-api
```

Then create a *configuration.xml* file holding configuration settings (see [configuration](#configuration) above) and a *index.php* file (see [initialization](#initialization) above) in project root with following code:

```php
// detects current development environment from ENVIRONMENT environment variable (eg: set in .htaccess via "SetEnv ENVIRONMENT local");
define("ENVIRONMENT", getenv("ENVIRONMENT"));

// starts API as error handler to listen for errors/exceptions thrown in STDOUT phase below
new FrontController("configuration.xml", getenv("ENVIRONMENT"), __DIR__, new EmergencyHandler());

// runs preferred STDOUT framework (eg: STDOUT MVC API) to handle requests into responses
```

Example of emergency handler:

```php
class EmergencyHandler implements \ErrorHandler
{
    public function handle($exception): void
    {
        var_dump($exception);
        die();
    }
}
```

## Unit Tests

For tests and examples, check following files/folders in API sources:

- [test.php](https://github.com/aherne/errors-api/blob/master/test.php): runs unit tests in console
- [unit-tests.xml](https://github.com/aherne/errors-api/blob/master/unit-tests.xml): sets up unit tests and mocks "loggers" tag
- [tests](https://github.com/aherne/errors-api/blob/master/tests): unit tests for classes from [src](https://github.com/aherne/errors-api/blob/master/src) folder

## Reference Guide

These classes are fully implemented by API:

- [Application](#class-application): reads [configuration](#configuration) XML file and encapsulates information inside
    - [Application\Route](#class-application-route): encapsulates [route](#routes) XML tag matching [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled
- [Request](#class-request): encapsulates handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) and [Application\Route](#class-application-route)

These abstract classes require to be extended by developers in order to gain an ability:

- [Reporter](#abstract-class-reporter): encapsulates [\Throwable](https://www.php.net/manual/en/class.throwable.php) reporting
- [Controller](#abstract-class-controller): encapsulates binding [Request](#class-request) to [\Lucinda\MVC\Response](https://github.com/aherne/mvc#class-response) based on [\Throwable](https://www.php.net/manual/en/class.throwable.php)

### Class Application

Class [Application](https://github.com/aherne/errors-api/blob/master/src/Application.php) extends [Lucinda\MVC\Application](https://github.com/aherne/mvc#Class-Application) and adds one method relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getDisplayErrors | void | bool | Gets whether or not errors should be displayed in current development environment |

### Class Application Route

Class [Application\Route](https://github.com/aherne/errors-api/blob/master/src/Application/Route.php) extends [Lucinda\MVC\Application\Route](https://github.com/aherne/mvc#Class-Application-Route) and adds following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getErrorType | void | [ErrorType](https://github.com/aherne/errors-api/blob/master/src/ErrorType.php) | Gets error type based on *error_type* attribute of matching [route](#routes) XML tag |
| getHttpStatus | void |  [\\Lucinda\\MVC\\Response\\HttpStatus](https://github.com/aherne/mvc/blob/v2.0/src/Response/HttpStatus.php) | Gets response http status code based on *http_status* attribute of matching [route](#routes) XML tag |

### Class Request

Class [Request](https://github.com/aherne/errors-api/blob/master/src/Request.php) encapsulates handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) and matching [Application\Route](#class-application-route). It defines following public methods relevant to developers:


| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getException | void | [\Throwable](https://www.php.net/manual/en/class.throwable.php) | Gets throwable that is being handled |
| getRoute | void | [Application\Route](#class-application-route) | Gets route information detected from XML based on throwable |

## Interface ErrorHandler

Interface [ErrorHandler](https://github.com/aherne/errors-api/blob/master/src/ErrorHandler.php) contains blueprint for handling [\Throwable](https://www.php.net/manual/en/class.throwable.php) via method:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| handle | [\Throwable](https://www.php.net/manual/en/class.throwable.php) | void | Handles error by delegating to reporting and rendering |

Usage example:

https://github.com/aherne/lucinda-framework/blob/master/src/EmergencyHandler.php

### Abstract Class Reporter

Abstract class [Reporter](https://github.com/aherne/errors-api/blob/master/src/Reporter.php) implements [\Lucinda\MVC\Runnable](https://github.com/aherne/mvc#interface-runnable) and encapsulates a single [\Throwable](https://www.php.net/manual/en/class.throwable.php) reporter. It defines following public method relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| run | void | void | Inherited prototype to be implemented by developers to report [\Throwable](https://www.php.net/manual/en/class.throwable.php) based on information saved by constructor |

Developers need to implement *run* method for each reporter, where they are able to access following protected fields injected by API via constructor:

| Field | Type | Description |
| --- | --- | --- |
| $request | [Request](#class-request) | Gets request information encapsulating handled throwable and matching route information detected from XML. |
| $xml | [\SimpleXMLElement](https://www.php.net/manual/en/class.simplexmlelement.php) | Gets a pointer to matching [reporter](#reporters) XML tag, to read attributes from. |

Usage example:

https://github.com/aherne/lucinda-framework-engine/blob/master/src/AbstractReporter.php
https://github.com/aherne/lucinda-framework/blob/master/src/Reporters/File.php

For more info how reporters are detected, check [How Are Reporters Located](#how-are-reporters-located) section below!

### Abstract Class Controller

Abstract class [Controller](https://github.com/aherne/errors-api/blob/master/src/Controller.php) implements [\Lucinda\MVC\Runnable](https://github.com/aherne/mvc#interface-runnable)) to set up response (views in particular) based on information detected beforehand. It defines following public method relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| run | void | void | Inherited prototype to be implemented by developers to set up response based on information saved by constructor |

Developers need to implement *run* method for each controller, where they are able to access following protected fields injected by API via constructor:

| Field | Type | Description |
| --- | --- | --- |
| $application | [Application](#class-application) | Gets application information detected from XML. |
| $request | [Request](#class-request) | Gets request information encapsulating handled throwable and matching route information detected from XML. |
| $response | [\Lucinda\MVC\Response](https://github.com/aherne/mvc#class-response) | Gets access to object based on which response can be manipulated. |

Usage example:

https://github.com/aherne/lucinda-framework/blob/master/src/Controllers/SecurityPacket.php

For more info how controllers are detected, check [How Are Controllers Located](#how-are-controllers-located) section below!

## Specifications

Since this API works on top of [Abstract MVC API](https://github.com/aherne/mvc) specifications it follows their requirements and adds extra ones as well:

- [How Is Response Format Detected](#how-is-response-format-detected)
- [How Are View Resolvers Located](#how-are-view-resolvers-located)
- [How Is Route Detected](#how-is-route-detected)
- [How Are Controllers Located](#how-are-controllers-located)
- [How Are Reporters Located](#how-are-reporters-located)
- [How Are Views Located](#how-are-views-located)

### How Is Response Format Detected

This follows parent API [specifications](https://github.com/aherne/mvc#how-is-response-format-detected) only that routes are detected based on [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled. One difference is that detected value can be overridden using *setDisplayFormat* method (see [Initialization](#initialization)).

### How Are View Resolvers Located

This follows parent API [specifications](https://github.com/aherne/mvc#how-are-view-resolvers-located) in its entirety.

### How Is Route detected

This follows parent API [specifications](https://github.com/aherne/mvc#how-are-view-resolvers-located) only that routes are detected based on [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled. Let's take this XML for example:

```xml
<application default_route="default" ...>
	...
</application>
<routes>
    <route id="default" .../>
    <route id="\Bar\Exception" .../>
    ...
</routes>
```

There will be following situations for above:

| If Throwable Is | Then Route ID Detected | Description |
| --- | --- | --- |
| \Foo\Exception | default | Because no matching route was found, that identified by *default_route* is used |
| \Bar\Exception | \Bar\Exception | Because throwable is matched to a route, specific route is used |

### How Are Controllers Located

This follows parent API [specifications](https://github.com/aherne/mvc#how-are-controllers-located) only that class defined as *controller* attribute in [route](#routes) tag must extend [Controller](#abstract-class-controller).

### How Are Reporters Located

To better understand how *class* attributes @ [reporter](#reporters) tags matching *development environment*, let's take this XML for example:

```xml
<reporters>
    <ENVIRONMENT1>
        <reporter class="Lucinda\Project\Reporters\File" .../>
        <reporter class="Lucinda\Project\Reporters\SysLog" .../>
    </ENVIRONMENT1>
    <ENVIRONMENT2>
        <reporter class="Lucinda\Project\Reporters\SysLog" .../>
    </ENVIRONMENT2>
    ...
</reporters>
```

In that case if "psr-4" attribute in composer.json associates "Lucinda\\Project\\" with "src/" folder then:

| ENVIRONMENT | Files Loaded | Classes Instanced |
| --- | --- | --- |
| ENVIRONMENT1 | src/Reporters/File.php<br/>src/Reporters/SysLog.php | Lucinda\Project\Reporters\File<br/>Lucinda\Project\Reporters\SysLog |
| ENVIRONMENT2 | src/Reporters/SysLog.php | Lucinda\Project\Reporters\SysLog |

All classes referenced above must be instance of [Reporter](#abstract-class-reporter)!

### How Are Views Located

This follows parent API [specifications](https://github.com/aherne/mvc#how-are-views-located) in its entirety. Extension is yet to be decided, since it depends on type of view resolved!
