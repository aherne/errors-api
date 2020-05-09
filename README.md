# STDERR MVC API

*Documentation below refers to latest API version, available in branch [v2.0.0](https://github.com/aherne/errors-api/tree/v2.0.0)! For older version in master branch, please check [Lucinda Framework](https://www.lucinda-framework.com/stderr-mvc).*

This API was created to efficiently handle errors or uncaught exceptions in a web application using a dialect of MVC paradigm where:

- *models* are reusable logic to report [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances handled or holders of data to be sent to views
- *views* are the response to send back to caller after an error/exception was handled
- *controllers* are binding [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances to models in order to configure views (typically send data to it)

![diagram](https://www.lucinda-framework.com/public/images/svg/stderr-mvc-api.svg)

Just as an MVC API for STDOUT handles user requests into responses, so does this MVC API for STDERR handle errors or uncaught exceptions that happen during previous handling process. Unlike anything else on the market, however, it does so in a manner that is both efficient and modular, without being bundled to any framework:

- first, MVC API for STDERR (this API) registers itself as sole handler of errors and uncaught exceptions encapsulated by [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances, then passively waits for latter to be triggered
- then, MVC API for STDOUT (your framework of choice) starts handling user requests into responses. Has any error or uncaught exception occurred in the process?
    - *yes*: MVC API for STDERR gets automatically awaken then starts handling respective [\Throwable](https://www.php.net/manual/en/class.throwable.php) into response
    - *no*: response is returned back to caller

Furthermore, this whole process is done in a manner that is infinitely extendable through a combination of:

- **[configuration](#configuration)**: setting up an XML file where this API is configured
- **[initialization](#initialization)**: instancing [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php) to register itself as sole [\Throwable](https://www.php.net/manual/en/class.throwable.php) handler
- **[handling](#handling)**: once any error or uncaught exception has occurred in STDOUT phase, method *handle* of above is called automatically kick-starting [\Throwable](https://www.php.net/manual/en/class.throwable.php)-response process

API is fully PSR-4 compliant, only requiring PHP7.1+ interpreter and SimpleXML extension. To quickly see how it works, check:

- **[installation](#installation)**: describes how to install API on your computer, in light of steps above
- **[reference guide](#reference-guide)**: describes all API classes, methods and fields relevant to developers
- **[unit tests](#unit-tests)**: API has 100% Unit Test coverage, using [UnitTest API](https://github.com/aherne/unit-testing) instead of PHPUnit for greater flexibility
- **[example](https://github.com/aherne/errors-api/blob/v2.0.0/tests/FrontController.php)**: shows a deep example of API functionality based on [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php) unit test

## Configuration

To configure this API you must have a XML with following tags inside:

- **[application](#application)**: (mandatory) configures handling on general application basis
- **[reporters](#reporters)**: (optional) configures how your application will report handled error/exception
- **[resolvers](#resolvers)**: (mandatory) configures formats in which your application is able to resolve responses to a handled error/exception
- **[exceptions](#exceptions)**: (mandatory) configures error/exception based routing to controllers/views

### Application

Maximal syntax of this tag is:

```xml
<application default_format="..." version="...">
	<paths controllers="..." resolvers="..." views="..." reporters="..."/>
	<display_errors>
		<{ENVIRONMENT}>1/0</{ENVIRONMENT}>
		...
	</display_errors>
</application>
```

Where:

- **application**: (mandatory) holds settings to configure your application for error handling
    - *default_format*: (mandatory) defines default display format (extension) for your application. Must match a *format* attribute @ **[resolvers](#resolvers)**! Example: "html"
    - *version*: (optional) defines your application version, to be used in versioning static resources. Example: "1.0.0"
    - **paths**: (optional) holds where core components used by API are located based on attributes:
        - *controllers*:  (optional)holds folder in which user-defined controllers will be located. Each controller must be a [Lucinda\STDERR\Controller](#abstract-class-controller) instance!  
        - *reporters*: (optional) holds folder in which user-defined reporters will be located. Each reporter must be a [Lucinda\STDERR\Reporter](#abstract-class-reporter) instance!
        - *resolvers*: (mandatory) holds folder in which user-defined view resolvers will be located. Each resolver must be a [Lucinda\STDERR\ViewResolver](#abstract-class-viewresolver) instance!
        - *views*: (optional) holds folder in which user-defined views will be located (if HTML).
    - **display_errors**: (optional) holds whether or not handled error/exception details will be exposed back to callers based on:
        - **{ENVIRONMENT}**: name of development environment (to be replaced with "local", "dev", "live", etc). Values of this tag:
            - 1: indicates error/exception details will be displayed to caller
            - 0: indicates error/exception details won't be displayed to caller

If no **display_errors** is defined or no **{ENVIRONMENT}** subtag is found matching current development environment, value 0 is assumed ([\Throwable](https://www.php.net/manual/en/class.throwable.php) details won't be exposed)!

Tag example:

```xml
<application default_format="html" version="1.0.1">
	<paths controllers="application/controllers" resolvers="application/resolvers" views="application/views" reporters="application/reporters"/>
	<display_errors>
		<local>1</local>
		<live>0</live>
	</display_errors>
</application>
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
            - *class*: (mandatory) name of user-defined class that will report [\Throwable](https://www.php.net/manual/en/class.throwable.php) (including namespace or subfolder), found in folder defined by *reporters* attribute of **paths** tag @ **[application](#application)**. Must be a [Lucinda\STDERR\Reporter](#abstract-class-reporter) instance!
			- {OPTIONS}: a list of extra attributes necessary to configure respective reporter identified by *class* above            

Tag example:

```xml
<reporters>
	<local>
		<reporter class="FileReporter" path="errors" format="%d %f %l %m"/>
	</local>
	<live>
		<reporter class="SysLogReporter" application="unittest" format="%v %f %l %m"/>
	</live>
</reporters>
```

### Resolvers

Maximal syntax of this tag is:

```xml
<resolvers>
	<resolver format="..." content_type="..." class="..." {OPTIONS}/>
	...
</resolvers>
```
Where:

- **resolvers**: (mandatory) holds settings to resolve views based on response format (extension). Holds a child for each format supported:
    - **resolver**: (mandatory) configures a format-specific view resolver based on attributes:
        - *format*: (mandatory) defines display format (extension) handled by view resolver. Example: "html"
        - *content_type*: (mandatory) defines content type matching display format above. Example: "text/html"
        - *class*: (mandatory) name of user-defined class that will resolve views (including namespace or subfolder), found in folder defined by *resolvers* attribute of **paths** tag @ **[application](#application)**. Must be a [Lucinda\STDERR\ViewResolver](#abstract-class-viewresolver) instance!
        - {OPTIONS}: a list of extra attributes necessary to configure respective resolver identified by *class* above            

Tag example:

```xml
<resolvers>
    <resolver format="html" content_type="text/html" class="ViewLanguageResolver" charset="UTF-8"/>
    <resolver format="json" content_type="application/json" class="JsonResolver" charset="UTF-8"/>
</resolvers>
```

### Exceptions

Maximal syntax of this tag is:

```xml
<exceptions controller="..." view="..." error_type="..." http_status="...">
    <exception class="..." controller="..." view="..." error_type="..." http_status="..."/>
    ...
</exceptions>
```

Where:

- **exceptions**: (mandatory) holds global routing rules for handled exceptions/errors based on attributes or error/exeception specific rules based on subtags:
    - *controller*: (optional) holds user-defined default controller (including namespace or subfolder) that will mitigate requests and responses based on models, found in folder defined by *controllers* attribute of **paths** tag @ **[application](#application)**. Must be a [Lucinda\STDERR\Controller](#abstract-class-controller) instance!
    - *view*: (optional) defines default template file that holds the recipe of response. Example: "error"
    - *error_type*: (mandatory) defines default exception/error originator. Must match one of const values in [Lucinda\STDERR\ErrorType](https://github.com/aherne/errors-api/blob/v2.0.0/src/ErrorType.php) enum! Example: "LOGICAL"
    - *http_status*: (mandatory) defines default response HTTP status. Example: "500"
    - **exception**: (optional) holds routing rules specific to an exception/error based on attributes:
        - *class*: (mandatory) error/exception class name. Must be a [\Throwable](https://www.php.net/manual/en/class.throwable.php) instance!
        - *controller*: (optional) see above!
        - *view*: (optional) see above!
        - *error_type*: (mandatory) see above!
        - *http_status*: (mandatory) see above!

Tag example:

```xml
<exceptions error_type="LOGICAL" controller="ErrorsController">
    <exception class="Lucinda\MVC\STDOUT\PathNotFoundException" http_status="404" error_type="CLIENT" view="404"/>
    <exception class="Lucinda\MVC\STDOUT\MethodNotAllowedException" http_status="405" error_type="CLIENT" view="405"/>
</exceptions>
```

## Initialization

Now that developers have finished setting up XML that configures the API, they are finally able to initialize it by instantiating [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php).

As a handler of [\Throwable](https://www.php.net/manual/en/class.throwable.php) instances, above needs to implement [Lucinda\STDERR\ErrorHandler](https://github.com/aherne/errors-api/blob/v2.0.0/src/ErrorHandler.php). Apart of method *run* required by interface above, [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php) comes with following public methods, all related to initialization process:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| __construct | string $documentDescriptor, string $developmentEnvironment, string $includePath, [Lucinda\STDERR\ErrorHandler](https://github.com/aherne/errors-api/blob/v2.0.0/src/ErrorHandler.php) $emergencyHandler | void | API registers itself as sole [\Throwable](https://www.php.net/manual/en/class.throwable.php) handler then starts the wait process |
| setDisplayFormat | string $displayFormat | void | Sets future response to use a different display format from that defined by *default_format* @ [application](#application) tag |

Where:

- *$documentDescriptor*: relative location of XML [configuration](#configuration) file. Example: "configuration.xml"
- *$developmentEnvironment*: name of development environment (to be replaced with "local", "dev", "live", etc) to be used in deciding how to report or whether or not to expose handled [\Throwable](https://www.php.net/manual/en/class.throwable.php)
- *$includePath*: absolute location of your project root (necessary because sometimes include paths are lost when errors are thrown). Example: __DIR__
- *$emergencyHandler*: a [ErrorHandler](https://github.com/aherne/errors-api/blob/v2.0.0/src/ErrorHandler.php) instance to be used in handling errors occurring during execution of [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php)'s *handle* method
- *$displayFormat*: value of display format, matching to a *format* attribute of a *resolver* @ [resolvers](#resolvers) XML tag

Interface [Lucinda\STDERR\ErrorHandler](https://github.com/aherne/errors-api/blob/v2.0.0/src/ErrorHandler.php) encapsulates handling a [\Throwable](https://www.php.net/manual/en/class.throwable.php) with following prototype method:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| handle | \Throwable $exception | void | Handles [\Throwable](https://www.php.net/manual/en/class.throwable.php) into a response back to caller |

Very important to notice that once handlers are registered, API employs Aspect Oriented Programming concepts to listen *asynchronously* for error events then triggering handler automatically. So once API is initialized, you can immediately start your preferred framework that handles user requests to responses!

## Handling

Once a [\Throwable](https://www.php.net/manual/en/class.throwable.php) event has occurred inside STDOUT request-response phase, *handle* method of [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php) is called. This will:

- registers handler presented as constructor argument to capture any error that might occur while handling
- constructs a [Lucinda\STDERR\Application](#class-application) object based on XML where API is configured and development environment
- constructs a [Lucinda\STDERR\Application\Route](#class-application-route) object by matching routes in [exceptions](#exceptions) tag @ XML to [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled
- constructs a [Lucinda\STDERR\Request](#class-request) object based on handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) and [Lucinda\STDERR\Application\Route](#class-application-route) above
- constructs a list of [Lucinda\STDERR\Reporter](#abstract-class-reporter) instances based on [reporters](#reporters) tag @ XML matching development environment
- if found, for each [Lucinda\STDERR\Reporter](#abstract-class-reporter) it calls its *run()* method in order to report [\Throwable](https://www.php.net/manual/en/class.throwable.php) to respective medium
- constructs a [Lucinda\STDERR\Application\Format](#class-application-format) object encapsulating final response format based on *default_format* attribute [application](#application) tag @ XML or the one manually set via *setDisplayFormat* method
- constructs a [Lucinda\STDERR\Response](#class-response) object based on all objects detected above
- locates a [Lucinda\STDERR\Controller](#abstract-class-controller) matching [\Throwable](https://www.php.net/manual/en/class.throwable.php) based on [exceptions](#exceptions) tag @ XML and objects detected above
- if found, it executes [Lucinda\STDERR\Controller](#abstract-class-controller)'s *run* method in order to manipulate response
- if [Lucinda\STDERR\Response](#class-response) doesn't have a body yet
    - constructs a [Lucinda\STDERR\ViewResolver](#abstract-class-viewresolver) object based on matching [resolver](#resolvers) tag @ XML and objects detected above. This will, for example, convert templates to a full response body.
    - if not found, program exits with error, otherwise it executes [Lucinda\STDERR\ViewResolver](#abstract-class-viewresolver)'s *run* method in order to convert view to a response body
- calls *commit* method of [Lucinda\STDERR\Response](#class-response) to send back response to caller

All components that are in developers' responsibility ([Lucinda\STDERR\Controller](#abstract-class-controller), [Lucinda\STDERR\ViewResolver](#abstract-class-viewresolver), [Lucinda\STDERR\Reporter](#abstract-class-reporter)) implement [Lucinda\STDERR\Runnable](https://github.com/aherne/errors-api/blob/v2.0.0/src/Runnable.php) interface, which only comes with a single method:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| run | void | void | Executes component's logic |

## Installation

First choose a folder, then write this command there using console:

```console
composer require lucinda/errors-api
```

Then create a *configuration.xml* file holding configuration settings (see [configuration](#configuration) above) and a *index.php* file (see [initialization](#initialization) above) in project root with following code:

```php
require(__DIR__."/vendor/autoload.php");

// detects current development environment from ENVIRONMENT environment variable (eg: set in .htaccess via "SetEnv ENVIRONMENT local");
define("ENVIRONMENT", getenv("ENVIRONMENT"));

// starts API as error handler to listen for errors/exceptions thrown in STDOUT phase below
new Lucinda\STDERR\FrontController("configuration.xml", getenv("ENVIRONMENT"), __DIR__, new EmergencyHandler());

// runs preferred STDOUT framework (eg: STDOUT MVC API) to handle requests into responses
```

Example of emergency handler:

```php
class EmergencyHandler implements \Lucinda\STDERR\ErrorHandler
{
    public function handle($exception): void
    {
        var_dump($exception);
        die();
    }
}
```

## Reference Guide

These classes are fully implemented by API:

- [Lucinda\STDERR\Application](#class-application): reads [configuration](#configuration) XML file and encapsulates information inside
    - [Lucinda\STDERR\Application\Route](#class-application-route): encapsulates [exception](#exceptions) XML tag matching [\Throwable](https://www.php.net/manual/en/class.throwable.php) handled
    - [Lucinda\STDERR\Application\Format](#class-application-format): encapsulates [resolver](#resolvers) XML tag matching *default_format* attribute @ [application](#application) XML tag or one set by *setFormat* method @ [Lucinda\STDERR\FrontController](https://github.com/aherne/errors-api/blob/v2.0.0/src/FrontController.php)
- [Lucinda\STDERR\Request](#class-request): encapsulates handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) and [Lucinda\STDERR\Application\Route](#class-application-route)
- [Lucinda\STDERR\Response](#class-response): encapsulates response to send back to caller
    - [Lucinda\STDERR\Response\Status](#class-response-status): encapsulates response HTTP status
    - [Lucinda\STDERR\Response\View](#class-response-view): encapsulates view template and data that will be bound into a response body

These abstract classes require to be extended by developers in order to gain an ability:

- [Lucinda\STDERR\Reporter](#abstract-class-reporter): encapsulates [\Throwable](https://www.php.net/manual/en/class.throwable.php) reporting
- [Lucinda\STDERR\Controller](#abstract-class-controller): encapsulates binding [Lucinda\STDERR\Request](#class-request) to [Lucinda\STDERR\Response](#class-response) based on [\Throwable](https://www.php.net/manual/en/class.throwable.php)
- [Lucinda\STDERR\ViewResolver](#abstract-class-viewresolver): encapsulates conversion of [Lucinda\STDERR\Response\View](#class-response-view) into a [Lucinda\STDERR\Response](#class-response) body

### Class Application

Class [Lucinda\STDERR\Application](https://github.com/aherne/errors-api/blob/v2.0.0/src/Application.php) encapsulates information detected from XML and defines following public methods relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getVersion | void | string | Gets application version based on *version* attribute @ [application](#application) XML tag |
| getTag | string $name | [\SimpleXMLElement](https://www.php.net/manual/en/class.simplexmlelement.php) | Gets a pointer to a custom tag in XML root |

### Class Application Route

Class [Lucinda\STDERR\Application\Route](https://github.com/aherne/errors-api/blob/v2.0.0/src/Application/Route.php) encapsulates information detected from matching [exception](#exceptions) XML tag and defines following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getController | void | string | Gets controller class name/path/namespace based on *controller* attribute of matching *exception* tag, child of [exceptions](#exceptions) XML tag |
| getErrorType | void | string | Gets error type based on *error_type* attribute of matching *exception* tag, child of [exceptions](#exceptions) XML tag |
| getHttpStatus | void | string | Gets response http status code based on *http_status* attribute  of matching *exception* tag, child of [exceptions](#exceptions) XML tag |
| getView | void | string | Gets view path based on *view* attribute  of matching *exception* tag, child of [exceptions](#exceptions) XML tag |

### Class Application Format

Class [Lucinda\STDERR\Application\Format](https://github.com/aherne/errors-api/blob/v2.0.0/src/Application/Format.php) encapsulates information detected from matching [resolver](#resolvers) XML tag and defines following public methods:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getCharacterEncoding | void | string | Gets character encoding based on *charset* attribute of matching *resolver* tag, child of [resolvers](#resolvers) XML tag |
| getContentType | void | string | Gets error type based on *error_type* attribute of matching *resolver* tag, child of [resolvers](#resolvers) XML tag |
| getName | void | string | Gets response http status code based on *http_status* attribute  of matching *resolver* tag, child of [resolvers](#resolvers) XML tag |
| getViewResolver | void | string | Gets view path based on *view* attribute  of matching *resolver* tag, child of [resolvers](#resolvers) XML tag |

### Class Request

Class [Lucinda\STDERR\Request](https://github.com/aherne/errors-api/blob/v2.0.0/src/Request.php) encapsulates handled [\Throwable](https://www.php.net/manual/en/class.throwable.php) and matching [Lucinda\STDERR\Application\Route](#class-application-route). It defines following public methods relevant to developers:


| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getException | void | [\Throwable](https://www.php.net/manual/en/class.throwable.php) | Gets throwable that is being handled |
| getRoute | void | [Lucinda\STDERR\Application\Route](#class-application-route) | Gets route information detected from XML based on throwable |

### Class Response

Class [Lucinda\STDERR\Response](https://github.com/aherne/errors-api/blob/v2.0.0/src/Response.php) encapsulates operations to be used in generating response. It defines following public methods relevant to developers:


| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getBody | void | string | Gets response body saved by method below. |
| setBody | string $body | void | Sets response body. |
| getStatus | void | [Lucinda\STDERR\Response\Status](#class-response-status) | Gets response http status based on code saved by method below. |
| setStatus | int $code | void | Sets response http status code. |
| headers | void | array | Gets all response http headers saved by methods below. |
| headers | string $name | ?string | Gets value of a response http header based on its name. If not found, null is returned! |
| headers | string $name, string $value | void | Sets value of response http header based on its name. |
| redirect | string $location, bool $permanent=true, bool $preventCaching=false | void | Redirects caller to location url using 301 http status if permanent, otherwise 302. |
| view | void | [Lucinda\STDERR\Response\View](#class-response-view) | Gets a pointer to view encapsulating data based on which response body will be compiled |

When API completes handling, it will call *commit* method to send headers and response body back to caller!

### Class Response Status

Class [Lucinda\STDERR\Response\Status](https://github.com/aherne/errors-api/blob/v2.0.0/src/Response/Status.php) encapsulates response HTTP status and defines following public methods relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getDescription | void | string | Gets response http status code description (eg: "not modified"). |
| getId | void | int | Sets response http status code. |

### Class Response View

Class [Lucinda\STDERR\Response\View](https://github.com/aherne/errors-api/blob/v2.0.0/src/Response/View.php) implements [\ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php) and encapsulates template and data that will later be bound to a response body. It defines following public methods relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| getFile | void | string | Gets location of template file saved by method below. |
| setFile | string | int | Sets location of template file to be used in generating response body. |
| getData | void | array | Gets all data that will be bound to template when response body will be generated. |

By virtue of implementing [\ArrayAccess](https://www.php.net/manual/en/class.arrayaccess.php), developers are able to work with this object as if it were an array:

```php
$this->response->view()["hello"] = "world";

```

### Abstract Class Reporter

Abstract class [Lucinda\STDERR\Reporter](https://github.com/aherne/errors-api/blob/v2.0.0/src/Reporter.php) implements [Lucinda\STDERR\Runnable](https://github.com/aherne/errors-api/blob/v2.0.0/src/Runnable.php)) and encapsulates a single [\Throwable](https://www.php.net/manual/en/class.throwable.php) reporter. It defines following public method relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| run | void | void | Inherited prototype to be implemented by developers to report [\Throwable](https://www.php.net/manual/en/class.throwable.php) based on information saved by constructor |

Developers need to implement *run* method for each reporter, where they are able to access following protected fields injected by API via constructor:

| Field | Type | Description |
| --- | --- | --- |
| $request | [Lucinda\STDERR\Request](#class-request) | Gets request information encapsulating handled throwable and matching route information detected from XML. |
| $xml | [\SimpleXMLElement](https://www.php.net/manual/en/class.simplexmlelement.php) | Gets a pointer to matching [reporter](#reporters) XML tag, to read attributes from. |

To better understand how *reporters* attribute @ [application](#application) and *class* attribute @ [reporter](#reporters) matching *development environment*  play together in locating [Lucinda\STDERR\Reporter](#abstract-class-reporter) that will report [\Throwable](https://www.php.net/manual/en/class.throwable.php) later on, let's take a look at table below:

| reporters | class | File Loaded | Class Instanced |
| --- | --- | --- | --- |
| application/reporters | FileLogger | application/reporters/FileLogger.php | FileLogger |
| application/reporters | foo/FileLogger | application/reporters/foo/FileLogger.php | FileLogger |
| application/reporters | \Foo\FileLogger | application/reporters/FileLogger.php | \Foo\FileLogger |
| application/reporters | foo/\Bar\FileLogger | application/reporters/foo/FileLogger.php | \Bar\FileLogger |

Example of reporter:

```php
class FileReporter extends \Lucinda\STDERR\Reporter
{
    public function run(): void
    {
        $exception = $this->request->getException();
        $replacements = [
            "%d"=>date("Y-m-d H:i:s"),
            "%e"=>get_class($exception),
            "%f"=>$exception->getFile(),
            "%l"=>$exception->getLine(),
            "%m"=>$exception->getMessage()
        ];
        $format = (string) $this->xml["format"];
        $message = str_replace(array_keys($replacements), array_values($replacements), $format);
        error_log($message."\n", 3, dirname(__DIR__, 3)."/".$this->xml["path"].".log");
    }
}
```

Defined in XML as:

```xml
<reporter class="FileReporter" path="errors" format="%d %m"/>
```

### Abstract Class Controller

Abstract class [Lucinda\STDERR\Controller](https://github.com/aherne/errors-api/blob/v2.0.0/src/Controller.php) implements [Lucinda\STDERR\Runnable](https://github.com/aherne/errors-api/blob/v2.0.0/src/Runnable.php)) to set up response (views in particular) based on information detected beforehand. It defines following public method relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| run | void | void | Inherited prototype to be implemented by developers to set up response based on information saved by constructor |

Developers need to implement *run* method for each controller, where they are able to access following protected fields injected by API via constructor:

| Field | Type | Description |
| --- | --- | --- |
| $application | [Lucinda\STDERR\Application](#class-application) | Gets application information detected from XML. |
| $request | [Lucinda\STDERR\Request](#class-request) | Gets request information encapsulating handled throwable and matching route information detected from XML. |
| $response | [Lucinda\STDERR\Response](#class-response) | Gets access to object based on which response can be manipulated. |

By far the most common operation a controller will do is sending data to view via *view* method of [Lucinda\STDERR\Response](#class-response). Example:

```php
$this->response->view()["hello"] = "world";
```

To better understand how *controllers* attribute @ [application](#application) and *class* attribute @ [exception](#exceptions) matching [\Throwable](https://www.php.net/manual/en/class.throwable.php) play together in locating [Lucinda\STDERR\Controller](#abstract-class-controller) that will mitigate requests and responses later on, let's take a look at table below:

| controllers | class | File Loaded | Class Instanced |
| --- | --- | --- | --- |
| application/controllers | ErrorController | application/controllers/ErrorController.php | ErrorController |
| application/controllers | foo/ErrorController | application/controllers/foo/ErrorController.php | ErrorController |
| application/controllers | \Foo\ErrorController | application/controllers/ErrorController.php | \Foo\ErrorController |
| application/controllers | foo/\Bar\ErrorController | application/controllers/foo/ErrorController.php | \Bar\ErrorController |

Example of controller for *PathNotFoundException*:

```php
class PathNotFoundController extends \Lucinda\STDERR\Controller
{
    public function run(): void
    {
        $this->response->view()["page"] = $_SERVER["REQUEST_URI"];
    }
}
```

Defined in XML as:

```xml
<exception class="PathNotFoundException" controller="PathNotFoundController" http_status="404" error_type="CLIENT" view="404"/>
```

### Abstract Class ViewResolver

Abstract class [Lucinda\STDERR\ViewResolver](https://github.com/aherne/errors-api/blob/v2.0.0/src/ViewResolver.php) implements [Lucinda\STDERR\Runnable](https://github.com/aherne/errors-api/blob/v2.0.0/src/Runnable.php)) and encapsulates conversion of [Lucinda\STDERR\Response\View](#class-response-view) to response body for a single [Lucinda\STDERR\Application\Format](#class-application-format). It defines following public method relevant to developers:

| Method | Arguments | Returns | Description |
| --- | --- | --- | --- |
| run | void | void | Inherited prototype to be implemented by developers in order to convert view to response body based on information saved by constructor |

Developers need to implement *run* method for each controller, where they are able to access following protected fields injected by API via constructor:

| Field | Type | Description |
| --- | --- | --- |
| $application | [Lucinda\STDERR\Application](#class-application) | Gets application information detected from XML. |
| $response | [Lucinda\STDERR\Response](#class-response) | Gets access to object based on which response can be manipulated. |

To better understand how *resolvers* attribute @ [application](#application) and *class* attribute @ [resolver](#resolvers) matching *response format* play together in locating class that will resolve views later on, let's take a look at table below:

| resolvers | class | File Loaded | Class Instanced |
| --- | --- | --- | --- |
| application/resolvers | HtmlResolver | application/resolvers/HtmlResolver.php | HtmlResolver |
| application/resolvers | foo/HtmlResolver | application/resolvers/foo/HtmlResolver.php | HtmlResolver |
| application/resolvers | \Foo\HtmlResolver | application/resolvers/HtmlResolver.php | \Foo\HtmlResolver |
| application/resolvers | foo/\Bar\HtmlResolver | application/resolvers/foo/HtmlResolver.php | \Bar\HtmlResolver |

Example of a resolver for *html* format:

```php
class HtmlRenderer extends \Lucinda\STDERR\ViewResolver
{
    public function run(): void
    {
        $view = $this->response->view();
        if ($view->getFile()) {
            if (!file_exists($view->getFile().".html")) {
                throw new Exception("View file not found");
            }
            ob_start();
            $_VIEW = $view->getData();
            require($view->getFile().".html");
            $output = ob_get_contents();
            ob_end_clean();
            $this->response->setBody($output);
        }
    }
}
```

Defined in XML as:

```xml
<resolver format="html" content_type="text/html" class="HtmlRenderer" charset="UTF-8"/>
```

## Unit Tests

For tests and examples, check following files/folders in API sources:

- [test.php](https://github.com/aherne/errors-api/blob/v2.0.0/test.php): runs unit tests in console
- [unit-tests.xml](https://github.com/aherne/errors-api/blob/v2.0.0/unit-tests.xml): sets up unit tests and mocks "loggers" tag
- [tests](https://github.com/aherne/errors-api/blob/v2.0.0/tests): unit tests for classes from [src](https://github.com/aherne/errors-api/blob/v2.0.0/src) folder
